<?php

require_once __DIR__ . '/../config/Database.php';

class TipoDocumento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // CRUD básico con soporte para cliente_id
    public function create($data) {
        try {
            // Validar campos requeridos
            if (empty($data['nombre']) || empty($data['cliente_id'])) {
                throw new Exception('Nombre y cliente_id son requeridos');
            }

            // Verificar que no exista una categoría con el mismo nombre para este cliente
            $existing = $this->db->select(
                'tipos_documento',
                '*',
                'nombre = ? AND cliente_id = ?',
                [$data['nombre'], $data['cliente_id']]
            );

            if (!empty($existing)) {
                throw new Exception('Ya existe una categoría con este nombre para este cliente');
            }

            // Generar código automáticamente si no se proporciona
            if (empty($data['codigo'])) {
                $data['codigo'] = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $data['nombre']), 0, 10));
            }

            $categoriaData = [
                'cliente_id' => $data['cliente_id'],
                'nombre' => trim($data['nombre']),
                'codigo' => $data['codigo'],
                'descripcion' => $data['descripcion'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $id = $this->db->insert('tipos_documento', $categoriaData);
            return $id;

        } catch (Exception $e) {
            error_log("Error en TipoDocumento::create - " . $e->getMessage());
            throw $e;
        }
    }

    public function update($id, $data) {
        try {
            $updateData = [];

            if (isset($data['nombre'])) {
                $updateData['nombre'] = trim($data['nombre']);
            }
            if (isset($data['codigo'])) {
                $updateData['codigo'] = $data['codigo'];
            }
            if (isset($data['descripcion'])) {
                $updateData['descripcion'] = $data['descripcion'];
            }
            if (isset($data['is_active'])) {
                $updateData['is_active'] = $data['is_active'];
            }

            $updateData['updated_at'] = date('Y-m-d H:i:s');

            $updated = $this->db->update('tipos_documento', $updateData, 'id = ?', [$id]);
            return $updated > 0;

        } catch (Exception $e) {
            error_log("Error en TipoDocumento::update - " . $e->getMessage());
            throw new Exception('Error al actualizar categoría: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            // Verificar si hay documentos asociados
            $documentos = $this->db->select('documentos', '*', 'categoria_id = ?', [$id]);
            if (!empty($documentos)) {
                throw new Exception('No se puede eliminar la categoría porque tiene documentos asociados');
            }

            $deleted = $this->db->delete('tipos_documento', 'id = ?', [$id]);
            return $deleted > 0;
        } catch (Exception $e) {
            error_log("Error en TipoDocumento::delete - " . $e->getMessage());
            throw $e;
        }
    }

    public function findById($id) {
        $results = $this->db->select('tipos_documento', '*', 'id = ?', [$id]);
        return !empty($results) ? $results[0] : null;
    }

    public function findByClienteId($clienteId) {
        try {
            return $this->db->select(
                'tipos_documento',
                '*',
                'cliente_id = ?',
                [$clienteId],
                'nombre ASC'
            );
        } catch (Exception $e) {
            error_log("Error en TipoDocumento::findByClienteId - " . $e->getMessage());
            return [];
        }
    }

    public function getActiveByClienteId($clienteId) {
        try {
            return $this->db->select(
                'tipos_documento',
                '*',
                'cliente_id = ? AND is_active = ?',
                [$clienteId, true],
                'nombre ASC'
            );
        } catch (Exception $e) {
            error_log("Error en TipoDocumento::getActiveByClienteId - " . $e->getMessage());
            return [];
        }
    }

    public function getAll() {
        return $this->db->select('tipos_documento', '*', '', [], 'nombre ASC');
    }

    public function getTotalCountByCliente($clienteId) {
        $results = $this->db->select('tipos_documento', '*', 'cliente_id = ?', [$clienteId]);
        return count($results);
    }

    // Métodos de compatibilidad con código existente
    public function findByCodigo($codigo) {
        $results = $this->db->select('tipos_documento', '*', 'codigo = ?', [$codigo]);
        return !empty($results) ? $results[0] : null;
    }

    public function setActive($id, $isActive) {
        return $this->update($id, ['is_active' => $isActive ? 1 : 0]);
    }

    public function getTotalCount($activeOnly = true) {
        $where = $activeOnly ? 'is_active = ?' : '';
        $params = $activeOnly ? [true] : [];
        $results = $this->db->select('tipos_documento', '*', $where, $params);
        return count($results);
    }
}
?>