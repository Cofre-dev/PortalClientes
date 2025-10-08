<?php
// Admin Dashboard Page (Simplified)
require_once __DIR__ . '/../../backend/config/Auth.php';
require_once __DIR__ . '/../../backend/models/User.php';

$auth = new Auth();

// Verificar autenticación de admin
if (!$auth->isLoggedIn() || !$auth->checkSessionTimeout() || $auth->getCurrentUserRole() !== 'admin') {
    header('Location: /');
    exit;
}

// Generar token JWT para las peticiones AJAX
$jwtToken = null;
$userData = null;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    try {
        $userModel = new User();
        $user = $userModel->findById($_SESSION['user_id']);

        if ($user && $user['role'] === 'admin') {
            // Generar token JWT directamente (ahora es método público)
            $jwtToken = $auth->generateToken($user);

            $userData = $user;
            // Limpiar password del array
            unset($userData['password']);
        }
    } catch (Exception $e) {
        error_log("Error generando token JWT para admin: " . $e->getMessage());
    }
}

$title = 'Dashboard Admin - ARA & Bustamante';
$favicon = '🛡️';
$bodyClass = 'admin-body';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'><?= $favicon ?></text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/styles.css">
</head>

<body class="<?= $bodyClass ?>">
    <div class="admin-dashboard">
        <nav class="navbar">
            <div class="nav-brand">
                <div class="brand-logo">
                    <i class="fas fa-user-shield"></i>
                    <span>ARA & BUSTAMANTE - ADMIN</span>
                </div>
            </div>
            <div class="nav-user">
                <div class="user-info">
                    <span>Panel Administrativo</span>
                    <div class="user-role">Admin</div>
                </div>
                <button id="logoutBtn" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i>
                    Salir
                </button>
            </div>
        </nav>

        <div class="dashboard-container">
            <div class="dashboard-header">
                <h2><i class="fas fa-tachometer-alt"></i> Panel de Administración</h2>
                <p>Gestión y administración del sistema</p>
            </div>

            <div class="admin-grid">
                <div class="admin-card">
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3>Gestión de Clientes</h3>
                    <p>Administrar clientes y empresas registradas</p>
                    <button id="clientesBtn" class="btn btn-primary">Gestionar</button>
                </div>

                <div class="admin-card">
                    <div class="card-icon">
                        <i class="fas fa-folder"></i>
                    </div>
                    <h3>Gestión de Documentos</h3>
                    <p>Administrar categorías y documentos</p>
                    <button id="documentosBtn" class="btn btn-primary">Gestionar</button>
                </div>

                <div class="admin-card">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3>Estadísticas</h3>
                    <p>Ver estadísticas del sistema</p>
                    <button id="estadisticasBtn" class="btn btn-primary">Ver</button>
                </div>
            </div>
        </div>
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
            <span id="notificationText"></span>
            <button class="notification-close" onclick="hideNotification()">×</button>
        </div>
    </div>

    <!-- Modal Gestión de Categorías -->
    <div id="categoriasModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-tags"></i> Gestión de Categorías</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('categoriasModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <h4 id="clienteNameCategorias">Cliente: </h4>
                    <p>Gestiona las categorías de documentos disponibles para este cliente.</p>
                </div>

                <div class="modal-section">
                    <h5>Agregar Nueva Categoría</h5>
                    <div class="form-row">
                        <select id="nuevaCategoriaSelect" class="form-control">
                            <option value="">Selecciona una categoría...</option>
                        </select>
                        <button id="agregarCategoriaBtn" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar
                        </button>
                    </div>
                </div>

                <div class="modal-section">
                    <h5>Categorías Asignadas</h5>
                    <div id="categoriasAsignadas" class="categorias-list">
                        <p class="text-muted">Cargando categorías...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Gestión de Documentos -->
    <div id="documentosModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3><i class="fas fa-folder"></i> Gestión de Documentos</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('documentosModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <h4 id="clienteNameDocumentos">Cliente: </h4>
                    <p>Gestiona los documentos por categoría para este cliente.</p>
                </div>

                <div class="categorias-grid" id="categoriasDocumentosGrid">
                    <p class="text-muted">Cargando categorías...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Documentos por Categoría -->
    <div id="documentosCategoriaModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3><i class="fas fa-file-alt"></i> Documentos - <span id="categoriaNombre"></span></h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('documentosCategoriaModal')">&times;</span>
            </div>
            <div class="modal-body">
                <div class="modal-section">
                    <div class="form-row">
                        <button id="subirDocumentoBtn" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Subir Documento
                        </button>
                        <button id="eliminarCategoriaClienteBtn" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Eliminar Categoría del Cliente
                        </button>
                    </div>
                </div>

                <!-- Upload Form -->
                <div id="uploadSection" class="modal-section" style="display: none;">
                    <h5>Subir Nuevo Documento</h5>
                    <form id="adminUploadForm" enctype="multipart/form-data">
                        <div class="form-row">
                            <input type="file" id="adminFileInput" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" required>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check"></i> Subir
                            </button>
                            <button type="button" onclick="adminDashboard.cancelarSubida()" class="btn btn-secondary">
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <div class="modal-section">
                    <h5>Documentos en esta Categoría</h5>
                    <div id="documentosLista" class="documentos-list">
                        <p class="text-muted">Cargando documentos...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmación Eliminar Categoría -->
    <div id="eliminarCategoriaModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h3>
                <span class="modal-close" onclick="adminDashboard.closeModal('eliminarCategoriaModal')">&times;</span>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar la categoría <strong id="categoriaEliminarNombre"></strong> para este cliente?</p>
                <p class="text-warning"><i class="fas fa-warning"></i> Esta acción no se puede deshacer.</p>
                <div class="form-row">
                    <button id="confirmarEliminarCategoriaBtn" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Sí, Eliminar
                    </button>
                    <button onclick="adminDashboard.closeModal('eliminarCategoriaModal')" class="btn btn-secondary">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="/assets/app.js"></script>
    <script>
    // Admin Dashboard App
    class AdminDashboard {
        constructor() {
            this.baseURL = '/api';
            this.userInfo = JSON.parse(localStorage.getItem('admin_userInfo') || '{}');
            this.init();
        }

        init() {
            this.checkAuth();
            this.bindEvents();
        }

        checkAuth() {
            const token = localStorage.getItem('admin_token');
            if (!token || !this.userInfo.username || this.userInfo.role !== 'admin') {
                window.location.href = '/admin/login/';
                return;
            }
        }

        bindEvents() {
            // Logout
            document.getElementById('logoutBtn').addEventListener('click', () => {
                this.logout();
            });

            // Gestión de Clientes
            document.getElementById('clientesBtn').addEventListener('click', () => {
                this.loadClientesManagement();
            });

            // Gestión de Documentos
            document.getElementById('documentosBtn').addEventListener('click', () => {
                this.loadDocumentosManagement();
            });

            // Estadísticas
            document.getElementById('estadisticasBtn').addEventListener('click', () => {
                this.loadEstadisticas();
            });
        }

        loadClientesManagement() {
            // Ocultar dashboard principal
            document.querySelector('.admin-grid').style.display = 'none';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-users"></i> Gestión de Clientes';
            document.querySelector('.dashboard-header p').textContent = 'Administrar clientes y empresas registradas';

            // Crear contenido de gestión de clientes
            const container = document.querySelector('.dashboard-container');
            let clientesSection = document.getElementById('clientes-section');

            if (!clientesSection) {
                clientesSection = document.createElement('div');
                clientesSection.id = 'clientes-section';
                clientesSection.innerHTML = `
                    <div class="management-header">
                        <button id="backToDashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </button>
                        <button id="addClienteBtn" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Cliente
                        </button>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Razón Social</th>
                                    <th>RUT</th>
                                    <th>Email</th>
                                    <th>Usuario</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="clientesTableBody">
                                <tr><td colspan="6">Cargando...</td></tr>
                            </tbody>
                        </table>
                    </div>
                `;
                container.appendChild(clientesSection);
            }

            clientesSection.style.display = 'block';
            this.loadClientes();
            this.bindClientesEvents();
        }

        loadDocumentosManagement() {
            // Ocultar dashboard principal
            document.querySelector('.admin-grid').style.display = 'none';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-folder"></i> Gestión de Documentos';
            document.querySelector('.dashboard-header p').textContent = 'Administrar categorías y documentos del sistema';

            // Crear contenido de gestión de documentos
            const container = document.querySelector('.dashboard-container');
            let documentosSection = document.getElementById('documentos-section');

            if (!documentosSection) {
                documentosSection = document.createElement('div');
                documentosSection.id = 'documentos-section';
                documentosSection.innerHTML = `
                    <div class="management-header">
                        <button id="backToDashboard2" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </button>
                        <button id="addCategoriaBtn" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Categoría
                        </button>
                    </div>
                    <div class="tabs">
                        <button class="tab-btn active" data-tab="categorias">Categorías</button>
                        <button class="tab-btn" data-tab="documentos">Documentos</button>
                    </div>
                    <div id="categorias-tab" class="tab-content active">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="categoriasTableBody">
                                    <tr><td colspan="5">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div id="documentos-tab" class="tab-content">
                        <div class="table-container">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Cliente</th>
                                        <th>Categoría</th>
                                        <th>Tamaño</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="documentosTableBody">
                                    <tr><td colspan="7">Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
                container.appendChild(documentosSection);
            }

            documentosSection.style.display = 'block';
            this.loadCategorias();
            this.bindDocumentosEvents();
        }

        loadEstadisticas() {
            // Ocultar dashboard principal
            document.querySelector('.admin-grid').style.display = 'none';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-chart-line"></i> Estadísticas del Sistema';
            document.querySelector('.dashboard-header p').textContent = 'Métricas y estadísticas generales';

            // Crear contenido de estadísticas
            const container = document.querySelector('.dashboard-container');
            let statsSection = document.getElementById('stats-section');

            if (!statsSection) {
                statsSection = document.createElement('div');
                statsSection.id = 'stats-section';
                statsSection.innerHTML = `
                    <div class="management-header">
                        <button id="backToDashboard3" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Dashboard
                        </button>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-users"></i></div>
                            <div class="stat-content">
                                <h3 id="totalUsuarios">-</h3>
                                <p>Total Usuarios</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-building"></i></div>
                            <div class="stat-content">
                                <h3 id="totalClientes">-</h3>
                                <p>Total Clientes</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
                            <div class="stat-content">
                                <h3 id="totalDocumentos">-</h3>
                                <p>Total Documentos</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon"><i class="fas fa-tags"></i></div>
                            <div class="stat-content">
                                <h3 id="totalCategorias">-</h3>
                                <p>Total Categorías</p>
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(statsSection);
            }

            statsSection.style.display = 'block';
            this.loadStats();
            this.bindStatsEvents();
        }

        bindClientesEvents() {
            document.getElementById('backToDashboard')?.addEventListener('click', () => {
                this.showDashboard();
            });
        }

        bindDocumentosEvents() {
            document.getElementById('backToDashboard2')?.addEventListener('click', () => {
                this.showDashboard();
            });

            // Tabs
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const tabName = e.target.dataset.tab;
                    this.switchTab(tabName);
                });
            });
        }

        bindStatsEvents() {
            document.getElementById('backToDashboard3')?.addEventListener('click', () => {
                this.showDashboard();
            });
        }

        showDashboard() {
            // Ocultar todas las secciones
            document.getElementById('clientes-section')?.style.setProperty('display', 'none');
            document.getElementById('documentos-section')?.style.setProperty('display', 'none');
            document.getElementById('stats-section')?.style.setProperty('display', 'none');

            // Mostrar dashboard principal
            document.querySelector('.admin-grid').style.display = 'grid';
            document.querySelector('.dashboard-header h2').innerHTML = '<i class="fas fa-tachometer-alt"></i> Panel de Administración';
            document.querySelector('.dashboard-header p').textContent = 'Gestión y administración del sistema';
        }

        switchTab(tabName) {
            // Remover clase active de todos los botones y contenidos
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

            // Activar el botón y contenido seleccionado
            document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
            document.getElementById(`${tabName}-tab`).classList.add('active');

            // Cargar datos según la pestaña
            if (tabName === 'categorias') {
                this.loadCategorias();
            } else if (tabName === 'documentos') {
                this.loadAllDocumentos();
            }
        }

        async loadClientes() {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/admin/clientes`);
                const tbody = document.getElementById('clientesTableBody');

                if (response && response.length > 0) {
                    tbody.innerHTML = response.map(cliente => `
                        <tr>
                            <td>${cliente.id}</td>
                            <td>${cliente.razon_social}</td>
                            <td>${cliente.rut_empresa}</td>
                            <td>${cliente.email}</td>
                            <td>${cliente.username || 'N/A'}</td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="adminDashboard.gestionarCategorias(${cliente.id}, '${cliente.razon_social}')" title="Gestionar Categorías">
                                    <i class="fas fa-tags"></i>
                                </button>
                                <button class="btn btn-sm btn-info" onclick="adminDashboard.gestionarDocumentos(${cliente.id}, '${cliente.razon_social}')" title="Gestionar Documentos">
                                    <i class="fas fa-folder"></i>
                                </button>
                                <button class="btn btn-sm btn-primary" onclick="adminDashboard.editCliente(${cliente.id})" title="Editar Cliente">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteCliente(${cliente.id})" title="Eliminar Cliente">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="6">No hay clientes registrados</td></tr>';
                }
            } catch (error) {
                console.error('Error loading clientes:', error);
                showNotification('Error al cargar clientes', 'error');
            } finally {
                hideLoading();
            }
        }

        async loadCategorias() {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/tipos-documento`);
                const tbody = document.getElementById('categoriasTableBody');

                if (response && response.length > 0) {
                    tbody.innerHTML = response.map(categoria => `
                        <tr>
                            <td>${categoria.id}</td>
                            <td>${categoria.nombre}</td>
                            <td>${categoria.descripcion || 'N/A'}</td>
                            <td>
                                <span class="status ${categoria.is_active ? 'active' : 'inactive'}">
                                    ${categoria.is_active ? 'Activo' : 'Inactivo'}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="adminDashboard.editCategoria(${categoria.id})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteCategoria(${categoria.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="5">No hay categorías registradas</td></tr>';
                }
            } catch (error) {
                console.error('Error loading categorias:', error);
                showNotification('Error al cargar categorías', 'error');
            } finally {
                hideLoading();
            }
        }

        async loadAllDocumentos() {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/documentos`);
                const tbody = document.getElementById('documentosTableBody');

                if (response && response.length > 0) {
                    tbody.innerHTML = response.map(doc => `
                        <tr>
                            <td>${doc.id}</td>
                            <td>${doc.nombre_archivo}</td>
                            <td>${doc.cliente_razon_social || 'N/A'}</td>
                            <td>${doc.categoria_nombre || 'N/A'}</td>
                            <td>${this.formatFileSize(doc.tamano)}</td>
                            <td>${new Date(doc.created_at).toLocaleDateString()}</td>
                            <td>
                                <button class="btn btn-sm btn-secondary" onclick="adminDashboard.downloadDocument('${doc.ruta_archivo}')">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminDashboard.deleteDocument(${doc.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="7">No hay documentos en el sistema</td></tr>';
                }
            } catch (error) {
                console.error('Error loading documentos:', error);
                showNotification('Error al cargar documentos', 'error');
            } finally {
                hideLoading();
            }
        }

        async loadStats() {
            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/admin/stats`);

                if (response) {
                    document.getElementById('totalUsuarios').textContent = response.usuarios || 0;
                    document.getElementById('totalClientes').textContent = response.clientes || 0;
                    document.getElementById('totalDocumentos').textContent = response.documentos || 0;
                    document.getElementById('totalCategorias').textContent = response.categorias || 0;
                }
            } catch (error) {
                console.error('Error loading stats:', error);
                showNotification('Error al cargar estadísticas', 'error');
            } finally {
                hideLoading();
            }
        }

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        async downloadDocument(ruta) {
            try {
                showLoading();

                // Primero verificar que la descarga está autorizada
                const verifyResponse = await makeRequest(`${this.baseURL}/documentos/verify-download?file=${encodeURIComponent(ruta)}`);

                if (verifyResponse.success) {
                    // Si está autorizada, proceder con la descarga
                    const token = localStorage.getItem('admin_token');
                    window.open(`${this.baseURL}/documentos/download?file=${encodeURIComponent(ruta)}&token=${encodeURIComponent(token)}`, '_blank');
                    showNotification('Descarga iniciada', 'success');
                } else {
                    showNotification('No tienes permisos para descargar este archivo', 'error');
                }
            } catch (error) {
                console.error('Error en descarga admin:', error);
                showNotification('Error al iniciar descarga: ' + error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        async logout() {
            try {
                await makeRequest(`${this.baseURL}/auth/logout`, { method: 'POST' });
            } catch (error) {
                console.error('Logout error:', error);
            }

            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_userInfo');
            showNotification('Sesión cerrada correctamente');

            setTimeout(() => {
                window.location.href = '/admin/login/';
            }, 1000);
        }

        // Nuevas funcionalidades para gestión de categorías y documentos
        gestionarCategorias(clienteId, clienteNombre) {
            this.currentClienteId = clienteId;
            this.currentClienteNombre = clienteNombre;

            document.getElementById('clienteNameCategorias').textContent = `Cliente: ${clienteNombre}`;
            this.showModal('categoriasModal');
            this.loadCategoriasDisponibles();
            this.loadCategoriasAsignadas();
        }

        gestionarDocumentos(clienteId, clienteNombre) {
            this.currentClienteId = clienteId;
            this.currentClienteNombre = clienteNombre;

            document.getElementById('clienteNameDocumentos').textContent = `Cliente: ${clienteNombre}`;
            this.showModal('documentosModal');
            this.loadCategoriasParaDocumentos();
        }

        async loadCategoriasDisponibles() {
            try {
                const categorias = await makeRequest(`${this.baseURL}/tipos-documento`);
                const select = document.getElementById('nuevaCategoriaSelect');

                select.innerHTML = '<option value="">Selecciona una categoría...</option>';

                if (categorias && categorias.length > 0) {
                    categorias.forEach(categoria => {
                        if (categoria.is_active) {
                            select.innerHTML += `<option value="${categoria.id}">${categoria.nombre}</option>`;
                        }
                    });
                }

                // Bind event
                document.getElementById('agregarCategoriaBtn').onclick = () => this.asignarCategoria();

            } catch (error) {
                console.error('Error loading categorías disponibles:', error);
                showNotification('Error al cargar categorías disponibles', 'error');
            }
        }

        async loadCategoriasAsignadas() {
            try {
                const response = await makeRequest(`${this.baseURL}/admin/clientes/${this.currentClienteId}/categorias`);
                const container = document.getElementById('categoriasAsignadas');

                if (response.success && response.data && response.data.length > 0) {
                    container.innerHTML = response.data.map(categoria => `
                        <div class="categoria-item">
                            <div class="categoria-info">
                                <h6>${categoria.nombre}</h6>
                                <p>${categoria.descripcion || 'Sin descripción'}</p>
                            </div>
                            <button class="btn btn-sm btn-danger" onclick="adminDashboard.eliminarCategoriaCliente(${categoria.id}, '${categoria.nombre}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="text-muted">No hay categorías asignadas a este cliente.</p>';
                }

            } catch (error) {
                console.error('Error loading categorías asignadas:', error);
                showNotification('Error al cargar categorías asignadas', 'error');
            }
        }

        async asignarCategoria() {
            const categoriaId = document.getElementById('nuevaCategoriaSelect').value;

            if (!categoriaId) {
                showNotification('Selecciona una categoría', 'warning');
                return;
            }

            try {
                showLoading();
                const response = await makeRequest(`${this.baseURL}/admin/clientes/${this.currentClienteId}/categorias`, {
                    method: 'POST',
                    body: JSON.stringify({ categoria_id: parseInt(categoriaId) })
                });

                if (response.success) {
                    showNotification('Categoría asignada exitosamente', 'success');
                    this.loadCategoriasAsignadas();
                    document.getElementById('nuevaCategoriaSelect').value = '';
                } else {
                    showNotification(response.message || 'Error al asignar categoría', 'error');
                }

            } catch (error) {
                console.error('Error asignando categoría:', error);
                showNotification('Error al asignar categoría', 'error');
            } finally {
                hideLoading();
            }
        }

        eliminarCategoriaCliente(categoriaId, categoriaNombre) {
            this.currentCategoriaId = categoriaId;
            document.getElementById('categoriaEliminarNombre').textContent = categoriaNombre;
            this.showModal('eliminarCategoriaModal');

            document.getElementById('confirmarEliminarCategoriaBtn').onclick = () => this.confirmarEliminarCategoria();
        }

        async confirmarEliminarCategoria() {
            try {
                showLoading();
                const response = await makeRequest(
                    `${this.baseURL}/admin/clientes/${this.currentClienteId}/categorias/${this.currentCategoriaId}`,
                    { method: 'DELETE' }
                );

                if (response.success) {
                    showNotification('Categoría eliminada del cliente exitosamente', 'success');
                    this.loadCategoriasAsignadas();
                    this.closeModal('eliminarCategoriaModal');
                } else {
                    showNotification(response.message || 'Error al eliminar categoría', 'error');
                }

            } catch (error) {
                console.error('Error eliminando categoría:', error);
                showNotification('Error al eliminar categoría', 'error');
            } finally {
                hideLoading();
            }
        }

        async loadCategoriasParaDocumentos() {
            try {
                const response = await makeRequest(`${this.baseURL}/admin/clientes/${this.currentClienteId}/categorias`);
                const container = document.getElementById('categoriasDocumentosGrid');

                if (response.success && response.data && response.data.length > 0) {
                    container.innerHTML = response.data.map(categoria => `
                        <div class="categoria-card" onclick="adminDashboard.abrirDocumentosCategoria(${categoria.id}, '${categoria.nombre}')">
                            <div class="categoria-icon">
                                <i class="fas fa-folder"></i>
                            </div>
                            <h5>${categoria.nombre}</h5>
                            <p>${categoria.descripcion || 'Sin descripción'}</p>
                            <div class="categoria-stats">
                                <small class="text-muted">Click para gestionar documentos</small>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="text-muted">Este cliente no tiene categorías asignadas. Asigna categorías primero en la gestión de categorías.</p>';
                }

            } catch (error) {
                console.error('Error loading categorías para documentos:', error);
                showNotification('Error al cargar categorías', 'error');
            }
        }

        async abrirDocumentosCategoria(categoriaId, categoriaNombre) {
            this.currentCategoriaId = categoriaId;
            this.currentCategoriaNombre = categoriaNombre;

            document.getElementById('categoriaNombre').textContent = categoriaNombre;
            this.showModal('documentosCategoriaModal');
            this.loadDocumentosCategoria();

            // Bind events
            document.getElementById('subirDocumentoBtn').onclick = () => this.mostrarFormularioSubida();
            document.getElementById('eliminarCategoriaClienteBtn').onclick = () => this.eliminarCategoriaClienteFromDocs();
            document.getElementById('adminUploadForm').onsubmit = (e) => this.subirDocumentoAdmin(e);
        }

        async loadDocumentosCategoria() {
            try {
                const response = await makeRequest(
                    `${this.baseURL}/admin/clientes/${this.currentClienteId}/documentos/${this.currentCategoriaId}`
                );
                const container = document.getElementById('documentosLista');

                if (response.success && response.data && response.data.length > 0) {
                    container.innerHTML = response.data.map(doc => `
                        <div class="documento-item">
                            <div class="documento-info">
                                <i class="fas ${this.getFileIcon(doc.nombre_archivo)}"></i>
                                <div class="documento-details">
                                    <h6>${doc.nombre_archivo}</h6>
                                    <small>Subido: ${new Date(doc.created_at).toLocaleDateString()}</small>
                                    <small>Tamaño: ${this.formatFileSize(doc.tamano)}</small>
                                </div>
                            </div>
                            <div class="documento-actions">
                                <button class="btn btn-sm btn-secondary" onclick="adminDashboard.downloadDocument('${doc.ruta_archivo}')">
                                    <i class="fas fa-download"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="adminDashboard.eliminarDocumento(${doc.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="text-muted">No hay documentos en esta categoría.</p>';
                }

            } catch (error) {
                console.error('Error loading documentos:', error);
                showNotification('Error al cargar documentos', 'error');
            }
        }

        mostrarFormularioSubida() {
            document.getElementById('uploadSection').style.display = 'block';
            document.getElementById('subirDocumentoBtn').style.display = 'none';
        }

        cancelarSubida() {
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('subirDocumentoBtn').style.display = 'inline-block';
            document.getElementById('adminUploadForm').reset();
        }

        async subirDocumentoAdmin(event) {
            event.preventDefault();

            const fileInput = document.getElementById('adminFileInput');
            const file = fileInput.files[0];

            if (!file) {
                showNotification('Selecciona un archivo', 'warning');
                return;
            }

            try {
                showLoading();

                const formData = new FormData();
                formData.append('archivo', file);
                formData.append('categoria_id', this.currentCategoriaId);
                formData.append('cliente_id', this.currentClienteId);

                const response = await fetch(`${this.baseURL}/documentos/upload`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('admin_token')}`
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Documento subido exitosamente', 'success');
                    this.loadDocumentosCategoria();
                    this.cancelarSubida();
                } else {
                    showNotification(result.error || 'Error al subir documento', 'error');
                }

            } catch (error) {
                console.error('Error subiendo documento:', error);
                showNotification('Error al subir documento', 'error');
            } finally {
                hideLoading();
            }
        }

        eliminarCategoriaClienteFromDocs() {
            this.eliminarCategoriaCliente(this.currentCategoriaId, this.currentCategoriaNombre);
        }

        getFileIcon(filename) {
            const extension = filename.split('.').pop().toLowerCase();
            const iconMap = {
                'pdf': 'fa-file-pdf',
                'doc': 'fa-file-word',
                'docx': 'fa-file-word',
                'xls': 'fa-file-excel',
                'xlsx': 'fa-file-excel',
                'jpg': 'fa-file-image',
                'jpeg': 'fa-file-image',
                'png': 'fa-file-image'
            };
            return iconMap[extension] || 'fa-file';
        }

        showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
    }

    // Función para obtener token de sesión de manera confiable (Admin)
    async function setupAdminSessionToken() {
        try {
            // Intentar usar token generado por PHP primero
            <?php if ($jwtToken && $userData): ?>
            localStorage.setItem('admin_token', '<?= $jwtToken ?>');
            localStorage.setItem('admin_userInfo', '<?= addslashes(json_encode($userData)) ?>');
            console.log('Token JWT Admin configurado desde PHP');
            return true;
            <?php endif; ?>

            // Si no hay token PHP, obtenerlo via API (fallback para producción)
            console.log('Obteniendo token admin via API como fallback...');
            const response = await fetch('/api/auth/session-token', {
                method: 'GET',
                credentials: 'include', // Importante para incluir cookies de sesión
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.token && data.user && data.user.role === 'admin') {
                    localStorage.setItem('admin_token', data.token);
                    localStorage.setItem('admin_userInfo', JSON.stringify(data.user));
                    console.log('Token admin configurado via API');
                    return true;
                }
            } else {
                console.error('Error obteniendo token admin:', await response.text());
            }

            return false;
        } catch (error) {
            console.error('Error configurando token admin:', error);
            return false;
        }
    }

    // Inicializar dashboard admin después de configurar el token
    setupAdminSessionToken().then(tokenReady => {
        if (!tokenReady) {
            console.warn('No se pudo configurar el token admin, redirigiendo al login...');
            window.location.href = '/';
            return;
        }

        // Initialize admin dashboard
        const adminDashboard = new AdminDashboard();

        // Make adminDashboard globally available
        window.adminDashboard = adminDashboard;
    }).catch(error => {
        console.error('Error inicializando dashboard admin:', error);
        window.location.href = '/';
    });
    </script>
</body>
</html>