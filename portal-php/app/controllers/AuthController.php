<?php

require_once __DIR__ . '/../config/Auth.php';

class AuthController {
    private $auth;

    public function __construct() {
        $this->auth = new Auth();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método POST']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';
        $csrfToken = $input['csrf_token'] ?? null;

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username y password son requeridos']);
            return;
        }

        $result = $this->auth->login($username, $password, $csrfToken);

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
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método POST']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
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
}