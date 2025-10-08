<?php
//database.php
require_once __DIR__ . '/JsonDatabase.php';

class Database {
    private static $instance = null;
    private $connection;
    private $host;
    private $port;
    private $dbname;
    private $username;
    private $password;
    private $charset = 'utf8mb4';
    private $fallbackMode = false;
    private $fallbackData = null;

    private function __construct() {
        $this->setEnvironmentConfig();
        $this->connectToMySQL();
    }

    private function setEnvironmentConfig() {
        // Detectar si estamos en entorno local (XAMPP)
        $isLocalEnvironment = $this->isLocalEnvironment();

        if ($isLocalEnvironment) {
            // Configuración local para XAMPP
            $this->host = 'localhost';
            $this->port = '3306';
            $this->dbname = 'araybust_documentos_local';
            $this->username = 'root';
            $this->password = '';
        } else {
            // Configuración de producción (credenciales remotas)
            $this->host = 'localhost';
            $this->port = '3306';
            $this->dbname = 'araybust_documentos';
            $this->username = 'araybust_mcofre';
            $this->password = 'Mat.FVC965';
        }
    }

    private function isLocalEnvironment() {
        // Múltiples métodos para detectar entorno local
        $localIndicators = [
            // Verificar si estamos en XAMPP (directorio típico)
            strpos(__DIR__, 'xampp') !== false,
            // Verificar si estamos en localhost
            $_SERVER['HTTP_HOST'] ?? '' === 'localhost',
            $_SERVER['SERVER_NAME'] ?? '' === 'localhost',
            // Verificar IP local
            in_array($_SERVER['SERVER_ADDR'] ?? '', ['127.0.0.1', '::1']),
            // Verificar puerto típico de XAMPP/desarrollo
            ($_SERVER['SERVER_PORT'] ?? '') === '80' && ($_SERVER['HTTP_HOST'] ?? '') === 'localhost'
        ];

        return in_array(true, $localIndicators, true);
    }

    private function connectToMySQL() {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbname};charset={$this->charset}";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}",
                PDO::ATTR_TIMEOUT            => 5
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

