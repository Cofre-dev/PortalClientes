<?php
// ARA & Bustamante Consultores - Portal Principal
// Punto de entrada principal del sistema

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

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

// Si la URI está vacía o es solo "/", redirigir a login
if (empty($uri) || $uri === '/') {
    $uri = '/login';
}

// Definir rutas de vistas separadas por nivel de acceso
switch ($uri) {
    // Rutas de cliente
    case '/login':
    case '/client/login':
        $router->renderView('client/login.php', [
            'title' => 'Portal Cliente - ARA & Bustamante',
            'favicon' => '🏢'
        ]);
        break;

    case '/register':
    case '/client/register':
        $router->renderView('client/register.php', [
            'title' => 'Registro Cliente - ARA & Bustamante',
            'favicon' => '🏢'
        ]);
        break;

    case '/dashboard':
    case '/client/dashboard':
        $router->renderView('client/dashboard.php', [
            'title' => 'Dashboard Cliente - ARA & Bustamante',
            'favicon' => '🏢'
        ]);
        break;

    // Rutas de administrador
    case '/admin':
    case '/admin/login':
        $router->renderView('admin/login.php', [
            'title' => 'Admin - ARA & Bustamante',
            'favicon' => '🛡️',
            'bodyClass' => 'admin-body'
        ]);
        break;

    case '/admin/dashboard':
        $router->renderView('admin/dashboard.php', [
            'title' => 'Dashboard Admin - ARA & Bustamante',
            'favicon' => '🛡️',
            'bodyClass' => 'admin-body'
        ]);
        break;

    default:
        // Si no es una ruta de vista conocida, mostrar 404
        http_response_code(404);
        echo '<h1>404 - Página no encontrada</h1>';
        echo '<p><a href="/portal-php/login">Volver al inicio</a></p>';
        break;
}
?>