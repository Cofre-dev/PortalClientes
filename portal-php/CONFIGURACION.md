# 🏢 ARA & Bustamante Consultores - Portal de Clientes

## 📋 Guía de Configuración y Despliegue

### 🚀 Configuración Rápida en XAMPP

#### 1. **Preparar el Entorno**
```bash
# 1. Asegúrate de que XAMPP esté instalado y funcionando
# 2. Copia la carpeta del proyecto a C:\xampp\htdocs\portal-php
# 3. Inicia Apache y MySQL desde el panel de XAMPP
```

#### 2. **Configurar la Base de Datos**
```sql
-- 1. Abre phpMyAdmin (http://localhost/phpmyadmin)
-- 2. Ejecuta el archivo: database/schema.sql
-- 3. Esto creará la base de datos 'portal_clientes' con todas las tablas
```

#### 3. **Configurar el Archivo de Conexión**
Edita `config/database.php` si es necesario:
```php
<?php
$host = 'localhost';
$dbname = 'portal_clientes';
$username = 'root';
$password = ''; // En XAMPP por defecto es vacío
?>
```

#### 4. **Acceder al Sistema**
- **Portal Cliente**: http://localhost/portal-php/
- **Panel Admin**: http://localhost/portal-php/admin/

---

## 👥 Usuarios Predefinidos

### 🔑 Administrador
- **Usuario**: `admin`
- **Contraseña**: `admin123`
- **Permisos**: Acceso completo al sistema

### 👤 Cliente de Ejemplo
- **Usuario**: `cliente1`
- **Contraseña**: `cliente123`
- **Empresa**: Empresa Ejemplo S.A.
- **RUT**: 12345678-9

---

## 🎨 Características de la Interfaz

### ✨ **Portal de Cliente**
- **Diseño elegante** con colores corporativos (azul marino y dorado)
- **Gestión por categorías** de documentos
- **Drag & Drop** para subir archivos
- **Vista de perfil** con información de la empresa
- **Responsive design** para móviles y tablets

### 🛡️ **Panel de Administrador**
- **Dashboard completo** con estadísticas
- **CRUD de usuarios** y empresas
- **Gestión de documentos** avanzada
- **Sistema de categorías** global
- **Logs y auditoría** del sistema
- **Configuración del sistema**

---

## 📊 Funcionalidades Implementadas

### 📁 **Gestión de Documentos**
- ✅ Subida de archivos por categorías
- ✅ Validación de tipos de archivo (.pdf, .doc, .docx, .xls, .xlsx, .jpg, .png)
- ✅ Límite de tamaño (10MB por archivo)
- ✅ Descarga de documentos
- ✅ Eliminación controlada (clientes solo pueden eliminar sus archivos)

### 🏷️ **Sistema de Categorías**
- ✅ 10 categorías predefinidas (Balance General, Facturas, etc.)
- ✅ Creación de categorías personalizadas por cliente
- ✅ Iconos y colores configurables
- ✅ Conteo de documentos por categoría

### 👥 **Gestión de Usuarios**
- ✅ Registro de nuevas empresas
- ✅ Autenticación segura con JWT
- ✅ Roles diferenciados (admin/cliente)
- ✅ Información completa de empresas

### 🔐 **Seguridad**
- ✅ Hashing de contraseñas con bcrypt
- ✅ Tokens JWT con expiración
- ✅ Validación de archivos subidos
- ✅ Headers de seguridad HTTP
- ✅ Auditoría de acciones (triggers en BD)

---

## 🗃️ Estructura de la Base de Datos

### 📋 **Tablas Principales**
- `usuarios` - Usuarios del sistema (admin/cliente)
- `clientes` - Información extendida de empresas
- `categorias_documento` - Tipos de documentos
- `documentos` - Archivos subidos
- `historial_acciones` - Auditoría del sistema
- `notificaciones` - Sistema de alertas
- `configuracion` - Ajustes del sistema

### 🔍 **Vistas Útiles**
- `vista_documentos_cliente` - Documentos con información completa
- `vista_estadisticas_cliente` - Estadísticas por empresa

### ⚡ **Triggers de Auditoría**
- Registro automático de subidas de archivos
- Registro automático de eliminaciones
- Registro automático de logins exitosos

---

## 🎯 Categorías Predefinidas

