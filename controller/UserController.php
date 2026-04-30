<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/database.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/model/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/controller/UploadController.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/view/includes/message.php';

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
            setError("Por favor, completa todos los campos para poder registrarse.", $location);
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
        if (!empty($password) && strlen($password) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if ($password !== $password_confirm) {
            $errors[] = "Las contraseñas no coinciden.";
        }
        if (!empty($errors)) {
            setError($errors, $location);
        }

        $stmt = $this->connection->prepare("CALL sp_comprove_email(:email, @result)");
        $stmt->execute([':email' => $email]);
        $stmt->closeCursor();

        $row = $this->connection->query("SELECT @result AS exist")->fetch();
        $exist = intval($row['exist']);

        if ($exist === 1) {
            setError("El correo electrónico ya está registrado.", $location);
        }

        if ($exist === 0) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $this->connection->prepare(
                "INSERT INTO Users (email, status, name, surname, password)
                 VALUES (:email, :status, :name, :surname, :password)"
            );
            $stmt->execute([
                ':email'    => $email,
                ':status'   => $status ? 1 : 0,
                ':name'     => $name,
                ':surname'  => $surname,
                ':password' => $password,
            ]);

            session_unset();
            $user = new User($email, $status, $name, $surname, $password);
            $user->setSessionUser();
            setSuccess('Registro completado correctamente.');
            header('Location: ' . VIEW_URL . '/index.php');
            exit();
        }
        // Si no se registró
        setError("Credenciales incorrectas.", $location);
    }

    public function login()
    {
        $location = AUTH_URL . "/login.php";

        if (empty($_POST['email']) || empty($_POST['password'])) {
            setError("Por favor, completa todos los campos para poder iniciar sesión.", $location);
        }
        $email = $_POST['email'];
        $password = $_POST['password'];
        if ($user = $this->getUser($email, $password)) {
            session_unset();
            $user->setSessionUser();
            setSuccess('Sesión iniciada correctamente.');
            header('Location: ' . VIEW_URL . '/profile.php');
            exit();
        } else {
            setError("El correo electrónico o la contraseña es incorrecta.", $location);
        }
        // Si no se logea
        setError("Credenciales incorrectas.", $location);
    }

    public function update()
    {
        $location = "/DAM-Transversal/view/profile.php";
        $errors = [];
        if (empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['email']) || empty($_POST['password'])) {
            setError("Por favor, completa todos los campos.", $location);
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
            setError($errors, $location);
        }

        if ($user = $this->getUser($email, $_SESSION['password'])) {

            $mensages = [];
            $user->updateUser($email, $name, $surname, $password, $bio);

            if ($user->isPromoter() && isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                $avatarResult = (new UploadController())->uploadAvatar($user, $_FILES['avatar']);
                if (is_array($avatarResult)) {
                    session_unset();
                    $user->setSessionUser();
                    setError($avatarResult, $location);
                    return;
                } else {
                    $mensages[] = $avatarResult;
                }
            }
            session_unset();
            $user->setSessionUser();
            $mensages[] = "Los datos se han actualizado correctamente.";
            setSuccess($mensages, $location);
        }
        // Si no se logea
        setError("Credenciales incorrectas.", $location);
    }

    public function delete()
    {
        $location = "/DAM-Transversal/view/profile.php";
        $errors = [];
        if (empty($_POST['name']) || empty($_POST['surname']) || empty($_POST['email']) || empty($_POST['password'])) {
            setError("Por favor, completa todos los campos.", $location);
        }
        // Recoger datos
        $email = $_POST['email'];
        $password = $_POST['password'];
        // VALIDACIONES
        if (strlen($password) < 6) {
            $errors[] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if (!empty($errors)) {
            setError($errors, $location);
        }

        if ($user = $this->getUser($email, $password)) {
            $userID = $user->getUserID();

            $stmt = $this->connection->prepare("DELETE FROM Users WHERE ID_User = :id");
            $stmt->execute([':id' => $userID]);

            (new UploadController)->deleteUserUploads($userID);
            $this->logout();
        } else {
            setError("Contraseña incorrecta. Asegurate de poner la contraseña correcta para borrar la cuenta", $location);
        }

        setError("Credenciales incorrectas.", $location);
    }

    public function logout()
    {
        setSuccess('Sesión cerrada correctamente.');

        unset(
            $_SESSION['email'],
            $_SESSION['status'],
            $_SESSION['name'],
            $_SESSION['surname'],
            $_SESSION['avatar'],
            $_SESSION['bio']
        );
        $_SESSION['status'] = 'guest';

        header("Location: " . AUTH_URL . "/login.php");
        exit();
    }
    public function getUser($email, $password)
    {
        $stmt = $this->connection->prepare(
            "SELECT * FROM Users WHERE email = :email AND password = :password"
        );
        $stmt->execute([
            ':email'    => $email,
            ':password' => $password,
        ]);
        $userRow = $stmt->fetch();
        
        if ($userRow) {
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
