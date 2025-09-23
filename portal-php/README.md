# Portal de Clientes - PHP

Portal de clientes migrado desde Django a PHP vanilla para compatibilidad con servidores básicos.

## Características

- **Backend PHP Vanilla**: Sin frameworks pesados, compatible con cualquier servidor PHP
- **Base de Datos MySQL**: Esquema simple y eficiente
- **Frontend Responsivo**: HTML, CSS y JavaScript vanilla
- **Autenticación JWT**: Sistema de tokens seguro
- **Subida de Archivos**: Gestión de documentos por categorías
- **API REST**: Endpoints simples y documentados

## Estructura del Proyecto

```
portal-php/
├── api/                    # Endpoints de la API
│   ├── auth.php           # Autenticación y registro
│   ├── documentos.php     # Gestión de documentos
│   └── tipos-documento.php # Tipos de documentos
├── config/                # Configuración
│   ├── database.php       # Conexión a BD
│   ├── auth.php          # Sistema de autenticación
│   └── cors.php          # Configuración CORS
├── database/              # Base de datos
│   └── schema.sql        # Esquema de la BD
├── frontend/              # Frontend
│   ├── index.html        # Página principal
│   ├── css/styles.css    # Estilos
│   └── js/app.js         # Lógica JavaScript
├── uploads/               # Archivos subidos
│   └── documentos/       # Documentos organizados por fecha
└── .htaccess             # Configuración Apache
```

## Instalación (MVP - Sin Base de Datos)

### 1. Requisitos

- PHP 7.4 o superior
- Servidor web (Apache/Nginx)
- ⚠️ **Para el MVP**: No se requiere base de datos (usa arrays en memoria)

### 2. Configuración Rápida

**¡Solo copia la carpeta al servidor web y funciona!**

No necesitas configurar base de datos para la demostración.

### 3. Configuración del Servidor

#### Apache
El archivo `.htaccess` ya está configurado. Asegúrate de que mod_rewrite esté habilitado.

#### Nginx
Agregar a la configuración:
```nginx
location / {
    try_files $uri $uri/ /frontend/index.html;
}

location /api/ {
    try_files $uri $uri/ =404;
}

location /uploads/ {
    try_files $uri =404;
}
```

### 4. Configuración de Permisos

```bash
chmod 755 uploads/
chmod 755 uploads/documentos/
```

## Uso

### Acceso al Portal

1. Abrir navegador en: `http://localhost/portal-php/`
2. Registrar nuevo cliente o iniciar sesión
3. Gestionar documentos desde el dashboard

### Usuarios por Defecto

**Cliente:**
- **Usuario**: cliente1
- **Contraseña**: cliente123

**Administrador:**
- **Usuario**: admin
- **Contraseña**: admin123

### Panel de Administración

Accede al panel de administración en: `http://localhost/portal-php/admin/`

En el panel puedes:
- Ver todos los usuarios y clientes
- Gestionar documentos
- Crear nuevos usuarios
- Ver estadísticas del sistema

### API Endpoints

#### Autenticación
- `POST /api/auth.php?action=login` - Iniciar sesión
- `POST /api/auth.php?action=register` - Registrar cliente

#### Documentos
- `GET /api/documentos.php` - Listar documentos del cliente
- `POST /api/documentos.php?action=subir-cliente&id={id}` - Subir archivo

#### Tipos de Documentos
- `GET /api/tipos-documento.php` - Listar tipos disponibles

## Migración desde Django

### Diferencias Principales

1. **Autenticación**: JWT en lugar de sessions de Django
2. **ORM**: PDO nativo en lugar del ORM de Django
3. **Archivos**: Sistema de subida simplificado
4. **Frontend**: JavaScript vanilla en lugar de templates Django

### Datos Equivalentes

| Django | PHP |
|--------|-----|
| `User` | `usuarios` |
| `Cliente` | `clientes` |
| `TipoDocumento` | `tipos_documento` |
| `Documento` | `documentos` |

## Seguridad

- Tokens JWT con expiración
- Validación de archivos subidos
- Sanitización de inputs
- Protección CORS configurada
- Permisos de archivos restrictivos

## Desarrollo

### Agregar Nuevos Tipos de Documentos

```sql
INSERT INTO tipos_documento (nombre, codigo) VALUES
('Nuevo Documento', 'NUEVO_DOC');
```

### Personalizar Frontend

Editar archivos en `frontend/`:
- `css/styles.css` - Estilos
- `js/app.js` - Lógica
- `index.html` - Estructura

### Modificar API

Los endpoints están en `api/`. Cada archivo maneja un recurso específico.

## Troubleshooting

### Error de Conexión a BD
- Verificar credenciales en `config/database.php`
- Comprobar que MySQL esté ejecutándose
- Verificar permisos del usuario de BD

### Archivos no se Suben
- Verificar permisos de `uploads/`
- Comprobar `upload_max_filesize` en PHP
- Revisar espacio en disco

### CORS Errors
- Verificar configuración en `config/cors.php`
- Comprobar que el dominio esté permitido

## Licencia

Proyecto desarrollado para uso interno de la empresa.