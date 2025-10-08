<?php
// Error Handler Configuration
class ErrorHandler {

    public static function setupErrorReporting() {
        // Detectar entorno de desarrollo
        $isDevelopment = self::isDevelopmentEnvironment();

        if ($isDevelopment) {
            // Entorno de desarrollo - mostrar algunos errores pero logear todos
            error_reporting(E_ALL);
            ini_set('display_errors', 0); // NO mostrar en frontend incluso en desarrollo
            ini_set('log_errors', 1);
        } else {
            // Entorno de producción - solo logear, no mostrar
            error_reporting(E_ALL);
            ini_set('display_errors', 0);
            ini_set('log_errors', 1);
        }

        // Configurar archivo de log
        $logDir = __DIR__ . '/../../storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        ini_set('error_log', $logDir . '/php_errors.log');

        // Configurar manejo de errores fatales
        register_shutdown_function([self::class, 'handleFatalError']);

        // Configurar manejo de excepciones no capturadas
        set_exception_handler([self::class, 'handleUncaughtException']);
    }

    private static function isDevelopmentEnvironment() {
        // Detectar si estamos en desarrollo basado en múltiples indicadores
        $indicators = [
            strpos(__DIR__, 'xampp') !== false,
            $_SERVER['HTTP_HOST'] ?? '' === 'localhost',
            $_SERVER['SERVER_NAME'] ?? '' === 'localhost',
            in_array($_SERVER['SERVER_ADDR'] ?? '', ['127.0.0.1', '::1']),
        ];

        return in_array(true, $indicators, true);
    }

    public static function handleFatalError() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::logError('FATAL', $error['message'], $error['file'], $error['line']);

            // En lugar de mostrar el error, mostrar una página de error amigable
            if (!headers_sent()) {
                http_response_code(500);
                if (self::isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => 'Error interno del servidor'
                    ]);
                } else {
                    echo self::getErrorPage();
                }
            }
        }
    }

    public static function handleUncaughtException($exception) {
        self::logError('EXCEPTION', $exception->getMessage(), $exception->getFile(), $exception->getLine());

        if (!headers_sent()) {
            http_response_code(500);
            if (self::isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => 'Error interno del servidor'
                ]);
            } else {
                echo self::getErrorPage();
            }
        }
    }

    private static function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private static function logError($type, $message, $file, $line) {
        $logEntry = sprintf(
            "[%s] %s: %s in %s on line %d" . PHP_EOL,
            date('Y-m-d H:i:s'),
            $type,
            $message,
            $file,
            $line
        );

        $logFile = __DIR__ . '/../../storage/logs/php_errors.log';
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }

    private static function getErrorPage() {
        return '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Error - ARA & Bustamante</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
                .error-container { max-width: 600px; margin: 50px auto; background: white; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; }
                .error-icon { font-size: 4rem; color: #e74c3c; margin-bottom: 20px; }
                h1 { color: #2c3e50; margin-bottom: 10px; }
                p { color: #7f8c8d; line-height: 1.6; }
                .btn { display: inline-block; padding: 12px 24px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                .btn:hover { background: #2980b9; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <div class="error-icon">⚠️</div>
                <h1>Error Interno del Servidor</h1>
                <p>Ha ocurrido un error interno en el servidor. Nuestro equipo técnico ha sido notificado y está trabajando para solucionarlo.</p>
                <p>Por favor, intenta nuevamente en unos momentos.</p>
                <a href="/portal-php/" class="btn">Volver al Inicio</a>
            </div>
        </body>
        </html>';
    }
}
?>