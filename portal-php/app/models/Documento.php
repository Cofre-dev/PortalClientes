<?php

require_once __DIR__ . '/../config/Database.php';

class Documento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByCategoria($categoriaId, $clienteId = null) {
        $documentos = $this->db->getTable('documentos');
        $filtered = [];

        foreach ($documentos as $doc) {
            if ($doc['categoria_id'] == $categoriaId) {
                if ($clienteId === null || $doc['cliente_id'] == $clienteId) {
                    $filtered[] = $doc;
                }
            }
        }

        return $filtered;
    }

    public function findById($id) {
        $documentos = $this->db->getTable('documentos');
        foreach ($documentos as $doc) {
            if ($doc['id'] == $id) {
                return $doc;
            }
        }
        return null;
    }

    public function create($docData) {
        $documentos = $this->db->getTable('documentos');

        $docData['id'] = $this->db->getNextId('documentos');
        $docData['fecha_subida'] = date('Y-m-d H:i:s');

        $documentos[] = $docData;
        return $this->db->saveTable('documentos', $documentos) ? $docData['id'] : false;
    }

    public function delete($id) {
        $documentos = $this->db->getTable('documentos');

        for ($i = 0; $i < count($documentos); $i++) {
            if ($documentos[$i]['id'] == $id) {
                $doc = $documentos[$i];
                array_splice($documentos, $i, 1);
                $this->db->saveTable('documentos', $documentos);
                return $doc;
            }
        }

        return false;
    }

    public function getTotalCount() {
        return count($this->db->getTable('documentos'));
    }

    public function getByClienteId($clienteId) {
        $documentos = $this->db->getTable('documentos');
        $filtered = [];

        foreach ($documentos as $doc) {
            if ($doc['cliente_id'] == $clienteId) {
                $filtered[] = $doc;
            }
        }

        return $filtered;
    }
}