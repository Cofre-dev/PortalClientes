<?php
// Script de configuración de base de datos para ARA & Bustamante
// Ejecutar desde la línea de comandos: php setup_database.php

echo "🔧 Configurando Base de Datos para ARA & Bustamante\n";
echo "==================================================\n\n";

try {
    // Configuración de conexión para XAMPP
    $host = 'localhost';
    $port = '3306'; // Puerto estándar MySQL en XAMPP
    $dbname = 'araybust_documentos_local';
    $username = 'root';
    $password = ''; // XAMPP por defecto no tiene password para root
    $charset = 'utf8mb4';

    // Primero conectar sin especificar base de datos para crearla
    echo "1. Conectando a MySQL...\n";
    $dsn = "mysql:host={$host};port={$port};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    echo "✅ Conexión exitosa a MySQL\n\n";

    // Crear base de datos si no existe
    echo "2. Creando base de datos...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Base de datos '{$dbname}' creada/verificada\n\n";

    // Conectar a la base de datos específica
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
    $pdo = new PDO($dsn, $username, $password, $options);

    // Crear tablas
    echo "3. Creando tablas...\n";

    // Tabla usuarios
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
    $pdo->exec($sql);
    echo "✅ Tabla 'usuarios' creada\n";

    // Tabla clientes
    $sql = "CREATE TABLE IF NOT EXISTS clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        empresa VARCHAR(200) NULL,
        nombre_cliente VARCHAR(150) NULL,
        razon_social VARCHAR(200) NULL,
        rut_empresa VARCHAR(12) NULL,
        correo_contacto VARCHAR(100) NULL,
        telefono VARCHAR(20) NULL,
        direccion TEXT NULL,
        email VARCHAR(100) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✅ Tabla 'clientes' creada\n";

    // Tabla tipos_documento
    $sql = "CREATE TABLE IF NOT EXISTS tipos_documento (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        codigo VARCHAR(50) NOT NULL UNIQUE,
        descripcion TEXT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✅ Tabla 'tipos_documento' creada\n";

    // Tabla documentos
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
    $pdo->exec($sql);
    echo "✅ Tabla 'documentos' creada\n";

    // Tabla sesiones_seguridad
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
    $pdo->exec($sql);
    echo "✅ Tabla 'sesiones_seguridad' creada\n";

    // Tabla logs_seguridad
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
    $pdo->exec($sql);
    echo "✅ Tabla 'logs_seguridad' creada\n";

    // Tabla cliente_categorias
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
    $pdo->exec($sql);
    echo "✅ Tabla 'cliente_categorias' creada\n\n";

    // Insertar datos iniciales
    echo "4. Insertando datos iniciales...\n";

    // Verificar si ya existen usuarios
    $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
    $userCount = $stmt->fetchColumn();

    if ($userCount == 0) {
        // Insertar usuario administrador
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (username, password, email, role, is_active)
            VALUES (?, ?, ?, 'admin', TRUE)
        ");
        $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin@araybustamante.com']);
        echo "✅ Usuario administrador creado (admin/admin123)\n";

        // Insertar usuario cliente
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (username, password, email, role, is_active)
            VALUES (?, ?, ?, 'cliente', TRUE)
        ");
        $stmt->execute(['cliente1', password_hash('cliente123', PASSWORD_DEFAULT), 'cliente1@empresa.com']);
        $clienteUserId = $pdo->lastInsertId();
        echo "✅ Usuario cliente creado (cliente1/cliente123)\n";

        // Insertar datos del cliente
        $stmt = $pdo->prepare("
            INSERT INTO clientes (user_id, empresa, nombre_cliente, razon_social, rut_empresa, correo_contacto, email)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $clienteUserId,
            'Empresa Demo S.A.',
            'Cliente Demo',
            'Empresa Demo S.A.',
            '99999999-9',
            'cliente1@empresa.com',
            'cliente1@empresa.com'
        ]);
        echo "✅ Perfil de cliente creado\n";
    } else {
        echo "ℹ️  Usuarios ya existen, saltando creación\n";
    }

    // Verificar tipos de documento
    $stmt = $pdo->query("SELECT COUNT(*) FROM tipos_documento");
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

        $stmt = $pdo->prepare("
            INSERT INTO tipos_documento (nombre, codigo, descripcion)
            VALUES (?, ?, ?)
        ");

        foreach ($tipos as $tipo) {
            $stmt->execute($tipo);
        }
        echo "✅ Tipos de documento creados (8 categorías)\n";
    } else {
        echo "ℹ️  Tipos de documento ya existen, saltando creación\n";
    }

    echo "\n🎉 ¡Configuración completada exitosamente!\n";
    echo "================================================\n";
    echo "📋 CREDENCIALES DE ACCESO:\n";
    echo "================================================\n";
    echo "🛡️  ADMINISTRADOR:\n";
    echo "   Usuario: admin\n";
    echo "   Contraseña: admin123\n";
    echo "   Email: admin@araybustamante.com\n\n";
    echo "👤 CLIENTE DEMO:\n";
    echo "   Usuario: cliente1\n";
    echo "   Contraseña: cliente123\n";
    echo "   Email: cliente1@empresa.com\n";
    echo "   Empresa: Empresa Demo S.A.\n";
    echo "   RUT: 99999999-9\n\n";
    echo "🌐 URL del Portal: http://localhost/\n";
    echo "📊 phpMyAdmin: http://localhost/phpmyadmin\n\n";
    echo "¡El sistema está listo para usar!\n";

} catch (PDOException $e) {
    echo "❌ Error de base de datos: " . $e->getMessage() . "\n";
    echo "\n🔧 SOLUCIONES POSIBLES:\n";
    echo "1. Asegúrate de que XAMPP esté ejecutándose\n";
    echo "2. Verifica que MySQL esté iniciado en el panel de XAMPP\n";
    echo "3. Comprueba que no hay otro servicio usando el puerto 3306\n";
    echo "4. Reinicia el servicio MySQL desde el panel de XAMPP\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
    exit(1);
}
?>