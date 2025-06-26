<?php
class Donante {
    private $conn;
    private $table = 'donante';

    public $id_donante;
    public $id_usuario;
    public $nombre_completo;
    public $fecha_nacimiento;
    public $dni;
    public $num_donaciones_realizadas;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                 SET id_usuario = :id_usuario, nombre_completo = :nombre_completo, 
                     fecha_nacimiento = :fecha_nacimiento, dni = :dni";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':id_usuario', $this->id_usuario);
        $stmt->bindParam(':nombre_completo', $this->nombre_completo);
        $stmt->bindParam(':fecha_nacimiento', $this->fecha_nacimiento);
        $stmt->bindParam(':dni', $this->dni);
        
        return $stmt->execute();
    }

    public function getByUsuario($id_usuario) {
        $query = "SELECT d.*, u.email FROM " . $this->table . " d
                 JOIN usuario u ON d.id_usuario = u.id_usuario 
                 WHERE d.id_usuario = :id_usuario";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_usuario', $id_usuario);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateDonaciones($id_donante) {
        $query = "UPDATE " . $this->table . " 
                 SET num_donaciones_realizadas = num_donaciones_realizadas + 1 
                 WHERE id_donante = :id_donante";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_donante', $id_donante);
        
        return $stmt->execute();
    }
}
?>