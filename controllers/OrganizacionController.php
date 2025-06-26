<?php
require_once(__DIR__ . '/../config/database.php');
class OrganizacionController {
    private $db;
    private $organizacion;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->organizacion = new Organizacion($this->db);
    }

    public function getAll() {
        $organizaciones = $this->organizacion->getAll();
        Response::success($organizaciones);
    }

    public function getPendientes() {
        $user = Auth::getAuthUser();
        
        // Verificar que es administrador
        $stmt = $this->db->prepare("SELECT id_admin FROM administrador WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        
        if(!$stmt->fetch()) {
            Response::error('Solo administradores pueden ver organizaciones pendientes', 403);
        }

        $organizaciones = $this->organizacion->getAll('pendiente');
        Response::success($organizaciones);
    }

    public function verificar($id) {
        $user = Auth::getAuthUser();
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Verificar que es administrador
        $stmt = $this->db->prepare("SELECT id_admin FROM administrador WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        
        if(!$stmt->fetch()) {
            Response::error('Solo administradores pueden verificar organizaciones', 403);
        }

        if($this->organizacion->updateEstado($id, $data['estado'])) {
            Response::success(null, 'Estado de organización actualizado');
        } else {
            Response::error('Error al actualizar estado');
        }
    }

    public function getMisNecesidades() {
        $user = Auth::getAuthUser();
        
        // Obtener organización del usuario
        $stmt = $this->db->prepare("SELECT id_organizacion FROM organizacion WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        $org = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$org) {
            Response::error('Usuario no es una organización', 403);
        }

        $query = "SELECT n.*, c.nombre as categoria_nombre,
                         (SELECT COUNT(*) FROM donacion d WHERE d.id_necesidad = n.id_necesidad) as total_donaciones
                  FROM necesidad n
                  JOIN categoria c ON n.id_categoria = c.id_categoria
                  WHERE n.id_organizacion = ?
                  ORDER BY n.fecha_publicacion DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$org['id_organizacion']]);
        
        $necesidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Response::success($necesidades);
    }
}
?>