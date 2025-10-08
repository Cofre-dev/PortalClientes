<?php

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/TipoDocumento.php';

class TipoDocumentoController {
    private $auth;
    private $tipoModel;

    public function __construct() {
        $this->auth = new Auth();
        $this->tipoModel = new TipoDocumento();
    }

    public function listar() {
        return $this->index();
    }

    public function index() {
        $userData = $this->auth->requireAuth();

        // For clients, only show their categories with document count
        $clienteId = $userData['role'] === 'admin' ? null : $userData['cliente_id'];
        $tipos = $this->tipoModel->getAllWithDocumentCount(true, $clienteId);

        echo json_encode($tipos);
    }

    public function crear() {
        return $this->create();
    }

    public function create() {
        $this->auth->requireAuth();

        $input = json_decode(file_get_contents('php://input'), true);
        $nombre = trim($input['nombre'] ?? '');
        $descripcion = trim($input['descripcion'] ?? '');
        $codigo = $input['codigo'] ?? strtoupper(str_replace(' ', '_', $nombre));

        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode(['error' => 'El nombre es requerido']);
            return;
        }

        $id = $this->tipoModel->create([
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'codigo' => $codigo
        ]);

        if ($id) {
            echo json_encode([
                'success' => true,
                'id' => $id,
                'message' => 'Categoría creada exitosamente'
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear categoría']);
        }
    }
}