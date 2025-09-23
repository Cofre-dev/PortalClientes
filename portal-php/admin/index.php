<?php
// ARA & Bustamante Consultores - Panel de Administración
// Archivo principal del panel administrativo

// Configuración básica
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers de seguridad
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Detectar si es una petición AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Si es una petición AJAX, redirigir a la API correspondiente
if ($isAjax) {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARA & Bustamante - Panel de Administración</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛡️</text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Estilos CSS embebidos -->
    <style>
    /* Incluir todos los estilos del frontend + estilos admin */
    <?php include '../frontend/css/styles.css'; ?>
    <?php include 'css/admin.css'; ?>
    </style>
</head>
<body class="admin-body">
    <div id="app">
        <!-- Login Admin -->
        <div id="adminLoginScreen" class="screen active">
            <div class="admin-login-background">
                <div class="admin-login-container">
                    <div class="admin-login-form">
                        <div class="admin-logo">
                            <div class="admin-logo-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h1>ARA & BUSTAMANTE</h1>
                            <p>Panel de Administración</p>
                        </div>

                        <div class="admin-form-section">
                            <h2>Acceso Administrativo</h2>
                            <form id="adminLoginForm">
                                <div class="form-group">
                                    <div class="input-wrapper">
                                        <i class="fas fa-user-shield"></i>
                                        <input type="text" id="adminUsername" name="username" placeholder="Usuario Administrador" value="admin" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="input-wrapper">
                                        <i class="fas fa-key"></i>
                                        <input type="password" id="adminPassword" name="password" placeholder="Contraseña" value="admin123" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i>
                                    Acceder al Panel
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Panel Admin -->
        <div id="adminPanelScreen" class="screen">
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
                        <h4>GESTIÓN</h4>
                        <ul>
                            <li><a href="#" class="nav-link active" data-tab="dashboard"><i class="fas fa-chart-line"></i>Dashboard</a></li>
                            <li><a href="#" class="nav-link" data-tab="usuarios"><i class="fas fa-users"></i>Usuarios</a></li>
                            <li><a href="#" class="nav-link" data-tab="clientes"><i class="fas fa-building"></i>Empresas</a></li>
                            <li><a href="#" class="nav-link" data-tab="documentos"><i class="fas fa-folder-open"></i>Documentos</a></li>
                            <li><a href="#" class="nav-link" data-tab="categorias"><i class="fas fa-tags"></i>Categorías</a></li>
                        </ul>
                    </div>

                    <div class="nav-section">
                        <h4>SISTEMA</h4>
                        <ul>
                            <li><a href="#" class="nav-link" data-tab="configuracion"><i class="fas fa-cog"></i>Configuración</a></li>
                            <li><a href="#" class="nav-link" data-tab="logs"><i class="fas fa-file-alt"></i>Logs</a></li>
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
                    </button>
                </div>
            </div>

            <main class="admin-main">
                <header class="admin-header">
                    <div class="header-left">
                        <button class="sidebar-toggle">
                            <i class="fas fa-bars"></i>
                        </button>
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
                                        <span class="stat-change positive">+2 este mes</span>
                                    </div>
                                </div>

                                <div class="stat-card">
                                    <div class="stat-icon primary">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3 id="dashboardClientes">0</h3>
                                        <p>Empresas Activas</p>
                                        <span class="stat-change positive">+1 esta semana</span>
                                    </div>
                                </div>

                                <div class="stat-card">
                                    <div class="stat-icon secondary">
                                        <i class="fas fa-folder-open"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3 id="dashboardDocumentos">0</h3>
                                        <p>Documentos Gestionados</p>
                                        <span class="stat-change positive">+15 hoy</span>
                                    </div>
                                </div>

                                <div class="stat-card">
                                    <div class="stat-icon warning">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="stat-info">
                                        <h3>5</h3>
                                        <p>Documentos Pendientes</p>
                                        <span class="stat-change neutral">Sin cambios</span>
                                    </div>
                                </div>
                            </div>

                            <div class="dashboard-widgets">
                                <div class="widget">
                                    <div class="widget-header">
                                        <h4><i class="fas fa-chart-bar"></i> Actividad Reciente</h4>
                                    </div>
                                    <div class="widget-content">
                                        <div class="activity-list">
                                            <div class="activity-item">
                                                <div class="activity-icon success">
                                                    <i class="fas fa-plus"></i>
                                                </div>
                                                <div class="activity-content">
                                                    <p>Nuevo cliente registrado: <strong>Empresa ABC</strong></p>
                                                    <span class="activity-time">Hace 2 horas</span>
                                                </div>
                                            </div>
                                            <div class="activity-item">
                                                <div class="activity-icon primary">
                                                    <i class="fas fa-upload"></i>
                                                </div>
                                                <div class="activity-content">
                                                    <p>Documento subido por <strong>Empresa XYZ</strong></p>
                                                    <span class="activity-time">Hace 4 horas</span>
                                                </div>
                                            </div>
                                            <div class="activity-item">
                                                <div class="activity-icon warning">
                                                    <i class="fas fa-edit"></i>
                                                </div>
                                                <div class="activity-content">
                                                    <p>Categoría actualizada: <strong>Facturas</strong></p>
                                                    <span class="activity-time">Ayer</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="widget">
                                    <div class="widget-header">
                                        <h4><i class="fas fa-exclamation-triangle"></i> Alertas del Sistema</h4>
                                    </div>
                                    <div class="widget-content">
                                        <div class="alert-list">
                                            <div class="alert-item warning">
                                                <i class="fas fa-hdd"></i>
                                                <span>Uso de almacenamiento al 75%</span>
                                            </div>
                                            <div class="alert-item success">
                                                <i class="fas fa-shield-alt"></i>
                                                <span>Sistema seguro y actualizado</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Otros tabs serían añadidos aquí -->

                </div>
            </main>
        </div>

        <!-- Loading Overlay -->
        <div id="loadingOverlay" class="loading-overlay">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando...</p>
            </div>
        </div>

        <!-- Notification -->
        <div id="notification" class="notification">
            <div class="notification-content">
                <i class="notification-icon"></i>
                <span id="notificationText"></span>
            </div>
            <button id="closeNotification" class="notification-close">&times;</button>
        </div>
    </div>

    <!-- Client Portal Link -->
    <a href="../" class="admin-link" style="background: var(--secondary-color);">
        <i class="fas fa-user"></i>
        Portal Cliente
    </a>

    <!-- JavaScript integrado -->
    <script>
    class AdminApp {
        constructor() {
            this.baseURL = window.location.origin + window.location.pathname.replace('admin/index.php', '').replace('admin/', '') + 'api';
            this.token = localStorage.getItem('admin_token');
            this.userInfo = JSON.parse(localStorage.getItem('admin_userInfo') || '{}');
            this.init();
        }

        init() {
            this.bindEvents();
            this.checkAuth();
        }

        bindEvents() {
            // Admin login form
            document.getElementById('adminLoginForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.login();
            });

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

            // Close notification
            document.getElementById('closeNotification').addEventListener('click', () => {
                this.hideNotification();
            });
        }

        checkAuth() {
            if (this.token && this.userInfo.username) {
                this.showScreen('adminPanelScreen');
                this.updateUserInfo();
                this.loadDashboard();
            } else {
                this.showScreen('adminLoginScreen');
            }
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
                configuracion: 'fa-cog',
                logs: 'fa-file-alt'
            };

            const titles = {
                dashboard: 'Dashboard',
                usuarios: 'Usuarios',
                clientes: 'Empresas',
                documentos: 'Documentos',
                categorias: 'Categorías',
                configuracion: 'Configuración',
                logs: 'Logs del Sistema'
            };

            document.querySelector('.breadcrumb i').className = `fas ${icons[tabName]}`;
            document.getElementById('currentPageTitle').textContent = titles[tabName];

            // Load appropriate content
            if (tabName === 'dashboard') {
                this.loadDashboard();
            }
        }

        showScreen(screenId) {
            document.querySelectorAll('.screen').forEach(screen => {
                screen.classList.remove('active');
            });
            document.getElementById(screenId).classList.add('active');
        }

        showLoading() {
            document.getElementById('loadingOverlay').classList.add('show');
        }

        hideLoading() {
            document.getElementById('loadingOverlay').classList.remove('show');
        }

        showNotification(message, type = 'success') {
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notificationText');

            notificationText.textContent = message;
            notification.className = `notification ${type} show`;

            setTimeout(() => {
                this.hideNotification();
            }, 5000);
        }

        hideNotification() {
            document.getElementById('notification').classList.remove('show');
        }

        async makeRequest(url, options = {}) {
            const defaultOptions = {
                headers: {
                    'Content-Type': 'application/json',
                }
            };

            if (this.token) {
                defaultOptions.headers.Authorization = `Bearer ${this.token}`;
            }

            const finalOptions = { ...defaultOptions, ...options };

            try {
                const response = await fetch(url, finalOptions);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Error en la solicitud');
                }

                return data;
            } catch (error) {
                console.error('Request error:', error);
                throw error;
            }
        }

        async login() {
            const username = document.getElementById('adminUsername').value;
            const password = document.getElementById('adminPassword').value;

            if (!username || !password) {
                this.showNotification('Por favor completa todos los campos', 'error');
                return;
            }

            this.showLoading();

            try {
                const data = await this.makeRequest(`${this.baseURL}/auth.php?action=login`, {
                    method: 'POST',
                    body: JSON.stringify({ username, password })
                });

                if (data.user.role !== 'admin') {
                    throw new Error('Acceso denegado. Solo administradores pueden acceder.');
                }

                this.token = data.token;
                this.userInfo = data.user;
                localStorage.setItem('admin_token', this.token);
                localStorage.setItem('admin_userInfo', JSON.stringify(this.userInfo));

                this.showNotification('Acceso administrativo exitoso');
                this.showScreen('adminPanelScreen');
                this.updateUserInfo();
                this.loadDashboard();

                // Clear form
                document.getElementById('adminLoginForm').reset();
            } catch (error) {
                this.showNotification(error.message, 'error');
            } finally {
                this.hideLoading();
            }
        }

        logout() {
            this.token = null;
            this.userInfo = {};
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_userInfo');
            this.showScreen('adminLoginScreen');
            this.showNotification('Sesión administrativa cerrada');
        }

        async loadDashboard() {
            this.showLoading();

            try {
                // Simular carga de estadísticas
                setTimeout(() => {
                    document.getElementById('dashboardUsuarios').textContent = '15';
                    document.getElementById('dashboardClientes').textContent = '8';
                    document.getElementById('dashboardDocumentos').textContent = '142';
                    document.getElementById('totalUsuarios').textContent = '15';
                    document.getElementById('totalClientes').textContent = '8';
                    document.getElementById('totalDocumentos').textContent = '142';
                    this.hideLoading();
                }, 1000);
            } catch (error) {
                this.showNotification('Error al cargar estadísticas', 'error');
                this.hideLoading();
            }
        }
    }

    // Initialize the admin app
    const adminApp = new AdminApp();
    </script>
</body>
</html>