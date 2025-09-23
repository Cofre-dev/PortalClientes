<?php

require_once __DIR__ . '/../config/Database.php';

class TipoDocumento {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getAll() {
        return $this->db->getTable('tipos_documento');
    }

    public function findById($id) {
        $tipos = $this->db->getTable('tipos_documento');
        foreach ($tipos as $tipo) {
            if ($tipo['id'] == $id) {
                return $tipo;
            }
        }
        return null;
    }

    public function create($tipoData) {
        $tipos = $this->db->getTable('tipos_documento');

        $tipoData['id'] = $this->db->getNextId('tipos_documento');
        $tipoData['total_documentos'] = 0;

        $tipos[] = $tipoData;
        return $this->db->saveTable('tipos_documento', $tipos) ? $tipoData['id'] : false;
    }

    public function incrementDocumentCount($tipoId) {
        $tipos = $this->db->getTable('tipos_documento');

        for ($i = 0; $i < count($tipos); $i++) {
            if ($tipos[$i]['id'] == $tipoId) {
                $tipos[$i]['total_documentos']++;
                break;
            }
        }

        return $this->db->saveTable('tipos_documento', $tipos);
    }

    public function decrementDocumentCount($tipoId) {
        $tipos = $this->db->getTable('tipos_documento');

        for ($i = 0; $i < count($tipos); $i++) {
            if ($tipos[$i]['id'] == $tipoId) {
                $tipos[$i]['total_documentos'] = max(0, $tipos[$i]['total_documentos'] - 1);
                break;
            }
        }

        return $this->db->saveTable('tipos_documento', $tipos);
    }
}