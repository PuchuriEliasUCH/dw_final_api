<?php
class Database
{
    private $host;
    private $database;
    private $username;
    private $password;
    private $conn;

    public function __construct()
    {
        $this->host = getenv('MYSQL_ADDON_HOST');
        $this->database = getenv('MYSQL_ADDON_DB');
        $this->username = getenv('MYSQL_ADDON_USER');
        $this->password = getenv('MYSQL_ADDON_PASSWORD');
    }

    public function connect()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}
