<?php
//ssh -i ssh-key-Monogatarya.key -L 3307:127.0.0.1:3306 ubuntu@130.110.233.182
//si
class Database
{
    private $host;
    private $port;
    private $user;
    private $password;
    private $database;
    public $connection;

    public function __construct()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        if ($host === 'localhost' || $host === '127.0.0.1' || str_contains($host, 'localhost:')) {
            $this->host = "127.0.0.1";
            $this->port = 3307;
        } else {
            $this->host = "localhost";
            $this->port = 3306;
        }

        $this->user = "admin";
        $this->password = "Monogatarya@2025";
        $this->database = "Monogatarya";
    }

    // Conexión a la base de datos con PDO
    public function getConnection()
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
            $this->connection = new PDO($dsn, $this->user, $this->password);

            // Configuración bd PDO
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
        
        return $this->connection;
    }
}
