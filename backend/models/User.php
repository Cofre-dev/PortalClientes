<?php

require_once __DIR__ . '/../config/Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findByUsername($username) {
        return $this->db->selectOne('usuarios', '*', 'username = :username', [':username' => $username]);
    }

    public function findByEmail($email) {
        return $this->db->selectOne('usuarios', '*', 'email = :email', [':email' => $email]);
    }

    public function findById($id) {
        return $this->db->selectOne('usuarios', '*', 'id = :id', [':id' => $id]);
    }

    public function create($userData) {
        try {
            // Verificar si ya existe el username o email
            if ($this->findByUsername($userData['username'])) {
                throw new Exception('El nombre de usuario ya existe');
            }

            if ($this->findByEmail($userData['email'])) {
                throw new Exception('El email ya está registrado');
            }

            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);

            $userId = $this->db->insert('usuarios', $userData);

            if ($userId) {
                return $userId; // esto lo que hace es retornar solo el ID, no el objeto completo
            }

            return false;

        } catch (Exception $e) {
            throw new Exception('Error al crear usuario: ' . $e->getMessage());
        }
    }

    public function update($id, $userData) {
        try {
            // Si hay una nueva contraseña, hacer hash
            if (isset($userData['password'])) {
                $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            }

            $updated = $this->db->update('usuarios', $userData, 'id = ?', [$id]);

            if ($updated) {
                return $this->findById($id);
            }

            return false;

        } catch (Exception $e) {
            throw new Exception('Error al actualizar usuario: ' . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            return $this->db->delete('usuarios', 'id = ?', [$id]);
        } catch (Exception $e) {
            throw new Exception('Error al eliminar usuario: ' . $e->getMessage());
        }
    }

    public function verifyPassword($user, $password) {
        return password_verify($password, $user['password']);
    }

    public function getAll($filters = []) {
        $where = '';
        $params = [];

        if (isset($filters['role'])) {
            $where = 'role = ?';
            $params[] = $filters['role'];
        }

        if (isset($filters['is_active'])) {
            $where .= ($where ? ' AND ' : '') . 'is_active = ?';
            $params[] = $filters['is_active'];
        }

        return $this->db->select('usuarios', '*', $where, $params, 'created_at DESC');
    }

    public function getTotalCount($filters = []) {
        $where = '';
        $params = [];

        if (isset($filters['role'])) {
            $where = 'role = ?';
            $params[] = $filters['role'];
        }

        if (isset($filters['is_active'])) {
            $where .= ($where ? ' AND ' : '') . 'is_active = ?';
            $params[] = $filters['is_active'];
        }

        $result = $this->db->selectOne('usuarios', 'COUNT(*) as total', $where, $params);
        return $result ? $result['total'] : 0;
    }

    public function setActive($id, $isActive) {
        return $this->update($id, ['is_active' => $isActive ? 1 : 0]);
    }

    public function getClientesWithUsers() {
        $sql = "
            SELECT
                u.id as user_id,
                u.username,
                u.email,
                u.is_active,
                u.created_at,
                c.id as cliente_id,
                c.razon_social,
                c.rut_empresa,
                c.telefono,
                c.direccion
            FROM usuarios u
            LEFT JOIN clientes c ON u.id = c.user_id
            WHERE u.role = 'cliente'
            ORDER BY u.created_at DESC
        ";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
}