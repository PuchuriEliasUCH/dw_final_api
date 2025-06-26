<?php
class Necesidad {
    private $conn;
    private $table = 'necesidad';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table . " 
                 SET id_organizacion = :id_organizacion, id_categoria = :id_categoria,
                     nombre = :nombre, prioridad = :prioridad, cantidad_solicitada = :cantidad_solicitada,
                     unidad_medida = :unidad_medida, descripcion_corta = :descripcion_corta,
                     descripcion_larga = :descripcion_larga";
        
        $stmt = $this->conn->prepare($query);
        
        foreach($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        
        return $stmt->execute();
    }

    public function getAll($filtros = []) {
        $query = "SELECT n.*, o.razon_social, c.nombre as categoria_nombre 
                 FROM " . $this->table . " n
                 JOIN organizacion o ON n.id_organizacion = o.id_organizacion
                 JOIN categoria c ON n.id_categoria = c.id_categoria
                 WHERE 1=1";
        
        $params = [];
        
        if(isset($filtros['estado'])) {
            $query .= " AND n.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }
        
        if(isset($filtros['prioridad'])) {
            $query .= " AND n.prioridad = :prioridad";
            $params[':prioridad'] = $filtros['prioridad'];
        }
        
        if(isset($filtros['categoria'])) {
            $query .= " AND n.id_categoria = :categoria";
            $params[':categoria'] = $filtros['categoria'];
        }
        
        $query .= " ORDER BY n.fecha_publicacion DESC";
        
        $stmt = $this->conn->prepare($query);
        foreach($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT n.*, o.razon_social, c.nombre as categoria_nombre 
                 FROM " . $this->table . " n
                 JOIN organizacion o ON n.id_organizacion = o.id_organizacion
                 JOIN categoria c ON n.id_categoria = c.id_categoria
                 WHERE n.id_necesidad = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateCantidadRecaudada($id, $cantidad) {
        $query = "UPDATE " . $this->table . " 
                 SET cantidad_recaudada = cantidad_recaudada + :cantidad 
                 WHERE id_necesidad = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':cantidad', $cantidad);
        $stmt->bindParam(':id', $id);
        
        return $stmt->execute();
    }
}
?>