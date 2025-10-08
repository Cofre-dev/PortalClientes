<?php

require_once __DIR__ . '/../config/Database.php';

class Documento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByCategoria($categoriaId, $clienteId = null) {
        if ($clienteId !== null) {
            return $this->db->select('documentos', '*', 'categoria_id = :categoria_id AND cliente_id = :cliente_id',
                [':categoria_id' => $categoriaId, ':cliente_id' => $clienteId], 'fecha_subida DESC');
        } else {
            return $this->db->select('documentos', '*', 'categoria_id = :categoria_id',
                [':categoria_id' => $categoriaId], 'fecha_subida DESC');
        }
    }

    public function findById($id) {
        return $this->db->selectOne('documentos', '*', 'id = :id', [':id' => $id]);
    }

    public function getPorCategoria($categoriaId, $clienteId = null) {
        return $this->findByCategoria($categoriaId, $clienteId);
    }

    public function getTodos($clienteId = null) {
        if ($clienteId !== null) {
            return $this->db->select('documentos', '*', 'cliente_id = :cliente_id',
                [':cliente_id' => $clienteId], 'fecha_subida DESC');
        } else {
            return $this->db->select('documentos', '*', '', [], 'fecha_subida DESC');
        }
    }

    public function getPorRuta($ruta) {
        return $this->db->selectOne('documentos', '*', 'ruta_archivo = :ruta', [':ruta' => $ruta]);
    }

    public function create($docData) {
        try {
            $docData['fecha_subida'] = date('Y-m-d H:i:s');
            $docData['created_at'] = date('Y-m-d H:i:s');
            $docData['updated_at'] = date('Y-m-d H:i:s');

            return $this->db->insert('documentos', $docData);
        } catch (Exception $e) {
            error_log("Error creating document: " . $e->getMessage());
            return false;
        }
    }

    public function update($id, $docData) {
        try {
            $docData['updated_at'] = date('Y-m-d H:i:s');
            return $this->db->update('documentos', $docData, 'id = :id', [':id' => $id]);
        } catch (Exception $e) {
            error_log("Error updating document: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $document = $this->findById($id);
            if ($document) {
                $deleted = $this->db->delete('documentos', 'id = :id', [':id' => $id]);
                return $deleted ? $document : false;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error deleting document: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalCount() {
        $result = $this->db->selectOne('documentos', 'COUNT(*) as total');
        return $result ? $result['total'] : 0;
    }

    public function getByClienteId($clienteId) {
        return $this->db->select('documentos', '*', 'cliente_id = ?', [$clienteId], 'fecha_subida DESC');
    }

    public function obtenerDocumentosPorClienteYCategoria($clienteId, $categoriaId) {
        $documentos = $this->db->select(
            'documentos',
            '*',
            'cliente_id = :cliente_id AND categoria_id = :categoria_id',
            [':cliente_id' => $clienteId, ':categoria_id' => $categoriaId],
            'created_at DESC'
        );

        // Enriquecer con información adicional
        foreach ($documentos as &$documento) {
            // Agregar información de la categoría
            $categoria = $this->db->selectOne(
                'tipos_documento',
                '*',
                'id = :id',
                [':id' => $categoriaId]
            );

            if ($categoria) {
                $documento['categoria_nombre'] = $categoria['nombre'];
                $documento['categoria_descripcion'] = $categoria['descripcion'];
            }

            // Agregar información del cliente
            $cliente = $this->db->selectOne(
                'clientes',
                '*',
                'id = :id',
                [':id' => $clienteId]
            );

            if ($cliente) {
                $documento['cliente_razon_social'] = $cliente['razon_social'];
                $documento['cliente_rut'] = $cliente['rut_empresa'];
            }
        }

        return $documentos;
    }
}