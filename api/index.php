<?php
// API Entry Point

// Configurar manejo de errores para que siempre devuelva JSON
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en output

// Handler de errores personalizado
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor',
        'message' => $errstr,
        'file' => basename($errfile),
        'line' => $errline
    ]);
    exit;
});

// No establecer headers JSON para descarga de archivos o subida de archivos
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$isDownload = strpos($uri, '/documentos/download') !== false;
$isUpload = strpos($uri, '/documentos/upload') !== false;

// Solo establecer Content-Type JSON si no es descarga ni upload
if (!$isDownload && !$isUpload) {
    header('Content-Type: application/json');
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Iniciar sesión solo si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

require_once __DIR__ . '/../backend/config/Router.php';

$router = new Router();

// Obtener la URI y método
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remover el prefijo /api
if (strpos($uri, '/api') === 0) {
    $uri = substr($uri, 4);
}

if (empty($uri) || $uri === '/') {
    echo json_encode(['error' => 'API endpoint not specified']);
    exit;
}

// Rutas de autenticación
$router->post('/auth/login', 'AuthController.php', 'login');
$router->post('/auth/register', 'AuthController.php', 'register');
$router->post('/auth/logout', 'AuthController.php', 'logout');
$router->get('/auth/session-token', 'AuthController.php', 'getSessionToken');
$router->get('/auth/debug-headers', 'AuthController.php', 'debugHeaders');

// Rutas de administración - Clientes
$router->get('/admin/clientes-estadisticas', 'ClientesController.php', 'estadisticas');
$router->post('/admin/clientes/create', 'ClientesController.php', 'crear');
$router->get('/admin/clientes', 'ClientesController.php', 'listar');
$router->get('/admin/clientes/{id}', 'ClientesController.php', 'obtener');
$router->put('/admin/clientes/{id}', 'ClientesController.php', 'actualizar');
$router->delete('/admin/clientes/{id}', 'ClientesController.php', 'eliminar');

// Rutas de administración - Categorías por cliente
$router->get('/admin/clientes/{id}/categorias', 'ClientesController.php', 'obtenerCategorias');
$router->post('/admin/clientes/{id}/categorias', 'ClientesController.php', 'asignarCategoria');
$router->delete('/admin/clientes/{cliente_id}/categorias/{categoria_id}', 'ClientesController.php', 'eliminarCategoria');
$router->get('/admin/clientes/{cliente_id}/documentos/{categoria_id}', 'ClientesController.php', 'obtenerDocumentosPorCategoria');

// Rutas de administración - Usuarios
$router->post('/admin/usuarios/create', 'UsuariosController.php', 'crear');
$router->get('/admin/usuarios', 'UsuariosController.php', 'listar');

// Rutas de administración - Estadísticas generales
$router->get('/admin/stats', 'AdminController.php', 'stats');

// Rutas para Tipos de Documento (Categorías por Cliente)
$router->get('/clientes/{clienteId}/categorias', 'TiposDocumentoController.php', 'listarPorCliente');
$router->post('/clientes/{clienteId}/categorias', 'TiposDocumentoController.php', 'crearParaCliente');
$router->put('/categorias/{id}', 'TiposDocumentoController.php', 'actualizar');
$router->delete('/categorias/{id}', 'TiposDocumentoController.php', 'eliminar');

// Rutas para Documentos
$router->get('/documentos', 'DocumentosController.php', 'listar');
$router->post('/documentos/upload', 'DocumentosController.php', 'upload');
$router->get('/documentos/verify-download', 'DocumentosController.php', 'verifyDownload');
$router->get('/documentos/download', 'DocumentosController.php', 'download');
$router->delete('/documentos/delete/{id}', 'DocumentosController.php', 'eliminar');
$router->put('/documentos/{id}/rename', 'DocumentosController.php', 'renombrar');

// Rutas para Consultas/Ayuda
$router->post('/consultas/crear', 'ConsultasController.php', 'crear');
$router->get('/consultas', 'ConsultasController.php', 'listar');
$router->get('/consultas/pendientes/count', 'ConsultasController.php', 'contarPendientes');
$router->get('/consultas/con-respuestas', 'ConsultasController.php', 'obtenerConsultasConRespuestas');
$router->put('/consultas/{id}/estado', 'ConsultasController.php', 'cambiarEstado');
$router->post('/consultas/{id}/mensajes', 'ConsultasController.php', 'enviarMensaje');
$router->get('/consultas/{id}/mensajes', 'ConsultasController.php', 'obtenerMensajes');

// Resolver ruta API
$result = $router->resolve($uri, $method);
?>