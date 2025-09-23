<?php
require_once __DIR__ . '/../../config/Auth.php';
$auth = new Auth();

// Verificar autenticación de admin
if (!$auth->checkSessionTimeout()) {
    header('Location: /portal-php/admin/login');
    exit;
}

$csrfToken = $auth->getCSRFToken();
?>

<div class="admin-dashboard">
    <div class="admin-sidebar">
        <div class="sidebar-header">
            <div class="brand-logo">
                <i class="fas fa-calculator"></i>
                <div class="brand-text">
                    <h3>ARA & BUSTAMANTE</h3>
                    <span>Panel de Administración</span>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <h4>PANEL PRINCIPAL</h4>
                <ul>
                    <li><a href="#" class="nav-link active" data-tab="dashboard"><i class="fas fa-chart-line"></i>Dashboard</a></li>
                    <li><a href="#" class="nav-link" data-tab="usuarios"><i class="fas fa-users"></i>Usuarios</a></li>
                    <li><a href="#" class="nav-link" data-tab="clientes"><i class="fas fa-building"></i>Empresas</a></li>
                </ul>
            </div>

            <div class="nav-section">
                <h4>GESTIÓN</h4>
                <ul>
                    <li><a href="#" class="nav-link" data-tab="documentos"><i class="fas fa-folder-open"></i>Documentos</a></li>
                    <li><a href="#" class="nav-link" data-tab="categorias"><i class="fas fa-tags"></i>Categorías</a></li>
                    <li><a href="#" class="nav-link" data-tab="uploads"><i class="fas fa-cloud-upload-alt"></i>Subir Documentos</a></li>
                </ul>
            </div>

            <div class="nav-section">
                <h4>SISTEMA</h4>
                <ul>
                    <li><a href="#" class="nav-link" data-tab="security"><i class="fas fa-shield-alt"></i>Seguridad</a></li>
                    <li><a href="#" class="nav-link" data-tab="logs"><i class="fas fa-file-alt"></i>Logs</a></li>
                    <li><a href="#" class="nav-link" data-tab="configuracion"><i class="fas fa-cog"></i>Configuración</a></li>
                </ul>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="user-details">
                    <span class="user-name" id="adminUserInfo">Administrador</span>
                    <span class="user-role">Super Admin</span>
                </div>
            </div>
            <button id="adminLogoutBtn" class="btn-logout">
                <i class="fas fa-sign-out-alt"></i>
                Cerrar Sesión
            </button>
        </div>
    </div>

    <main class="admin-main">
        <header class="admin-header">
            <div class="header-left">
                <div class="breadcrumb">
                    <i class="fas fa-chart-line"></i>
                    <span id="currentPageTitle">Dashboard</span>
                </div>
            </div>

            <div class="header-right">
                <div class="header-stats">
                    <div class="stat-item">
                        <i class="fas fa-users"></i>
                        <span id="totalUsuarios">0</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-building"></i>
                        <span id="totalClientes">0</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-file-alt"></i>
                        <span id="totalDocumentos">0</span>
                    </div>
                </div>
                <div class="header-actions">
                    <a href="/portal-php/client/login" class="btn btn-outline" style="margin-left: 1rem;">
                        <i class="fas fa-user"></i>
                        Portal Cliente
                    </a>
                </div>
            </div>
        </header>

        <div class="admin-content">
            <!-- Dashboard Tab -->
            <div id="dashboard-tab" class="tab-content active">
                <div class="dashboard-grid">
                    <div class="stats-row">
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="dashboardUsuarios">0</h3>
                                <p>Usuarios Totales</p>
                                <span class="stat-change positive">Activos en el sistema</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon primary">
                                <i class="fas fa-building"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="dashboardClientes">0</h3>
                                <p>Empresas Registradas</p>
                                <span class="stat-change positive">Clientes activos</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon secondary">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="dashboardDocumentos">0</h3>
                                <p>Documentos Gestionados</p>
                                <span class="stat-change positive">Total en el sistema</span>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3 id="securityEvents">0</h3>
                                <p>Eventos de Seguridad</p>
                                <span class="stat-change neutral">Últimas 24h</span>
                            </div>
                        </div>
                    </div>

                    <div class="dashboard-widgets">
                        <div class="widget">
                            <div class="widget-header">
                                <h4><i class="fas fa-chart-bar"></i> Actividad Reciente</h4>
                            </div>
                            <div class="widget-content">
                                <div class="activity-list" id="recentActivity">
                                    <div class="activity-item">
                                        <div class="activity-icon success">
                                            <i class="fas fa-sync"></i>
                                        </div>
                                        <div class="activity-content">
                                            <p>Sistema iniciado correctamente</p>
                                            <span class="activity-time">Ahora</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="widget">
                            <div class="widget-header">
                                <h4><i class="fas fa-exclamation-triangle"></i> Estado del Sistema</h4>
                            </div>
                            <div class="widget-content">
                                <div class="alert-list">
                                    <div class="alert-item success">
                                        <i class="fas fa-shield-alt"></i>
                                        <span>Sistema seguro y funcionando</span>
                                    </div>
                                    <div class="alert-item success">
                                        <i class="fas fa-database"></i>
                                        <span>Base de datos operativa</span>
                                    </div>
                                    <div class="alert-item success">
                                        <i class="fas fa-check-circle"></i>
                                        <span>Todos los servicios activos</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usuarios Tab -->
            <div id="usuarios-tab" class="tab-content">
                <div class="content-header">
                    <h2><i class="fas fa-users"></i> Gestión de Usuarios</h2>
                    <p>Administra todos los usuarios del sistema</p>
                </div>
                <div id="usersContent">
                    <p>Cargando usuarios...</p>
                </div>
            </div>

            <!-- Clientes Tab -->
            <div id="clientes-tab" class="tab-content">
                <div class="content-header">
                    <h2><i class="fas fa-building"></i> Gestión de Empresas</h2>
                    <p>Administra las empresas clientes registradas</p>
                </div>
                <div id="clientsContent">
                    <p>Cargando empresas...</p>
                </div>
            </div>

            <!-- Documentos Tab -->
            <div id="documentos-tab" class="tab-content">
                <div class="content-header">
                    <h2><i class="fas fa-folder-open"></i> Gestión de Documentos</h2>
                    <p>Supervisa todos los documentos del sistema</p>
                </div>
                <div id="documentsContent">
                    <p>Cargando documentos...</p>
                </div>
            </div>

            <!-- Categorías Tab -->
            <div id="categorias-tab" class="tab-content">
                <div class="content-header">
                    <h2><i class="fas fa-tags"></i> Gestión de Categorías</h2>
                    <p>Administra las categorías de documentos</p>
                </div>
                <div id="categoriesContent">
                    <p>Cargando categorías...</p>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="security-tab" class="tab-content">
                <div class="content-header">
                    <h2><i class="fas fa-shield-alt"></i> Panel de Seguridad</h2>
                    <p>Monitorea la seguridad del sistema</p>
                </div>
                <div id="securityContent">
                    <p>Cargando información de seguridad...</p>
                </div>
            </div>

            <!-- Logs Tab -->
            <div id="logs-tab" class="tab-content">
                <div class="content-header">
                    <h2><i class="fas fa-file-alt"></i> Logs del Sistema</h2>
                    <p>Revisa los logs de actividad y errores</p>
                </div>
                <div id="logsContent">
                    <p>Cargando logs...</p>
                </div>
            </div>

            <!-- Otros tabs placeholders -->
            <div id="uploads-tab" class="tab-content">
                <div class="content-header">
                    <h2><i class="fas fa-cloud-upload-alt"></i> Subir Documentos</h2>
                    <p>Sube documentos para los clientes</p>
                </div>
                <div id="uploadsContent">
                    <p>Próximamente...</p>
                </div>
            </div>

            <div id="configuracion-tab" class="tab-content">
                <div class="content-header">
                    <h2><i class="fas fa-cog"></i> Configuración del Sistema</h2>
                    <p>Configura parámetros del sistema</p>
                </div>
                <div id="configContent">
                    <p>Próximamente...</p>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.content-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.content-header h2 {
    font-size: var(--font-size-2xl);
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.content-header p {
    color: var(--text-secondary);
    font-size: var(--font-size-lg);
}

.admin-dashboard .admin-main {
    margin-left: 280px;
}

.admin-dashboard .header-actions {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .admin-dashboard .admin-main {
        margin-left: 0;
    }

    .admin-dashboard .admin-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }
}
</style>

