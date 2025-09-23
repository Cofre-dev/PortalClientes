<?php
// ARA & Bustamante Consultores - API Endpoints
// Manejo de todas las rutas de API

error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS Headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/app/config/Router.php';

$router = new Router();

// Obtener la URI y método
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remover el prefijo del proyecto si existe
$basePath = '/portal-php';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Remover /api del inicio si existe
if (strpos($uri, '/api') === 0) {
    $uri = substr($uri, 4);
}

// Si la URI está vacía, mostrar error
if (empty($uri) || $uri === '/') {
    http_response_code(404);
    echo json_encode(['error' => 'API endpoint not specified']);
    exit;
}

// Definir rutas de API
$router->post('/auth/login', 'AuthController', 'login');
$router->post('/auth/register', 'AuthController', 'register');
$router->post('/auth/logout', 'AuthController', 'logout');
$router->get('/auth/csrf', 'AuthController', 'getCSRF');

$router->get('/tipos-documento', 'TipoDocumentoController', 'index');
$router->post('/tipos-documento', 'TipoDocumentoController', 'create');

$router->get('/documentos', 'DocumentController', 'index');
$router->post('/documentos/upload', 'DocumentController', 'upload');
$router->delete('/documentos/delete/{id}', 'DocumentController', 'delete');
$router->get('/documentos/download', 'DocumentController', 'download');

// Resolver la ruta
$router->resolve($uri, $method);
?>