1. **Balance General** - Estados financieros patrimoniales
2. **Estado de Resultados** - Ingresos y gastos del período
3. **Flujo de Efectivo** - Movimientos de caja
4. **Declaración de Impuestos** - Formularios tributarios
5. **Análisis Financiero** - Reportes y proyecciones
6. **Cartola Bancaria** - Estados de cuenta bancarios
7. **Facturas** - Facturas de venta y compra
8. **Boletas** - Boletas de honorarios
9. **Contratos** - Documentos contractuales
10. **Nóminas** - Liquidaciones de sueldo

---

## 🛠️ Solución de Problemas

### ❌ **"No se ve la interfaz"**
- Verifica que Apache esté ejecutándose en XAMPP
- Asegúrate de acceder a `http://localhost/portal-php/`
- Revisa que no haya errores en `C:\xampp\apache\logs\error.log`

### ❌ **"Error de conexión a la base de datos"**
- Verifica que MySQL esté ejecutándose en XAMPP
- Comprueba que la base de datos `portal_clientes` existe
- Revisa las credenciales en `config/database.php`

### ❌ **"No puedo subir archivos"**
- Verifica permisos de la carpeta `uploads/`
- Comprueba `upload_max_filesize` en `php.ini`
- Asegúrate de que el tipo de archivo esté permitido

### ❌ **"Error de autenticación"**
- Verifica que los usuarios existan en la base de datos
- Comprueba que las contraseñas sean correctas
- Revisa que el token JWT no haya expirado

---

## 🔧 Configuraciones Avanzadas

### 📁 **Límites de Archivos**
Edita `php.ini` en XAMPP:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

### 🔒 **Configuración de Seguridad**
En producción, considera:
- Cambiar las contraseñas por defecto
- Configurar HTTPS
- Ajustar permisos de archivos
- Configurar backups automáticos

### 📧 **Configuración de Email** (Opcional)
Para notificaciones por email, configura:
```php
// En config/email.php
$smtp_host = 'smtp.gmail.com';
$smtp_port = 587;
$smtp_user = 'tu-email@gmail.com';
$smtp_pass = 'tu-contraseña';
```

---

## 🎨 Personalización

### 🎨 **Cambiar Colores**
Edita las variables CSS en `index.php`:
```css
:root {
    --primary-color: #1a365d;     /* Color principal */
    --secondary-color: #d4af37;   /* Color dorado */
    --accent-color: #2c5282;      /* Color de acento */
}
```

### 🏢 **Cambiar Información de la Empresa**
Edita la base de datos:
```sql
UPDATE configuracion
SET valor = 'Tu Empresa Consultora'
WHERE clave = 'company_name';
```

### 📧 **Cambiar Emails**
```sql
UPDATE configuracion
SET valor = 'contacto@tuempresa.com'
WHERE clave = 'company_email';
```

---

## 📈 Próximas Mejoras

### 🔄 **Funcionalidades Planificadas**
- [ ] Sistema de notificaciones por email
- [ ] Generación de reportes en PDF
- [ ] API REST completa
- [ ] App móvil
- [ ] Integración con sistemas contables
- [ ] Firmas digitales
- [ ] Versionado de documentos
- [ ] Chat en tiempo real

### 🔧 **Mejoras Técnicas**
- [ ] Cache de consultas frecuentes
- [ ] Compresión de archivos
- [ ] CDN para archivos estáticos
- [ ] Backup automático
- [ ] Monitoreo de performance
- [ ] Tests automatizados

---

## 📞 Soporte Técnico

### 📧 **Contacto**
- **Email**: soporte@arabustamante.cl
- **Teléfono**: +56 2 2345 6789
- **Horario**: Lunes a Viernes, 9:00 - 18:00

### 📚 **Documentación**
- Manual de usuario (próximamente)
- Documentación técnica de la API
- Videos tutoriales

---

## ✅ Lista de Verificación Post-Instalación

- [ ] XAMPP instalado y funcionando
- [ ] Base de datos creada exitosamente
- [ ] Portal cliente accesible en `http://localhost/portal-php/`
- [ ] Panel admin accesible en `http://localhost/portal-php/admin/`
- [ ] Login de administrador funciona (`admin` / `admin123`)
- [ ] Login de cliente funciona (`cliente1` / `cliente123`)
- [ ] Subida de archivos operativa
- [ ] Descarga de archivos operativa
- [ ] Creación de categorías funciona
- [ ] Interfaz responsive en móvil

¡Listo! Tu portal ARA & Bustamante está configurado y operativo. 🎉