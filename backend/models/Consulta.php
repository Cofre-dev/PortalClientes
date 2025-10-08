<?php

require_once __DIR__ . '/../config/Database.php';

class Consulta {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear($clienteId, $userId, $asunto, $mensaje) {
        try {
            $data = [
                'cliente_id' => $clienteId,
                'user_id' => $userId,
                'asunto' => $asunto,
                'mensaje' => $mensaje,
                'estado' => 'pendiente', // pendiente, en_proceso, resuelto
                'fecha' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->db->insert('consultas', $data);
        } catch (Exception $e) {
            error_log("Error creando consulta: " . $e->getMessage());
            return false;
        }
    }

    public function getAll() {
        return $this->db->select('consultas', '*', '', [], 'fecha DESC');
    }

    public function getPendientes() {
        return $this->db->select('consultas', '*', 'estado = ?', ['pendiente'], 'fecha DESC');
    }

    public function getPorCliente($clienteId) {
        return $this->db->select('consultas', '*', 'cliente_id = ?', [$clienteId], 'fecha DESC');
    }

    public function getTotalPendientes() {
        $pendientes = $this->getPendientes();
        return count($pendientes);
    }

    public function findById($id) {
        $consultas = $this->db->select('consultas', '*', 'id = ?', [$id]);
        return $consultas ? $consultas[0] : null;
    }

    public function cambiarEstado($id, $nuevoEstado) {
        try {
            $updated = $this->db->update('consultas', ['estado' => $nuevoEstado], 'id = ?', [$id]);
            return $updated > 0;
        } catch (Exception $e) {
            error_log("Error cambiando estado de consulta: " . $e->getMessage());
            return false;
        }
    }
}
?>
