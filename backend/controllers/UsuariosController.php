<?php

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../config/Database.php';

class UsuariosController {
    private $auth;
    private $db;

    public function __construct() {
        $this->auth = new Auth();
        $this->db = Database::getInstance();
    }

    private function verifyAuth() {
        // Obtener headers de forma compatible
        $headers = function_exists('getallheaders') ? getallheaders() : [];

        // Fallback para obtener Authorization header
        $authHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (strpos($authHeader, 'Bearer ') !== 0) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de autorización requerido']);
            return false;
        }

        $token = substr($authHeader, 7);

        $tokenData = $this->auth->validateToken($token);
        if (!$tokenData) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido o expirado']);
            return false;
        }

        // Verificar que sea admin
        if ($tokenData['role'] !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado: se requieren permisos de administrador']);
            return false;
        }

        return true;
    }

    public function crear() {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método POST']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos JSON inválidos']);
            return;
        }

        $result = $this->crearUsuario($input);

        if ($result['success']) {
            http_response_code(201);
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
        }
    }

    private function crearUsuario($datos) {
        try {
            // Validar datos requeridos
            if (empty($datos['username']) || empty($datos['email']) ||
                empty($datos['password']) || empty($datos['role'])) {
                return [
                    'success' => false,
                    'message' => 'Todos los campos obligatorios deben ser completados'
                ];
            }

            // Validar formato email
            if (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
                return [
                    'success' => false,
                    'message' => 'El formato del correo electrónico no es válido'
                ];
            }

            // Validar rol
            if (!in_array($datos['role'], ['admin', 'cliente'])) {
                return [
                    'success' => false,
                    'message' => 'El rol especificado no es válido'
                ];
            }

            // Validar longitud de contraseña
            if (strlen($datos['password']) < 6) {
                return [
                    'success' => false,
                    'message' => 'La contraseña debe tener al menos 6 caracteres'
                ];
            }

            // Validar username (solo letras, números y guiones bajos)
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $datos['username'])) {
                return [
                    'success' => false,
                    'message' => 'El nombre de usuario solo puede contener letras, números y guiones bajos'
                ];
            }

            // Verificar si el username ya existe
            $usernameExistente = $this->db->selectOne('usuarios', 'id', 'username = ?', [$datos['username']]);
            if ($usernameExistente) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un usuario con este nombre de usuario'
                ];
            }

            // Verificar si el email ya existe
            $emailExistente = $this->db->selectOne('usuarios', 'id', 'email = ?', [$datos['email']]);
            if ($emailExistente) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un usuario con este correo electrónico'
                ];
            }

            // Insertar el usuario
            $userData = [
                'username' => trim($datos['username']),
                'password' => password_hash($datos['password'], PASSWORD_DEFAULT),
                'email' => trim(strtolower($datos['email'])),
                'role' => $datos['role'],
                'is_active' => true
            ];

            $userId = $this->db->insert('usuarios', $userData);

            return [
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'user_id' => $userId
            ];

        } catch (Exception $e) {
            error_log("Error creando usuario: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno al crear el usuario'
            ];
        }
    }

    public function listar() {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método GET']);
            return;
        }

        try {
            $usuarios = $this->db->select(
                'usuarios',
                'id, username, email, role, is_active, created_at',
                '',
                [],
                'username ASC'
            );

            echo json_encode([
                'success' => true,
                'data' => $usuarios
            ]);

        } catch (Exception $e) {
            error_log("Error obteniendo usuarios: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener los usuarios'
            ]);
        }
    }
}

?>