<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/DAM-Transversal/core/database.php';
class User
{
    private $connection;
    private $email;
    private $status;
    private $name;
    private $surname;
    private $password;

    public function __construct($email, $status, $name, $surname, $password)
    {
        $this->email = $email;
        $this->status = $status ? 'promoter' : 'reader';
        $this->name = $name;
        $this->surname = $surname;
        $this->password = $password;
        $this->connection = (new Database())->getConnection();
    }

    public function setSessionUser()
    {
        $_SESSION['email'] = $this->email;
        $_SESSION['status'] = $this->status;
        $_SESSION['name'] = $this->name;
        $_SESSION['surname'] = $this->surname;
        $_SESSION['password'] = $this->password;

        $stmt = $this->connection->prepare("SELECT avatar, bio FROM Users WHERE email = :email");
        $stmt->execute([':email' => $this->email]);
        $userRow = $stmt->fetch();

        $_SESSION['avatar'] = $userRow['avatar'];
        $_SESSION['bio'] = $userRow['bio'];
    }

    public function updateUser($email, $name, $surname, $password, $bio)
    {
        $this->email = $email;
        $this->name = $name;
        $this->surname = $surname;
        $this->password = $password;

        $stmt = $this->connection->prepare("CALL sp_update_user(:name, :surname, :email, :password, :bio)");
        $stmt->execute([
            ':name'     => $this->name,
            ':surname'  => $this->surname,
            ':email'    => $this->email,
            ':password' => $this->password,
            ':bio'      => $bio,
        ]);
        $stmt->closeCursor();
    }

    public function updateAvatar($avatar)
    {
        $stmt = $this->connection->prepare(
            "UPDATE Users SET avatar = :avatar WHERE email = :email"
        );
        $stmt->execute([
            ':avatar' => $avatar,
            ':email'  => $this->email,
        ]);
    }

    public function isPromoter()
    {
        return $this->status === 'promoter';
    }

    public function getUserID()
    {
        $stmt = $this->connection->prepare("SELECT ID_User FROM Users WHERE email = :email");
        $stmt->execute([':email' => $this->email]);
        $userRow = $stmt->fetch();
        return $userRow['ID_User'];
    }

    /*     public function getLoggedUserProfile()
        {
            $stmt = $this->connection->prepare("SELECT name, surname, email, status FROM Users WHERE email = ?");
            $stmt->bind_param('s', $_SESSION['email']);
            $stmt->execute();
            $result = $stmt->get_result();

            return $result->fetch_assoc() ?: null;
        }    
             public function getLoggedUserProfileData()
        {
            $data = [
                'name' => '',
                'surname' => '',
                'email' => '',
                'status' => 'invitado',
            ];

            $profile = $this->getLoggedUserProfile();
            if (empty($profile)) {
                return $data;
            }

            return [
                'name' => $profile['name'],
                'surname' => $profile['surname'],
                'email' => $profile['email'],
                'status' => $profile['status'] ? 'promotor' : 'lector',
            ];
        } */
}
