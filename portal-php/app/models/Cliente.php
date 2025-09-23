<?php

require_once __DIR__ . '/../config/Database.php';

class Cliente {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUserId($userId) {
        $clientes = $this->db->getTable('clientes');
        foreach ($clientes as $cliente) {
            if ($cliente['user_id'] == $userId) {
                return $cliente;
            }
        }
        return null;
    }

    public function findById($id) {
        $clientes = $this->db->getTable('clientes');
        foreach ($clientes as $cliente) {
            if ($cliente['id'] == $id) {
                return $cliente;
            }
        }
        return null;
    }

    public function create($clienteData) {
        $clientes = $this->db->getTable('clientes');

        $clienteData['id'] = $this->db->getNextId('clientes');
        $clienteData['created_at'] = date('Y-m-d H:i:s');

        $clientes[] = $clienteData;
        return $this->db->saveTable('clientes', $clientes) ? $clienteData['id'] : false;
    }

    public function getAll() {
        return $this->db->getTable('clientes');
    }

    public function getTotalCount() {
        return count($this->db->getTable('clientes'));
    }
}