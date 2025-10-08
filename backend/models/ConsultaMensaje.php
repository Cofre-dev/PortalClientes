<?php

require_once __DIR__ . '/../config/Database.php';

class ConsultaMensaje {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function crear($consultaId, $userId, $mensaje, $esAdmin = false) {
        try {
            $data = [
                'consulta_id' => $consultaId,
                'user_id' => $userId,
                'mensaje' => $mensaje,
                'es_admin' => $esAdmin ? 1 : 0,
                'leido' => 0,
                'fecha' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ];

            return $this->db->insert('consultas_mensajes', $data);
        } catch (Exception $e) {
            error_log("Error creando mensaje de consulta: " . $e->getMessage());
            return false;
        }
    }

    public function getPorConsulta($consultaId) {
        return $this->db->select('consultas_mensajes', '*', 'consulta_id = ?', [$consultaId], 'fecha ASC');
    }

    public function getTotalPorConsulta($consultaId) {
        $mensajes = $this->getPorConsulta($consultaId);
        return count($mensajes);
    }

    public function marcarComoLeido($consultaId, $esAdmin = false) {
        try {
            // Marcar todos los mensajes de la consulta como leídos
            $condition = $esAdmin ? 'consulta_id = ? AND es_admin = 0' : 'consulta_id = ? AND es_admin = 1';
            $updated = $this->db->update('consultas_mensajes', ['leido' => 1], $condition, [$consultaId]);
            return $updated > 0;
        } catch (Exception $e) {
            error_log("Error marcando mensajes como leídos: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalNoLeidos($consultaId, $esAdmin = false) {
        $condition = $esAdmin ? 'consulta_id = ? AND es_admin = 0 AND leido = 0' : 'consulta_id = ? AND es_admin = 1 AND leido = 0';
        $mensajes = $this->db->select('consultas_mensajes', '*', $condition, [$consultaId]);
        return count($mensajes);
    }

    public function getConsultasConMensajesNoLeidos($esAdmin = true) {
        // Obtener todos los mensajes no leídos
        $condition = $esAdmin ? 'es_admin = 0 AND leido = 0' : 'es_admin = 1 AND leido = 0';
        $mensajes = $this->db->select('consultas_mensajes', '*', $condition, []);

        // Agrupar por consulta_id
        $consultasConMensajes = [];
        foreach ($mensajes as $mensaje) {
            $consultaId = $mensaje['consulta_id'];
            if (!isset($consultasConMensajes[$consultaId])) {
                $consultasConMensajes[$consultaId] = [
                    'consulta_id' => $consultaId,
                    'total_no_leidos' => 0
                ];
            }
            $consultasConMensajes[$consultaId]['total_no_leidos']++;
        }

        return array_values($consultasConMensajes);
    }
}
?>
