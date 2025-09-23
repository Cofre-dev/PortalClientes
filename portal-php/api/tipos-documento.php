<?php
require_once '../config/cors.php';
require_once '../config/auth.php';

$auth = new Auth();

// Verificar autenticación
$token = $auth->getAuthHeaders();
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token de acceso requerido']);
    exit;
}

$userData = $auth->validateToken($token);
if (!$userData) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $tipos = DataStore::getTiposDocumento();
        echo json_encode($tipos);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
}
?>