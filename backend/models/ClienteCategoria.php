<?php

require_once __DIR__ . '/../config/JsonDatabase.php';

class ClienteCategoria {
    private $db;

    public function __construct() {
        $this->db = JsonDatabase::getInstance();
    }

    public function asignarCategoria($clienteId, $categoriaId) {
        // Verificar que no existe ya la relación
        $relaciones = $this->db->select('cliente_categorias');
        foreach ($relaciones as $relacion) {
            if ($relacion['cliente_id'] == $clienteId && $relacion['categoria_id'] == $categoriaId) {
                return false; // Ya existe
            }
        }

        return $this->db->insert('cliente_categorias', [
            'cliente_id' => $clienteId,
            'categoria_id' => $categoriaId
        ]);
    }

    public function eliminarCategoria($clienteId, $categoriaId) {
        return $this->db->delete(
            'cliente_categorias',
            'cliente_id = :cliente_id AND categoria_id = :categoria_id',
            [':cliente_id' => $clienteId, ':categoria_id' => $categoriaId]
        );
    }

    public function obtenerCategoriasPorCliente($clienteId) {
        $relaciones = $this->db->select(
            'cliente_categorias',
            '*',
            'cliente_id = :cliente_id',
            [':cliente_id' => $clienteId]
        );

        if (empty($relaciones)) {
            return [];
        }

        // Obtener información completa de las categorías
        $categorias = [];
        $tiposDocumento = $this->db->select('tipos_documento');

        foreach ($relaciones as $relacion) {
            foreach ($tiposDocumento as $tipo) {
                if ($tipo['id'] == $relacion['categoria_id'] && $tipo['is_active']) {
                    $categorias[] = $tipo;
                    break;
                }
            }
        }

        return $categorias;
    }

    public function obtenerClientesPorCategoria($categoriaId) {
        $relaciones = $this->db->select(
            'cliente_categorias',
            '*',
            'categoria_id = :categoria_id',
            [':categoria_id' => $categoriaId]
        );

        if (empty($relaciones)) {
            return [];
        }

        // Obtener información completa de los clientes
        $clientes = [];
        $clientesData = $this->db->select('clientes');

        foreach ($relaciones as $relacion) {
            foreach ($clientesData as $cliente) {
                if ($cliente['id'] == $relacion['cliente_id']) {
                    $clientes[] = $cliente;
                    break;
                }
            }
        }

        return $clientes;
    }

    public function tieneCategoria($clienteId, $categoriaId) {
        $relaciones = $this->db->select(
            'cliente_categorias',
            '*',
            'cliente_id = :cliente_id AND categoria_id = :categoria_id',
            [':cliente_id' => $clienteId, ':categoria_id' => $categoriaId]
        );

        return !empty($relaciones);
    }
}
?>