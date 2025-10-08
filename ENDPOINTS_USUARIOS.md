# Documentación de Endpoints - Gestión de Usuarios y Clientes

## Autenticación

Todos los endpoints requieren autenticación con token JWT en el header:
```
Authorization: Bearer {token}
```

Solo usuarios con rol `admin` pueden acceder a estos endpoints.

---

## 1. Crear Usuario Administrador o Cliente

**Endpoint:** `POST /api/admin/usuarios/create`

**Descripción:** Crea un nuevo usuario en el sistema (administrador o cliente).

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {admin_token}
```

**Body - Crear Administrador:**
```json
{
  "username": "admin2",
  "email": "admin2@araybustamante.com",
  "password": "Admin123!",
  "role": "admin"
}
```

**Body - Crear Usuario Cliente:**
```json
{
  "username": "cliente2",
  "email": "cliente2@empresa.com",
  "password": "Cliente123!",
  "role": "cliente"
}
```

**Validaciones:**
- `username`: Requerido, solo letras, números y guiones bajos
- `email`: Requerido, formato válido de email
- `password`: Requerido, mínimo 6 caracteres
- `role`: Requerido, valores: "admin" o "cliente"

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "message": "Usuario creado exitosamente",
  "user_id": 3
}
```

**Respuestas de Error:**

400 - Datos inválidos:
```json
{
  "success": false,
  "message": "Todos los campos obligatorios deben ser completados"
}
```

400 - Usuario duplicado:
```json
{
  "success": false,
  "message": "Ya existe un usuario con este nombre de usuario"
}
```

401 - Sin autorización:
```json
{
  "error": "Token de autorización requerido"
}
```

403 - Permisos insuficientes:
```json
{
  "error": "Acceso denegado: se requieren permisos de administrador"
}
```

---

## 2. Listar Todos los Usuarios

**Endpoint:** `GET /api/admin/usuarios`

**Descripción:** Obtiene lista de todos los usuarios registrados.

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "username": "admin",
      "email": "admin@araybustamante.com",
      "role": "admin",
      "is_active": true,
      "created_at": "2024-01-01 00:00:00"
    },
    {
      "id": 2,
      "username": "cliente1",
      "email": "cliente1@empresa.com",
      "role": "cliente",
      "is_active": true,
      "created_at": "2024-01-02 10:30:00"
    }
  ]
}
```

---

## 3. Crear Cliente (Empresa)

**Endpoint:** `POST /api/admin/clientes/create`

**Descripción:** Crea un nuevo cliente (empresa) en el sistema. Opcionalmente puede crear el usuario asociado automáticamente.

**Headers:**
```
Content-Type: application/json
Authorization: Bearer {admin_token}
```

**Opción 1 - Crear Cliente con Usuario Nuevo:**
```json
{
  "razon_social": "Empresa Demo S.A.",
  "rut_empresa": "76.123.456-7",
  "email": "contacto@empresa.com",
  "telefono": "+56 9 1234 5678",
  "direccion": "Av. Principal 123, Santiago",
  "nombre_cliente": "Juan Pérez",
  "crear_usuario": true,
  "username": "empresa_demo",
  "password": "Demo123!"
}
```

**Opción 2 - Crear Cliente sin Usuario:**
```json
{
  "razon_social": "Empresa Demo S.A.",
  "rut_empresa": "76.123.456-7",
  "email": "contacto@empresa.com",
  "telefono": "+56 9 1234 5678",
  "direccion": "Av. Principal 123, Santiago"
}
```

**Opción 3 - Asociar Cliente a Usuario Existente:**
```json
{
  "razon_social": "Empresa Demo S.A.",
  "rut_empresa": "76.123.456-7",
  "email": "contacto@empresa.com",
  "telefono": "+56 9 1234 5678",
  "direccion": "Av. Principal 123, Santiago",
  "user_id": 5
}
```

**Campos:**
- `razon_social`: Requerido - Nombre legal de la empresa
- `rut_empresa`: Requerido - RUT de la empresa (formato: XX.XXX.XXX-X)
- `email`: Requerido - Email de contacto de la empresa
- `telefono`: Opcional - Teléfono de contacto
- `direccion`: Opcional - Dirección física de la empresa
- `nombre_cliente`: Opcional - Nombre del contacto principal
- `crear_usuario`: Boolean - Si es true, crea usuario automáticamente
- `username`: Requerido si `crear_usuario` es true
- `password`: Requerido si `crear_usuario` es true
- `user_id`: ID de usuario existente para asociar

**Respuesta Exitosa (201):**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "razon_social": "Empresa Demo S.A.",
    "rut_empresa": "76.123.456-7",
    "email": "contacto@empresa.com",
    "telefono": "+56 9 1234 5678",
    "direccion": "Av. Principal 123, Santiago",
    "user_id": 5,
    "created_at": "2025-09-29 15:30:00"
  },
  "user_id": 5,
  "message": "Cliente creado exitosamente con usuario asociado"
}
```

