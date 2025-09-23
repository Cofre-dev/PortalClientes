<?php

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/Documento.php';
require_once __DIR__ . '/../models/TipoDocumento.php';

class DocumentController {
    private $auth;
    private $documentModel;
    private $tipoModel;

    public function __construct() {
        $this->auth = new Auth();
        $this->documentModel = new Documento();
        $this->tipoModel = new TipoDocumento();
    }

    public function index() {
        $userData = $this->auth->requireAuth();

        $categoriaId = $_GET['categoria_id'] ?? null;
        if (!$categoriaId) {
            http_response_code(400);
            echo json_encode(['error' => 'categoria_id es requerido']);
            return;
        }

        $clienteId = $userData['role'] === 'admin' ? null : $userData['cliente_id'];
        $documentos = $this->documentModel->findByCategoria($categoriaId, $clienteId);

        echo json_encode($documentos);
    }

    public function upload() {
        $userData = $this->auth->requireAuth();

        if (!isset($_FILES['archivo'])) {
            http_response_code(400);
            echo json_encode(['error' => 'No se subió ningún archivo']);
            return;
        }

        $file = $_FILES['archivo'];
        $categoriaId = $_POST['categoria_id'] ?? null;

        if (!$categoriaId) {
            http_response_code(400);
            echo json_encode(['error' => 'categoria_id es requerido']);
            return;
        }

        // Validar archivo
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file['size'] > $maxSize) {
            http_response_code(400);
            echo json_encode(['error' => 'El archivo es demasiado grande. Máximo 10MB.']);
            return;
        }

        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/png'
        ];

        if (!in_array($file['type'], $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Tipo de archivo no permitido']);
            return;
        }

        // Crear directorio
        $uploadDir = __DIR__ . '/../../storage/uploads/documentos/cliente/' . date('Y') . '/' . date('m') . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;
        $relativePath = 'documentos/cliente/' . date('Y') . '/' . date('m') . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $docId = $this->documentModel->create([
                'categoria_id' => $categoriaId,
                'cliente_id' => $userData['cliente_id'],
                'nombre_archivo' => $file['name'],
                'ruta_archivo' => $relativePath,
                'tamano' => $file['size'],
                'subido_por_cliente' => $userData['cliente_id']
            ]);

            if ($docId) {
                $this->tipoModel->incrementDocumentCount($categoriaId);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Archivo subido exitosamente',
                'id' => $docId
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al subir el archivo']);
        }
    }

    public function delete($id) {
        $userData = $this->auth->requireAuth();

        $documento = $this->documentModel->findById($id);
        if (!$documento) {
            http_response_code(404);
            echo json_encode(['error' => 'Documento no encontrado']);
            return;
        }

        // Solo el cliente que subió puede eliminar (o admin)
        if ($userData['role'] !== 'admin' && $documento['subido_por_cliente'] != $userData['cliente_id']) {
            http_response_code(403);
            echo json_encode(['error' => 'No tienes permisos para eliminar este documento']);
            return;
        }

        $deletedDoc = $this->documentModel->delete($id);
        if ($deletedDoc) {
            // Eliminar archivo físico
            $fullPath = __DIR__ . '/../../storage/uploads/' . $deletedDoc['ruta_archivo'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $this->tipoModel->decrementDocumentCount($deletedDoc['categoria_id']);

            echo json_encode(['success' => true, 'message' => 'Documento eliminado exitosamente']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Error al eliminar documento']);
        }
    }

    public function download() {
        $userData = $this->auth->requireAuth();

        $archivo = $_GET['file'] ?? '';
        if (!$archivo) {
            http_response_code(400);
            echo json_encode(['error' => 'Archivo no especificado']);
            return;
        }

        $filePath = __DIR__ . '/../../storage/uploads/' . $archivo;
        if (file_exists($filePath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($archivo) . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Archivo no encontrado']);
        }
    }
}