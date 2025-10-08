<?php

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../views/admin/crearClientes.php';
require_once __DIR__ . '/../models/Cliente.php';

class ClientesController {
    private $auth;
    private $cliente;

    
    public function __construct() {
        $this->auth = new Auth();
        $this->cliente = new Cliente();
        // $this->clientesManager = new Cliente();
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

    public function listar() {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método GET']);
            return;
        }

        try {
            error_log("ClientesController::listar - Iniciando...");

            $clientes = $this->cliente->getAllWithUsers();

            error_log("ClientesController::listar - Clientes obtenidos: " . count($clientes));

            echo json_encode([
                'success' => true,
                'data' => $clientes,
                'message' => 'Clientes obtenidos exitosamente',
                'debug_info' => [
                    'total_clientes' => count($clientes),
                    'timestamp' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (Exception $exception) {
            error_log("ClientesController::listar - ERROR: " . $exception->getMessage());
            error_log("ClientesController::listar - ERROR TRACE: " . $exception->getTraceAsString());

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'error al obtener clientes: '.$exception->getMessage(),
                'debug_info' => [
                    'error' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine()
                ]
            ]);
        }

    }

    public function obtener($id) {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método GET']);
            return;
        }

        try {
            $cliente = $this -> cliente -> findById($id);

            if ($cliente) {
                echo json_encode([
                    'success' => true,
                    'data' => $cliente,
                    'message' => 'cliente encontrado'
                ]);
            } else {
                http_response_code((404));
                    echo json_encode([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ]);
            }
        }
        catch(Exception $exception){
            http_response_code(500);
                        echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $exception->getMessage()
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
        $required = ['razon_social', 'rut_empresa', 'email'];
        foreach ($required as $field) {
            if (empty($input[$field])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "El campo {$field} es requerido"
                ]);
                return;
            }
        }

        try {
            $clienteId = $this->cliente->create([
                'razon_social' => $input['razon_social'],
                'rut_empresa' => $input['rut_empresa'],
                'email' => $input['email'],
                'telefono' => $input['telefono'] ?? null,
                'direccion' => $input['direccion'] ?? null,
                'user_id' => $input['user_id'] ?? null,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            if ($clienteId) {
                $clienteCreado = $this->cliente->findById($clienteId);
                http_response_code(201);
                echo json_encode([
                    'success' => true,
                    'data' => $clienteCreado,
                    'message' => 'Cliente creado exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al crear el cliente'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en crear cliente: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function actualizar($id) {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método PUT']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos JSON inválidos']);
            return;
        }

        try {
            $datosActualizar = [];
            if (isset($input['razon_social'])) $datosActualizar['razon_social'] = $input['razon_social'];
            if (isset($input['rut_empresa'])) $datosActualizar['rut_empresa'] = $input['rut_empresa'];
            if (isset($input['email'])) $datosActualizar['email'] = $input['email'];
            if (isset($input['telefono'])) $datosActualizar['telefono'] = $input['telefono'];
            if (isset($input['direccion'])) $datosActualizar['direccion'] = $input['direccion'];
            $datosActualizar['updated_at'] = date('Y-m-d H:i:s');

            $clienteActualizado = $this->cliente->update($id, $datosActualizar);

            if ($clienteActualizado) {
                echo json_encode([
                    'success' => true,
                    'data' => $clienteActualizado,
                    'message' => 'Cliente actualizado exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al actualizar el cliente'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en actualizar cliente: " . $e->getMessage());
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

   public function eliminar($id) {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método DELETE']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['admin_password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Contraseña de administrador requerida']);
            return;
        }

        // Validar contraseña de admin (aquí deberías implementar tu lógica de validación)
        // Por ahora asumimos que la validación pasa

        try {
            $eliminado = $this->cliente->delete($id);

            if ($eliminado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cliente eliminado exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Error al eliminar el cliente'
                ]);
            }
        } catch (Exception $e) {
            error_log("Error en eliminar cliente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function estadisticas() {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método GET']);
            return;
        }

        try {
            $totalClientes = $this->cliente->getTotalCount();
            $clientes = $this->cliente->getAllWithUsers();
            
            $clientesActivos = count(array_filter($clientes, function($c) {
                return $c['is_active'] == 1;
            }));
            
            $totalDocumentos = array_sum(array_column($clientes, 'total_documentos'));

            echo json_encode([
                'success' => true,
                'data' => [
                    'total_clientes' => $totalClientes,
                    'clientes_activos' => $clientesActivos,
                    'total_documentos' => $totalDocumentos,
                    'nuevos_este_mes' => 0 // Implementar lógica si es necesario
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);
        } catch (Exception $e) {
            error_log("Error en estadísticas clientes: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
            ]);
        }
    }

    // Nuevas funcionalidades para gestión de categorías por cliente
    public function obtenerCategorias($clienteId) {
        if (!$this->verifyAuth()) return;

        try {
            require_once __DIR__ . '/../models/ClienteCategoria.php';
            $clienteCategoria = new ClienteCategoria();

            $categorias = $clienteCategoria->obtenerCategoriasPorCliente($clienteId);

            echo json_encode([
                'success' => true,
                'data' => $categorias
            ]);

        } catch (Exception $e) {
            error_log("Error en obtenerCategorias: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener categorías: ' . $e->getMessage()
            ]);
        }
    }

    public function asignarCategoria($clienteId) {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método POST']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['categoria_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'categoria_id es requerido']);
            return;
        }

        try {
            require_once __DIR__ . '/../models/ClienteCategoria.php';
            $clienteCategoria = new ClienteCategoria();

            $resultado = $clienteCategoria->asignarCategoria($clienteId, $input['categoria_id']);

            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría asignada exitosamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'La categoría ya está asignada a este cliente o error en la asignación'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en asignarCategoria: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al asignar categoría: ' . $e->getMessage()
            ]);
        }
    }

    public function eliminarCategoria($clienteId, $categoriaId) {
        if (!$this->verifyAuth()) return;

        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['error' => 'Solo se permite método DELETE']);
            return;
        }

        try {
            require_once __DIR__ . '/../models/ClienteCategoria.php';
            $clienteCategoria = new ClienteCategoria();

            $resultado = $clienteCategoria->eliminarCategoria($clienteId, $categoriaId);

            if ($resultado > 0) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría eliminada del cliente exitosamente'
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'No se encontró la relación cliente-categoría'
                ]);
            }

        } catch (Exception $e) {
            error_log("Error en eliminarCategoria: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar categoría: ' . $e->getMessage()
            ]);
        }
    }

    public function obtenerDocumentosPorCategoria($clienteId, $categoriaId) {
        if (!$this->verifyAuth()) return;

        try {
            require_once __DIR__ . '/../models/Documento.php';
            $documentoModel = new Documento();

            $documentos = $documentoModel->obtenerDocumentosPorClienteYCategoria($clienteId, $categoriaId);

            echo json_encode([
                'success' => true,
                'data' => $documentos
            ]);

        } catch (Exception $e) {
            error_log("Error en obtenerDocumentosPorCategoria: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener documentos: ' . $e->getMessage()
            ]);
        }
    }

}

?>