            $this -> initializeTables();

        } catch (PDOException $e) {
            // Si falla la conexión, mostrar instrucciones y usar datos mock como fallback
            error_log("MySQL Connection failed: " . $e->getMessage());
            $this->showMySQLSetupInstructions($e);

            // Modo fallback con datos mock
            $this->connection = null;
            $this->fallbackMode = true;
            $this->initializeMockData();
            return;
        }
    }

    private function showMySQLSetupInstructions($error) {
        $errorMessage = $error->getMessage();

        echo '<!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Configuración de Base de Datos - ARA & Bustamante</title>
                <style>
                    body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
                    .error-box { background: #fee; border: 1px solid #fcc; border-radius: 8px; padding: 20px; margin: 20px 0; }
                    .success-box { background: #efe; border: 1px solid #cfc; border-radius: 8px; padding: 20px; margin: 20px 0; }
                    .warning-box { background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 20px; margin: 20px 0; }
                    .code { background: #f5f5f5; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
                    .step { margin: 15px 0; padding: 10px; border-left: 4px solid #007cba; }
                    h1 { color: #007cba; }
                    h2 { color: #005a8b; }
                    .credentials { background: #f0f9ff; padding: 15px; border-radius: 8px; margin: 10px 0; }
                </style>
            </head>
            <body>
                <h1>🔧 Configuración de Base de Datos MySQL</h1>

                <div class="error-box">
                    <h2>❌ No se pudo conectar a MySQL</h2>
                    <p><strong>Error:</strong> ' . htmlspecialchars($errorMessage) . '</p>
                </div>

                <div class="warning-box">
                    <h2>🏃‍♂️ Pasos para Configurar MySQL</h2>

                    <div class="step">
                        <h3>1. Iniciar XAMPP</h3>
                        <p>• Abre el Panel de Control de XAMPP</p>
                        <p>• Haz clic en "Start" en la fila de <strong>MySQL</strong></p>
                        <p>• Espera a que aparezca el texto en verde</p>
                    </div>

                    <div class="step">
                        <h3>2. Ejecutar Script de Configuración</h3>
                        <p>Ejecuta este comando en tu terminal (como administrador):</p>
                        <div class="code">cd C:\\xampp\\htdocs\\portal-php<br>C:\\xampp\\php\\php.exe setup_database.php</div>
                    </div>

                    <div class="step">
                        <h3>3. Configuración Manual (Alternativa)</h3>
                        <p>Si el script automático no funciona, puedes configurar manualmente:</p>
                        <p>• Abre phpMyAdmin: <a href="http://localhost/phpmyadmin" target="_blank">http://localhost/phpmyadmin</a></p>
                        <p>• Crea la base de datos local:</p>
                        <div class="code">CREATE DATABASE araybust_documentos_local CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</div>
                        <p>• No necesitas crear usuario (XAMPP usa root sin contraseña por defecto)</p>
                    </div>
                </div>

                <div class="credentials">
                    <h3>📋 Información de la Base de Datos Local (XAMPP)</h3>
                    <p><strong>Servidor:</strong> localhost:3306</p>
                    <p><strong>Base de Datos:</strong> araybust_documentos_local</p>
                    <p><strong>Usuario:</strong> root</p>
                    <p><strong>Contraseña:</strong> (vacía - configuración estándar XAMPP)</p>
                </div>

                <div class="success-box">
                    <h3>✅ Después de la Configuración</h3>
                    <p>Una vez que MySQL esté configurado, recarga esta página y el portal funcionará automáticamente.</p>
                    <p><strong>Credenciales de acceso al portal:</strong></p>
                    <p>• <strong>Administrador:</strong> usuario <code>admin</code>, contraseña <code>admin123</code></p>
                    <p>• <strong>Cliente demo:</strong> usuario <code>cliente1</code>, contraseña <code>cliente123</code></p>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button onclick="window.location.reload()" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        🔄 Verificar Conexión Nuevamente
                    </button>
                </div>

            </body>
            </html>';
    }

    public static function getInstance() {
        if (self::$instance === null) {
            // Usar JSON Database por defecto para mayor simplicidad
            self::$instance = JsonDatabase::getInstance();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function isFallbackMode() {
        return $this->fallbackMode;
    }

    private function initializeTables() {

        $sql = "CREATE TABLE IF NOT EXISTS usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    email VARCHAR(100) NOT NULL UNIQUE,
                    role ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente',
                    is_active BOOLEAN DEFAULT TRUE,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->connection->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            empresa VARCHAR(200) NOT NULL,
            nombre_cliente VARCHAR(150) NOT NULL,
            rut_empresa VARCHAR(12) NOT NULL UNIQUE,
            correo_contacto VARCHAR(100) NOT NULL,
            telefono VARCHAR(20) NULL,
            direccion TEXT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->connection->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS tipos_documento (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            codigo VARCHAR(50) NOT NULL UNIQUE,
            descripcion TEXT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->connection->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS documentos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            categoria_id INT NOT NULL,
            cliente_id INT NOT NULL,
            nombre_archivo VARCHAR(255) NOT NULL,
            ruta_archivo VARCHAR(500) NOT NULL,
            tamano BIGINT NOT NULL,
            tipo_mime VARCHAR(100) NOT NULL,
            subido_por_cliente BOOLEAN DEFAULT TRUE,
            fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (categoria_id) REFERENCES tipos_documento(id) ON DELETE RESTRICT,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->connection->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS sesiones_seguridad (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(128) NOT NULL,
            user_id INT NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NOT NULL,
            csrf_token VARCHAR(128) NOT NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_session_id (session_id),
            INDEX idx_user_id (user_id),
            INDEX idx_last_activity (last_activity)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->connection->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS logs_seguridad (
            id INT AUTO_INCREMENT PRIMARY KEY,
            evento VARCHAR(100) NOT NULL,
            user_id INT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent TEXT NULL,
            detalles JSON NULL,
            nivel ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL,
            INDEX idx_evento (evento),
            INDEX idx_user_id (user_id),
            INDEX idx_created_at (created_at),
            INDEX idx_nivel (nivel)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->connection->exec($sql);

        // Tabla para categorías específicas de cada cliente
        $sql = "CREATE TABLE IF NOT EXISTS cliente_categorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cliente_id INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            codigo VARCHAR(50) NULL,
            descripcion TEXT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_client_category (cliente_id, codigo),
            INDEX idx_cliente_activo (cliente_id, is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $this->connection->exec($sql);

        $this->insertInitialData();
    }

    private function initializeMockData() {
        $this->fallbackData = [
            'usuarios' => [
                ['id' => 1, 'username' => 'admin', 'password' => password_hash('admin123', PASSWORD_DEFAULT), 'email' => 'admin@araybustamante.com', 'role' => 'admin', 'is_active' => true],
                ['id' => 2, 'username' => 'cliente1', 'password' => password_hash('cliente123', PASSWORD_DEFAULT), 'email' => 'cliente1@empresa.com', 'role' => 'cliente', 'is_active' => true]
            ],
            'clientes' => [
                ['id' => 1, 'empresa' => 'Empresa Demo S.A.', 'nombre_cliente' => 'Cliente Demo', 'rut_empresa' => '99999999-9', 'correo_contacto' => 'cliente1@empresa.com']
            ],
            'tipos_documento' => [
                ['id' => 1, 'nombre' => 'Cartola Bancaria', 'codigo' => 'CART_BANC', 'descripcion' => 'Movimientos bancarios del período', 'is_active' => true],
                ['id' => 2, 'nombre' => 'Facturas de Venta', 'codigo' => 'FACT_VENTA', 'descripcion' => 'Facturas emitidas por la empresa', 'is_active' => true],
                ['id' => 3, 'nombre' => 'Facturas de Compra', 'codigo' => 'FACT_COMPRA', 'descripcion' => 'Facturas recibidas de proveedores', 'is_active' => true]
            ],
            'documentos' => []
        ];
    }

    private function insertInitialData() {
        try {

            $stmt = $this -> connection -> query("SELECT COUNT(*) FROM usuarios");
            $userCount = $stmt -> fetchColumn();

            if ($userCount == 0) {
                $stmt = $this -> connection -> prepare("
                    INSERT INTO usuarios (username, password, email, role, is_active)
                    VALUES (?, ?, ?, 'admin', TRUE)
                ");

                $stmt -> execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin@araybustamante.com']);

                $stmt = $this -> connection -> prepare("
                    INSERT INTO usuarios (username, password, email, role, is_active)
                    VALUES (?, ?, ?, 'cliente', TRUE)
                ");

                $stmt -> execute(['cliente1', password_hash('cliente123', PASSWORD_DEFAULT), 'cliente1@empresa.com']);

                $clienteUserId = $this -> connection -> lastInsertId();

                $stmt = $this -> connection -> prepare("
                    INSERT INTO clientes (empresa, nombre_cliente, rut_empresa, correo_contacto)
                    VALUES (?, ?, ?, ?)
                ");

                $stmt->execute(['Empresa Demo S.A.', 'Cliente Demo', '99999999-9', 'cliente1@empresa.com']);
            }

            $stmt = $this->connection->query("SELECT COUNT(*) FROM tipos_documento");

            $tipoCount = $stmt->fetchColumn();

            if ($tipoCount == 0) {
                $tipos = [
                    ['Cartola Bancaria', 'CART_BANC', 'Movimientos bancarios del período'],
                    ['Facturas de Venta', 'FACT_VENTA', 'Facturas emitidas por la empresa'],
                    ['Facturas de Compra', 'FACT_COMPRA', 'Facturas recibidas de proveedores'],
                    ['Boletas de Honorarios', 'BOL_HONOR', 'Boletas de honorarios del período'],
                    ['Remuneraciones', 'REMUNER', 'Liquidaciones de sueldo'],
                    ['Declaraciones', 'DECLARAC', 'Declaraciones de impuestos y formularios'],
                    ['Contratos', 'CONTRATO', 'Contratos y acuerdos comerciales'],
                    ['Balances', 'BALANCE', 'Estados financieros y balances']
                ];

                $stmt = $this -> connection -> prepare("
                    INSERT INTO tipos_documento (nombre, codigo, descripcion)
                    VALUES (?, ?, ?)
                ");

                foreach ($tipos as $tipo) {
                    $stmt->execute($tipo);
                }
            }

        } catch (PDOException $e) {
            error_log("Error insertando datos iniciales: " . $e->getMessage());
        }
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this -> connection -> prepare($sql);
            $stmt -> execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Error en consulta: " . $e -> getMessage());
        }
    }

    public function insert($table, $data) {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->query($sql, $data);

        return $this->connection->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = []) {
        $set = '';

        foreach (array_keys($data) as $column) {
            $set .= "{$column} = :{$column}, ";
        }

        $set = rtrim($set, ', ');

        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $params = array_merge($data, $whereParams);

        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }

    public function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $whereParams);
        return $stmt->rowCount();
    }

    public function select($table, $columns = '*', $where = '', $whereParams = [], $orderBy = '', $limit = '') {
        if ($this->fallbackMode) {
            return $this->mockSelect($table, $where, $whereParams);
        }

        $sql = "SELECT {$columns} FROM {$table}";

        if ($where) {
            $sql .= " WHERE {$where}";
        }

        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }

        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }

        $stmt = $this->query($sql, $whereParams);
        return $stmt->fetchAll();
    }

    private function mockSelect($table, $where = '', $whereParams = []) {
        if (!isset($this->fallbackData[$table])) {
            return [];
        }

        $data = $this->fallbackData[$table];

        if ($where) {
            // Filtro simple para username y password
            if (strpos($where, 'username') !== false && isset($whereParams[':username'])) {
                $username = $whereParams[':username'];
                $data = array_filter($data, function($row) use ($username) {
                    return isset($row['username']) && $row['username'] === $username;
                });
            }
        }

        return array_values($data);
    }

    public function selectOne($table, $columns = '*', $where = '', $whereParams = []) {
        $results = $this->select($table, $columns, $where, $whereParams, '', '1');
        return $results ? $results[0] : null;
    }

    public function getTotalDocumentosPorCategoria() {
        $sql = "
            SELECT
                td.id,
                td.nombre,
                td.codigo,
                td.descripcion,
                COUNT(d.id) as total_documentos
            FROM tipos_documento td
            LEFT JOIN documentos d ON td.id = d.categoria_id
            WHERE td.is_active = TRUE
            GROUP BY td.id, td.nombre, td.codigo, td.descripcion
            ORDER BY td.nombre
        ";

        $stmt = $this->query($sql);
        return $stmt->fetchAll();
    }

    public function getDashboardStats() {
        if ($this->fallbackMode) {
            return [
                'usuarios' => count($this->fallbackData['usuarios']),
                'clientes' => count($this->fallbackData['clientes']),
                'documentos' => count($this->fallbackData['documentos']),
                'categorias' => count($this->fallbackData['tipos_documento'])
            ];
        }

        $stats = [];

        $stmt = $this->query("SELECT COUNT(*) as total FROM usuarios WHERE is_active = TRUE");
        $stats['usuarios'] = $stmt->fetchColumn();

        $stmt = $this->query("SELECT COUNT(*) as total FROM clientes");
        $stats['clientes'] = $stmt->fetchColumn();

        $stmt = $this->query("SELECT COUNT(*) as total FROM documentos");
        $stats['documentos'] = $stmt->fetchColumn();

        $stmt = $this->query("SELECT COUNT(*) as total FROM tipos_documento WHERE is_active = TRUE");
        $stats['categorias'] = $stmt->fetchColumn();

        return $stats;
    }
}

?>