<?php

require_once __DIR__ . '/../config/Auth.php';
require_once __DIR__ . '/../models/Documento.php';

class DocumentosController {
    private $auth;
    private $documentoModel;

    public function __construct() {
        $this->auth = new Auth();
        $this->documentoModel = new Documento();
    }

    public function listar() {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            $categoriaId = $_GET['categoria_id'] ?? null;
            $clienteId = null;

            // Si es cliente, solo mostrar sus documentos
            if ($userData['role'] === 'cliente') {
                $clienteId = $userData['cliente_id'] ?? null;
                if (!$clienteId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Usuario cliente sin ID de cliente asociado']);
                    return;
                }
            }

            if ($categoriaId) {
                $documentos = $this->documentoModel->getPorCategoria($categoriaId, $clienteId);
            } else {
                $documentos = $this->documentoModel->getTodos($clienteId);
            }

            echo json_encode($documentos);

        } catch (Exception $e) {
            error_log("Documentos listar error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function upload() {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método POST']);
                return;
            }

            // Determinar cliente ID según el rol
            $clienteId = null;
            if ($userData['role'] === 'cliente') {
                $clienteId = $userData['cliente_id'] ?? null;
                if (!$clienteId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'Usuario cliente sin ID de cliente asociado']);
                    return;
                }
            } elseif ($userData['role'] === 'admin') {
                // El admin puede subir documentos para otros clientes
                $clienteId = $_POST['cliente_id'] ?? null;
                if (!$clienteId) {
                    http_response_code(400);
                    echo json_encode(['error' => 'cliente_id es requerido para administradores']);
                    return;
                }
            } else {
                http_response_code(403);
                echo json_encode(['error' => 'Rol no autorizado para subir documentos']);
                return;
            }

            $categoriaId = $_POST['categoria_id'] ?? null;
            if (!$categoriaId) {
                http_response_code(400);
                echo json_encode(['error' => 'categoria_id es requerido']);
                return;
            }

            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
                http_response_code(400);
                echo json_encode(['error' => 'No se pudo subir el archivo']);
                return;
            }

            $file = $_FILES['archivo'];

            // Validar archivo
            $this->validateFile($file);

            // Crear directorio de uploads si no existe
            $uploadDir = __DIR__ . '/../../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generar nombre único para el archivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '_' . time() . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            // Mover archivo
            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                http_response_code(500);
                echo json_encode(['error' => 'Error al guardar el archivo']);
                return;
            }

            // Guardar en base de datos
            $documentoData = [
                'categoria_id' => $categoriaId,
                'cliente_id' => $clienteId,
                'nombre_archivo' => $file['name'],
                'ruta_archivo' => 'uploads/' . $fileName,
                'tamano' => $file['size'],
                'tipo_mime' => $file['type'],
                'subido_por_cliente' => ($userData['role'] === 'cliente')
            ];

            $documentoId = $this->documentoModel->create($documentoData);

            if ($documentoId) {
                echo json_encode([
                    'success' => true,
                    'id' => $documentoId,
                    'message' => 'Archivo subido exitosamente'
                ]);
            } else {
                // Eliminar archivo si no se pudo guardar en BD
                unlink($filePath);
                http_response_code(500);
                echo json_encode(['error' => 'Error al guardar información del archivo']);
            }

        } catch (Exception $e) {
            error_log("Documentos upload error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function verifyDownload() {
        try {
            // Verificar autenticación usando método estándar para APIs
            $userData = $this->auth->requireAuth();

            $file = $_GET['file'] ?? null;
            if (!$file) {
                http_response_code(400);
                echo json_encode(['error' => 'Parámetro file requerido']);
                return;
            }

            $filePath = __DIR__ . '/../../' . $file;

            // Verificar que el archivo existe y está en el directorio permitido
            $realPath = realpath($filePath);
            $uploadDir = realpath(__DIR__ . '/../../uploads/');

            if (!$realPath || strpos($realPath, $uploadDir) !== 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Archivo no encontrado']);
                return;
            }

            if (!file_exists($realPath)) {
                http_response_code(404);
                echo json_encode(['error' => 'Archivo no encontrado']);
                return;
            }

            // Para clientes, verificar que el archivo les pertenece
            if ($userData['role'] === 'cliente') {
                $clienteId = $userData['cliente_id'] ?? null;
                $documento = $this->documentoModel->getPorRuta($file);

                if (!$documento || $documento['cliente_id'] != $clienteId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'No tienes permisos para descargar este archivo']);
                    return;
                }
            }

            // Si llegamos aquí, la descarga está autorizada
            echo json_encode([
                'success' => true,
                'download_url' => "/api/documentos/download?file=" . urlencode($file) . "&token=" . urlencode($this->auth->getAuthHeaders()),
                'filename' => $documento['nombre_archivo'] ?? basename($realPath)
            ]);

        } catch (Exception $e) {
            error_log("Documentos verifyDownload error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function download() {
        try {
            // Verificar autenticación para descargas (manejo especial)
            $userData = $this->verifyDownloadAuth();

            $file = $_GET['file'] ?? null;
            if (!$file) {
                http_response_code(400);
                echo json_encode(['error' => 'Parámetro file requerido']);
                return;
            }

            $filePath = __DIR__ . '/../../' . $file;

            // Verificar que el archivo existe y está en el directorio permitido
            $realPath = realpath($filePath);
            $uploadDir = realpath(__DIR__ . '/../../uploads/');

            if (!$realPath || strpos($realPath, $uploadDir) !== 0) {
                http_response_code(404);
                echo json_encode(['error' => 'Archivo no encontrado']);
                return;
            }

            if (!file_exists($realPath)) {
                http_response_code(404);
                echo json_encode(['error' => 'Archivo no encontrado']);
                return;
            }

            // Para clientes, verificar que el archivo les pertenece
            if ($userData['role'] === 'cliente') {
                $clienteId = $userData['cliente_id'] ?? null;
                $documento = $this->documentoModel->getPorRuta($file);

                if (!$documento || $documento['cliente_id'] != $clienteId) {
                    http_response_code(403);
                    echo json_encode(['error' => 'No tienes permisos para descargar este archivo']);
                    return;
                }
            }

            // Limpiar cualquier output buffer
            if (ob_get_level()) {
                ob_end_clean();
            }

            // Usar el nombre original del archivo del documento
            $documento = $this->documentoModel->getPorRuta($file);
            $fileName = $documento ? $documento['nombre_archivo'] : basename($realPath);

            // Enviar archivo
            $mimeType = mime_content_type($realPath);

            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . filesize($realPath));
            header('Cache-Control: no-cache, must-revalidate');
            header('Pragma: no-cache');

            readfile($realPath);
            exit;

        } catch (Exception $e) {
            error_log("Documentos download error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function eliminar($id) {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método DELETE']);
                return;
            }

            $documento = $this->documentoModel->findById($id);
            if (!$documento) {
                http_response_code(404);
                echo json_encode(['error' => 'Documento no encontrado']);
                return;
            }

            // Para clientes, verificar que el documento les pertenece y fue subido por ellos
            if ($userData['role'] === 'cliente') {
                $clienteId = $userData['cliente_id'] ?? null;
                if (!$clienteId || $documento['cliente_id'] != $clienteId || !$documento['subido_por_cliente']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'No tienes permisos para eliminar este documento']);
                    return;
                }
            }

            // Eliminar archivo físico
            $filePath = __DIR__ . '/../../' . $documento['ruta_archivo'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Eliminar de base de datos
            $deleted = $this->documentoModel->delete($id);

            if ($deleted) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Documento eliminado exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al eliminar documento']);
            }

        } catch (Exception $e) {
            error_log("Documentos eliminar error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    public function renombrar($id) {
        try {
            // Verificar autenticación
            $userData = $this->auth->requireAuth();

            if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
                http_response_code(405);
                echo json_encode(['error' => 'Solo se permite método PUT']);
                return;
            }

            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);

            if ($input === null) {
                http_response_code(400);
                echo json_encode(['error' => 'JSON inválido']);
                return;
            }

            $nuevoNombre = trim($input['nuevo_nombre'] ?? '');
            if (empty($nuevoNombre)) {
                http_response_code(400);
                echo json_encode(['error' => 'nuevo_nombre es requerido']);
                return;
            }

            $documento = $this->documentoModel->findById($id);
            if (!$documento) {
                http_response_code(404);
                echo json_encode(['error' => 'Documento no encontrado']);
                return;
            }

            // Para clientes, verificar que el documento les pertenece y fue subido por ellos
            if ($userData['role'] === 'cliente') {
                $clienteId = $userData['cliente_id'] ?? null;
                if (!$clienteId || $documento['cliente_id'] != $clienteId || !$documento['subido_por_cliente']) {
                    http_response_code(403);
                    echo json_encode(['error' => 'No tienes permisos para renombrar este documento']);
                    return;
                }
            }

            // Mantener la extensión original
            $extension = pathinfo($documento['nombre_archivo'], PATHINFO_EXTENSION);
            $nuevoNombreCompleto = $nuevoNombre . ($extension ? '.' . $extension : '');

            $updated = $this->documentoModel->update($id, ['nombre_archivo' => $nuevoNombreCompleto]);

            if ($updated) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Documento renombrado exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al renombrar documento']);
            }

        } catch (Exception $e) {
            error_log("Documentos renombrar error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Error interno del servidor']);
        }
    }

    private function verifyDownloadAuth() {
        // Método 1: Intentar autenticación JWT estándar
        $token = $this->auth->getAuthHeaders();
        if ($token) {
            $userData = $this->auth->validateToken($token);
            if ($userData) {
                return $userData;
            }
        }

        // Método 2: Verificar sesión PHP (fallback para descargas directas)
        if ($this->auth->isLoggedIn()) {
            require_once __DIR__ . '/../models/User.php';
            require_once __DIR__ . '/../models/Cliente.php';

            $userModel = new User();
            $user = $userModel->findById($_SESSION['user_id']);

            if ($user && $user['is_active']) {
                // Si es cliente, agregar datos del cliente
                if ($user['role'] === 'cliente') {
                    $clienteModel = new Cliente();
                    $cliente = $clienteModel->findByUserId($user['id']);
                    if ($cliente) {
                        $user['cliente_id'] = $cliente['id'];
                        $user['razon_social'] = $cliente['razon_social'];
                        $user['rut_empresa'] = $cliente['rut_empresa'];
                        $user['email'] = $cliente['email'] ?? $user['email'];
                    }
                }
                return $user;
            }
        }

        // Si no hay autenticación válida, enviar página de error HTML amigable
        http_response_code(401);
        echo '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso no autorizado - ARA & Bustamante</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }
        .error-container {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .error-icon {
            font-size: 4rem;
            color: #e53e3e;
            margin-bottom: 1rem;
        }
        h1 {
            color: #2d3748;
            margin-bottom: 1rem;
        }
        p {
            color: #4a5568;
            margin-bottom: 2rem;
        }
        .btn {
            background-color: #1a365d;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #2d4a68;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🔒</div>
        <h1>Acceso no autorizado</h1>
        <p>Necesitas iniciar sesión para descargar este archivo.</p>
        <a href="/" class="btn">Ir al Portal de Login</a>
    </div>
    <script>
        // Auto-redirigir después de 5 segundos
        setTimeout(() => {
            window.location.href = "/";
        }, 5000);
    </script>
</body>
</html>';
        exit;
    }

    private function validateFile($file) {
        $maxSize = 10 * 1024 * 1024; // 10MB
        $allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'image/jpeg',
            'image/jpg',
            'image/png'
        ];

        if ($file['size'] > $maxSize) {
            throw new Exception('El archivo es demasiado grande. Máximo 10MB.');
        }

        if (!in_array($file['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten PDF, DOC, XLS, JPG y PNG.');
        }

        // Validar extensión
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception('Extensión de archivo no permitida.');
        }
    }
}
?>