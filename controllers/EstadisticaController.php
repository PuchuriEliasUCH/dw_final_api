<?php
class EstadisticaController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getDashboard() {
        $user = Auth::getAuthUser();
        
        // Verificar que es administrador
        $stmt = $this->db->prepare("SELECT id_admin FROM administrador WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        
        if(!$stmt->fetch()) {
            Response::error('Solo administradores pueden ver estadísticas', 403);
        }

        $estadisticas = [];

        // Total usuarios
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM usuario WHERE estado = 'activo'");
        $estadisticas['total_usuarios'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total donantes
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM donante");
        $estadisticas['total_donantes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total organizaciones
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM organizacion WHERE estado_verificacion = 'aprobada'");
        $estadisticas['total_organizaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Organizaciones pendientes
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM organizacion WHERE estado_verificacion = 'pendiente'");
        $estadisticas['organizaciones_pendientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total necesidades
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM necesidad WHERE estado = 'activa'");
        $estadisticas['total_necesidades'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total donaciones
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM donacion");
        $estadisticas['total_donaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Donaciones por estado
        $stmt = $this->db->query("SELECT estado, COUNT(*) as total FROM donacion GROUP BY estado");
        $estadisticas['donaciones_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Necesidades por prioridad
        $stmt = $this->db->query("SELECT prioridad, COUNT(*) as total FROM necesidad WHERE estado = 'activa' GROUP BY prioridad");
        $estadisticas['necesidades_por_prioridad'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top donantes
        $stmt = $this->db->query("SELECT d.nombre_completo, d.num_donaciones_realizadas 
                                 FROM donante d 
                                 ORDER BY d.num_donaciones_realizadas DESC 
                                 LIMIT 10");
        $estadisticas['top_donantes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Donaciones recientes
        $stmt = $this->db->query("SELECT don.id_donacion, don.cantidad_donanda, don.fecha_emision, don.estado,
                                         dn.nombre_completo, n.nombre as necesidad_nombre, o.razon_social
                                  FROM donacion don
                                  JOIN donante dn ON don.id_donante = dn.id_donante
                                  JOIN necesidad n ON don.id_necesidad = n.id_necesidad
                                  JOIN organizacion o ON n.id_organizacion = o.id_organizacion
                                  ORDER BY don.fecha_emision DESC
                                  LIMIT 10");
        $estadisticas['donaciones_recientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($estadisticas);
    }

    public function getEstadisticasDonante() {
        $user = Auth::getAuthUser();
        
        // Obtener donante
        $stmt = $this->db->prepare("SELECT id_donante FROM donante WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        $donante = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$donante) {
            Response::error('Usuario no es donante', 403);
        }

        $estadisticas = [];

        // Total donaciones
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM donacion WHERE id_donante = ?");
        $stmt->execute([$donante['id_donante']]);
        $estadisticas['total_donaciones'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Donaciones por estado
        $stmt = $this->db->prepare("SELECT estado, COUNT(*) as total FROM donacion WHERE id_donante = ? GROUP BY estado");
        $stmt->execute([$donante['id_donante']]);
        $estadisticas['donaciones_por_estado'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Historial de donaciones
        $stmt = $this->db->prepare("SELECT d.*, n.nombre as necesidad_nombre, o.razon_social
                                   FROM donacion d
                                   JOIN necesidad n ON d.id_necesidad = n.id_necesidad
                                   JOIN organizacion o ON n.id_organizacion = o.id_organizacion
                                   WHERE d.id_donante = ?
                                   ORDER BY d.fecha_emision DESC");
        $stmt->execute([$donante['id_donante']]);
        $estadisticas['historial_donaciones'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        Response::success($estadisticas);
    }
}
?>