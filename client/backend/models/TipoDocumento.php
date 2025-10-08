<?php

require_once __DIR__ . '/../config/Database.php';

class TipoDocumento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findById($id) {
        return $this->db->selectOne('tipos_documento', '*', 'id = :id', [':id' => $id]);
    }

    public function findByCodigo($codigo) {
        return $this->db->selectOne('tipos_documento', '*', 'codigo = :codigo', [':codigo' => $codigo]);
    }

    public function getTodos() {
        return $this->db->getTotalDocumentosPorCategoria();
    }

    public function create($tipoData) {
        try {
            // Verificar si ya existe el código
            if ($this->findByCodigo($tipoData['codigo'])) {
                throw new Exception('El código de tipo de documento ya existe');
            }

            // Insertar tipo de documento
            $tipoId = $this->db->insert('tipos_documento', $tipoData);

            if ($tipoId) {
                return $this->findById($tipoId);
            }

            return false;

        } catch (Exception $e) {
            throw new Exception('Error al crear tipo de documento: ' . $e->getMessage());
        }
    }

    public function update($id, $tipoData) {
        try {
            // Verificar si el código ya existe en otro tipo
            if (isset($tipoData['codigo'])) {
                $existingTipo = $this->findByCodigo($tipoData['codigo']);
                if ($existingTipo && $existingTipo['id'] != $id) {
                    throw new Exception('El código de tipo de documento ya existe');
                }
            }

            $updated = $this->db->update('tipos_documento', $tipoData, 'id = ?', [$id]);

            if ($updated) {
                return $this->findById($id);
            }

            return false;

        } catch (Exception $e) {
            throw new Exception('Error al actualizar tipo de documento: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            // Verificar si hay documentos asociados
            $documentos = $this->db->selectOne('documentos', 'COUNT(*) as total', 'categoria_id = ?', [$id]);

            if ($documentos && $documentos['total'] > 0) {
                throw new Exception('No se puede eliminar el tipo de documento porque tiene documentos asociados');
            }

            return $this->db->delete('tipos_documento', 'id = ?', [$id]);
        } catch (Exception $e) {
            throw new Exception('Error al eliminar tipo de documento: ' . $e->getMessage());
        }
    }

    public function setActive($id, $isActive) {
        return $this->update($id, ['is_active' => $isActive ? 1 : 0]);
    }

    public function getAll($activeOnly = true) {
        $where = $activeOnly ? 'is_active = TRUE' : '';
        return $this->db->select('tipos_documento', '*', $where, [], 'nombre ASC');
    }

    public function getAllWithDocumentCount($activeOnly = true, $clienteId = null) {
        $sql = "
            SELECT
                td.id,
                td.nombre,
                td.codigo,
                td.descripcion,
                td.is_active,
                td.created_at,
                td.updated_at,
                COUNT(d.id) as total_documentos
            FROM tipos_documento td
            LEFT JOIN documentos d ON td.id = d.categoria_id
        ";

        $params = [];
        $conditions = [];

        if ($activeOnly) {
            $conditions[] = 'td.is_active = TRUE';
        }

        if ($clienteId) {
            $conditions[] = '(d.cliente_id = ? OR d.cliente_id IS NULL)';
            $params[] = $clienteId;
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= "
            GROUP BY td.id, td.nombre, td.codigo, td.descripcion, td.is_active, td.created_at, td.updated_at
            ORDER BY td.nombre ASC
        ";

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getTotalCount($activeOnly = true) {
        $where = $activeOnly ? 'is_active = TRUE' : '';
        $result = $this->db->selectOne('tipos_documento', 'COUNT(*) as total', $where);
        return $result ? $result['total'] : 0;
    }

    public function getDocumentsByCategoria($categoriaId, $clienteId = null, $limit = null) {
        $sql = "
            SELECT
                d.*,
                td.nombre as categoria_nombre,
                td.codigo as categoria_codigo,
                c.razon_social as cliente_nombre
            FROM documentos d
            JOIN tipos_documento td ON d.categoria_id = td.id
            JOIN clientes c ON d.cliente_id = c.id
            WHERE d.categoria_id = ?
        ";

        $params = [$categoriaId];

        if ($clienteId) {
            $sql .= " AND d.cliente_id = ?";
            $params[] = $clienteId;
        }

        $sql .= " ORDER BY d.fecha_subida DESC";

        if ($limit) {
            $sql .= " LIMIT ?";
            $params[] = $limit;
        }

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    public function getStats() {
        $sql = "
            SELECT
                COUNT(*) as total_categorias,
                COUNT(CASE WHEN is_active = TRUE THEN 1 END) as categorias_activas,
                COUNT(CASE WHEN is_active = FALSE THEN 1 END) as categorias_inactivas
            FROM tipos_documento
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }

    public function getMostUsedCategories($limit = 5) {
        $sql = "
            SELECT
                td.id,
                td.nombre,
                td.codigo,
                COUNT(d.id) as total_documentos
            FROM tipos_documento td
            LEFT JOIN documentos d ON td.id = d.categoria_id
            WHERE td.is_active = TRUE
            GROUP BY td.id, td.nombre, td.codigo
            HAVING total_documentos > 0
            ORDER BY total_documentos DESC
            LIMIT ?
        ";

        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }

    // Método para compatibilidad con el controlador existente
    public function incrementDocumentCount($tipoId) {
        // Ya no es necesario con MySQL porque usamos COUNT en las consultas
        return true;
    }

    public function decrementDocumentCount($tipoId) {
        // Ya no es necesario con MySQL porque usamos COUNT en las consultas
        return true;
    }
}