<?php
// Descarga directa de archivos - Sin headers JSON
session_start();

require_once __DIR__ . '/backend/config/Auth.php';
require_once __DIR__ . '/backend/models/Documento.php';

try {
    $auth = new Auth();
    $documentoModel = new Documento();

    // Verificar autenticación usando sesión PHP
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        die('No autorizado');
    }

    $userData = [
        'user_id' => $_SESSION['user_id'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
        'cliente_id' => $_SESSION['cliente_id'] ?? null
    ];

    if (!$userData['user_id']) {
        http_response_code(401);
        die('No autorizado');
    }

    $file = $_GET['file'] ?? null;
    if (!$file) {
        http_response_code(400);
        die('Parámetro file requerido');
    }

    $filePath = __DIR__ . '/' . $file;

    // Verificar que el archivo existe y está en el directorio permitido
    $realPath = realpath($filePath);
    $uploadDir = realpath(__DIR__ . '/uploads/');

    if (!$realPath || strpos($realPath, $uploadDir) !== 0) {
        http_response_code(404);
        die('Archivo no encontrado');
    }

    if (!file_exists($realPath)) {
        http_response_code(404);
        die('Archivo no encontrado');
    }

    // Para clientes, verificar que el archivo les pertenece
    if ($userData['role'] === 'cliente') {
        $clienteId = $userData['cliente_id'] ?? null;
        $documento = $documentoModel->getPorRuta($file);

        if (!$documento || $documento['cliente_id'] != $clienteId) {
            http_response_code(403);
            die('No tienes permisos para descargar este archivo');
        }

        // Usar el nombre original del archivo del documento
        $fileName = $documento['nombre_archivo'];
    } else {
        $fileName = basename($realPath);
    }

    // Limpiar cualquier output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Headers para descarga
    $mimeType = mime_content_type($realPath);

    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    header('Content-Length: ' . filesize($realPath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');

    // Enviar archivo
    readfile($realPath);
    exit;

} catch (Exception $e) {
    error_log("Download error: " . $e->getMessage());
    http_response_code(500);
    die('Error interno del servidor');
}
?>