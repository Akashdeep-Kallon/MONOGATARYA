<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/model/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/UploadController.php';

class UserController
{
    private $connection;

    public function __construct()
    {
        $this->connection = (new Database())->getConnection();
    }

    public function register($status)
    {
        $location = AUTH_URL . "/register-" . ($status ? 'promoter' : 'reader') . ".php";
        $errors = [];
        // Validar campos vacíos
        if (empty($_POST['name']) || empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['password_confirm'])) {
            $this->message("Por favor, completa todos los campos para poder registrarse.", $location);
        }
        // Recoger datos
        $name = $_POST['name'];
        $surname = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $password_confirm = $_POST['password_confirm'];
        // VALIDACIONES
        if (strlen($name) < 2) {
            $errors[] = "Introduce un nombre válido (mínimo 2 caracteres).";
        }
        if (strlen($surname) < 2) {
            $errors[] = "Introduce un apellido válido (mínimo 2 caracteres).";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Introduce un correo electrónico válido.";
        }
        if (strlen($password) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if ($password !== $password_confirm) {
            $errors[] = "Las contraseñas no coinciden.";
        }
        if (!empty($errors)) {
            $this->message($errors, $location);
        }

        $this->connection->query("CALL sp_comprove_email('$email', @result)");
        $result = $this->connection->query("SELECT @result AS exist");
        $exist = intval($result->fetch_assoc()["exist"]);

        if ($exist === 1) {
            $this->message("El correo electrónico ya está registrado.", $location);
        }

        if ($exist === 0) {
            $this->connection->query("INSERT INTO Users (email, status, name, surname, password) 
            VALUES ('$email', " . ($status ? 1 : 0) . ", '$name', '$surname', '$password')");
            session_unset();
            $user = new User($email, $status, $name, $surname, $password);
            $user->setSessionUser();
            header('Location: ' . VIEW_URL . '/index.php');
            exit();
        }
        // Si no se registró
        $this->message("Credenciales incorrectas.", $location);
    }

    public function login()
    {
        $location = AUTH_URL . "/login.php";

        if (empty($_POST['email']) || empty($_POST['password'])) {
            $this->message("Por favor, completa todos los campos para poder iniciar sesión.", $location);
        }
        $email = $_POST['email'];
        $password = $_POST['password'];
        if ($user = $this->getUser($email, $password)) {
            session_unset();
            $user->setSessionUser();
            header('Location: ' . VIEW_URL . '/profile.php');
            exit();
        } else {
            $this->message("El correo electrónico o la contraseña es incorrecta.", $location);
        }
        // Si no se logea
        $this->message("Credenciales incorrectas.", $location);
    }

    public function update()
    {
        $location = "/DAM-Transversal/view/profile.php";
        $errors = [];
        if (empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['email']) || empty($_POST['password'])) {
            $this->message("Por favor, completa todos los campos.", $location);
        }
        // Recoger datos
        $name = $_POST['name'];
        $surname = $_POST['surname'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $bio = $_POST['bio'];
        // VALIDACIONES
        if (strlen($name) < 2) {
            $errors[] = "Introduce un nombre válido (mínimo 2 caracteres).";
        }
        if (strlen($surname) < 2) {
            $errors[] = "Introduce un apellido válido (mínimo 2 caracteres).";
        }
        if (strlen($password) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if (!empty($errors)) {
            $this->message($errors, $location);
        }

        if ($user = $this->getUser($email, $password)) {

            $mensages = [];
            $user->updateUser($email, $name, $surname, $password, $bio);

            if ($user->isPromoter() && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $avatarResult = (new UploadController())->uploadAvatar($user, $_FILES['avatar']);
                if (is_array($avatarResult)) {
                    $mensages = array_merge($mensages, $avatarResult);
                } else {
                    $mensages[] = $avatarResult;
                }
            }
            session_unset();
            $user->setSessionUser();
            $mensages[] = "Los datos se han actualizado correctamente.";
            $this->message($mensages, $location);

        }
        // Si no se logea
        $this->message("Credenciales incorrectas.", $location);
    }

    public function delete()
    {
        $location = "/DAM-Transversal/view/profile.php";
        $errors = [];
        if (empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['email']) || empty($_POST['password'])) {
            $this->message("Por favor, completa todos los campos.", $location);
        }
        // Recoger datos
        $email = $_POST['email'];
        $password = $_POST['password'];
        // VALIDACIONES
        if (strlen($password) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if (!empty($errors)) {
            $this->message($errors, $location);
        }

        if ($user = $this->getUser($email, $password)) {
            $userID = $user->getUserID();
            $this->connection->query("DELETE FROM Users WHERE ID_User = '$userID';");
            (new UploadController)->deleteUserUploads($userID);
            $this->logout();
        } else {
            $this->message("Contraseña incorrecta. Asegurate de poner la contraseña correcta para borrar la cuenta", $location);
        }

        $this->message("Credenciales incorrectas.", $location);
    }

    public function logout()
    {
        session_unset();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
        header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
        header("Location: " . AUTH_URL . "/login.php");
        exit();
    }
    public function getUser($email, $password)
    {
        $userQuery = $this->connection->query("SELECT * FROM Users WHERE email = '$email' AND password = '$password'");
        if ($userRow = $userQuery->fetch_assoc()) {
            return new User(
                $userRow['email'],
                $userRow['status'],
                $userRow['name'],
                $userRow['surname'],
                $userRow['password']
            );
        }
        return false;
    }

    public function message($message, $location)
    {
        if (!isset($_SESSION['login_error']) || !is_array($_SESSION['login_error'])) {
            $_SESSION['login_error'] = [];
        }
        if (is_array($message)) {
            $_SESSION['login_error'] = array_merge($_SESSION['login_error'], $message);
        } else {
            $_SESSION['login_error'][] = $message;
        }
        header("Location: " . $location);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $userController = new UserController();

    if (isset($_POST['register_lector'])) {
        $userController->register(false);
    }
    if (isset($_POST['register_promoter'])) {
        $userController->register(true);
    }
    if (isset($_POST['login'])) {
        $userController->login();
    }
    if (isset($_POST['logout'])) {
        $userController->logout();
    }
    if (isset($_POST['update'])) {
        $userController->update();
    }
    if (isset($_POST['delete'])) {
        $userController->delete();
    }
}
