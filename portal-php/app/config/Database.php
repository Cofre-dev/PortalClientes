<?php

class Database {
    private static $instance = null;
    private $connection;
    private $dataFile;

    private function __construct() {
        $this->dataFile = __DIR__ . '/../../storage/database/data.json';
        $this->initializeData();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeData() {
        if (!file_exists($this->dataFile)) {
            $initialData = [
                'usuarios' => [
                    [
                        'id' => 1,
                        'username' => 'admin',
                        'password' => password_hash('admin123', PASSWORD_DEFAULT),
                        'email' => 'admin@portal.com',
                        'role' => 'admin',
                        'is_active' => true,
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 2,
                        'username' => 'cliente1',
                        'password' => password_hash('cliente123', PASSWORD_DEFAULT),
                        'email' => 'cliente1@empresa.com',
                        'role' => 'cliente',
                        'is_active' => true,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ],
                'clientes' => [
                    [
                        'id' => 1,
                        'user_id' => 2,
                        'razon_social' => 'Empresa Demo S.A.',
                        'rut_empresa' => '12345678-9',
                        'email' => 'cliente1@empresa.com',
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ],
                'tipos_documento' => [
                    ['id' => 1, 'nombre' => 'Cartola Bancaria', 'codigo' => 'CART_BANC', 'descripcion' => 'Movimientos bancarios del período', 'total_documentos' => 3],
                    ['id' => 2, 'nombre' => 'Facturas de Venta', 'codigo' => 'FACT_VENTA', 'descripcion' => 'Facturas emitidas por la empresa', 'total_documentos' => 5],
                    ['id' => 3, 'nombre' => 'Facturas de Compra', 'codigo' => 'FACT_COMPRA', 'descripcion' => 'Facturas recibidas de proveedores', 'total_documentos' => 8],
                    ['id' => 4, 'nombre' => 'Boletas de Honorarios', 'codigo' => 'BOL_HONOR', 'descripcion' => 'Boletas de honorarios del período', 'total_documentos' => 2],
                    ['id' => 5, 'nombre' => 'Remuneraciones', 'codigo' => 'REMUNER', 'descripcion' => 'Liquidaciones de sueldo', 'total_documentos' => 4]
                ],
                'documentos' => [
                    [
                        'id' => 1,
                        'categoria_id' => 1,
                        'cliente_id' => 1,
                        'nombre_archivo' => 'cartola_enero_2024.pdf',
                        'ruta_archivo' => 'documentos/cliente/2024/01/cartola_enero_2024.pdf',
                        'tamano' => 245760,
                        'fecha_subida' => '2024-01-15 10:30:00',
                        'subido_por_cliente' => 1
                    ],
                    [
                        'id' => 2,
                        'categoria_id' => 2,
                        'cliente_id' => 1,
                        'nombre_archivo' => 'facturas_venta_enero.xlsx',
                        'ruta_archivo' => 'documentos/consultora/2024/01/facturas_venta_enero.xlsx',
                        'tamano' => 156780,
                        'fecha_subida' => '2024-01-10 14:20:00',
                        'subido_por_cliente' => 0
                    ]
                ],
                'counters' => [
                    'usuarios' => 3,
                    'clientes' => 2,
                    'tipos_documento' => 6,
                    'documentos' => 3
                ]
            ];

            if (!is_dir(dirname($this->dataFile))) {
                mkdir(dirname($this->dataFile), 0777, true);
            }

            file_put_contents($this->dataFile, json_encode($initialData, JSON_PRETTY_PRINT));
        }
    }

    public function getData() {
        if (!file_exists($this->dataFile)) {
            $this->initializeData();
        }

        $data = json_decode(file_get_contents($this->dataFile), true);
        return $data ?: [];
    }

    public function saveData($data) {
        return file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }

    public function getTable($tableName) {
        $data = $this->getData();
        return $data[$tableName] ?? [];
    }

    public function saveTable($tableName, $tableData) {
        $data = $this->getData();
        $data[$tableName] = $tableData;
        return $this->saveData($data);
    }

    public function getNextId($tableName) {
        $data = $this->getData();
        $currentId = $data['counters'][$tableName] ?? 1;
        $data['counters'][$tableName] = $currentId + 1;
        $this->saveData($data);
        return $currentId;
    }
}