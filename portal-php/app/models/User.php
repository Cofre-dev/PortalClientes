<?php

require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUsername($username) {
        $users = $this->db->getTable('usuarios');
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }
        return null;
    }

    public function findById($id) {
        $users = $this->db->getTable('usuarios');
        foreach ($users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }

    public function create($userData) {
        $users = $this->db->getTable('usuarios');

        // Check if username already exists
        if ($this->findByUsername($userData['username'])) {
            return false;
        }

        $userData['id'] = $this->db->getNextId('usuarios');
        $userData['created_at'] = date('Y-m-d H:i:s');
        $userData['is_active'] = true;
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);

        $users[] = $userData;
        return $this->db->saveTable('usuarios', $users) ? $userData['id'] : false;
    }

    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password']);
    }

    public function getAll() {
        return $this->db->getTable('usuarios');
    }

    public function getTotalCount() {
        return count($this->db->getTable('usuarios'));
    }
}