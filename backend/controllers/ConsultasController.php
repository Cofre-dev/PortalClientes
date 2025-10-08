<?php

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/Consulta.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/ConsultaMensaje.php';
require_once __DIR__ . '/../models/User.php';

class ConsultasController {
    private $auth;
    private $consultaModel;
    private $mensajeModel;

    public function __construct() {
        $this->auth = new Auth();
        $this->consultaModel = new Consulta();
        $this->mensajeModel = new ConsultaMensaje();
    }

    public function crear() {
        try {
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método POST']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['asunto']) || !isset($input['mensaje'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Asunto y mensaje son requeridos']);
                return;
            }

            $clienteId = null;
            $userId = $userData['user_id'] ?? $userData['id'] ?? null;

            if ($userData['role'] === 'cliente') {
                $clienteId = $userData['cliente_id'] ?? null;
                if (!$clienteId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Usuario sin cliente asociado']);
                    return;
                }
            }

            $consultaId = $this->consultaModel->crear(
                $clienteId,
                $userId,
                $input['asunto'],
                $input['mensaje']
            );

            if ($consultaId) {
                echo json_encode([
                    'success' => true,
                    'id' => $consultaId,
                    'message' => 'Consulta enviada exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al crear consulta']);
            }

        } catch (Exception $e) {
            error_log("ConsultasController::crear - ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function listar() {
        try {
            $userData = $this->auth->requireAuth();

            // Si es admin, mostrar todas las consultas
            if ($userData['role'] === 'admin') {
                $consultas = $this->consultaModel->getAll();

                // Enriquecer con datos del cliente
                $clienteModel = new Cliente();
                foreach ($consultas as &$consulta) {
                    if ($consulta['cliente_id']) {
                        $cliente = $clienteModel->findById($consulta['cliente_id']);
                        if ($cliente) {
                            $consulta['cliente_razon_social'] = $cliente['razon_social'] ?? 'N/A';
                            $consulta['cliente_rut'] = $cliente['rut_empresa'] ?? 'N/A';
                        }
                    }
                }
            }
            // Si es cliente, mostrar solo sus consultas
            else if ($userData['role'] === 'cliente') {
                $clienteId = $userData['cliente_id'] ?? null;
                if (!$clienteId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Usuario sin cliente asociado']);
                    return;
                }
                $consultas = $this->consultaModel->getPorCliente($clienteId);
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'No autorizado']);
                return;
            }

            echo json_encode([
                'success' => true,
                'data' => $consultas
            ]);

        } catch (Exception $e) {
            error_log("ConsultasController::listar - ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function cambiarEstado($id) {
        try {
            $userData = $this->auth->requireAdmin();

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método PUT']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['estado'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Estado es requerido']);
                return;
            }

            $updated = $this->consultaModel->cambiarEstado($id, $input['estado']);

            if ($updated) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado actualizado'
                ]);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Consulta no encontrada']);
            }

        } catch (Exception $e) {
            error_log("ConsultasController::cambiarEstado - ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function enviarMensaje($consultaId) {
        try {
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método POST']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            if (!isset($input['mensaje']) || empty(trim($input['mensaje']))) {
                http_response_code(400);
                echo json_encode(['error' => 'El mensaje es requerido']);
                return;
            }

            // Verificar que la consulta existe
            $consulta = $this->consultaModel->findById($consultaId);
            if (!$consulta) {
                http_response_code(404);
                echo json_encode(['error' => 'Consulta no encontrada']);
                return;
            }

            // Verificar permisos
            $esAdmin = $userData['role'] === 'admin';
            if (!$esAdmin) {
                // Si es cliente, verificar que la consulta le pertenece
                $clienteId = $userData['cliente_id'] ?? null;
                if (!$clienteId || $consulta['cliente_id'] != $clienteId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'No tienes permisos para responder esta consulta']);
                    return;
                }
            }

            $userId = $userData['user_id'] ?? $userData['id'] ?? null;
            $mensajeId = $this->mensajeModel->crear(
                $consultaId,
                $userId,
                trim($input['mensaje']),
                $esAdmin
            );

            if ($mensajeId) {
                // Si hay un nuevo mensaje, cambiar estado a "en_proceso" si estaba pendiente
                if ($consulta['estado'] === 'pendiente') {
                    $this->consultaModel->cambiarEstado($consultaId, 'en_proceso');
                }

                // Obtener el mensaje creado con información del usuario
                $userModel = new User();
                $user = $userModel->findById($userId);

                $mensaje = [
                    'id' => $mensajeId,
                    'consulta_id' => $consultaId,
                    'user_id' => $userId,
                    'mensaje' => trim($input['mensaje']),
                    'es_admin' => $esAdmin ? 1 : 0,
                    'fecha' => date('Y-m-d H:i:s'),
                    'username' => $user ? $user['username'] : '',
                    'user_role' => $user ? $user['role'] : ''
                ];

                echo json_encode([
                    'success' => true,
                    'id' => $mensajeId,
                    'message' => 'Mensaje enviado exitosamente',
                    'data' => $mensaje
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al enviar mensaje']);
            }

        } catch (Exception $e) {
            error_log("ConsultasController::enviarMensaje - ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function obtenerMensajes($consultaId) {
        try {
            $userData = $this->auth->requireAuth();

            // Verificar que la consulta existe
            $consulta = $this->consultaModel->findById($consultaId);
            if (!$consulta) {
                http_response_code(404);
                echo json_encode(['error' => 'Consulta no encontrada']);
                return;
            }

            // Verificar permisos
            $esAdmin = $userData['role'] === 'admin';
            if (!$esAdmin) {
                // Si es cliente, verificar que la consulta le pertenece
                $clienteId = $userData['cliente_id'] ?? null;
                if (!$clienteId || $consulta['cliente_id'] != $clienteId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'No tienes permisos para ver esta consulta']);
                    return;
                }
            }

            // Obtener mensajes
            $mensajes = $this->mensajeModel->getPorConsulta($consultaId);

            // Enriquecer con información del usuario
            $userModel = new User();
            foreach ($mensajes as &$mensaje) {
                if ($mensaje['user_id']) {
                    $user = $userModel->findById($mensaje['user_id']);
                    if ($user) {
                        $mensaje['username'] = $user['username'];
                        $mensaje['user_role'] = $user['role'];
                    }
                }
            }

            // Marcar mensajes como leídos
            $this->mensajeModel->marcarComoLeido($consultaId, $esAdmin);

            echo json_encode([
                'success' => true,
                'data' => $mensajes
            ]);

        } catch (Exception $e) {
            error_log("ConsultasController::obtenerMensajes - ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function contarPendientes() {
        try {
            $userData = $this->auth->requireAdmin();

            $totalPendientes = $this->consultaModel->getTotalPendientes();

            echo json_encode([
                'success' => true,
                'total' => $totalPendientes
            ]);

        } catch (Exception $e) {
            error_log("ConsultasController::contarPendientes - ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function obtenerConsultasConRespuestas() {
        try {
            $userData = $this->auth->requireAdmin();

            // Obtener consultas con mensajes no leídos del cliente
            $consultasConMensajes = $this->mensajeModel->getConsultasConMensajesNoLeidos(true);

            // Enriquecer con información de la consulta
            foreach ($consultasConMensajes as &$item) {
                $consulta = $this->consultaModel->findById($item['consulta_id']);
                if ($consulta) {
                    $item['asunto'] = $consulta['asunto'];
                    $item['estado'] = $consulta['estado'];

                    // Obtener información del cliente
                    if ($consulta['cliente_id']) {
                        $clienteModel = new Cliente();
                        $cliente = $clienteModel->findById($consulta['cliente_id']);
                        if ($cliente) {
                            $item['cliente_razon_social'] = $cliente['razon_social'] ?? 'N/A';
                        }
                    }
                }
            }

            echo json_encode([
                'success' => true,
                'data' => $consultasConMensajes
            ]);

        } catch (Exception $e) {
            error_log("ConsultasController::obtenerConsultasConRespuestas - ERROR: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    private function findById($id) {
        $consultas = $this->consultaModel->getAll();
        foreach ($consultas as $consulta) {
            if ($consulta['id'] == $id) {
                return $consulta;
            }
        }
        return null;
    }
}
?>
