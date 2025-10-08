<?php
//security.php
class Security {

    public static function startSecureSession() {
        // Solo configurar sesión si no hay una activa
        if (session_status() === PHP_SESSION_NONE) {
            // Detectar si estamos en HTTPS
            $isHTTPS = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $isForwardedHTTPS = !empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';
            $isCloudflareHTTPS = !empty($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== false;

            $useSecure = $isHTTPS || $isForwardedHTTPS || $isCloudflareHTTPS;

            // Configuración segura de sesión
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', $useSecure ? 1 : 0);
            ini_set('session.use_strict_mode', 1);

            // Configurar SameSite apropiadamente para producción
            if ($useSecure) {
                ini_set('session.cookie_samesite', 'Lax'); // Más permisivo para HTTPS
            } else {
                ini_set('session.cookie_samesite', 'Strict'); // Estricto para desarrollo local
            }

            // Configurar dominio y path para producción
            if (isset($_SERVER['HTTP_HOST'])) {
                $domain = $_SERVER['HTTP_HOST'];
                // Si es un dominio en producción, configurar dominio de cookie
                if (strpos($domain, 'localhost') === false && strpos($domain, '127.0.0.1') === false) {
                    ini_set('session.cookie_domain', '.' . $domain);
                }
            }

            // Iniciar sesión
            session_start();
        }

        // Regenerar ID cada 30 minutos
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif (time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }

    public static function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) &&
               hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function generateCSRF() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    public static function validatePassword($password) {
        // Mínimo 8 caracteres, al menos una mayúscula, una minúscula y un número
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/', $password);
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateRUT($rut) {
        // Validación básica de RUT chileno
        $rut = preg_replace('/[^0-9kK\-]/', '', $rut);
        return preg_match('/^\d{1,8}-[\dkK]$/', $rut);
    }

    public static function rateLimitCheck($identifier, $maxAttempts = 5, $timeWindow = 900) {
        $file = __DIR__ . '/../../storage/security/rate_limit.json';
        $data = [];

        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true) ?: [];
        }

        $now = time();
        $key = md5($identifier);

        // Limpiar intentos expirados
        if (isset($data[$key])) {
            $data[$key] = array_filter($data[$key], function($timestamp) use ($now, $timeWindow) {
                return ($now - $timestamp) < $timeWindow;
            });
        }

        // Verificar límite
        $attempts = count($data[$key] ?? []);
        if ($attempts >= $maxAttempts) {
            return false;
        }

        // Registrar intento
        $data[$key][] = $now;

        // Guardar datos
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }
        file_put_contents($file, json_encode($data));

        return true;
    }

    public static function logSecurityEvent($type, $details, $severity = 'info') {
        $logFile = __DIR__ . '/../../storage/security/security.log';

        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'severity' => $severity,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];

        if (!is_dir(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }

        file_put_contents($logFile, json_encode($entry) . "\n", FILE_APPEND | LOCK_EX);
    }

    public static function checkPermission($userRole, $requiredRole) {
        $roles = ['cliente' => 1, 'admin' => 2];

        $userLevel = $roles[$userRole] ?? 0;
        $requiredLevel = $roles[$requiredRole] ?? 0;

        return $userLevel >= $requiredLevel;
    }
}

?>