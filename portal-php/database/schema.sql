-- Crear base de datos para ARA & Bustamante Consultores
CREATE DATABASE IF NOT EXISTS portal_clientes;
USE portal_clientes;

-- Tabla de usuarios (incluye tanto clientes como administradores)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(254) NOT NULL,
    role ENUM('cliente', 'admin') DEFAULT 'cliente',
    is_active BOOLEAN DEFAULT TRUE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Tabla de clientes (empresas) - extiende la información de usuarios tipo 'cliente'
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    razon_social VARCHAR(255) NOT NULL,
    rut_empresa VARCHAR(15) NOT NULL UNIQUE,
    direccion TEXT,
    telefono VARCHAR(20),
    contacto_principal VARCHAR(100),
    giro_comercial VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_rut (rut_empresa),
    INDEX idx_user_id (user_id)
);

-- Tabla de categorías de documentos (antes tipos_documento)
CREATE TABLE categorias_documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    codigo VARCHAR(150) NOT NULL UNIQUE,
    icono VARCHAR(50) DEFAULT 'fa-folder',
    color VARCHAR(7) DEFAULT '#d4af37',
    created_by INT, -- Usuario que creó la categoría (cliente o admin)
    is_global BOOLEAN DEFAULT FALSE, -- Si es global (creada por admin) o específica del cliente
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_codigo (codigo),
    INDEX idx_created_by (created_by),
    INDEX idx_is_global (is_global)
);

-- Tabla de documentos mejorada
CREATE TABLE documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    categoria_id INT NOT NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(500) NOT NULL,
    tamano_archivo INT NOT NULL, -- en bytes
    tipo_mime VARCHAR(100) NOT NULL,
    hash_archivo VARCHAR(64), -- SHA-256 para verificar integridad
    subido_por_admin BOOLEAN DEFAULT FALSE,
    subido_por_cliente BOOLEAN DEFAULT FALSE,
    subido_por INT, -- ID del usuario que subió el archivo
    estado ENUM('pendiente', 'aprobado', 'rechazado', 'revision') DEFAULT 'pendiente',
    notas TEXT, -- Notas del admin sobre el documento
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_aprobacion TIMESTAMP NULL,
    aprobado_por INT NULL, -- ID del admin que aprobó
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (categoria_id) REFERENCES categorias_documento(id) ON DELETE CASCADE,
    FOREIGN KEY (subido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    FOREIGN KEY (aprobado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cliente_categoria (cliente_id, categoria_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_subida (fecha_subida),
    INDEX idx_subido_por (subido_por)
);

-- Tabla de historial de acciones (para auditoría)
CREATE TABLE historial_acciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion ENUM('login', 'logout', 'upload', 'download', 'delete', 'approve', 'reject', 'create_category', 'create_user', 'edit_user', 'delete_user') NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id INT,
    descripcion TEXT,
    ip_address VARCHAR(45), -- Soporta IPv6
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_accion (usuario_id, accion),
    INDEX idx_fecha (created_at),
    INDEX idx_tabla_registro (tabla_afectada, registro_id)
);

-- Tabla de notificaciones
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    titulo VARCHAR(255) NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    url_accion VARCHAR(255), -- URL para redirigir cuando hagan clic
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_leida (usuario_id, leida),
    INDEX idx_fecha (created_at)
);

-- Tabla de configuración del sistema
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descripcion TEXT,
    tipo ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
    categoria VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clave (clave),
    INDEX idx_categoria (categoria)
);

-- Insertar categorías globales por defecto
INSERT INTO categorias_documento (nombre, descripcion, codigo, is_global, icono, color) VALUES
('Balance General', 'Estados financieros que muestran la situación patrimonial', 'BALANCE_GENERAL', TRUE, 'fa-balance-scale', '#1a365d'),
('Estado de Resultados', 'Resumen de ingresos y gastos del período', 'ESTADO_RESULTADOS', TRUE, 'fa-chart-line', '#2c5282'),
('Flujo de Efectivo', 'Movimientos de entrada y salida de efectivo', 'FLUJO_EFECTIVO', TRUE, 'fa-money-bill-wave', '#38a169'),
('Declaración de Impuestos', 'Declaraciones y formularios tributarios', 'DECLARACION_IMPUESTOS', TRUE, 'fa-file-invoice', '#d69e2e'),
('Análisis Financiero', 'Reportes de análisis y proyecciones', 'ANALISIS_FINANCIERO', TRUE, 'fa-chart-pie', '#d4af37'),
('Cartola Bancaria', 'Estados de cuenta y movimientos bancarios', 'CARTOLA_BANCARIA', TRUE, 'fa-university', '#e53e3e'),
('Facturas', 'Facturas de venta y compra', 'FACTURAS', TRUE, 'fa-file-invoice-dollar', '#805ad5'),
('Boletas', 'Boletas de honorarios y servicios', 'BOLETAS', TRUE, 'fa-receipt', '#319795'),
('Contratos', 'Contratos comerciales y laborales', 'CONTRATOS', TRUE, 'fa-handshake', '#dd6b20'),
('Nóminas', 'Liquidaciones de sueldo y remuneraciones', 'NOMINAS', TRUE, 'fa-users', '#3182ce');

