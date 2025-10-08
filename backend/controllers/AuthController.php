<?php

require_once __DIR__ . '/../config/Auth.php';

class AuthController {
    private $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    public function login() {
        try {
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método POST']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            error_log("Auth login - Raw input: " . $rawInput);

            $input = json_decode($rawInput, true);
            error_log("Auth login - Decoded input: " . print_r($input, true));

            $username = $input['username'] ?? '';
            $password = $input['password'] ?? '';
            $csrfToken = $input['csrf_token'] ?? null;

            error_log("Auth login - Username: $username, Password length: " . strlen($password));

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username y password son requeridos']);
            return;
        }

        $result = $this -> auth -> login($username, $password, $csrfToken);

        if (isset($result['error'])) {
            http_response_code(401);
            echo json_encode($result);
        } else {
            echo json_encode([
                'success' => true,
                'token' => $result['token'],
                'user' => $result['user'],
                'message' => 'Login exitoso'
            ]);
        }

        } catch (Exception $e) {
            error_log("Auth login exception: " . $e->getMessage());
            error_log("Auth login stack trace: " . $e->getTraceAsString());
            http_response_code(500);
            echo json_encode([
                'error' => 'Error interno del servidor',
                'debug_message' => $e->getMessage(),
                'debug_file' => $e->getFile(),
                'debug_line' => $e->getLine()
            ]);
        }

    }

    public function register() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método POST']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            error_log("Raw input received: " . $rawInput);

            $input = json_decode($rawInput, true);
            error_log("Decoded input: " . print_r($input, true));

            if ($input === null) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                return;
            }

            $csrfToken = $input['csrf_token'] ?? null;

            $required = ['username', 'password', 'email', 'razon_social', 'rut_empresa'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "El campo {$field} es requerido"]);
                    return;
                }
            }

            $result = $this->auth->register($input, $csrfToken);

            if (isset($result['error'])) {
                http_response_code(400);
                echo json_encode($result);
            } else {
                echo json_encode([
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente'
                ]);
            }

        } catch (Exception $e) {
            error_log("AuthController register error: " . $e->getMessage());

            // Si es un error específico de duplicado, mostrarlo
            if (strpos($e->getMessage(), 'ya existe') !== false ||
                strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'ya está registrado') !== false) {
                http_response_code(400);
                echo json_encode(['error' => $e->getMessage()]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error del servidor']);
            }
        }
    }

    public function logout() {
        $result = $this->auth->logout();
        echo json_encode($result);
    }

    public function getCSRF() {
        echo json_encode([
            'csrf_token' => $this->auth->getCSRFToken()
        ]);
    }

    public function getSessionToken() {
        try {
            // Verificar que hay una sesión activa
            if (!$this->auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['error' => 'No hay sesión activa']);
                return;
            }

            require_once __DIR__ . '/../models/User.php';
            require_once __DIR__ . '/../models/Cliente.php';

            $userModel = new User();
            $user = $userModel->findById($_SESSION['user_id']);

            if (!$user || !$user['is_active']) {
                http_response_code(401);
                echo json_encode(['error' => 'Usuario inválido o inactivo']);
                return;
            }

            // Si es cliente, agregar datos del cliente
            if ($user['role'] === 'cliente') {
                $clienteModel = new Cliente();
                $cliente = $clienteModel->findByUserId($user['id']);
                if ($cliente) {
                    $user['cliente_id'] = $cliente['id'];
                    $user['razon_social'] = $cliente['razon_social'];
                    $user['rut_empresa'] = $cliente['rut_empresa'];
                    $user['email'] = $cliente['email'] ?? $user['email'];
                }
            }

            // Generar token
            $token = $this->auth->generateToken($user);

            // Limpiar información sensible
            unset($user['password']);

            echo json_encode([
                'success' => true,
                'token' => $token,
                'user' => $user
            ]);

        } catch (Exception $e) {
            error_log("getSessionToken error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function debugHeaders() {
        $debug = [
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'NO METHOD',
            'uri' => $_SERVER['REQUEST_URI'] ?? 'NO URI',
            'headers' => [],
            'server_vars' => [],
            'session' => []
        ];

        // Capturar headers
        if (function_exists('getallheaders')) {
            $debug['headers'] = getallheaders();
        }

        // Capturar variables $_SERVER relacionadas con auth
        $authVars = [
            'HTTP_AUTHORIZATION',
            'HTTP_X_AUTHORIZATION',
            'HTTP_X_ACCESS_TOKEN',
            'REDIRECT_HTTP_AUTHORIZATION',
            'REDIRECT_HTTP_X_AUTHORIZATION',
            'PHP_AUTH_DIGEST',
            'PHP_AUTH_USER',
            'REMOTE_USER'
        ];

        foreach ($authVars as $var) {
            if (isset($_SERVER[$var])) {
                $debug['server_vars'][$var] = $_SERVER[$var];
            }
        }

        // Capturar info de sesión (sin datos sensibles)
        if (session_status() === PHP_SESSION_ACTIVE) {
            $debug['session'] = [
                'session_id' => session_id(),
                'user_id' => $_SESSION['user_id'] ?? 'NO USER ID',
                'user_role' => $_SESSION['user_role'] ?? 'NO ROLE',
                'login_time' => $_SESSION['login_time'] ?? 'NO LOGIN TIME'
            ];
        }

        // Test del método getAuthHeaders
        $debug['token_result'] = $this->auth->getAuthHeaders();

        echo json_encode($debug, JSON_PRETTY_PRINT);
    }
}