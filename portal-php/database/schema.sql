-- Crear base de datos
CREATE DATABASE IF NOT EXISTS portal_clientes;
USE portal_clientes;

-- Tabla de usuarios
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(254),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de clientes (empresas)
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    razon_social VARCHAR(255) NOT NULL,
    rut_empresa VARCHAR(12) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabla de tipos de documentos
CREATE TABLE tipos_documento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL UNIQUE,
    codigo VARCHAR(150) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de documentos
CREATE TABLE documentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    tipo_documento_id INT NOT NULL,
    archivo_consultora VARCHAR(255),
    archivo_cliente VARCHAR(255),
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (tipo_documento_id) REFERENCES tipos_documento(id),
    UNIQUE KEY unique_cliente_documento (cliente_id, tipo_documento_id)
);

-- Insertar tipos de documentos por defecto
INSERT INTO tipos_documento (nombre, codigo) VALUES
('Balance General', 'BAL_GEN'),
('Estado de Resultados', 'EST_RES'),
('Flujo de Efectivo', 'FLU_EFE'),
('Declaración de Impuestos', 'DEC_IMP'),
('Análisis Financiero', 'ANA_FIN');

-- Crear usuario administrador por defecto
INSERT INTO usuarios (username, password_hash, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@portal.com');