-- Crear usuario administrador principal
INSERT INTO usuarios (username, password_hash, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@arabustamante.cl', 'admin');

-- Crear usuario cliente de ejemplo
INSERT INTO usuarios (username, password_hash, email, role) VALUES
('cliente1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cliente@ejemplo.cl', 'cliente');

-- Crear cliente de ejemplo
INSERT INTO clientes (user_id, razon_social, rut_empresa, direccion, telefono, contacto_principal, giro_comercial) VALUES
((SELECT id FROM usuarios WHERE username = 'cliente1'), 'Empresa Ejemplo S.A.', '12345678-9', 'Av. Providencia 123, Santiago', '+56 2 2345 6789', 'Juan Pérez', 'Servicios de Consultoría');

-- Insertar configuraciones por defecto
INSERT INTO configuracion (clave, valor, descripcion, tipo, categoria) VALUES
('max_file_size', '10485760', 'Tamaño máximo de archivo en bytes (10MB)', 'number', 'uploads'),
('allowed_file_types', '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png', 'Tipos de archivo permitidos', 'string', 'uploads'),
('session_timeout', '3600', 'Tiempo de expiración de sesión en segundos', 'number', 'security'),
('max_login_attempts', '5', 'Máximo número de intentos de login', 'number', 'security'),
('company_name', 'ARA & Bustamante Consultores', 'Nombre de la empresa', 'string', 'general'),
('company_email', 'contacto@arabustamante.cl', 'Email de contacto de la empresa', 'string', 'general'),
('company_phone', '+56 2 2345 6789', 'Teléfono de la empresa', 'string', 'general'),
('enable_notifications', 'true', 'Activar sistema de notificaciones', 'boolean', 'general'),
('backup_retention_days', '30', 'Días de retención de backups', 'number', 'system'),
('log_retention_days', '90', 'Días de retención de logs', 'number', 'system');

-- Crear vistas útiles
CREATE VIEW vista_documentos_cliente AS
SELECT
    d.id,
    d.nombre_archivo,
    d.nombre_original,
    d.tamano_archivo,
    d.estado,
    d.fecha_subida,
    d.subido_por_admin,
    d.subido_por_cliente,
    c.razon_social,
    c.rut_empresa,
    cat.nombre as categoria_nombre,
    cat.codigo as categoria_codigo,
    u.username as subido_por_usuario
FROM documentos d
JOIN clientes c ON d.cliente_id = c.id
JOIN categorias_documento cat ON d.categoria_id = cat.id
LEFT JOIN usuarios u ON d.subido_por = u.id;

CREATE VIEW vista_estadisticas_cliente AS
SELECT
    c.id as cliente_id,
    c.razon_social,
    c.rut_empresa,
    COUNT(d.id) as total_documentos,
    COUNT(CASE WHEN d.subido_por_cliente = TRUE THEN 1 END) as documentos_cliente,
    COUNT(CASE WHEN d.subido_por_admin = TRUE THEN 1 END) as documentos_admin,
    COUNT(CASE WHEN d.estado = 'pendiente' THEN 1 END) as documentos_pendientes,
    COUNT(CASE WHEN d.estado = 'aprobado' THEN 1 END) as documentos_aprobados,
    MAX(d.fecha_subida) as ultima_actividad
FROM clientes c
LEFT JOIN documentos d ON c.id = d.cliente_id
GROUP BY c.id, c.razon_social, c.rut_empresa;

-- Crear índices adicionales para optimización
CREATE INDEX idx_documentos_cliente_fecha ON documentos(cliente_id, fecha_subida DESC);
CREATE INDEX idx_historial_usuario_fecha ON historial_acciones(usuario_id, created_at DESC);
CREATE INDEX idx_notificaciones_usuario_fecha ON notificaciones(usuario_id, created_at DESC);

-- Crear triggers para auditoría
DELIMITER //

CREATE TRIGGER after_documento_insert
AFTER INSERT ON documentos
FOR EACH ROW
BEGIN
    INSERT INTO historial_acciones (usuario_id, accion, tabla_afectada, registro_id, descripcion)
    VALUES (NEW.subido_por, 'upload', 'documentos', NEW.id,
            CONCAT('Subido documento: ', NEW.nombre_original, ' para cliente ID: ', NEW.cliente_id));
END//

CREATE TRIGGER after_documento_delete
AFTER DELETE ON documentos
FOR EACH ROW
BEGIN
    INSERT INTO historial_acciones (usuario_id, accion, tabla_afectada, registro_id, descripcion)
    VALUES (OLD.subido_por, 'delete', 'documentos', OLD.id,
            CONCAT('Eliminado documento: ', OLD.nombre_original, ' del cliente ID: ', OLD.cliente_id));
END//

CREATE TRIGGER after_usuario_login
AFTER UPDATE ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.last_login != OLD.last_login THEN
        INSERT INTO historial_acciones (usuario_id, accion, descripcion)
        VALUES (NEW.id, 'login', CONCAT('Login exitoso para usuario: ', NEW.username));
    END IF;
END//

DELIMITER ;

-- Comentarios para documentar el esquema
ALTER TABLE usuarios COMMENT = 'Tabla principal de usuarios del sistema (clientes y administradores)';
ALTER TABLE clientes COMMENT = 'Información extendida de empresas clientes';
ALTER TABLE categorias_documento COMMENT = 'Categorías para organizar documentos (globales y específicas)';
ALTER TABLE documentos COMMENT = 'Archivos subidos por clientes y administradores';
ALTER TABLE historial_acciones COMMENT = 'Registro de auditoría de todas las acciones del sistema';
ALTER TABLE notificaciones COMMENT = 'Sistema de notificaciones para usuarios';
ALTER TABLE configuracion COMMENT = 'Configuraciones del sistema';