<?php

require_once __DIR__ . '/../config/Database.php';

class Cliente {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /*Funciones para validar datos y que no se dupliquen */
    public function findById($id) {
        return $this->db->selectOne('clientes', '*', 'id = :id', [':id' => $id]);
    }

    public function findByUserId($userId) {
        return $this->db->selectOne('clientes', '*', 'user_id = :user_id', [':user_id' => $userId]);
    }

    public function findByRut($rut) {
        return $this->db->selectOne('clientes', '*', 'rut_empresa = :rut', [':rut' => $rut]);
    }

    public function findByCompany($company) {
        return $this->db->selectOne('clientes','*','razon_social = :company', [':company' => $company]);
    }

    public function create($clienteData) {
        try {
            // Verificar si ya existe el RUT
            if ($this->findByRut($clienteData['rut_empresa'])) {
                throw new Exception('El RUT de empresa ya está registrado');
            }

            // Insertar cliente
            $clienteId = $this->db->insert('clientes', $clienteData);

            if ($clienteId) {
                return $clienteId; // Retornar solo el ID, no el objeto completo
            }

            return false;

        } catch (Exception $e) {
            throw new Exception('Error al crear cliente: ' . $e->getMessage());
        }
    }

    public function update($id, $clienteData) {
        try {
            // Verificar si el RUT ya existe en otro cliente
            if (isset($clienteData['rut_empresa'])) {
                $existingCliente = $this->findByRut($clienteData['rut_empresa']);
                if ($existingCliente && $existingCliente['id'] != $id) {
                    throw new Exception('El RUT de empresa ya está registrado');
                }
            }

            $updated = $this->db->update('clientes', $clienteData, 'id = ?', [$id]);

            if ($updated) {
                return $this->findById($id);
            }

            return false;

        } catch (Exception $e) {
            throw new Exception('Error al actualizar cliente: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            return $this->db->delete('clientes', 'id = ?', [$id]);
        } catch (Exception $e) {
            throw new Exception('Error al eliminar cliente: ' . $e->getMessage());
        }
    }

    public function getAll() {
        return $this->db->select('clientes', '*', '', [], 'created_at DESC');
    }

    public function getAllWithUsers() {
        try {
            // Primero verificar qué columnas existen realmente
            $checkColumns = $this->db->query("DESCRIBE clientes");
            $columns = $checkColumns->fetchAll();
            $columnNames = array_column($columns, 'Field');

            // Adaptar el SQL según las columnas que existan
            $empresaColumn = in_array('empresa', $columnNames) ? 'c.empresa' : 'c.razon_social';
            $nombreColumn = in_array('nombre_cliente', $columnNames) ? 'c.nombre_cliente' : 'c.nombre_cliente';
            $correoColumn = in_array('correo_contacto', $columnNames) ? 'c.correo_contacto' : 'c.email';

            // SQL simplificado sin JOINs problemáticos
            $sql = "
                SELECT
                    c.id,
                    {$empresaColumn} as razon_social,
                    c.rut_empresa,
                    {$correoColumn} as cliente_email,
                    c.telefono,
                    c.direccion,
                    c.created_at,
                    c.updated_at,
                    NULL as user_id,
                    NULL as username,
                    NULL as user_email,
                    1 as is_active,
                    (SELECT COUNT(*) FROM documentos d WHERE d.cliente_id = c.id) as total_documentos
                FROM clientes c
                ORDER BY c.created_at DESC
            ";

            $stmt = $this->db->query($sql);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAllWithUsers: " . $e->getMessage());
            // Fallback: consulta simple sin conteos
            try {
                $sql = "SELECT * FROM clientes ORDER BY created_at DESC";
                $stmt = $this->db->query($sql);
                $clientes = $stmt->fetchAll();

                // Añadir campos faltantes manualmente
                foreach ($clientes as &$cliente) {
                    $cliente['total_documentos'] = 0;
                    $cliente['user_id'] = null;
                    $cliente['username'] = null;
                    $cliente['user_email'] = null;
                    $cliente['is_active'] = 1;

                    // Mapear nombres de campos si es necesario
                    if (isset($cliente['empresa']) && !isset($cliente['razon_social'])) {
                        $cliente['razon_social'] = $cliente['empresa'];
                    }
                    if (isset($cliente['correo_contacto']) && !isset($cliente['cliente_email'])) {
                        $cliente['cliente_email'] = $cliente['correo_contacto'];
                    }
                }

                return $clientes;
            } catch (Exception $fallbackError) {
                error_log("Error in getAllWithUsers fallback: " . $fallbackError->getMessage());
                return [];
            }
        }
    }

    public function getTotalCount() {
        $result = $this->db->selectOne('clientes', 'COUNT(*) as total');
        return $result ? $result['total'] : 0;
    }

    public function getClienteWithDocumentStats($clienteId) {
        $sql = "
            SELECT
                c.*,
                u.username,
                u.email as user_email,
                u.is_active,
                COUNT(d.id) as total_documentos,
                COUNT(CASE WHEN d.subido_por_cliente = 1 THEN 1 END) as documentos_subidos_cliente,
                COUNT(CASE WHEN d.subido_por_cliente = 0 THEN 1 END) as documentos_subidos_consultora
            FROM clientes c
            LEFT JOIN usuarios u ON c.user_id = u.id
            LEFT JOIN documentos d ON c.id = d.cliente_id
            WHERE c.id = ?
            GROUP BY c.id
        ";

        $stmt = $this->db->query($sql, [$clienteId]);
        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function getDocumentosPorCategoria($clienteId) {
        $sql = "
            SELECT
                td.id,
                td.nombre,
                td.codigo,
                td.descripcion,
                COUNT(d.id) as total_documentos
            FROM tipos_documento td
            LEFT JOIN documentos d ON td.id = d.categoria_id AND d.cliente_id = ?
            WHERE td.is_active = TRUE
            GROUP BY td.id, td.nombre, td.codigo, td.descripcion
            ORDER BY td.nombre
        ";

        $stmt = $this->db->query($sql, [$clienteId]);
        return $stmt->fetchAll();
    }

    public function getRecentActivity($clienteId, $limit = 10) {
        $sql = "
            SELECT
                d.id,
                d.nombre_archivo,
                d.fecha_subida,
                d.subido_por_cliente,
                td.nombre as categoria_nombre
            FROM documentos d
            JOIN tipos_documento td ON d.categoria_id = td.id
            WHERE d.cliente_id = ?
            ORDER BY d.fecha_subida DESC
            LIMIT ?
        ";

        $stmt = $this->db->query($sql, [$clienteId, $limit]);
        return $stmt->fetchAll();
    }
}