<script>
// Admin Dashboard App
class AdminDashboard {
    constructor() {
        this.baseURL = '/portal-php/api';
        this.userInfo = JSON.parse(localStorage.getItem('admin_userInfo') || '{}');
        this.init();
    }

    init() {
        this.checkAuth();
        this.bindEvents();
        this.updateUserInfo();
        this.loadDashboard();
    }

    checkAuth() {
        const token = localStorage.getItem('admin_token');
        if (!token || !this.userInfo.username || this.userInfo.role !== 'admin') {
            window.location.href = '/portal-php/admin/login';
            return;
        }
    }

    bindEvents() {
        // Admin logout
        document.getElementById('adminLogoutBtn').addEventListener('click', () => {
            this.logout();
        });

        // Navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                this.switchTab(link.dataset.tab);
            });
        });
    }

    updateUserInfo() {
        document.getElementById('adminUserInfo').textContent = this.userInfo.username || 'Administrador';
    }

    switchTab(tabName) {
        // Update navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');

        // Update breadcrumb
        const icons = {
            dashboard: 'fa-chart-line',
            usuarios: 'fa-users',
            clientes: 'fa-building',
            documentos: 'fa-folder-open',
            categorias: 'fa-tags',
            uploads: 'fa-cloud-upload-alt',
            security: 'fa-shield-alt',
            logs: 'fa-file-alt',
            configuracion: 'fa-cog'
        };

        const titles = {
            dashboard: 'Dashboard',
            usuarios: 'Usuarios',
            clientes: 'Empresas',
            documentos: 'Documentos',
            categorias: 'Categorías',
            uploads: 'Subir Documentos',
            security: 'Seguridad',
            logs: 'Logs del Sistema',
            configuracion: 'Configuración'
        };

        document.querySelector('.breadcrumb i').className = `fas ${icons[tabName]}`;
        document.getElementById('currentPageTitle').textContent = titles[tabName];

        // Load appropriate content
        this.loadTabContent(tabName);
    }

    loadTabContent(tabName) {
        switch (tabName) {
            case 'dashboard':
                this.loadDashboard();
                break;
            case 'usuarios':
                this.loadUsers();
                break;
            case 'clientes':
                this.loadClients();
                break;
            case 'documentos':
                this.loadDocuments();
                break;
            case 'categorias':
                this.loadCategories();
                break;
            case 'security':
                this.loadSecurity();
                break;
            case 'logs':
                this.loadLogs();
                break;
        }
    }

    async logout() {
        try {
            await makeRequest('/portal-php/api/auth/logout', { method: 'POST' });
        } catch (error) {
            console.error('Logout error:', error);
        }

        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_userInfo');
        showNotification('Sesión administrativa cerrada');

        setTimeout(() => {
            window.location.href = '/portal-php/admin/login';
        }, 1000);
    }

    async loadDashboard() {
        showLoading();

        try {
            // Simular carga de estadísticas - en producción se cargarían de la API
            setTimeout(() => {
                document.getElementById('dashboardUsuarios').textContent = '2';
                document.getElementById('dashboardClientes').textContent = '1';
                document.getElementById('dashboardDocumentos').textContent = '2';
                document.getElementById('securityEvents').textContent = '0';
                document.getElementById('totalUsuarios').textContent = '2';
                document.getElementById('totalClientes').textContent = '1';
                document.getElementById('totalDocumentos').textContent = '2';
                hideLoading();
            }, 500);
        } catch (error) {
            showNotification('Error al cargar estadísticas', 'error');
            hideLoading();
        }
    }

    loadUsers() {
        document.getElementById('usersContent').innerHTML = `
            <div class="admin-table">
                <div class="table-header">
                    <h3>Lista de Usuarios</h3>
                    <button class="btn btn-primary" onclick="adminDashboard.showCreateUserModal()">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </button>
                </div>
                <div class="table-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>admin</td>
                                <td>admin@portal.com</td>
                                <td><span class="badge badge-warning">Admin</span></td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>
                                    <button class="btn-icon" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>cliente1</td>
                                <td>cliente1@empresa.com</td>
                                <td><span class="badge badge-primary">Cliente</span></td>
                                <td><span class="badge badge-success">Activo</span></td>
                                <td>
                                    <button class="btn-icon" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    loadClients() {
        document.getElementById('clientsContent').innerHTML = `
            <div class="admin-table">
                <div class="table-header">
                    <h3>Empresas Registradas</h3>
                </div>
                <div class="table-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Razón Social</th>
                                <th>RUT</th>
                                <th>Email</th>
                                <th>Usuario</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Empresa Demo S.A.</td>
                                <td>12345678-9</td>
                                <td>cliente1@empresa.com</td>
                                <td>cliente1</td>
                                <td>2024-01-01</td>
                                <td>
                                    <button class="btn-icon" title="Ver Documentos"><i class="fas fa-folder"></i></button>
                                    <button class="btn-icon" title="Editar"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    loadDocuments() {
        document.getElementById('documentsContent').innerHTML = `
            <div class="admin-table">
                <div class="table-header">
                    <h3>Documentos del Sistema</h3>
                </div>
                <div class="table-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Archivo</th>
                                <th>Categoría</th>
                                <th>Cliente</th>
                                <th>Tamaño</th>
                                <th>Fecha</th>
                                <th>Origen</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>cartola_enero_2024.pdf</td>
                                <td>Cartola Bancaria</td>
                                <td>Empresa Demo S.A.</td>
                                <td>240 KB</td>
                                <td>2024-01-15</td>
                                <td><span class="badge badge-primary">Cliente</span></td>
                                <td>
                                    <button class="btn-icon" title="Descargar"><i class="fas fa-download"></i></button>
                                    <button class="btn-icon danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>facturas_venta_enero.xlsx</td>
                                <td>Facturas de Venta</td>
                                <td>Empresa Demo S.A.</td>
                                <td>153 KB</td>
                                <td>2024-01-10</td>
                                <td><span class="badge badge-warning">Consultora</span></td>
                                <td>
                                    <button class="btn-icon" title="Descargar"><i class="fas fa-download"></i></button>
                                    <button class="btn-icon danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    loadCategories() {
        document.getElementById('categoriesContent').innerHTML = `
            <div class="admin-table">
                <div class="table-header">
                    <h3>Categorías de Documentos</h3>
                    <button class="btn btn-primary" onclick="adminDashboard.showCreateCategoryModal()">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </button>
                </div>
                <div class="table-content">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Total Docs</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Cartola Bancaria</td>
                                <td>CART_BANC</td>
                                <td>Movimientos bancarios del período</td>
                                <td>3</td>
                                <td>
                                    <button class="btn-icon" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Facturas de Venta</td>
                                <td>FACT_VENTA</td>
                                <td>Facturas emitidas por la empresa</td>
                                <td>5</td>
                                <td>
                                    <button class="btn-icon" title="Editar"><i class="fas fa-edit"></i></button>
                                    <button class="btn-icon danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    loadSecurity() {
        document.getElementById('securityContent').innerHTML = `
            <div class="security-dashboard">
                <div class="security-stats">
                    <div class="security-card">
                        <h4><i class="fas fa-shield-check"></i> Estado de Seguridad</h4>
                        <div class="security-status success">
                            <i class="fas fa-check-circle"></i>
                            <span>Sistema Seguro</span>
                        </div>
                    </div>
                    <div class="security-card">
                        <h4><i class="fas fa-key"></i> Autenticación</h4>
                        <ul>
                            <li><i class="fas fa-check"></i> CSRF Protection: Activo</li>
                            <li><i class="fas fa-check"></i> Rate Limiting: Activo</li>
                            <li><i class="fas fa-check"></i> Session Security: Activo</li>
                        </ul>
                    </div>
                </div>
                <div class="security-logs">
                    <h4><i class="fas fa-history"></i> Eventos Recientes</h4>
                    <div class="log-entries">
                        <div class="log-entry info">
                            <span class="log-time">$(new Date().toLocaleString())</span>
                            <span class="log-message">Sistema iniciado - Admin login exitoso</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    loadLogs() {
        document.getElementById('logsContent').innerHTML = `
            <div class="logs-dashboard">
                <div class="logs-filters">
                    <select class="form-control">
                        <option>Todos los niveles</option>
                        <option>Info</option>
                        <option>Warning</option>
                        <option>Error</option>
                    </select>
                    <input type="date" class="form-control" value="${new Date().toISOString().split('T')[0]}">
                    <button class="btn btn-primary">Filtrar</button>
                </div>
                <div class="logs-content">
                    <div class="log-entry info">
                        <span class="log-level">INFO</span>
                        <span class="log-time">${new Date().toLocaleString()}</span>
                        <span class="log-message">Admin login exitoso - Usuario: admin</span>
                    </div>
                    <div class="log-entry info">
                        <span class="log-level">INFO</span>
                        <span class="log-time">${new Date().toLocaleString()}</span>
                        <span class="log-message">Sistema iniciado correctamente</span>
                    </div>
                </div>
            </div>
        `;
    }

    showCreateUserModal() {
        showNotification('Funcionalidad en desarrollo', 'info');
    }

    showCreateCategoryModal() {
        showNotification('Funcionalidad en desarrollo', 'info');
    }
}

// CSS adicional para las tablas admin
const adminStyles = `
<style>
.admin-table {
    background: var(--bg-card);
    border-radius: 12px;
    box-shadow: var(--shadow-md);
    overflow: hidden;
}

.table-header {
    padding: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.table-header h3 {
    margin: 0;
    color: var(--text-primary);
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th,
.data-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}

.data-table th {
    background: var(--bg-secondary);
    font-weight: 600;
    color: var(--text-primary);
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 12px;
    font-size: var(--font-size-xs);
    font-weight: 600;
}

.badge-success {
    background: rgba(56, 161, 105, 0.1);
    color: var(--success-color);
}

.badge-warning {
    background: rgba(214, 158, 46, 0.1);
    color: var(--warning-color);
}

.badge-primary {
    background: rgba(26, 54, 93, 0.1);
    color: var(--primary-color);
}

.security-dashboard,
.logs-dashboard {
    display: grid;
    gap: 2rem;
}

.security-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.security-card {
    background: var(--bg-card);
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: var(--shadow-md);
}

.security-status.success {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--success-color);
    font-weight: 600;
}

.logs-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-control {
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: 8px;
}

.log-entry {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    align-items: center;
}

.log-level {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: var(--font-size-xs);
    font-weight: 600;
    background: rgba(56, 161, 105, 0.1);
    color: var(--success-color);
}

.log-time {
    color: var(--text-muted);
    font-size: var(--font-size-sm);
}
</style>
`;

document.head.insertAdjacentHTML('beforeend', adminStyles);

// Initialize admin dashboard
const adminDashboard = new AdminDashboard();
</script>