**Respuestas de Error:**

400 - Campo requerido faltante:
```json
{
  "success": false,
  "message": "El campo razon_social es requerido"
}
```

400 - RUT duplicado:
```json
{
  "success": false,
  "message": "Error al crear cliente: El RUT de empresa ya está registrado"
}
```

---

## 4. Listar Todos los Clientes

**Endpoint:** `GET /api/admin/clientes`

**Descripción:** Obtiene lista de todos los clientes con información de usuario asociado.

**Headers:**
```
Authorization: Bearer {admin_token}
```

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "razon_social": "Empresa Demo S.A.",
      "rut_empresa": "76.123.456-7",
      "cliente_email": "contacto@empresa.com",
      "telefono": "+56 9 1234 5678",
      "direccion": "Av. Principal 123, Santiago",
      "user_id": 2,
      "username": "cliente1",
      "is_active": true,
      "total_documentos": 15,
      "created_at": "2024-01-01 00:00:00"
    }
  ],
  "message": "Clientes obtenidos exitosamente"
}
```

---

## 5. Obtener Cliente Específico

**Endpoint:** `GET /api/admin/clientes/{id}`

**Descripción:** Obtiene información detallada de un cliente específico.

**Ejemplo:** `GET /api/admin/clientes/1`

**Respuesta Exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "razon_social": "Empresa Demo S.A.",
    "rut_empresa": "76.123.456-7",
    "email": "contacto@empresa.com",
    "telefono": "+56 9 1234 5678",
    "direccion": "Av. Principal 123, Santiago",
    "user_id": 2,
    "created_at": "2024-01-01 00:00:00"
  },
  "message": "cliente encontrado"
}
```

---

## Ejemplos de Uso con cURL

### Crear Administrador
```bash
curl -X POST http://localhost/api/admin/usuarios/create \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "username": "admin2",
    "email": "admin2@araybustamante.com",
    "password": "Admin123!",
    "role": "admin"
  }'
```

### Crear Cliente con Usuario
```bash
curl -X POST http://localhost/api/admin/clientes/create \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  -d '{
    "razon_social": "Nueva Empresa SPA",
    "rut_empresa": "77.888.999-0",
    "email": "contacto@nuevaempresa.cl",
    "telefono": "+56 9 8765 4321",
    "direccion": "Calle Nueva 456, Valparaíso",
    "crear_usuario": true,
    "username": "nuevaempresa",
    "password": "Empresa123!"
  }'
```

### Listar Usuarios
```bash
curl -X GET http://localhost/api/admin/usuarios \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

### Listar Clientes
```bash
curl -X GET http://localhost/api/admin/clientes \
  -H "Authorization: Bearer YOUR_ADMIN_TOKEN"
```

---

## Flujo Completo: Crear Cliente con Acceso al Sistema

1. **Crear usuario cliente**:
   ```json
   POST /api/admin/usuarios/create
   {
     "username": "empresa_xyz",
     "email": "contacto@xyz.com",
     "password": "Xyz123!",
     "role": "cliente"
   }
   ```
   Respuesta: `{"user_id": 10}`

2. **Crear empresa asociada**:
   ```json
   POST /api/admin/clientes/create
   {
     "razon_social": "XYZ Limitada",
     "rut_empresa": "77.555.666-8",
     "email": "contacto@xyz.com",
     "telefono": "+56 9 5555 6666",
     "user_id": 10
   }
   ```

3. **O crear ambos en un solo paso**:
   ```json
   POST /api/admin/clientes/create
   {
     "razon_social": "XYZ Limitada",
     "rut_empresa": "77.555.666-8",
     "email": "contacto@xyz.com",
     "telefono": "+56 9 5555 6666",
     "crear_usuario": true,
     "username": "empresa_xyz",
     "password": "Xyz123!"
   }
   ```

Ahora el cliente puede iniciar sesión con:
- Username: `empresa_xyz`
- Password: `Xyz123!`

---

## Notas Importantes

1. **Seguridad**: Todos los endpoints verifican que el usuario autenticado sea administrador
2. **Validaciones**: Los passwords se hashean automáticamente con bcrypt
3. **Duplicados**: El sistema previene duplicación de usernames, emails y RUTs
4. **Relaciones**: Al crear cliente con usuario, se establece la relación automáticamente
5. **Formato RUT**: Se acepta con o sin puntos y guión (XX.XXX.XXX-X o XXXXXXXXX)
6. **Emails**: Se convierten automáticamente a minúsculas

---

## Códigos de Estado HTTP

- `200` - OK (operación exitosa GET)
- `201` - Created (recurso creado exitosamente)
- `400` - Bad Request (datos inválidos)
- `401` - Unauthorized (sin token o token inválido)
- `403` - Forbidden (sin permisos de admin)
- `404` - Not Found (recurso no encontrado)
- `405` - Method Not Allowed (método HTTP incorrecto)
- `500` - Internal Server Error (error del servidor)