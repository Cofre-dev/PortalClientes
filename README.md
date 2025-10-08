# 🏢 Portal de Clientes

<div align="center">
[JWT](https://img.shields.io/badge/JWT-000000?style=for-the-badge&logo=JSON%20web%20tokens&logoColor=white)

**Sistema integral de gestión documental y administración para empresas consultoras**

[🚀 Instalación](#-instalación) • [📖 Documentación](#-documentación) • [🎯 Características](#-características) • [🛠️ Tecnologías](#️-tecnologías)

</div>

---

## 📋 Tabla de Contenidos

- [🎯 Características](#-características)
- [🛠️ Tecnologías](#️-tecnologías)
- [📁 Estructura del Proyecto](#-estructura-del-proyecto)
- [🚀 Instalación](#-instalación)
- [⚙️ Configuración](#️-configuración)
- [📖 Documentación de la API](#-documentación-de-la-api)
- [🎨 Interfaz de Usuario](#-interfaz-de-usuario)
- [🔐 Autenticación](#-autenticación)
- [👨‍💼 Panel de Administración](#-panel-de-administración)
- [👤 Portal del Cliente](#-portal-del-cliente)
- [📱 Responsivo](#-responsivo)
- [🧪 Testing](#-testing)
- [🚀 Despliegue](#-despliegue)
- [🤝 Contribución](#-contribución)
- [📄 Licencia](#-licencia)

---

## 🎯 Características

### ✨ Características Principales

- **🔐 Autenticación JWT Segura** - Sistema de login con tokens JWT para máxima seguridad
- **👥 Gestión Multiusuario** - Soporte para administradores y clientes con roles específicos
- **📁 Gestión Documental Completa** - Upload, download, visualización y organización de documentos
- **📊 Dashboard Administrativo** - Panel completo con estadísticas en tiempo real
- **🎨 Diseño Moderno** - Interfaz glassmorphism responsive y elegante
- **⚡ Tiempo Real** - Actualizaciones automáticas de datos y estadísticas
- **🔍 Búsqueda Avanzada** - Filtros por cliente, tipo de documento y texto
- **📱 100% Responsive** - Optimizado para desktop, tablet y móvil

### 🏢 Para Administradores

- ✅ **Dashboard Completo** con estadísticas del sistema
- ✅ **Gestión de Usuarios** - Crear/editar/eliminar clientes y administradores
- ✅ **Gestión de Documentos** - Ver, subir, descargar y eliminar documentos
- ✅ **Tipos de Documento** - Crear y gestionar categorías de documentos
- ✅ **Asignación de Documentos** - Vincular tipos de documentos a clientes
- ✅ **Reportes y Estadísticas** - Métricas en tiempo real del sistema
- ✅ **Drag & Drop** - Carga de archivos por arrastrar y soltar

### 👤 Para Clientes

- ✅ **Portal Personalizado** - Dashboard específico por empresa
- ✅ **Gestión de Documentos** - Ver y descargar documentos asignados
- ✅ **Upload de Archivos** - Subir documentos con correlativo automático
- ✅ **Historial Completo** - Seguimiento de todos los documentos
- ✅ **Perfil de Usuario** - Información de la empresa y contacto

---

## 🛠️ Tecnologías

### 🎨 Frontend
```json
{
  "framework": "Vue.js 3.5.17",
  "router": "Vue Router 4.5.1",
  "http": "Axios 1.10.0",
  "bundler": "Vite 7.0.0",
  "styling": "CSS3 + Variables",
  "icons": "Font Awesome",
  "effects": "Glassmorphism + Animations"
}
```

### 🔧 Backend
```json
{
  "framework": "Django 5.2.3",
  "api": "Django REST Framework 3.16.0",
  "auth": "Simple JWT 5.5.0",
  "database": "PostgreSQL + SQLite",
  "cors": "Django CORS Headers 4.7.0",
  "server": "Gunicorn 23.0.0",
  "storage": "WhiteNoise 6.9.0"
}
```

### 🗄️ Base de Datos
- **PostgreSQL** - Producción
- **SQLite** - Desarrollo
- **Migraciones Django** - Control de versiones de BD

---

## 📁 Estructura del Proyecto

```
PortalClientes/
├── 🎨 frontend/                     # Aplicación Vue.js
│   ├── 📄 public/                   # Archivos estáticos
│   ├── 🎯 src/                      # Código fuente
│   │   ├── 🧩 components/           # Componentes Vue
│   │   │   ├── DashboardAdmin.vue   # Panel administrador
│   │   │   ├── DashboardCliente.vue # Portal cliente
│   │   │   └── HelloWorld.vue       # Componente base
│   │   ├── 🖼️ assets/              # Recursos (CSS, imágenes)
│   │   ├── 🛣️ router/              # Configuración de rutas
│   │   ├── 📱 views/               # Vistas principales
│   │   │   ├── LoginView.vue        # Vista de login
│   │   │   └── PortalView.vue       # Vista portal
│   │   ├── App.vue                  # Componente raíz
│   │   └── main.js                  # Punto de entrada
│   ├── 📦 package.json              # Dependencias frontend
│   ├── ⚙️ vite.config.js           # Configuración Vite
│   └── 🔧 jsconfig.json            # Configuración JavaScript
├── 🔧 backend/                      # API Django
│   └── Customers/                   # Proyecto Django
│       ├── 🏢 Portal/              # App principal
│       │   ├── 📊 models.py        # Modelos de datos
│       │   ├── 🔗 views.py         # Endpoints API
│       │   ├── 📝 serializers.py   # Serializadores
│       │   ├── 🛣️ urls.py          # URLs de la app
│       │   └── 🔧 admin.py         # Admin Django
│       ├── ⚙️ Customers/           # Configuración proyecto
│       │   ├── settings.py          # Configuración Django
│       │   ├── urls.py              # URLs principales
│       │   └── wsgi.py              # WSGI config
│       ├── 🗄️ db.sqlite3           # Base de datos desarrollo
│       ├── 📁 media/               # Archivos subidos
│       ├── 📋 requirements.txt      # Dependencias Python
│       └── 🚀 manage.py            # CLI Django
└── 📖 README.md                     # Este archivo
```

---

## 🚀 Instalación

### 📋 Prerrequisitos

```bash
# Verificar versiones mínimas
node --version    # >= 18.0.0
npm --version     # >= 8.0.0
python --version  # >= 3.9.0
pip --version     # >= 21.0.0
```

### 🔧 Configuración del Entorno

#### 1️⃣ Clonar el Repositorio
```bash
git clone <repository-url>
cd PortalClientes
```

#### 2️⃣ Configurar Backend (Django)
```bash
# Navegar al directorio backend
cd backend/Customers

# Crear entorno virtual
python -m venv venv

# Activar entorno virtual
# Windows:
venv\Scripts\activate
# Mac/Linux:
source venv/bin/activate

# Instalar dependencias
pip install -r requirements.txt

# Configurar base de datos
python manage.py makemigrations
python manage.py migrate

# Crear superusuario (opcional)
python manage.py createsuperuser

# Iniciar servidor de desarrollo
python manage.py runserver
```

#### 3️⃣ Configurar Frontend (Vue.js)
```bash
# Navegar al directorio frontend
cd frontend

# Instalar dependencias
npm install

# Iniciar servidor de desarrollo
npm run dev
```

### 🌐 Acceso a la Aplicación

| Servicio | URL | Descripción |
|----------|-----|-------------|
| 🎨 Frontend | `http://localhost:5173` | Interfaz de usuario |
| 🔧 Backend API | `http://localhost:8000` | API REST |
| 👨‍💼 Django Admin | `http://localhost:8000/admin` | Panel admin Django |

---

## ⚙️ Configuración

### 🔧 Variables de Entorno

#### Frontend (.env)
```bash
# Crear archivo .env en /frontend
VITE_API_URL=http://localhost:8000
VITE_APP_TITLE="Portal Ara y Bustamante"
```

#### Backend (.env)
```bash
# Crear archivo .env en /backend/Customers
DEBUG=True
SECRET_KEY=tu-clave-secreta-aqui
DATABASE_URL=postgresql://user:password@localhost:5432/portaldb
ALLOWED_HOSTS=localhost,127.0.0.1
CORS_ALLOWED_ORIGINS=http://localhost:5173
```

### 🗄️ Configuración de Base de Datos

#### SQLite (Desarrollo)
```python
# settings.py - Ya configurado por defecto
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.sqlite3',
        'NAME': BASE_DIR / 'db.sqlite3',
    }
}
```

#### PostgreSQL (Producción)
```python
# settings.py - Para producción
DATABASES = {
    'default': {
        'ENGINE': 'django.db.backends.postgresql',
        'NAME': 'portaldb',
        'USER': 'usuario',
        'PASSWORD': 'contraseña',
        'HOST': 'localhost',
        'PORT': '5432',
    }
}
```
---

## 📖 Documentación de la API

### 🔐 Autenticación

#### Login
```http
POST /api/token/
Content-Type: application/json

{
  "username": "usuario",
  "password": "contraseña"
}
```

**Respuesta:**
```json
{
  "access": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "refresh": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

#### Refresh Token
```http
POST /api/token/refresh/
Content-Type: application/json

{
  "refresh": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
}
```

### 👤 Perfil de Usuario
```http
GET /api/me/
Authorization: Bearer <access_token>
```

### 🏢 Endpoints de Clientes

| Método | Endpoint | Descripción | Permisos |
|--------|----------|-------------|----------|
| `GET` | `/api/clientes/` | Listar clientes | Admin |
| `POST` | `/api/clientes/` | Crear cliente | Admin |
| `GET` | `/api/clientes/{id}/` | Detalle cliente | Admin |
| `PUT` | `/api/clientes/{id}/` | Actualizar cliente | Admin |
| `DELETE` | `/api/clientes/{id}/` | Eliminar cliente | Admin |

### 👨‍💼 Endpoints de Administradores

| Método | Endpoint | Descripción | Permisos |
|--------|----------|-------------|----------|
| `GET` | `/api/administradores/` | Listar admins | Admin |
| `POST` | `/api/administradores/` | Crear admin | Admin |
| `DELETE` | `/api/administradores/{id}/` | Eliminar admin | Admin |

### 📁 Endpoints de Documentos

| Método | Endpoint | Descripción | Permisos |
|--------|----------|-------------|----------|
| `GET` | `/api/categorias/` | Categorías documentos | Autenticado |
| `POST` | `/api/categorias/{id}/upload-file/` | Subir archivo | Cliente |
| `POST` | `/api/categorias/{id}/subir-consultora/` | Subir archivo consultora | Admin |
| `GET` | `/api/admin-documents/` | Todos los documentos | Admin |
| `POST` | `/api/admin-upload/` | Subir como admin | Admin |
| `DELETE` | `/api/archivos/{id}/` | Eliminar archivo | Admin |

### 📊 Endpoints de Estadísticas

| Método | Endpoint | Descripción | Permisos |
|--------|----------|-------------|----------|
| `GET` | `/api/admin-stats/` | Estadísticas sistema | Admin |
| `POST` | `/api/asignar-documentos/` | Asignar documentos | Admin |

### 📝 Tipos de Documento

| Método | Endpoint | Descripción | Permisos |
|--------|----------|-------------|----------|
| `GET` | `/api/tipos-documento/` | Listar tipos | Admin |
| `POST` | `/api/tipos-documento/` | Crear tipo | Admin |
| `DELETE` | `/api/tipos-documento/{id}/` | Eliminar tipo | Admin |

---

## 🎨 Interfaz de Usuario

### 🎭 Diseño y Estilo

- **🎨 Glassmorphism**: Efectos de vidrio modernos con `backdrop-filter`
- **🌈 Paleta de Colores**:
  - Azul Principal: `#021144`
  - Verde Principal: `#2bd17b`
  - Gradientes suaves entre tonos
- **📱 Responsive Design**: Breakpoints optimizados para todos los dispositivos
- **⚡ Animaciones**: Transiciones suaves y hover effects
- **🎯 UX Intuitivo**: Navegación clara y feedback visual

### 🧩 Componentes Principales

#### 🏠 App.vue
- Layout principal de la aplicación
- Header dinámico que se oculta en el portal
- Manejo de rutas y navegación

#### 🔐 LoginView.vue
- Formulario de login con validación
- Efectos glassmorphism
- Toggle de visibilidad de contraseña
- Estados de carga y error

#### 👨‍💼 DashboardAdmin.vue
- Panel completo de administración
- Navegación por tabs
- Gestión de usuarios y documentos
- Estadísticas en tiempo real
- Drag & drop para archivos

#### 👤 DashboardCliente.vue
- Portal específico para cada cliente
- Vista de documentos asignados
- Upload de archivos con correlativo
- Perfil de empresa

---

## 🔐 Autenticación

### 🎫 JWT (JSON Web Tokens)

El sistema utiliza **Django REST Framework Simple JWT** para autenticación:

```javascript
// Interceptor de Axios para tokens automáticos
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('accessToken')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  }
)
```

### 🔒 Roles y Permisos

| Rol | Permisos |
|-----|----------|
| **👨‍💼 Administrador** | Acceso completo al sistema, gestión de usuarios, documentos y estadísticas |
| **👤 Cliente** | Acceso limitado a sus documentos, upload de archivos, descarga |

### 🛡️ Middleware de Seguridad

```python
# settings.py - Configuración de seguridad
CORS_ALLOWED_ORIGINS = [
    "http://localhost:5173",  # Frontend desarrollo
]

SIMPLE_JWT = {
    'ACCESS_TOKEN_LIFETIME': timedelta(minutes=60),
    'REFRESH_TOKEN_LIFETIME': timedelta(days=7),
    'ROTATE_REFRESH_TOKENS': True,
}
```

---

## 👨‍💼 Panel de Administración

### 📊 Dashboard Principal

- **📈 Estadísticas en Tiempo Real**:
  - Total de clientes
  - Total de documentos
  - Archivos subidos (últimos 6 meses)
  - Administradores activos

- **⚡ Acciones Rápidas**:
  - Crear usuarios
  - Subir documentos
  - Asignar categorías
  - Actualizar datos

### 👥 Gestión de Usuarios

#### Crear Cliente
```json
{
  "razon_social": "Empresa Ejemplo SPA",
  "rut_empresa": "12345678-9",
  "username": "empresa_usuario",
  "password": "contraseña_segura",
  "email": "contacto@empresa.com",
  "first_name": "Juan",
  "last_name": "Pérez"
}
```

#### Crear Administrador
```json
{
  "username": "admin_usuario",
  "password": "contraseña_segura",
  "rol": "Contador",
  "email": "admin@araybustamante.com",
  "first_name": "María",
  "last_name": "González"
}
```

### 📁 Gestión de Documentos

- **👀 Ver Documentos**: Tabla con filtros por cliente, tipo y búsqueda
- **⬆️ Subir Documentos**: Drag & drop con validación de tamaño
- **⬇️ Descargar**: Enlaces directos a archivos
- **🗑️ Eliminar**: Con confirmación de seguridad

### 🏷️ Tipos de Documento

Ejemplos de tipos comunes:
- Balance General
- Estado de Resultados
- Certificado de Renta
- Declaración IVA
- Nómina de Trabajadores

---

## 👤 Portal del Cliente

### 🏠 Dashboard Personalizado

- **🏢 Información de la Empresa**: Razón social, RUT
- **📊 Resumen de Documentos**: Cantidad total y por tipo
- **📅 Actividad Reciente**: Últimos documentos subidos

### 📁 Gestión de Documentos

- **📋 Lista de Categorías**: Documentos asignados por el administrador
- **⬆️ Subir Archivos**:
  - Validación de tipo y tamaño
  - Correlativo automático
  - Feedback visual de progreso

- **📖 Historial**: Todos los archivos subidos con:
  - Fecha y hora
  - Correlativo
  - Quien lo subió (cliente o consultora)

### 👤 Perfil de Usuario

- Información de contacto
- Datos de la empresa
- Estadísticas personales

---

## 📱 Responsivo

### 📏 Breakpoints

```css
/* Tablet */
@media (max-width: 968px) {
  .main-header { width: 100%; }
  .main-content { margin-left: 0; }
}

/* Mobile */
@media (max-width: 768px) {
  .admin-dashboard { padding: 1rem; }
  .stat-card { min-width: 100%; }
}

/* Small Mobile */
@media (max-width: 480px) {
  .hero-title { font-size: 1.8rem; }
  .form-row { flex-direction: column; }
}
```

### 📲 Características Mobile

- **📱 Navigation**: Menú hamburguesa automático
- **👆 Touch Friendly**: Botones y áreas de toque optimizadas
- **📸 Upload Mobile**: Soporte para cámara y galería
- **💬 Feedback**: Toasts y alertas adaptadas

---

## 🧪 Testing

### 🔧 Frontend Testing

```bash
# Lint y formato
npm run lint
npm run format

# Build de producción
npm run build
npm run preview
```

### 🔧 Backend Testing

```bash
# Tests de Django
python manage.py test

# Verificar migraciones
python manage.py check

# Colectar archivos estáticos
python manage.py collectstatic
```

### 🌐 Testing de API

```bash
# Usando curl - Test de login
curl -X POST http://localhost:8000/api/token/ \
  -H "Content-Type: application/json" \
  -d '{"username":"admin","password":"admin123"}'

# Test de endpoint protegido
curl -X GET http://localhost:8000/api/me/ \
  -H "Authorization: Bearer <token>"
```

---

## 🚀 Despliegue

### 🔧 Configuración de Producción

#### Frontend (Netlify/Vercel)
```bash
# Build de producción
npm run build

# Variables de entorno
VITE_API_URL=https://tu-api.herokuapp.com
```

#### Backend (Heroku/Railway)
```bash
# Procfile
web: gunicorn Customers.wsgi --log-file -

# Variables de entorno
DEBUG=False
SECRET_KEY=tu-clave-secreta-super-segura
DATABASE_URL=postgresql://...
ALLOWED_HOSTS=tu-dominio.com
```

### 🗄️ Base de Datos en Producción

```python
# settings.py - Configuración automática
import dj_database_url
DATABASES['default'] = dj_database_url.parse(
    os.environ.get('DATABASE_URL')
)
```

### 📁 Archivos Estáticos

```python
# settings.py - WhiteNoise para archivos estáticos
MIDDLEWARE = [
    'whitenoise.middleware.WhiteNoiseMiddleware',
    # ... otros middlewares
]

STATICFILES_STORAGE = 'whitenoise.storage.CompressedManifestStaticFilesStorage'
```

---

## 🤝 Contribución

### 🔧 Setup de Desarrollo

1. **Fork** el repositorio
2. **Clone** tu fork localmente
3. **Crea** una rama para tu feature: `git checkout -b feature/nueva-funcionalidad`
4. **Desarrolla** y **testea** tus cambios
5. **Commit** con mensajes descriptivos
6. **Push** a tu fork: `git push origin feature/nueva-funcionalidad`
7. **Crea** un Pull Request

### 📝 Estándares de Código

#### Frontend (Vue.js)
```javascript
// Usar Composition API
import { ref, onMounted } from 'vue'

// Nombres descriptivos en español
const isLoading = ref(false)
const adminStats = ref({})

// Funciones async/await
async function fetchData() {
  try {
    const response = await apiClient.get('/api/data')
    return response.data
  } catch (error) {
    console.error('Error:', error)
  }
}
```

#### Backend (Django)
```python
# Docstrings en español
def admin_stats(request):
    """Endpoint para obtener estadísticas del panel de administrador"""
    if not is_admin(request.user):
        return Response(
            {'error': 'Solo administradores pueden ver estadísticas'},
            status=status.HTTP_403_FORBIDDEN
        )
```

### 🐛 Reporte de Bugs

Incluir en el issue:
- **📝 Descripción** detallada del problema
- **🔄 Pasos** para reproducir
- **💻 Entorno** (OS, browser, versions)
- **📷 Screenshots** si es aplicable
- **📋 Logs** relevantes

---

## 📄 Licencia

Este proyecto está bajo la **Licencia MIT**.

```
MIT License

Copyright (c) 2024 Ara y Bustamante Consultores

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 📞 Contacto y Soporte

### 🏢 Ara y Bustamante Consultores
- **🌐 Web**: [www.araybustamante.com](https://www.araybustamante.com)
- **📧 Email**: info@araybustamante.com
- **📱 Teléfono**: +56 2 2XXX XXXX

### 👨‍💻 Desarrollador
- **🐙 GitHub**: [github.com/usuario](https://github.com/usuario)
- **📧 Email**: desarrollador@araybustamante.com

---

<div align="center">

**🚀 ¡Gracias por usar Portal de Clientes! 🚀**

*Desarrollado con ❤️ para Ara y Bustamante Consultores*

[![Volver arriba](https://img.shields.io/badge/⬆️-Volver%20arriba-blue?style=flat-square)](#-portal-de-clientes---ara-y-bustamante-consultores)

</div>
