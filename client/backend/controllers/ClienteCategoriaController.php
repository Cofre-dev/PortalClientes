<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';

class ClienteCategoriaController {
    private $db;
    private $auth;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->auth = new Auth();
    }

    private function verifyAuth() {
    
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

    public function listarPorCliente($clienteId) {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método GET']);
            return;
        }

        try {
            // Obtener categorías específicas del cliente + categorías globales + conteo de documentos
            $sql = "
                SELECT
                    'client' as tipo,
                    cc.id,
                    cc.nombre,
                    cc.codigo,
                    cc.descripcion,
                    cc.is_active,
                    cc.created_at,
                    (SELECT COUNT(*) FROM documentos d WHERE d.categoria_id = cc.id AND d.cliente_id = ?) as total_documentos
                FROM cliente_categorias cc
                WHERE cc.cliente_id = ? AND cc.is_active = TRUE

                UNION ALL

                SELECT
                    'global' as tipo,
                    td.id,
                    td.nombre,
                    td.codigo,
                    td.descripcion,
                    td.is_active,
                    td.created_at,
                    (SELECT COUNT(*) FROM documentos d WHERE d.categoria_id = td.id AND d.cliente_id = ?) as total_documentos
                FROM tipos_documento td
                WHERE td.is_active = TRUE

                ORDER BY created_at DESC
            ";

            $stmt = $this->db->query($sql, [$clienteId, $clienteId, $clienteId]);
            $categorias = $stmt->fetchAll();

            // Formatear los datos
            foreach ($categorias as &$categoria) {
                $categoria['total_documentos'] = intval($categoria['total_documentos']);
            }

            echo json_encode([
                'success' => true,
                'data' => $categorias,
                'message' => 'Categorías obtenidas exitosamente'
            ]);

        } catch (Exception $e) {
            error_log("Error obteniendo categorías del cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener categorías del cliente'
            ]);
        }
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

        // Validar campos requeridos
        if (empty($input['nombre']) || empty($input['cliente_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Nombre y cliente_id son requeridos'
            ]);
            return;
        }

        try {
            // Generar código automáticamente si no se proporciona
            $codigo = $input['codigo'] ?? null;
            if (empty($codigo)) {
                // Generar código basado en el nombre
                $codigo = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '_', $input['nombre']), 0, 20));

                // Asegurar que sea único para este cliente
                $counter = 1;
                $originalCodigo = $codigo;
                while ($this->existeCodigoParaCliente($input['cliente_id'], $codigo)) {
                    $codigo = $originalCodigo . '_' . $counter;
                    $counter++;
                }
            } else {
                // Verificar que el código no exista para este cliente
                if ($this->existeCodigoParaCliente($input['cliente_id'], $codigo)) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Ya existe una categoría con ese código para este cliente'
                    ]);
                    return;
                }
            }

            $categoriaId = $this->db->insert('cliente_categorias', [
                'cliente_id' => $input['cliente_id'],
                'nombre' => $input['nombre'],
                'codigo' => $codigo,
                'descripcion' => $input['descripcion'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($categoriaId) {
                $categoria = $this->db->selectOne('cliente_categorias', '*', 'id = ?', [$categoriaId]);

                echo json_encode([
                    'success' => true,
                    'data' => $categoria,
                    'message' => 'Categoría creada exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear la categoría'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error creando categoría de cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error interno al crear la categoría'
            ]);
        }
    }

    public function eliminar($clienteId, $categoriaId) {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método DELETE']);
            return;
        }

        try {
            // Verificar que la categoría pertenece al cliente
            $categoria = $this->db->selectOne(
                'cliente_categorias',
                '*',
                'id = ? AND cliente_id = ?',
                [$categoriaId, $clienteId]
            );

            if (!$categoria) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Categoría no encontrada para este cliente'
                ]);
                return;
            }

            // Verificar si hay documentos asociados
            $documentos = $this->db->selectOne(
                'documentos',
                'COUNT(*) as total',
                'categoria_id = ? AND cliente_id = ?',
                [$categoriaId, $clienteId]
            );

            if ($documentos['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'No se puede eliminar la categoría porque tiene ' . $documentos['total'] . ' documentos asociados'
                ]);
                return;
            }

            // Eliminar la categoría
            $eliminado = $this->db->delete('cliente_categorias', 'id = ? AND cliente_id = ?', [$categoriaId, $clienteId]);

            if ($eliminado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría eliminada exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar la categoría'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error eliminando categoría de cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error interno al eliminar la categoría'
            ]);
        }
    }

    private function existeCodigoParaCliente($clienteId, $codigo) {
        $categoria = $this->db->selectOne(
            'cliente_categorias',
            'id',
            'cliente_id = ? AND codigo = ?',
            [$clienteId, $codigo]
        );
        return $categoria !== null;
    }
}

?>