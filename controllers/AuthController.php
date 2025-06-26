<?php
// controllers/AuthController.php
class AuthController {
    private $db;
    private $usuario;

    public function __construct() {
        $database = new Database();
        $this->db = $database->connect();
        $this->usuario = new Usuario($this->db);
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if(!isset($data['email']) || !isset($data['password']) || !isset($data['tipo'])) {
            Response::error('Datos incompletos: email, password y tipo son requeridos');
        }

        // Validar email único
        $stmt = $this->db->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
        $stmt->execute([$data['email']]);
        if($stmt->fetch()) {
            Response::error('El email ya está registrado');
        }

        $this->usuario->email = $data['email'];
        $this->usuario->password = $data['password'];
        $this->usuario->estado = 'activo';

        if($this->usuario->create()) {
            $user_id = $this->usuario->id_usuario;
            
            // Crear perfil según tipo
            if($data['tipo'] == 'donante') {
                $donante = new Donante($this->db);
                $donante->id_usuario = $user_id;
                $donante->nombre_completo = $data['nombre_completo'];
                $donante->fecha_nacimiento = $data['fecha_nacimiento'];
                $donante->dni = $data['dni'];
                $donante->create();
            } elseif($data['tipo'] == 'organizacion') {
                $organizacion = new Organizacion($this->db);
                $orgData = [
                    'id_usuario' => $user_id,
                    'razon_social' => $data['razon_social'],
                    'ruc' => $data['ruc'],
                    'telefono' => $data['telefono'],
                    'direccion' => $data['direccion'],
                    'tipo_organizacion' => $data['tipo_organizacion'],
                    'otro_tipo' => $data['otro_tipo'] ?? null,
                    'descripcion_corta' => $data['descripcion_corta'],
                    'descripcion_larga' => $data['descripcion_larga']
                ];
                $organizacion->create($orgData);
            }

            // Iniciar sesión automáticamente después del registro
            $userData = [
                'id_usuario' => $user_id,
                'email' => $data['email']
            ];
            Auth::login($userData);

            Response::success([
                'user_id' => $user_id,
                'email' => $data['email'],
                'tipo' => $data['tipo']
            ], 'Usuario registrado e iniciada sesión exitosamente');
        } else {
            Response::error('Error al registrar usuario');
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"), true);
        
        if(!isset($data['email']) || !isset($data['password'])) {
            Response::error('Email y contraseña requeridos');
        }

        $user = $this->usuario->login($data['email'], $data['password']);
        
        if($user) {
            Auth::login($user);
            
            // Determinar tipo de usuario
            $tipo_usuario = $this->getTipoUsuario($user['id_usuario']);
            
            Response::success([
                'user_id' => $user['id_usuario'],
                'email' => $user['email'],
                'tipo' => $tipo_usuario
            ], 'Login exitoso');
        } else {
            Response::error('Credenciales inválidas', 401);
        }
    }

    public function logout() {
        Auth::logout();
        Response::success(null, 'Sesión cerrada exitosamente');
    }

    public function checkAuth() {
        if(Auth::isLoggedIn()) {
            $user = Auth::getAuthUser();
            $tipo_usuario = $this->getTipoUsuario($user['user_id']);
            
            Response::success([
                'authenticated' => true,
                'user' => $user,
                'tipo' => $tipo_usuario
            ]);
        } else {
            Response::success(['authenticated' => false]);
        }
    }

    private function getTipoUsuario($user_id) {
        // Verificar si es donante
        $stmt = $this->db->prepare("SELECT id_donante FROM donante WHERE id_usuario = ?");
        $stmt->execute([$user_id]);
        if($stmt->fetch()) return 'donante';
        
        // Verificar si es organización
        $stmt = $this->db->prepare("SELECT id_organizacion FROM organizacion WHERE id_usuario = ?");
        $stmt->execute([$user_id]);
        if($stmt->fetch()) return 'organizacion';
        
        // Verificar si es administrador
        $stmt = $this->db->prepare("SELECT id_admin FROM administrador WHERE id_usuario = ?");
        $stmt->execute([$user_id]);
        if($stmt->fetch()) return 'administrador';
        
        return 'usuario';
    }
}

?>