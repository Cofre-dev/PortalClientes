<?php

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/TipoDocumento.php';

class TiposDocumentoController {
    private $auth;
    private $tipoDocumento;

    public function __construct() {
        $this->auth = new Auth();
        $this->tipoDocumento = new TipoDocumento();
    }

    // Listar categorías de un cliente específico
    public function listarPorCliente($clienteId) {
        try {
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método GET']);
                return;
            }

            $categorias = $this->tipoDocumento->findByClienteId($clienteId);

            echo json_encode([
                'success' => true,
                'data' => $categorias
            ]);

        } catch (Exception $e) {
            error_log("TiposDocumentoController::listarPorCliente - ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener categorías'
            ]);
        }
    }

    // Crear categoría para un cliente
    public function crearParaCliente($clienteId) {
        try {
            $userData = $this->auth->requireAuth();  // Admins y clientes autenticados pueden crear

            // Si es cliente, verificar que solo cree para su propio cliente_id
            if ($userData['role'] === 'cliente') {
                if (!isset($userData['cliente_id']) || $userData['cliente_id'] != $clienteId) {
                    http_response_code(403);
                    echo json_encode([
                        'success' => false,
                        'message' => 'No tienes permiso para crear categorías en este cliente'
                    ]);
                    return;
                }
            }

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método POST']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input || empty($input['nombre'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'El nombre es requerido'
                ]);
                return;
            }

            $categoriaData = [
                'cliente_id' => $clienteId,
                'nombre' => $input['nombre'],
                'codigo' => $input['codigo'] ?? null,
                'descripcion' => $input['descripcion'] ?? null,
                'is_active' => $input['is_active'] ?? true
            ];

            $categoriaId = $this->tipoDocumento->create($categoriaData);

            if ($categoriaId) {
                $categoria = $this->tipoDocumento->findById($categoriaId);
                echo json_encode([
                    'success' => true,
                    'data' => $categoria,
                    'message' => 'Categoría creada exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear categoría'
                ]);
            }

        } catch (Exception $e) {
            error_log("TiposDocumentoController::crearParaCliente - ERROR: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // Actualizar categoría
    public function actualizar($categoriaId) {
        try {
            $userData = $this->auth->requireAdmin();

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método PUT']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Datos inválidos'
                ]);
                return;
            }

            $updated = $this->tipoDocumento->update($categoriaId, $input);

            if ($updated) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría actualizada exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
            }

        } catch (Exception $e) {
            error_log("TiposDocumentoController::actualizar - ERROR: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    // Eliminar categoría
    public function eliminar($categoriaId) {
        try {
            $userData = $this->auth->requireAdmin();

            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método DELETE']);
                return;
            }

            $deleted = $this->tipoDocumento->delete($categoriaId);

            if ($deleted) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría eliminada exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Categoría no encontrada'
                ]);
            }

        } catch (Exception $e) {
            error_log("TiposDocumentoController::eliminar - ERROR: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
?>