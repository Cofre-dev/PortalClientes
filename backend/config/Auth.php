<?php
//auth.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/Security.php';

class Auth {
    private $secretKey;
    private $userModel;
    private $clienteModel;

    public function __construct() {
        // Generar clave secreta más segura
        $this->secretKey = hash('sha256', 'ARA_BUSTAMANTE_2024_' . ($_ENV['APP_SECRET'] ?? 'default_secret'));
        $this->userModel = new User();
        $this->clienteModel = new Cliente();

        // Iniciar sesión segura
        Security::startSecureSession();
    }

    public function login($username, $password, $csrfToken = null) {
        // Validar CSRF si se proporciona
        if ($csrfToken && !Security::validateCSRF($csrfToken)) {
            Security::logSecurityEvent('csrf_validation_failed', [
                'username' => $username,
                'action' => 'login'
            ], 'warning');
            return ['error' => 'Token de seguridad inválido'];
        }

        // // Rate limiting
        // $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        // if (!Security::rateLimitCheck("login_$clientIP", 5, 900)) {
        //     Security::logSecurityEvent('rate_limit_exceeded', [
        //         'username' => $username,
        //         'ip' => $clientIP
        //     ], 'warning');
        //     return ['error' => 'Demasiados intentos de login. Intenta en 15 minutos.'];
        // }

        // Sanitizar entrada
        $username = Security::sanitizeInput($username);

        $user = $this->userModel->findByUsername($username);

        if ($user && $user['is_active'] && $this->userModel->verifyPassword($user, $password)) {
            // Log successful login
            Security::logSecurityEvent('login_success', [
                'username' => $username,
                'role' => $user['role']
            ], 'info');

            // Si es cliente, agregar datos del cliente
            if ($user['role'] === 'cliente') {
                $cliente = $this->clienteModel->findByUserId($user['id']);
                if ($cliente) {
                    $user['cliente_id'] = $cliente['id'];
                    $user['razon_social'] = $cliente['razon_social'];
                    $user['rut_empresa'] = $cliente['rut_empresa'];
                    $user['email'] = $cliente['email'] ?? $user['email'];
                }
            }

            // Crear sesión segura
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['login_time'] = time();

            return [
                'token' => $this->generateToken($user),
                'user' => $this->sanitizeUser($user)
            ];
        } else {
            // Log failed login
            Security::logSecurityEvent('login_failed', [
                'username' => $username
            ], 'warning');
            return ['error' => 'Credenciales inválidas'];
        }
    }

    public function register($userData, $csrfToken = null) {
        try {
            // Validar CSRF si se proporciona
            if ($csrfToken && !Security::validateCSRF($csrfToken)) {
                Security::logSecurityEvent('csrf_validation_failed', [
                    'username' => $userData['username'] ?? 'unknown',
                    'action' => 'register'
                ], 'warning');
                return ['error' => 'Token de seguridad inválido'];
            }

            // Sanitizar datos
            $userData = Security::sanitizeInput($userData);

            // Validaciones de seguridad
            if (!Security::validateEmail($userData['email'])) {
                return ['error' => 'Email inválido'];
            }

            if (!Security::validatePassword($userData['password'])) {
                return ['error' => 'La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número'];
            }

            if (!Security::validateRUT($userData['rut_empresa'])) {
                return ['error' => 'RUT de empresa inválido'];
            }

            // Rate limiting
            // $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            // if (!Security::rateLimitCheck("register_$clientIP", 3, 3600)) {
            //     Security::logSecurityEvent('rate_limit_exceeded', [
            //         'action' => 'register',
            //         'ip' => $clientIP
            //     ], 'warning');
            //     return ['error' => 'Demasiados intentos de registro. Intenta en 1 hora.'];
            // }

            // Crear usuario
            $userId = $this->userModel->create([
                'username' => $userData['username'],
                'password' => $userData['password'],
                'email' => $userData['email'],
                'role' => 'cliente'
            ]);

            if (!$userId) {
                return ['error' => 'Error al crear usuario o usuario ya existe'];
            }

            // Crear cliente
            $clienteId = $this->clienteModel->create([
                'user_id' => $userId,
                'razon_social' => $userData['razon_social'],
                'rut_empresa' => $userData['rut_empresa'],
                'email' => $userData['email']
            ]);

            if ($clienteId === false) {
                return ['error' => 'Error al crear perfil de cliente'];
            }

            Security::logSecurityEvent('register_success', [
                'username' => $userData['username'],
                'email' => $userData['email']
            ], 'info');

            return ['success' => true];

        } catch (Exception $e) {
            Security::logSecurityEvent('register_error', [
                'error' => $e->getMessage()
            ], 'error');
            return ['error' => 'Error interno del servidor'];
        }
    }

