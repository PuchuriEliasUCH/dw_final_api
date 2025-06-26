<?php
require_once(__DIR__ . '/../config/database.php');
class UsuarioController {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
    }

    public function getProfile() {
        $user = Auth::getAuthUser();
        
        // Obtener datos del usuario
        $stmt = $this->db->prepare("SELECT id_usuario, email, fecha_registro, estado FROM usuario WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$usuario) {
            Response::error('Usuario no encontrado', 404);
        }

        // Determinar tipo de usuario
        $tipo_usuario = null;
        $datos_perfil = null;

        // Verificar si es donante
        $stmt = $this->db->prepare("SELECT * FROM donante WHERE id_usuario = ?");
        $stmt->execute([$user['user_id']]);
        $donante = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($donante) {
            $tipo_usuario = 'donante';
            $datos_perfil = $donante;
        } else {
            // Verificar si es organización
            $stmt = $this->db->prepare("SELECT * FROM organizacion WHERE id_usuario = ?");
            $stmt->execute([$user['user_id']]);
            $organizacion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if($organizacion) {
                $tipo_usuario = 'organizacion';
                $datos_perfil = $organizacion;
            } else {
                // Verificar si es administrador
                $stmt = $this->db->prepare("SELECT * FROM administrador WHERE id_usuario = ?");
                $stmt->execute([$user['user_id']]);
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if($admin) {
                    $tipo_usuario = 'administrador';
                    $datos_perfil = $admin;
                }
            }
        }

        Response::success([
            'usuario' => $usuario,
            'tipo_usuario' => $tipo_usuario,
            'datos_perfil' => $datos_perfil
        ]);
    }

    public function updateProfile() {
        $user = Auth::getAuthUser();
        $data = json_decode(file_get_contents("php://input"), true);
        
        $this->db->beginTransaction();
        
        try {
            // Actualizar datos básicos del usuario si se proporcionan
            if(isset($data['email'])) {
                $stmt = $this->db->prepare("UPDATE usuario SET email = ? WHERE id_usuario = ?");
                $stmt->execute([$data['email'], $user['user_id']]);
            }

            // Actualizar datos específicos según tipo de usuario
            if(isset($data['tipo_usuario'])) {
                switch($data['tipo_usuario']) {
                    case 'donante':
                        $stmt = $this->db->prepare("UPDATE donante SET nombre_completo = ?, fecha_nacimiento = ? WHERE id_usuario = ?");
                        $stmt->execute([$data['nombre_completo'], $data['fecha_nacimiento'], $user['user_id']]);
                        break;
                        
                    case 'organizacion':
                        $stmt = $this->db->prepare("UPDATE organizacion SET razon_social = ?, telefono = ?, direccion = ?, descripcion_corta = ?, descripcion_larga = ? WHERE id_usuario = ?");
                        $stmt->execute([
                            $data['razon_social'], 
                            $data['telefono'], 
                            $data['direccion'], 
                            $data['descripcion_corta'], 
                            $data['descripcion_larga'], 
                            $user['user_id']
                        ]);
                        break;
                }
            }

            $this->db->commit();
            Response::success(null, 'Perfil actualizado exitosamente');
            
        } catch(Exception $e) {
            $this->db->rollback();
            Response::error('Error al actualizar perfil: ' . $e->getMessage());
        }
    }
}
?>