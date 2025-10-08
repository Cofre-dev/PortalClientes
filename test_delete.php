<?php
// Archivo de prueba para debug

header('Content-Type: application/json');

$logFile = __DIR__ . '/delete_debug.log';

// Log de la petición
$log = "=== " . date('Y-m-d H:i:s') . " ===\n";
$log .= "Method: " . $_SERVER['REQUEST_METHOD'] . "\n";
$log .= "URL: " . $_SERVER['REQUEST_URI'] . "\n";
$log .= "Headers:\n";
foreach (getallheaders() as $name => $value) {
    $log .= "  $name: $value\n";
}

$rawInput = file_get_contents('php://input');
$log .= "Raw Input: " . $rawInput . "\n";

$decoded = json_decode($rawInput, true);
$log .= "Decoded: " . json_encode($decoded) . "\n";
$log .= "JSON Error: " . json_last_error_msg() . "\n";
$log .= "\n";

file_put_contents($logFile, $log, FILE_APPEND);

echo json_encode([
    'success' => true,
    'received' => $decoded,
    'raw' => $rawInput,
    'method' => $_SERVER['REQUEST_METHOD']
]);
?>