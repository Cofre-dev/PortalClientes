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
            // JsonDatabase->delete() retorna el número de filas eliminadas
            $deleted = $this->db->delete('clientes', 'id = ?', [$id]);
            return $deleted > 0;
        } catch (Exception $e) {
            error_log("Error en Cliente::delete - " . $e->getMessage());
            throw new Exception('Error al eliminar cliente: ' . $e->getMessage());
        }
    }

    public function getAll() {
        return $this->db->select('clientes', '*', '', [], 'created_at DESC');
    }

    public function getAllWithUsers() {
        try {
            // Usar select en lugar de query para compatibilidad con JsonDatabase
            $clientes = $this->db->select('clientes', '*', '', [], 'created_at DESC');

            if (empty($clientes)) {
                return [];
            }

            // Obtener todos los usuarios para hacer el JOIN manualmente
            $usuarios = $this->db->select('usuarios', '*', '', []);
            $usuariosMap = [];
            foreach ($usuarios as $usuario) {
                $usuariosMap[$usuario['id']] = $usuario;
            }

            // Obtener conteo de documentos por cliente
            $documentos = $this->db->select('documentos', '*', '', []);
            $documentosCount = [];
            foreach ($documentos as $doc) {
                if (isset($doc['cliente_id'])) {
                    if (!isset($documentosCount[$doc['cliente_id']])) {
                        $documentosCount[$doc['cliente_id']] = 0;
                    }
                    $documentosCount[$doc['cliente_id']]++;
                }
            }

            // Procesar cada cliente
            $resultado = [];
            foreach ($clientes as $cliente) {
                // Agregar información del usuario si existe
                if (isset($cliente['user_id']) && isset($usuariosMap[$cliente['user_id']])) {
                    $usuario = $usuariosMap[$cliente['user_id']];
                    $cliente['username'] = $usuario['username'];
                    $cliente['user_email'] = $usuario['email'];
                    $cliente['is_active'] = $usuario['is_active'];
                } else {
                    $cliente['username'] = null;
                    $cliente['user_email'] = null;
                    $cliente['is_active'] = 1;
                }

                // Agregar conteo de documentos
                $cliente['total_documentos'] = $documentosCount[$cliente['id']] ?? 0;

                // Normalizar campos para compatibilidad
                if (!isset($cliente['razon_social']) && isset($cliente['empresa'])) {
                    $cliente['razon_social'] = $cliente['empresa'];
                }
                if (!isset($cliente['email']) && isset($cliente['correo_contacto'])) {
                    $cliente['email'] = $cliente['correo_contacto'];
                }
                if (!isset($cliente['cliente_email'])) {
                    $cliente['cliente_email'] = $cliente['email'] ?? $cliente['correo_contacto'] ?? null;
                }

                $resultado[] = $cliente;
            }

            return $resultado;

        } catch (Exception $e) {
            error_log("Error in getAllWithUsers: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());

            // Fallback simple
            try {
                $clientes = $this->db->select('clientes', '*', '', [], 'created_at DESC');

                // Agregar campos mínimos
                foreach ($clientes as &$cliente) {
                    if (!isset($cliente['total_documentos'])) $cliente['total_documentos'] = 0;
                    if (!isset($cliente['username'])) $cliente['username'] = null;
                    if (!isset($cliente['is_active'])) $cliente['is_active'] = 1;
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
        try {
            // Obtener cliente
            $cliente = $this->findById($clienteId);
            if (!$cliente) {
                return null;
            }

            // Obtener usuario si existe
            if (isset($cliente['user_id']) && $cliente['user_id']) {
                $usuarios = $this->db->select('usuarios', '*', 'id = ?', [$cliente['user_id']]);
                if (!empty($usuarios)) {
                    $usuario = $usuarios[0];
                    $cliente['username'] = $usuario['username'];
                    $cliente['user_email'] = $usuario['email'];
                    $cliente['is_active'] = $usuario['is_active'];
                }
            }

            // Contar documentos
            $documentos = $this->db->select('documentos', '*', 'cliente_id = ?', [$clienteId]);
            $cliente['total_documentos'] = count($documentos);

            $subidosCliente = 0;
            $subidosConsultora = 0;
            foreach ($documentos as $doc) {
                if (isset($doc['subido_por_cliente']) && $doc['subido_por_cliente']) {
                    $subidosCliente++;
                } else {
                    $subidosConsultora++;
                }
            }

            $cliente['documentos_subidos_cliente'] = $subidosCliente;
            $cliente['documentos_subidos_consultora'] = $subidosConsultora;

            return $cliente;

        } catch (Exception $e) {
            error_log("Error in getClienteWithDocumentStats: " . $e->getMessage());
            return null;
        }
    }

    public function getDocumentosPorCategoria($clienteId) {
        try {
            // Obtener tipos de documento activos
            $tipos = $this->db->select('tipos_documento', '*', 'is_active = ?', [true]);

            // Obtener documentos del cliente
            $documentos = $this->db->select('documentos', '*', 'cliente_id = ?', [$clienteId]);

            // Contar por categoría
            $conteos = [];
            foreach ($documentos as $doc) {
                $catId = $doc['categoria_id'];
                if (!isset($conteos[$catId])) {
                    $conteos[$catId] = 0;
                }
                $conteos[$catId]++;
            }

            // Construir resultado
            $resultado = [];
            foreach ($tipos as $tipo) {
                $tipo['total_documentos'] = $conteos[$tipo['id']] ?? 0;
                $resultado[] = $tipo;
            }

            return $resultado;

        } catch (Exception $e) {
            error_log("Error in getDocumentosPorCategoria: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentActivity($clienteId, $limit = 10) {
        try {
            // Obtener documentos del cliente
            $documentos = $this->db->select('documentos', '*', 'cliente_id = ?', [$clienteId], 'created_at DESC');

            // Limitar resultados
            $documentos = array_slice($documentos, 0, $limit);

            // Obtener tipos de documento
            $tipos = $this->db->select('tipos_documento', '*');
            $tiposMap = [];
            foreach ($tipos as $tipo) {
                $tiposMap[$tipo['id']] = $tipo;
            }

            // Agregar nombre de categoría
            foreach ($documentos as &$doc) {
                if (isset($doc['categoria_id']) && isset($tiposMap[$doc['categoria_id']])) {
                    $doc['categoria_nombre'] = $tiposMap[$doc['categoria_id']]['nombre'];
                } else {
                    $doc['categoria_nombre'] = 'Sin categoría';
                }

                // Mapear fecha_subida si no existe
                if (!isset($doc['fecha_subida']) && isset($doc['created_at'])) {
                    $doc['fecha_subida'] = $doc['created_at'];
                }
            }

            return $documentos;

        } catch (Exception $e) {
            error_log("Error in getRecentActivity: " . $e->getMessage());
            return [];
        }
    }
}