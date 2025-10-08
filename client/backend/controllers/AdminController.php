<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';

class AdminController {
    private $db;
    private $auth;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new Auth();
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


    public function testEndpoint() {
        // Endpoint de test muy simple para verificar routing
        echo json_encode([
            'success' => true,
            'message' => 'Test endpoint funciona correctamente',
            'timestamp' => date('Y-m-d H:i:s'),
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A'
        ]);
    }

    public function getStats() {
        return $this->stats();
    }

    public function debugClientes() {
        // Método de debug SIN autenticación para troubleshooting
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método GET']);
            return;
        }

        try {
            // Verificar estructura de tabla clientes
            $checkColumns = $this->db->query("DESCRIBE clientes");
            $columns = $checkColumns->fetchAll();
            $columnNames = array_column($columns, 'Field');

            // Obtener todos los clientes (query simple)
            $clientesBasicos = $this->db->query("SELECT * FROM clientes")->fetchAll();

            // Determinar columnas correctas
            $empresaColumn = in_array('empresa', $columnNames) ? 'empresa' : 'razon_social';
            $correoColumn = in_array('correo_contacto', $columnNames) ? 'correo_contacto' : 'email';

            echo json_encode([
                'debug' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'table_columns' => $columnNames,
                'total_clientes' => count($clientesBasicos),
                'clientes_raw' => $clientesBasicos,
                'column_mapping' => [
                    'empresa_column' => $empresaColumn,
                    'correo_column' => $correoColumn
                ],
                'message' => 'Debug completo de estructura y datos'
            ], JSON_PRETTY_PRINT);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error en debug',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], JSON_PRETTY_PRINT);
        }
    }

    public function getClientes() {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método GET']);
            return;
        }

        try {
            error_log("AdminController::getClientes - Iniciando...");

            // Primero verificar qué columnas existen realmente
            error_log("AdminController::getClientes - Verificando columnas...");
            $checkColumns = $this->db->query("DESCRIBE clientes");
            $columns = $checkColumns->fetchAll();
            $columnNames = array_column($columns, 'Field');
            error_log("AdminController::getClientes - Columnas encontradas: " . implode(', ', $columnNames));

            // Adaptar el SQL según las columnas que existan
            $empresaColumn = in_array('empresa', $columnNames) ? 'c.empresa' : 'c.razon_social';
            $correoColumn = in_array('correo_contacto', $columnNames) ? 'c.correo_contacto' : 'c.email';
            error_log("AdminController::getClientes - Usando empresa_column: " . $empresaColumn);
            error_log("AdminController::getClientes - Usando correo_column: " . $correoColumn);

            $sql = "SELECT c.id, {$empresaColumn} as empresa, c.nombre_cliente, c.rut_empresa,
                           {$correoColumn} as correo_contacto,
                           c.telefono, c.direccion, c.created_at,
                           COUNT(d.id) as total_documentos,
                           COUNT(DISTINCT d.categoria_id) as categorias_activas
                    FROM clientes c
                    LEFT JOIN documentos d ON c.id = d.cliente_id
                    GROUP BY c.id, {$empresaColumn}, c.nombre_cliente, c.rut_empresa,
                            {$correoColumn}, c.telefono, c.direccion, c.created_at
                    ORDER BY {$empresaColumn} ASC";

            error_log("AdminController::getClientes - SQL generado: " . $sql);

            $stmt = $this->db->query($sql);
            $clientes = $stmt->fetchAll();

            error_log("AdminController::getClientes - Clientes obtenidos: " . count($clientes));

            // Formatear los datos para el frontend
            foreach ($clientes as &$cliente) {
                $cliente['total_documentos'] = intval($cliente['total_documentos']);
                $cliente['categorias_activas'] = intval($cliente['categorias_activas']);
            }

            error_log("AdminController::getClientes - Enviando respuesta exitosa...");

            echo json_encode([
                'success' => true,
                'data' => $clientes
            ]);
        } catch (Exception $e) {
            error_log("AdminController::getClientes - ERROR: " . $e->getMessage());
            error_log("AdminController::getClientes - ERROR FILE: " . $e->getFile());
            error_log("AdminController::getClientes - ERROR LINE: " . $e->getLine());
            error_log("AdminController::getClientes - ERROR TRACE: " . $e->getTraceAsString());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener lista de clientes',
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        }
    }

    public function stats() {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método GET']);
            return;
        }

        try {
            // Obtener estadísticas generales
            $stats = [
                'total_documentos' => $this->getTotalDocumentos(),
                'total_clientes' => $this->getTotalClientes(),
                'total_categorias' => $this->getTotalCategorias(),
                'documentos_hoy' => $this->getDocumentosHoy()
            ];

            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
        } catch (Exception $e) {
            error_log("Error getting admin stats: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ]);
        }
    }


    private function getTotalDocumentos() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM documentos");
            $result = $stmt->fetch();
            return intval($result['total']);
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getTotalClientes() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM clientes");
            $result = $stmt->fetch();
            return intval($result['total']);
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getTotalCategorias() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM tipos_documento WHERE is_active = TRUE");
            $result = $stmt->fetch();
            return intval($result['total']);
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getDocumentosHoy() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total FROM documentos WHERE DATE(fecha_subida) = CURDATE()");
            $result = $stmt->fetch();
            return intval($result['total']);
        } catch (Exception $e) {
            return 0;
        }
    }
}

?>