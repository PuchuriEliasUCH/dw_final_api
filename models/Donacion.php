<?php
class Donacion {
    private $conn;
    private $table = 'donacion';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                 SET id_donante = :id_donante, id_necesidad = :id_necesidad,
                     cantidad_donanda = :cantidad_donanda, comentario = :comentario";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    public function getByDonante($id_donante) {
        $query = "SELECT d.*, n.nombre as necesidad_nombre, o.razon_social 
                 FROM " . $this->table . " d
                 JOIN necesidad n ON d.id_necesidad = n.id_necesidad
                 JOIN organizacion o ON n.id_organizacion = o.id_organizacion
                 WHERE d.id_donante = :id_donante
                 ORDER BY d.fecha_emision DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_donante', $id_donante);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEstado($id, $estado) {
        $query = "UPDATE " . $this->table . " 
                 SET estado = :estado";
        
        if($estado == 'entregada') {
            $query .= ", fecha_entreda = NOW()";
        }
        
        $query .= " WHERE id_donacion = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>