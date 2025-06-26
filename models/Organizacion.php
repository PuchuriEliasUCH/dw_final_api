<?php
// models/Organizacion.php
class Organizacion {
    private $conn;
    private $table = 'organizacion';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                 SET id_usuario = :id_usuario, razon_social = :razon_social, 
                     ruc = :ruc, telefono = :telefono, direccion = :direccion,
                     tipo_organizacion = :tipo_organizacion, otro_tipo = :otro_tipo,
                     descripcion_corta = :descripcion_corta, descripcion_larga = :descripcion_larga";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute();
    }

    public function getAll($estado = null) {
        $query = "SELECT * FROM " . $this->table;
        if($estado) {
            $query .= " WHERE estado_verificacion = :estado";
        }
        $query .= " ORDER BY fecha_verificacion DESC";
        
        $stmt = $this->conn->prepare($query);
        if($estado) {
            $stmt->bindParam(':estado', $estado);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateEstado($id, $estado) {
        $query = "UPDATE " . $this->table . " 
                 SET estado_verificacion = :estado, fecha_verificacion = CURDATE() 
                 WHERE id_organizacion = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>