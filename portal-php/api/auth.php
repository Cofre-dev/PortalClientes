<?php
require_once '../config/cors.php';
require_once '../config/auth.php';

$auth = new Auth();
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $_GET['action'] ?? '';

        if ($action === 'login') {
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';

            if (empty($username) || empty($password)) {
                http_response_code(400);
                echo json_encode(['error' => 'Username y password son requeridos']);
                exit;
            }

            $token = $auth->login($username, $password);
            if ($token) {
                echo json_encode([
                    'success' => true,
                    'token' => $token,
                    'message' => 'Login exitoso'
                ]);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Credenciales inválidas']);
            }

        } elseif ($action === 'register') {
            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            $email = $input['email'] ?? '';
            $razon_social = $input['razon_social'] ?? '';
            $rut_empresa = $input['rut_empresa'] ?? '';

            if (empty($username) || empty($password) || empty($razon_social) || empty($rut_empresa)) {
                http_response_code(400);
                echo json_encode(['error' => 'Todos los campos son requeridos']);
                exit;
            }

            if ($auth->register($username, $password, $email, $razon_social, $rut_empresa)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Error al registrar usuario']);
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Acción no encontrada']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
}
?>