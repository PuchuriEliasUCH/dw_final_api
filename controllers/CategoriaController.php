<?php
require_once(__DIR__ . '/../config/database.php');
class CategoriaController {
    private $db;
    private $categoria;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getAll() {
        $query = "SELECT * FROM categoria WHERE estado = 'activa' ORDER BY nombre";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success($categorias);
    }

    public function create() {
        $user = Auth::getAuthUser();
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Verificar que es administrador
        $stmt = $this->db->prepare("SELECT id_admin FROM administrador WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        
        if(!$stmt->fetch()) {
            Response::error('Solo administradores pueden crear categorías', 403);
        }

        $query = "INSERT INTO categoria (nombre, estado) VALUES (?, 'activa')";
        $stmt = $this->db->prepare($query);
        
        if($stmt->execute([$data['nombre']])) {
            Response::success(['id' => $this->db->lastInsertId()], 'Categoría creada exitosamente');
        } else {
            Response::error('Error al crear categoría');
        }
    }

    public function update($id) {
        $user = Auth::getAuthUser();
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Verificar que es administrador
        $stmt = $this->db->prepare("SELECT id_admin FROM administrador WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        
        if(!$stmt->fetch()) {
            Response::error('Solo administradores pueden modificar categorías', 403);
        }

        $query = "UPDATE categoria SET nombre = ?, estado = ? WHERE id_categoria = ?";
        $stmt = $this->db->prepare($query);
        
        if($stmt->execute([$data['nombre'], $data['estado'], $id])) {
            Response::success(null, 'Categoría actualizada exitosamente');
        } else {
            Response::error('Error al actualizar categoría');
        }
    }
}

?>