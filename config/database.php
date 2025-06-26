<?php
class Database {    
    private $host =     getenv('MYSQL_ADDON_HOST');
    private $database = getenv('MYSQL_ADDON_DB');
    private $username = getenv('MYSQL_ADDON_USER');
    private $password = getenv('MYSQL_ADDON_PASSWORD');
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->database,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>