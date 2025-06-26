<?php
// Configurar sesión
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en HTTPS
session_name('DONATION_API_SESSION');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:3000'); // Especificar origen exacto
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true'); // Importante para sesiones

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Autoload de clases
spl_autoload_register(function ($class) {
    $directories = ['models/', 'controllers/', 'utils/', 'config/'];
    
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Router simple
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remover query string
$path = parse_url($request_uri, PHP_URL_PATH);
$path = str_replace('/api', '', $path); // Remover prefijo /api si existe

// Rutas
try {
    switch (true) {
        // AUTH ROUTES
        case $path === '/auth/register' && $request_method === 'POST':
            $controller = new AuthController();
            $controller->register();
            break;
            
        case $path === '/auth/login' && $request_method === 'POST':
            $controller = new AuthController();
            $controller->login();
            break;

        case $path === '/auth/logout' && $request_method === 'POST':
            $controller = new AuthController();
            $controller->logout();
            break;

        case $path === '/auth/check' && $request_method === 'GET':
            $controller = new AuthController();
            $controller->checkAuth();
            break;

        // NECESIDADES ROUTES
        case $path === '/necesidades/card' && $request_method === 'GET':
            $controller = new NecesidadController();
            $controller->getCardView();
            break;

        case $path === '/necesidades' && $request_method === 'GET':
            $controller = new NecesidadController();
            $controller->getAll();
            break;
            
        case preg_match('/\/necesidades\/(\d+)/', $path, $matches) && $request_method === 'GET':
            $controller = new NecesidadController();
            $controller->getById($matches[1]);
            break;
            
        case $path === '/necesidades' && $request_method === 'POST':
            $controller = new NecesidadController();
            $controller->create();
            break;

        // DONACIONES ROUTES
        case $path === '/donaciones' && $request_method === 'POST':
            $controller = new DonacionController();
            $controller->create();
            break;
            
        case $path === '/mis-donaciones' && $request_method === 'GET':
            $controller = new DonacionController();
            $controller->getMisDonaciones();
            break;
            
        case preg_match('/\/donaciones\/(\d+)\/estado/', $path, $matches) && $request_method === 'PUT':
            $controller = new DonacionController();
            $controller->updateEstado($matches[1]);
            break;

        // CATEGORIAS ROUTES
        case $path === '/categorias' && $request_method === 'GET':
            $controller = new CategoriaController();
            $controller->getAll();
            break;
            
        case $path === '/categorias' && $request_method === 'POST':
            $controller = new CategoriaController();
            $controller->create();
            break;

        // ORGANIZACIONES ROUTES
        case $path === '/organizaciones' && $request_method === 'GET':
            $controller = new OrganizacionController();
            $controller->getAll();
            break;
            
        case $path === '/organizaciones/pendientes' && $request_method === 'GET':
            $controller = new OrganizacionController();
            $controller->getPendientes();
            break;
            
        case preg_match('/\/organizaciones\/(\d+)\/verificar/', $path, $matches) && $request_method === 'PUT':
            $controller = new OrganizacionController();
            $controller->verificar($matches[1]);
            break;

        // ESTADISTICAS ROUTES
        case $path === '/estadisticas/dashboard' && $request_method === 'GET':
            $controller = new EstadisticaController();
            $controller->getDashboard();
            break;
            
        case $path === '/estadisticas/donante' && $request_method === 'GET':
            $controller = new EstadisticaController();
            $controller->getEstadisticasDonante();
            break;

        // USUARIO ROUTES
        case $path === '/usuario/perfil' && $request_method === 'GET':
            $controller = new UsuarioController();
            $controller->getProfile();
            break;
            
        case $path === '/usuario/perfil' && $request_method === 'PUT':
            $controller = new UsuarioController();
            $controller->updateProfile();
            break;
            
        // ORGANIZACION ROUTES ADICIONALES
        case $path === '/organizacion/mis-necesidades' && $request_method === 'GET':
            $controller = new OrganizacionController();
            $controller->getMisNecesidades();
            break;

        // CATEGORIA ROUTES ADICIONALES
        case preg_match('/\/categorias\/(\d+)/', $path, $matches) && $request_method === 'PUT':
            $controller = new CategoriaController();
            $controller->update($matches[1]);
            break;

        default:
            Response::error('Endpoint no encontrado', 404);
    }
} catch (Exception $e) {
    Response::error('Error interno del servidor: ' . $e->getMessage(), 500);
}

?>