    public function generateToken($user) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'cliente_id' => $user['cliente_id'] ?? null,
            'exp' => time() + (24 * 60 * 60) // 24 horas
        ]);

        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64Header . "." . $base64Payload, $this->secretKey, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64Header . "." . $base64Payload . "." . $base64Signature;
    }

    public function validateToken($token) {
        try {
            $parts = explode('.', $token);
            if (count($parts) !== 3) return false;

            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);

            if ($payload['exp'] < time()) return false;

            $expectedSignature = hash_hmac('sha256', $parts[0] . "." . $parts[1], $this->secretKey, true);
            $actualSignature = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[2]));

            if (!hash_equals($expectedSignature, $actualSignature)) return false;

            return $payload;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAuthHeaders() {
        // Método 1: getallheaders() si está disponible
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                return str_replace('Bearer ', '', $headers['Authorization']);
            }
            if (isset($headers['X-Authorization'])) {
                return str_replace('Bearer ', '', $headers['X-Authorization']);
            }
            if (isset($headers['X-Access-Token'])) {
                return $headers['X-Access-Token'];
            }
        }

        // Método 2: $_SERVER variables (más compatible)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return str_replace('Bearer ', '', $_SERVER['HTTP_AUTHORIZATION']);
        }

        // Método 3: Headers alternativos
        if (isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
            return str_replace('Bearer ', '', $_SERVER['HTTP_X_AUTHORIZATION']);
        }

        if (isset($_SERVER['HTTP_X_ACCESS_TOKEN'])) {
            return $_SERVER['HTTP_X_ACCESS_TOKEN'];
        }

        // Método 4: Buscar en diferentes formatos de headers
        $authHeaders = [
            'HTTP_AUTHORIZATION',
            'REDIRECT_HTTP_AUTHORIZATION',
            'PHP_AUTH_DIGEST',
            'PHP_AUTH_USER',
            'REDIRECT_HTTP_X_AUTHORIZATION'
        ];

        foreach ($authHeaders as $header) {
            if (isset($_SERVER[$header]) && !empty($_SERVER[$header])) {
                $value = $_SERVER[$header];
                if (strpos($value, 'Bearer ') === 0) {
                    return substr($value, 7);
                }
                return $value;
            }
        }

        // Método 5: Para descarga directa, buscar en query string
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }

        return null;
    }

    private function sanitizeUser($user) {
        unset($user['password']);
        return $user;
    }

    public function requireAuth() {
        $token = $this->getAuthHeaders();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de acceso requerido']);
            exit;
        }

        $userData = $this->validateToken($token);
        if (!$userData) {
            http_response_code(401);
            echo json_encode(['error' => 'Token inválido']);
            exit;
        }

        return $userData;
    }

    public function requireAdmin() {
        $userData = $this->requireAuth();

        if ($userData['role'] !== 'admin') {
            Security::logSecurityEvent('unauthorized_admin_access', [
                'user_id' => $userData['user_id'] ?? 'unknown',
                'role' => $userData['role'] ?? 'unknown'
            ], 'warning');

            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado. Solo administradores.']);
            exit;
        }

        return $userData;
    }

    public function logout() {
        // Log logout
        if (isset($_SESSION['user_id'])) {
            Security::logSecurityEvent('logout', [
                'user_id' => $_SESSION['user_id']
            ], 'info');
        }

        // Destruir sesión
        session_destroy();

        // Limpiar cookies de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        return ['success' => true];
    }

    public function checkSessionTimeout($maxLifetime = 3600) {
        if (isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > $maxLifetime) {
                Security::logSecurityEvent('session_timeout', [
                    'user_id' => $_SESSION['user_id'] ?? 'unknown'
                ], 'info');

                $this->logout();
                return false;
            }
        }
        return true;
    }

    public function getCSRFToken() {
        return Security::generateCSRF();
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
    }

    public function isAdmin() {
        return $this->isLoggedIn() && $_SESSION['user_role'] === 'admin';
    }

    public function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }

    public function getCurrentUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
}

?>