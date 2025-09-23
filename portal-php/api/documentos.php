<?php
require_once '../config/cors.php';
require_once '../config/auth.php';

$auth = new Auth();

// Verificar autenticación
$token = $auth->getAuthHeaders();
if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Token de acceso requerido']);
    exit;
}

$userData = $auth->validateToken($token);
if (!$userData) {
    http_response_code(401);
    echo json_encode(['error' => 'Token inválido']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$clienteId = $userData['cliente_id'];

switch ($method) {
    case 'GET':
        // Obtener documentos del cliente desde el array
        $documentos = DataStore::getDocumentosByClienteId($clienteId);

        // Convertir rutas de archivos a URLs completas
        foreach ($documentos as &$doc) {
            if ($doc['archivo_consultora']) {
                $doc['archivo_consultora_url'] = 'http://localhost/portal-php/uploads/' . $doc['archivo_consultora'];
            }
            if ($doc['archivo_cliente']) {
                $doc['archivo_cliente_url'] = 'http://localhost/portal-php/uploads/' . $doc['archivo_cliente'];
            }
        }

        echo json_encode($documentos);
        break;

    case 'POST':
        $action = $_GET['action'] ?? '';
        $documentoId = $_GET['id'] ?? '';

        if ($action === 'subir-cliente' && $documentoId) {
            // Verificar que el documento pertenece al cliente
            $documento = DataStore::getDocumentoById($documentoId);

            if (!$documento || $documento['cliente_id'] != $clienteId) {
                http_response_code(404);
                echo json_encode(['error' => 'Documento no encontrado']);
                exit;
            }

            if (!isset($_FILES['file'])) {
                http_response_code(400);
                echo json_encode(['error' => 'No se subió ningún archivo']);
                exit;
            }

            $file = $_FILES['file'];
            $uploadDir = '../uploads/documentos/cliente/' . date('Y') . '/' . date('m') . '/';

            // Crear directorio si no existe
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($file['name']);
            $filePath = $uploadDir . $fileName;
            $relativePath = 'documentos/cliente/' . date('Y') . '/' . date('m') . '/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                // Actualizar en el array
                DataStore::updateDocumento($documentoId, ['archivo_cliente' => $relativePath]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Archivo subido exitosamente',
                    'archivo_cliente_url' => 'http://localhost/portal-php/uploads/' . $relativePath
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al subir el archivo']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
}
?>