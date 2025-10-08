<?php

require_once __DIR__ . '/../config/Database.php';

class Transaccion {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function registrar($tipo, $documentoId = null, $userId = null, $clienteId = null, $detalles = null) {
        try {
            $data = [
                'tipo' => $tipo, // 'upload', 'download', 'delete', 'rename'
                'documento_id' => $documentoId,
                'user_id' => $userId,
                'cliente_id' => $clienteId,
                'detalles' => $detalles,
                'fecha' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            return $this->db->insert('transacciones', $data);
        } catch (Exception $e) {
            error_log("Error registrando transacción: " . $e->getMessage());
            return false;
        }
    }

    public function getTodas() {
        return $this->db->select('transacciones', '*', '', [], 'fecha DESC');
    }

    public function getPorCliente($clienteId) {
        return $this->db->select('transacciones', '*', 'cliente_id = ?', [$clienteId], 'fecha DESC');
    }

    public function getTotalCount() {
        $transacciones = $this->db->select('transacciones', '*');
        return count($transacciones);
    }
}
?>
