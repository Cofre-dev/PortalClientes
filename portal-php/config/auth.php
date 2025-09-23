<?php
require_once 'data.php';

class Auth {
    private $secretKey = 'tu_clave_secreta_jwt_aqui_cambiar_en_produccion';

    public function __construct() {
        // Ya no necesitamos conexión a BD
    }

    public function login($username, $password) {
        $user = DataStore::getUsuarioByUsername($username);

        if ($user && $user['is_active'] && $user['password'] === $password) {
            // Si es cliente, agregar datos del cliente
            if ($user['role'] === 'cliente') {
                $cliente = DataStore::getClienteByUserId($user['id']);
                if ($cliente) {
                    $user['cliente_id'] = $cliente['id'];
                    $user['razon_social'] = $cliente['razon_social'];
                    $user['rut_empresa'] = $cliente['rut_empresa'];
                }
            }
            return $this->generateToken($user);
        }
        return false;
    }

    public function register($username, $password, $email, $razon_social, $rut_empresa) {
        // Verificar si el usuario ya existe
        if (DataStore::getUsuarioByUsername($username)) {
            return false;
        }

        try {
            // Crear usuario
            $userData = [
                'username' => $username,
                'password' => $password, // En MVP sin hash, en producción usar password_hash()
                'email' => $email,
                'role' => 'cliente',
                'is_active' => true
            ];
            $userId = DataStore::createUsuario($userData);

            // Crear cliente
            $clienteData = [
                'user_id' => $userId,
                'razon_social' => $razon_social,
                'rut_empresa' => $rut_empresa
            ];
            DataStore::createCliente($clienteData);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    private function generateToken($user) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'role' => $user['role'],
            'cliente_id' => isset($user['cliente_id']) ? $user['cliente_id'] : null,
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
        $headers = getallheaders();
        return isset($headers['Authorization']) ? str_replace('Bearer ', '', $headers['Authorization']) : null;
    }
}
?>