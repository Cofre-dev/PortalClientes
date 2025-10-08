<?php

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/TipoDocumento.php';

class TiposDocumentoController {
    private $auth;
    private $tipoDocumentoModel;

    public function __construct() {
        $this->auth = new Auth();
        $this->tipoDocumentoModel = new TipoDocumento();
    }

    public function listar() {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            // Obtener tipos de documento
            $tipos = $this->tipoDocumentoModel->getTodos();

            echo json_encode($tipos);

        } catch (Exception $e) {
            error_log("TiposDocumento listar error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function crear() {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método POST']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if ($input === null) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                return;
            }

            $required = ['nombre', 'codigo'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => "El campo {$field} es requerido"]);
                    return;
                }
            }

            $tipoData = [
                'nombre' => trim($input['nombre']),
                'codigo' => trim($input['codigo']),
                'descripcion' => trim($input['descripcion'] ?? ''),
                'is_active' => true
            ];

            $tipoId = $this->tipoDocumentoModel->create($tipoData);

            if ($tipoId) {
                echo json_encode([
                    'success' => true,
                    'id' => $tipoId,
                    'message' => 'Tipo de documento creado exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Error al crear tipo de documento']);
            }

        } catch (Exception $e) {
            error_log("TiposDocumento crear error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function obtener($id) {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            $tipo = $this->tipoDocumentoModel->findById($id);

            if ($tipo) {
                echo json_encode($tipo);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Tipo de documento no encontrado']);
            }

        } catch (Exception $e) {
            error_log("TiposDocumento obtener error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function actualizar($id) {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método PUT']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if ($input === null) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                return;
            }

            $updateData = [];
            if (isset($input['nombre'])) $updateData['nombre'] = trim($input['nombre']);
            if (isset($input['codigo'])) $updateData['codigo'] = trim($input['codigo']);
            if (isset($input['descripcion'])) $updateData['descripcion'] = trim($input['descripcion']);
            if (isset($input['is_active'])) $updateData['is_active'] = (bool)$input['is_active'];

            if (empty($updateData)) {
                http_response_code(400);
                echo json_encode(['error' => 'No hay datos para actualizar']);
                return;
            }

            $updated = $this->tipoDocumentoModel->update($id, $updateData);

            if ($updated) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tipo de documento actualizado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Tipo de documento no encontrado']);
            }

        } catch (Exception $e) {
            error_log("TiposDocumento actualizar error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function eliminar($id) {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método DELETE']);
                return;
            }

            $deleted = $this->tipoDocumentoModel->delete($id);

            if ($deleted) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Tipo de documento eliminado exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Tipo de documento no encontrado']);
            }

        } catch (Exception $e) {
            error_log("TiposDocumento eliminar error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }
}
?>