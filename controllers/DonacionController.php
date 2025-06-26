<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../models/Donacion.php');
require_once(__DIR__ . '/../models/Necesidad.php');
require_once(__DIR__ . '/../utils/Response.php');
require_once(__DIR__ . '/../utils/Auth.php'); 

class DonacionController {
    private $db;
    private $donacion;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->donacion = new Donacion($this->db);
    }

    public function create() {
        Auth::requireAuth();
        $user = Auth::getAuthUser();
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Verificar que el usuario es un donante
        $stmt = $this->db->prepare("SELECT id_donante FROM donante WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        $donante = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$donante) {
            Response::error('Solo los donantes pueden hacer donaciones', 403);
        }

        // Verificar disponibilidad de la necesidad
        $necesidad = new Necesidad($this->db);
        $necesidadData = $necesidad->getById($data['id_necesidad']);
        
        if(!$necesidadData || $necesidadData['estado'] != 'activa') {
            Response::error('Necesidad no disponible');
        }

        $cantidad_disponible = $necesidadData['cantidad_solicitada'] - $necesidadData['cantidad_recaudada'];
        if($data['cantidad_donanda'] > $cantidad_disponible) {
            Response::error('Cantidad excede lo disponible');
        }

        $donacionData = [
            'id_donante' => $donante['id_donante'],
            'id_necesidad' => $data['id_necesidad'],
            'cantidad_donanda' => $data['cantidad_donanda'],
            'comentario' => $data['comentario'] ?? null
        ];

        $this->db->beginTransaction();
        
        try {
            $donacion_id = $this->donacion->create($donacionData);
            
            // Actualizar cantidad recaudada
            $necesidad->updateCantidadRecaudada($data['id_necesidad'], $data['cantidad_donanda']);
            
            // Crear historial
            $stmt = $this->db->prepare("INSERT INTO historial_donacion (id_donante, id_donacion, accion) VALUES (?, ?, 'reservada')");
            $stmt->execute([$donante['id_donante'], $donacion_id]);
            
            $this->db->commit();
            Response::success(['id_donacion' => $donacion_id], 'Donación registrada exitosamente');
            
        } catch(Exception $e) {
            $this->db->rollback();
            Response::error('Error al procesar donación');
        }
    }

    public function getMisDonaciones() {
        Auth::requireAuth();
        $user = Auth::getAuthUser();
        
        $stmt = $this->db->prepare("SELECT id_donante FROM donante WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        $donante = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$donante) {
            Response::error('Usuario no es donante', 403);
        }

        $donaciones = $this->donacion->getByDonante($donante['id_donante']);
        Response::success($donaciones);
    }

    public function updateEstado($id) {
        Auth::requireAuth();
        $user = Auth::getAuthUser();
        $data = json_decode(file_get_contents("php://input"), true);
        
        if($this->donacion->updateEstado($id, $data['estado'])) {
            // Actualizar historial
            $stmt = $this->db->prepare("INSERT INTO historial_donacion (id_donante, id_donacion, accion) 
                                      SELECT d.id_donante, d.id_donacion, ? 
                                      FROM donacion d WHERE d.id_donacion = ?");
            $stmt->execute([$data['estado'], $id]);
            
            // Si se entregó, actualizar contador del donante
            if($data['estado'] == 'entregada') {
                $stmt = $this->db->prepare("UPDATE donante SET num_donaciones_realizadas = num_donaciones_realizadas + 1 
                                          WHERE id_donante = (SELECT id_donante FROM donacion WHERE id_donacion = ?)");
                $stmt->execute([$id]);
            }
            
            Response::success(null, 'Estado actualizado');
        } else {
            Response::error('Error al actualizar estado');
        }
    }
}
?>