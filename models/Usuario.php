<?php
class Usuario {
    private $conn;
    private $table = 'usuario';

    public $id_usuario;
    public $email;
    public $password;
    public $fecha_registro;
    public $estado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET email = :email, password = :password, estado = :estado";
        
        $stmt = $this->conn->prepare($query);
        
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
        $this->estado = htmlspecialchars(strip_tags($this->estado));
        
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':estado', $this->estado);
        
        if($stmt->execute()) {
            $this->id_usuario = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function login($email, $password) {
        $query = "SELECT id_usuario, email, password, estado FROM " . $this->table . " 
                 WHERE email = :email AND estado = 'activo'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password'])) {
                return $row;
            }
        }
        return false;
    }

    public function getById($id) {
        $query = "SELECT id_usuario, email, fecha_registro, estado FROM " . $this->table . " 
                 WHERE id_usuario = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>