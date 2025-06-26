<?php
// controllers/NecesidadController.php
class NecesidadController {
    private $db;
    private $necesidad;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->necesidad = new Necesidad($this->db);
    }

    public function getAll() {
        $filtros = [];
        
        if(isset($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }
        if(isset($_GET['prioridad'])) {
            $filtros['prioridad'] = $_GET['prioridad'];
        }
        if(isset($_GET['categoria'])) {
            $filtros['categoria'] = $_GET['categoria'];
        }

        $necesidades = $this->necesidad->getAll($filtros);
        Response::success($necesidades);
    }

    public function getById($id) {
        $necesidad = $this->necesidad->getById($id);
        
        if($necesidad) {
            Response::success($necesidad);
        } else {
            Response::error('Necesidad no encontrada', 404);
        }
    }

    public function create() {
        Auth::requireAuth();
        $user = Auth::getAuthUser();
        $data = json_decode(file_get_contents("php://input"), true);
        
        // Verificar que el usuario es una organización
        $stmt = $this->db->prepare("SELECT id_organizacion FROM organizacion WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        $org = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$org) {
            Response::error('Solo las organizaciones pueden crear necesidades', 403);
        }

        $necesidadData = [
            'id_organizacion' => $org['id_organizacion'],
            'id_categoria' => $data['id_categoria'],
            'nombre' => $data['nombre'],
            'prioridad' => $data['prioridad'],
            'cantidad_solicitada' => $data['cantidad_solicitada'],
            'unidad_medida' => $data['unidad_medida'] ?? null,
            'descripcion_corta' => $data['descripcion_corta'],
            'descripcion_larga' => $data['descripcion_larga']
        ];

        if($this->necesidad->create($necesidadData)) {
            Response::success(null, 'Necesidad creada exitosamente');
        } else {
            Response::error('Error al crear necesidad');
        }
    }
}

?>