<?php
// Client Dashboard Page
require_once __DIR__ . '/../../backend/config/Auth.php';
require_once __DIR__ . '/../../backend/models/User.php';
require_once __DIR__ . '/../../backend/models/Cliente.php';

$auth = new Auth();

// Verificar autenticación
if (!$auth->isLoggedIn() || !$auth->checkSessionTimeout() || $auth->getCurrentUserRole() !== 'cliente') {
    header('Location: /');
    exit;
}

$csrfToken = $auth->getCSRFToken();

// Obtener información del usuario para el mensaje de bienvenida
$welcomeMessage = '';
$displayName = 'Usuario';

try {
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'cliente') {
        $userModel = new User();
        $clienteModel = new Cliente();

        $user = $userModel->findById($_SESSION['user_id']);
        if ($user) {
            $displayName = $user['username'];

            // Si es cliente, obtener información de la empresa
            if ($user['role'] === 'cliente') {
                $cliente = $clienteModel->findByUserId($user['id']);
                if ($cliente && !empty($cliente['razon_social'])) {
                    $displayName = $cliente['razon_social'];
                }
            }
        }
    }
} catch (Exception $e) {
    // Si hay algún error, usar el nombre por defecto
    error_log("Error obteniendo información del usuario: " . $e->getMessage());
}

// Generar token JWT para las peticiones AJAX
$jwtToken = null;
$userData = null;
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    try {
        $userModel = new User();
        $clienteModel = new Cliente();

        $user = $userModel->findById($_SESSION['user_id']);
        if ($user && $user['role'] === 'cliente') {
            // Agregar información del cliente si existe
            $cliente = $clienteModel->findByUserId($user['id']);
            if ($cliente) {
                $user['cliente_id'] = $cliente['id'];
                $user['razon_social'] = $cliente['razon_social'];
                $user['rut_empresa'] = $cliente['rut_empresa'];
                $user['email'] = $cliente['email'] ?? $user['email'];
            }

            // Generar token JWT directamente (ahora es método público)
            $jwtToken = $auth->generateToken($user);

            $userData = $user;
            // Limpiar password del array
            unset($userData['password']);
        }
    } catch (Exception $e) {
        error_log("Error generando token JWT: " . $e->getMessage());
    }
}

$title = 'Dashboard Cliente - ARA & Bustamante';
$favicon = '🏢';
$bodyClass = '';
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
<style>
/* Category Card Hover Effects */
.category-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,123,186,0.15);
    border-color: #007cba;
}

.category-card:hover .category-icon i {
    color: #007cba;
    transform: scale(1.1);
}

.category-card:active {
    transform: translateY(-1px);
}

/* Welcome Message Styles */
.welcome-message {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 1000;
    animation: slideInDown 0.6s ease-out;
    max-width: 600px;
    width: 90%;
}

.welcome-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 15px;
    position: relative;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.welcome-icon {
    background: rgba(255, 255, 255, 0.2);
    padding: 12px;
    border-radius: 50%;
    font-size: 24px;
    flex-shrink: 0;
    animation: pulse 2s infinite;
}

.welcome-text {
    flex: 1;
}

.welcome-text h2 {
    margin: 0 0 5px 0;
    font-size: 1.4rem;
    font-weight: 600;
}

.welcome-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.welcome-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.welcome-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}

/* Animations */
@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-50px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

.welcome-message.fade-out {
    animation: slideOutUp 0.5s ease-in forwards;
}

@keyframes slideOutUp {
    from {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
    to {
        opacity: 0;
        transform: translateX(-50%) translateY(-50px);
    }
}

/* Enhanced Document Modal Styles */
.modal-content.large {
    max-width: 900px;
    width: 90%;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-actions {
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.85rem;
}

/* Upload Section */
.upload-section {
    margin-bottom: 2rem;
}

.upload-area {
    position: relative;
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    background: #fafafa;
}

.upload-area:hover {
    border-color: #007cba;
    background: #f0f9ff;
}

.upload-area.drag-over {
    border-color: #007cba;
    background: #e6f3ff;
    transform: scale(1.02);
}

.upload-content h4 {
    margin: 10px 0 5px 0;
    color: #333;
}

.upload-content p {
    margin: 5px 0;
    color: #666;
}

.upload-formats {
    font-size: 0.85rem;
    color: #999;
}

.upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 124, 186, 0.9);
    color: white;
    display: none;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    border-radius: 6px;
}

.upload-area.drag-over .upload-overlay {
    display: flex;
}

.upload-overlay i {
    font-size: 3rem;
    margin-bottom: 10px;
}

/* Upload Progress */
.upload-progress {
    margin-top: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 10px;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #007cba, #0056b3);
    width: 0%;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.9rem;
    color: #666;
}

/* Documents Section */
.documents-section {
    border-top: 1px solid #eee;
    padding-top: 1.5rem;
}

.documents-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.documents-header h4 {
    margin: 0;
    color: #333;
}

.documents-stats {
    display: flex;
    gap: 15px;
    font-size: 0.9rem;
    color: #666;
}

.documents-stats span {
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Document Items */
.document-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border: 1px solid #eee;
    border-radius: 6px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
    background: white;
}

.document-item:hover {
    background: #f8f9fa;
    border-color: #007cba;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.document-icon {
    font-size: 1.8rem;
    margin-right: 15px;
    color: #007cba;
    flex-shrink: 0;
}

.document-info {
    flex: 1;
    min-width: 0;
}

.document-name {
    font-weight: 500;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #333;
}

.document-meta {
    display: flex;
    gap: 15px;
    font-size: 0.85rem;
    color: #666;
}

.document-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.document-actions {
    display: flex;
    gap: 5px;
    flex-shrink: 0;
}

.btn-icon {
    width: 32px;
    height: 32px;
    border: none;
    background: #f8f9fa;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    color: #666;
}

.btn-icon:hover {
    background: #007cba;
    color: white;
    transform: scale(1.1);
}

.btn-icon.danger:hover {
    background: #dc3545;
}

/* Empty Documents */
.empty-documents {
    text-align: center;
    padding: 3rem 2rem;
    color: #666;
}

.empty-documents i {
    font-size: 4rem;
    margin-bottom: 1rem;
    color: #ddd;
}

.empty-documents h4 {
    margin-bottom: 0.5rem;
    color: #333;
}

/* Rename Modal */
.current-name {
    margin-top: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 0.9rem;
    color: #666;
}

/* Delete Confirmation Modal Styles */
.delete-warning {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 10px;
}

.warning-icon {
    font-size: 3rem;
    color: #dc3545;
    flex-shrink: 0;
    text-align: center;
    width: 80px;
}

.warning-text {
    flex: 1;
}

.warning-text h4 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.2rem;
}

.warning-text p {
    margin: 0 0 15px 0;
    color: #666;
    line-height: 1.4;
}

.file-info {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    border-left: 4px solid #dc3545;
    font-size: 0.9rem;
}

.file-info strong {
    color: #333;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
}

.btn-danger:disabled {
    background-color: #6c757d;
    border-color: #6c757d;
    opacity: 0.65;
    cursor: not-allowed;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .welcome-message {
        top: 10px;
        width: 95%;
    }

    .welcome-content {
        padding: 15px 20px;
        gap: 12px;
    }

    .welcome-text h2 {
        font-size: 1.2rem;
    }

    .welcome-text p {
        font-size: 0.9rem;
    }

    .welcome-icon {
        padding: 10px;
        font-size: 20px;
    }

    .modal-content.large {
        width: 95%;
        margin: 10px;
    }

    .documents-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .documents-stats {
        flex-direction: column;
        gap: 5px;
    }

    .document-meta {
        flex-direction: column;
        gap: 5px;
    }

    .upload-area {
        padding: 1.5rem 1rem;
    }

    .delete-warning {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .warning-icon {
        width: 100%;
        font-size: 2.5rem;
    }

    .warning-text h4 {
        font-size: 1.1rem;
    }
}

/* Ensure welcome message appears above other elements */
.welcome-message {
    position: fixed;
    z-index: 9999;
}
    </style>
</head>

<body class="<?= $bodyClass ?>">
    <div class="client-dashboard">
        <!-- Welcome Message -->
        <div id="welcomeMessage" class="welcome-message">
            <div class="welcome-content">
                <div class="welcome-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <div class="welcome-text">
                    <h2>¡Bienvenido, <?= htmlspecialchars($displayName) ?>!</h2>
                </div>
                <button class="welcome-close" onclick="hideWelcomeMessage()" title="Cerrar mensaje">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <nav class="navbar">
            <div class="nav-brand">
                <div class="brand-logo">
                    <i class="fas fa-calculator"></i>
                    <span>ARA & BUSTAMANTE CONSULTORES</span>
                </div>
            </div>
            <div class="nav-center">
                <div class="nav-tabs">
                    <button class="nav-tab active" data-tab="documents">
                        <i class="fas fa-folder-open"></i>
                        Documentos
                    </button>
                    <button class="nav-tab" data-tab="profile">
                        <i class="fas fa-user-circle"></i>
                        Mi Perfil
                    </button>
                </div>
            </div>
            <div class="nav-user">
                <div class="user-info">
                    <span id="userInfo"></span>
                    <div class="user-role">Portal Cliente</div>
                </div>
                <button id="logoutBtn" class="btn btn-outline">
                    <i class="fas fa-sign-out-alt"></i>
                    Salir
                </button>
            </div>
        </nav>

        <!-- Documents Tab -->
        <div id="documentsTab" class="tab-content active">
            <div class="dashboard-container">
                <div class="dashboard-header">
                    <div class="header-left">
                        <h2><i class="fas fa-folder-open"></i> Gestión de Documentos</h2>
                        <p>Organice y acceda a todos sus documentos contables</p>
                        <div class="help-text" style="margin-top: 10px; padding: 10px; background: rgba(0,123,186,0.1); border-radius: 6px; border-left: 4px solid #007cba;">
                            <small style="color: #005a8b;">
                                <i class="fas fa-info-circle"></i>
                                <strong>¿Cómo subir archivos?</strong> Haz clic en cualquier categoría abajo para abrir su gestión de documentos.
                            </small>
                        </div>
                    </div>
                    <div class="header-actions">
                        <button id="createCategoryBtn" class="btn btn-secondary">
                            <i class="fas fa-plus"></i>
                            Nueva Categoría
                        </button>
                        <button id="refreshBtn" class="btn btn-outline">
                            <i class="fas fa-sync-alt"></i>
                            Actualizar
                        </button>
                    </div>
                </div>

                <div class="categories-container">
                    <div id="categoriesGrid" class="categories-grid">
                        <!-- Las categorías se cargarán aquí dinámicamente -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Tab -->
        <div id="profileTab" class="tab-content">
            <div class="dashboard-container">
                <div class="profile-container">
                    <div class="profile-header">
                        <h2><i class="fas fa-user-circle"></i> Información de la Empresa</h2>
                    </div>
                    <div class="profile-content">
                        <div class="profile-card">
                            <div class="profile-info">
                                <div class="info-group">
                                    <label>Razón Social:</label>
                                    <span id="profileRazonSocial">-</span>
                                </div>
                                <div class="info-group">
                                    <label>RUT:</label>
                                    <span id="profileRut">-</span>
                                </div>
                                <div class="info-group">
                                    <label>Email:</label>
                                    <span id="profileEmail">-</span>
                                </div>
                                <div class="info-group">
                                    <label>Usuario:</label>
                                    <span id="profileUsername">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Modal -->
        <div id="categoryModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-folder-plus"></i> Nueva Categoría de Documentos</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="categoryForm">
                        <input type="hidden" id="categoryCsrfToken" value="<?= htmlspecialchars($csrfToken) ?>">
                        <div class="form-group">
                            <label for="categoryName">Nombre de la Categoría *</label>
                            <input type="text" id="categoryName" placeholder="Ej: Cartola Bancaria, Facturas, Boletas, etc."
                                   required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="categoryDescription">Descripción</label>
                            <textarea id="categoryDescription" placeholder="Descripción opcional de la categoría"
                                      rows="3" maxlength="500"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-action="cancel">Cancelar</button>
                    <button type="submit" form="categoryForm" class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Crear Categoría
                    </button>
                </div>
            </div>
        </div>

        <!-- Enhanced Document Modal -->
        <div id="documentModal" class="modal">
            <div class="modal-content large">
                <div class="modal-header">
                    <h3><i class="fas fa-folder-open"></i> <span id="modalCategoryName">Documentos</span></h3>
                    <div class="modal-actions">
                        <button id="refreshDocumentsBtn" class="btn btn-outline btn-sm">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                        <button class="modal-close">&times;</button>
                    </div>
                </div>
                <div class="modal-body">
                    <!-- Upload Area -->
                    <div class="upload-section">
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <h4>Subir Archivos</h4>
                                <p>Arrastra archivos aquí o haz clic para seleccionar</p>
                                <span class="upload-formats">PDF, DOC, XLS, JPG, PNG (máx. 10MB)</span>
                                <input type="file" id="fileInput" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            </div>
                            <div class="upload-overlay">
                                <i class="fas fa-download"></i>
                                <p>Suelta los archivos aquí</p>
                            </div>
                        </div>

                        <!-- Upload Progress -->
                        <div id="uploadProgress" class="upload-progress" style="display: none;">
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            <span class="progress-text">Subiendo archivo...</span>
                        </div>
                    </div>

                    <!-- Documents List -->
                    <div class="documents-section">
                        <div class="documents-header">
                            <h4><i class="fas fa-file-alt"></i> Archivos en esta categoría</h4>
                            <div class="documents-stats" id="documentsStats">
                                <span>0 archivos</span>
                            </div>
                        </div>
                        <div class="documents-list" id="documentsList">
                            <!-- Los documentos se cargarán aquí -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rename Modal -->
        <div id="renameModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-edit"></i> Renombrar Archivo</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="renameForm">
                        <div class="form-group">
                            <label for="newFileName">Nuevo nombre:</label>
                            <input type="text" id="newFileName" class="form-control" required maxlength="255">
                            <small class="form-text">Ingrese el nombre sin la extensión</small>
                        </div>
                        <div class="current-name">
                            <strong>Nombre actual:</strong> <span id="currentFileName"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-action="cancel">Cancelar</button>
                    <button type="submit" form="renameForm" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div id="deleteConfirmModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-exclamation-triangle"></i> Confirmar eliminación</h3>
                    <button class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="delete-warning">
                        <div class="warning-icon">
                            <i class="fas fa-trash-alt"></i>
                        </div>
                        <div class="warning-text">
                            <h4>¿Estás seguro de que deseas eliminar este documento?</h4>
                            <p>Esta acción no se puede deshacer. El archivo será eliminado permanentemente.</p>
                            <div class="file-info">
                                <strong>Archivo:</strong> <span id="deleteFileName">-</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-action="cancel">Cancelar</button>
                    <button type="button" id="confirmDeleteBtn" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar archivo
                    </button>
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

    <!-- Scripts -->
    <script src="/assets/app.js"></script>
    <script>
    // Cliente Dashboard App
    class ClientDashboard {
        constructor() {
            this.baseURL = '/api';
            this.userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}');
            this.categories = [];
            this.currentCategory = null;
            this.isDownloading = false;
            this.init();
        }

        init() {
            this.checkAuth();
            this.bindEvents();
            this.updateUserInfo();
            this.loadCategories();
            this.loadProfile();
        }

        checkAuth() {
            const token = localStorage.getItem('token');
            if (!token || !this.userInfo.username || this.userInfo.role !== 'cliente') {
                window.location.href = '/client/login/';
                return;
            }
        }

        bindEvents() {
            // Logout
            document.getElementById('logoutBtn').addEventListener('click', () => {
                this.logout();
            });

            // Navigation tabs
            document.querySelectorAll('.nav-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    this.switchTab(tab.dataset.tab);
                });
            });

            // Create category
            document.getElementById('createCategoryBtn').addEventListener('click', () => {
                this.showCategoryModal();
            });

            // Refresh
            document.getElementById('refreshBtn').addEventListener('click', () => {
                this.loadCategories();
            });

            // Category form
            document.getElementById('categoryForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.createCategory();
            });

            // Modals
            this.bindModalEvents();

            // File upload
            this.bindFileUploadEvents();

            // Refresh documents button
            document.getElementById('refreshDocumentsBtn').addEventListener('click', () => {
                if (this.currentCategory) {
                    this.loadDocumentsForCategory(this.currentCategory.id);
                }
            });

            // Rename form
            document.getElementById('renameForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveRename();
            });

            // Rename modal
            document.getElementById('renameModal').addEventListener('click', (e) => {
                if (e.target.classList.contains('modal') || e.target.classList.contains('modal-close') || e.target.dataset.action === 'cancel') {
                    this.hideRenameModal();
                }
            });

            // Delete confirmation modal
            document.getElementById('deleteConfirmModal').addEventListener('click', (e) => {
                if (e.target.classList.contains('modal') || e.target.classList.contains('modal-close') || e.target.dataset.action === 'cancel') {
                    this.hideDeleteConfirmModal();
                }
            });

            // Confirm delete button
            document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
                if (this.currentDeleteId) {
                    this.deleteDocument(this.currentDeleteId);
                }
            });
        }

        bindModalEvents() {
            // Category modal
            document.getElementById('categoryModal').addEventListener('click', (e) => {
                if (e.target.classList.contains('modal') || e.target.classList.contains('modal-close') || e.target.dataset.action === 'cancel') {
                    this.hideCategoryModal();
                }
            });

            // Document modal
            document.getElementById('documentModal').addEventListener('click', (e) => {
                if (e.target.classList.contains('modal') || e.target.classList.contains('modal-close')) {
                    this.hideDocumentModal();
                }
            });
        }

        bindFileUploadEvents() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('fileInput');

            // Enhanced drag and drop with visual feedback
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('drag-over');
            });

            uploadArea.addEventListener('dragleave', (e) => {
                e.preventDefault();
                if (!uploadArea.contains(e.relatedTarget)) {
                    uploadArea.classList.remove('drag-over');
                }
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                this.handleFileSelection(files);
            });

            uploadArea.addEventListener('click', () => {
                fileInput.click();
            });

            fileInput.addEventListener('change', (e) => {
                this.handleFileSelection(e.target.files);
            });
        }

        updateUserInfo() {
            document.getElementById('userInfo').textContent = this.userInfo.razon_social || this.userInfo.username;
        }

        switchTab(tabName) {
            switchTab(tabName);

            if (tabName === 'documents') {
                this.loadCategories();
            } else if (tabName === 'profile') {
                this.loadProfile();
            }
        }

        async logout() {
            try {
                await makeRequest(`${this.baseURL}/auth/logout`, { method: 'POST' });
            } catch (error) {
                console.error('Logout error:', error);
            }

            localStorage.removeItem('token');
            localStorage.removeItem('userInfo');
            showNotification('Sesión cerrada correctamente');

            setTimeout(() => {
                window.location.href = '/client/login/';
            }, 1000);
        }

        async loadCategories() {
            showLoading();

            try {
                const categories = await makeRequest(`${this.baseURL}/tipos-documento`);
                this.categories = categories;
                this.renderCategories(categories);
            } catch (error) {
                showNotification(error.message, 'error');
                this.renderCategories([]);
            }
            finally {
                hideLoading();
            }
        }

        renderCategories(categories) {
            const container = document.getElementById('categoriesGrid');

            if (categories.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                        <i class="fas fa-folder-plus" style="font-size: 4rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                        <h3 style="color: var(--text-secondary);">No hay categorías disponibles</h3>
                        <p style="color: var(--text-muted);">Crea tu primera categoría para organizar tus documentos.</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = categories.map(category => this.createCategoryCard(category)).join('');

            // Add event listeners to category cards
            this.bindCategoryCardEvents();
        }

        bindCategoryCardEvents() {
            const categoryCards = document.querySelectorAll('.category-card');
            categoryCards.forEach(card => {
                card.addEventListener('click', (e) => {
                    e.preventDefault();
                    const categoryData = JSON.parse(card.getAttribute('data-category'));
                    this.showDocumentModal(categoryData);
                });
            });
        }

        createCategoryCard(category) {
            return `
                <div class="category-card" data-category-id="${category.id}" data-category='${JSON.stringify(category).replace(/'/g, '&#39;')}' style="cursor: pointer; transition: all 0.3s ease;">
                    <div class="category-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>${escapeHtml(category.nombre)}</h3>
                    <p>${escapeHtml(category.descripcion || 'Sin descripción')}</p>
                    <div class="category-stats">
                        <div class="stat-item">
                            <i class="fas fa-file"></i>
                            <span>${category.total_documentos || 0} documentos</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-clock"></i>
                            <span>Actualizado</span>
                        </div>
                    </div>
                    <div style="margin-top: 10px; padding: 8px; background: rgba(0,123,186,0.1); border-radius: 4px; text-align: center;">
                        <small style="color: #007cba; font-weight: 500;">
                            <i class="fas fa-mouse-pointer"></i> Clic para gestionar documentos
                        </small>
                    </div>
                </div>
            `;
        }

        async createCategory() {
            const nombre = document.getElementById('categoryName').value.trim();
            const descripcion = document.getElementById('categoryDescription').value.trim();
            const csrfToken = document.getElementById('categoryCsrfToken').value;

            if (!nombre) {
                showNotification('El nombre de la categoría es requerido', 'error');
                return;
            }

            showLoading();

            try {
                await makeRequest(`${this.baseURL}/tipos-documento`, {
                    method: 'POST',
                    body: JSON.stringify({
                        nombre,
                        descripcion: descripcion || null,
                        codigo: nombre.toUpperCase().replace(/\s+/g, '_'),
                        csrf_token: csrfToken
                    })
                });

                showNotification('Categoría creada exitosamente');
                this.hideCategoryModal();
                this.loadCategories();
            } catch (error) {
                showNotification(error.message, 'error');
            }
            finally {
                hideLoading();
            }
        }

        showCategoryModal() {
            showModal('categoryModal');
        }

        hideCategoryModal() {
            hideModal('categoryModal');
        }

        showDocumentModal(category) {
            this.currentCategory = category;
            document.getElementById('modalCategoryName').textContent = category.nombre;
            showModal('documentModal');
            this.loadDocumentsForCategory(category.id);
        }

        hideDocumentModal() {
            hideModal('documentModal');
            this.currentCategory = null;
            document.getElementById('documentsList').innerHTML = '';
        }

        async loadDocumentsForCategory(categoryId) {
            try {
                const documents = await makeRequest(`${this.baseURL}/documentos?categoria_id=${categoryId}`);
                this.renderDocuments(documents);
            } catch (error) {
                showNotification(error.message, 'error');
                this.renderDocuments([]);
            }
        }

        renderDocuments(documents) {
            const container = document.getElementById('documentsList');
            const statsContainer = document.getElementById('documentsStats');

            // Update stats
            const fileCount = documents.length;
            const totalSize = documents.reduce((sum, doc) => sum + (doc.tamano || 0), 0);
            statsContainer.innerHTML = `
                <span><i class="fas fa-file"></i> ${fileCount} archivo${fileCount !== 1 ? 's' : ''}</span>
                <span><i class="fas fa-weight-hanging"></i> ${formatFileSize(totalSize)}</span>
            `;

            if (documents.length === 0) {
                container.innerHTML = `
                    <div class="empty-documents">
                        <i class="fas fa-file-plus"></i>
                        <h4>No hay documentos</h4>
                        <p>Sube tu primer archivo usando el área de arriba</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = documents.map(doc => this.createDocumentItem(doc)).join('');

            // Add event listeners to document actions
            this.bindDocumentActionEvents();
        }

        bindDocumentActionEvents() {
            const container = document.getElementById('documentsList');
            container.addEventListener('click', (e) => {
                if (e.target.closest('.btn-icon')) {
                    e.preventDefault();
                    const button = e.target.closest('.btn-icon');
                    const action = button.getAttribute('data-action');
                    const documentId = button.getAttribute('data-document-id');

                    switch (action) {
                        case 'download':
                            const filePath = button.getAttribute('data-file-path');
                            this.downloadDocument(filePath);
                            break;
                        case 'rename':
                            const fileName = button.getAttribute('data-file-name');
                            this.showRenameModal(documentId, fileName);
                            break;
                        case 'delete':
                            const deleteFileName = button.getAttribute('data-file-name');
                            this.showDeleteConfirmModal(documentId, deleteFileName);
                            break;
                    }
                }
            });
        }

        createDocumentItem(document) {
            // Try multiple conditions to handle different data types
            const isSubidoPorCliente = document.subido_por_cliente === true ||
                                     document.subido_por_cliente === 1 ||
                                     document.subido_por_cliente === "1" ||
                                     document.subido_por_cliente === "true";

            const clienteIdsMatch = document.cliente_id == this.userInfo.cliente_id ||
                                  String(document.cliente_id) === String(this.userInfo.cliente_id);

            const canEdit = isSubidoPorCliente && clienteIdsMatch && this.userInfo.cliente_id;

            const fileIcon = getFileIcon(document.nombre_archivo);
            const fileSize = formatFileSize(document.tamano);

            return `
                <div class="document-item" data-document-id="${document.id}">
                    <div class="document-icon">
                        <i class="fas ${fileIcon}"></i>
                    </div>
                    <div class="document-info">
                        <div class="document-name" title="${escapeHtml(document.nombre_archivo)}">${escapeHtml(document.nombre_archivo)}</div>
                        <div class="document-meta">
                            <span><i class="fas fa-weight-hanging"></i> ${fileSize}</span>
                            <span><i class="fas fa-calendar"></i> ${formatDate(document.fecha_subida)}</span>
                            <span><i class="fas fa-user"></i> ${document.subido_por_cliente ? 'Cliente' : 'Consultora'}</span>
                        </div>
                    </div>
                    <div class="document-actions">
                        <button class="btn-icon" data-action="download" data-document-id="${document.id}" data-file-path="${document.ruta_archivo}" title="Descargar">
                            <i class="fas fa-download"></i>
                        </button>
                        ${canEdit ? `
                            <button class="btn-icon" data-action="rename" data-document-id="${document.id}" data-file-name="${escapeHtml(document.nombre_archivo)}" title="Renombrar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-icon danger" data-action="delete" data-document-id="${document.id}" data-file-name="${escapeHtml(document.nombre_archivo)}" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        handleFileSelection(files) {
            if (!this.currentCategory) {
                showNotification('Error: No hay categoría seleccionada', 'error');
                return;
            }

            Array.from(files).forEach(file => {
                this.uploadFile(file);
            });
        }

        async uploadFile(file) {
            try {
                validateFile(file);

                const formData = new FormData();
                formData.append('archivo', file);
                formData.append('categoria_id', this.currentCategory.id);

                this.showUploadProgress(file.name);

                const xhr = new XMLHttpRequest();

                // Upload progress
                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        this.updateUploadProgress(percentComplete);
                    }
                });

                // Upload complete
                xhr.addEventListener('load', () => {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            showNotification('Archivo subido exitosamente');
                            this.loadDocumentsForCategory(this.currentCategory.id);
                        } catch (error) {
                            showNotification('Error procesando respuesta del servidor', 'error');
                        }
                    } else {
                        try {
                            const error = JSON.parse(xhr.responseText);
                            showNotification(error.error || 'Error al subir archivo', 'error');
                        } catch (e) {
                            showNotification('Error al subir archivo', 'error');
                        }
                    }
                    this.hideUploadProgress();
                });

                // Upload error
                xhr.addEventListener('error', () => {
                    showNotification('Error de conexión al subir archivo', 'error');
                    this.hideUploadProgress();
                });

                // Send request
                xhr.open('POST', `${this.baseURL}/documentos/upload`);
                xhr.setRequestHeader('Authorization', `Bearer ${localStorage.getItem('token')}`);
                xhr.send(formData);

            } catch (error) {
                showNotification(error.message, 'error');
                this.hideUploadProgress();
            }
        }

        showUploadProgress(fileName) {
            const progressContainer = document.getElementById('uploadProgress');
            const progressText = progressContainer.querySelector('.progress-text');
            progressText.textContent = `Subiendo: ${fileName}`;
            progressContainer.style.display = 'block';
            this.updateUploadProgress(0);
        }

        updateUploadProgress(percent) {
            const progressFill = document.querySelector('.progress-fill');
            progressFill.style.width = `${percent}%`;

            if (percent === 100) {
                const progressText = document.querySelector('.progress-text');
                progressText.textContent = 'Procesando archivo...';
            }
        }

        hideUploadProgress() {
            const progressContainer = document.getElementById('uploadProgress');
            progressContainer.style.display = 'none';
        }

        async deleteDocument(documentId) {
            // Prevent multiple clicks
            if (this.isDeleting) {
                return;
            }

            this.isDeleting = true;
            showLoading();

            try {
                await makeRequest(`${this.baseURL}/documentos/delete/${documentId}`, {
                    method: 'DELETE'
                });

                showNotification('Documento eliminado exitosamente');
                this.hideDeleteConfirmModal();
                this.loadDocumentsForCategory(this.currentCategory.id);
            } catch (error) {
                showNotification(error.message, 'error');
            } finally {
                hideLoading();
                this.isDeleting = false;
            }
        }

        async downloadDocument(filePath) {
            // Prevenir múltiples descargas simultáneas
            if (this.isDownloading) {
                return;
            }

            this.isDownloading = true;

            try {
                showLoading();

                // Primero verificar que la descarga está autorizada
                const verifyResponse = await makeRequest(`${this.baseURL}/documentos/verify-download?file=${encodeURIComponent(filePath)}`);

                if (verifyResponse.success) {
                    // Si está autorizada, proceder con la descarga
                    const token = localStorage.getItem('token');
                    const link = document.createElement('a');
                    link.href = `${this.baseURL}/documentos/download?file=${encodeURIComponent(filePath)}&token=${encodeURIComponent(token)}`;
                    link.download = verifyResponse.filename || '';
                    link.target = '_blank'; // Abre en nueva pestaña para evitar interferencias
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    showNotification('Descarga iniciada', 'success');
                } else {
                    showNotification('No tienes permisos para descargar este archivo', 'error');
                }
            } catch (error) {
                console.error('Error en descarga:', error);
                showNotification('Error al iniciar descarga: ' + error.message, 'error');
            } finally {
                hideLoading();
                // Permitir nuevas descargas después de un breve delay
                setTimeout(() => {
                    this.isDownloading = false;
                }, 1000);
            }
        }

        showRenameModal(documentId, currentFileName) {
            this.currentRenameId = documentId;

            // Extract filename without extension
            const lastDot = currentFileName.lastIndexOf('.');
            const nameWithoutExt = lastDot > 0 ? currentFileName.substring(0, lastDot) : currentFileName;

            document.getElementById('currentFileName').textContent = currentFileName;
            document.getElementById('newFileName').value = nameWithoutExt;

            showModal('renameModal');

            // Focus on input after modal is shown
            setTimeout(() => {
                document.getElementById('newFileName').focus();
                document.getElementById('newFileName').select();
            }, 100);
        }

        hideRenameModal() {
            hideModal('renameModal');
            this.currentRenameId = null;
            document.getElementById('renameForm').reset();
        }

        async saveRename() {
            const newName = document.getElementById('newFileName').value.trim();

            if (!newName) {
                showNotification('El nombre del archivo no puede estar vacío', 'error');
                return;
            } else if (!this.currentRenameId){
                showNotification('Error: No hay archivo seleccionado para renombrar', 'error')
                return;
            }

            showLoading();

            try {
                await makeRequest(`${this.baseURL}/documentos/${this.currentRenameId}/rename`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        nuevo_nombre: newName
                    })
                });

                showNotification('Archivo renombrado exitosamente');
                this.hideRenameModal();
                this.loadDocumentsForCategory(this.currentCategory.id);
            } catch (error) {
                showNotification(error.message, 'error');
            } finally {
                hideLoading();
            }
        }

        loadProfile() {
            if (!this.userInfo || !this.userInfo.id) return;

            document.getElementById('profileRazonSocial').textContent = this.userInfo.razon_social || '-';
            document.getElementById('profileRut').textContent = this.userInfo.rut_empresa || '-';
            document.getElementById('profileEmail').textContent = this.userInfo.email || '-';
            document.getElementById('profileUsername').textContent = this.userInfo.username || '-';
        }

        showDeleteConfirmModal(documentId, fileName) {
            this.currentDeleteId = documentId;
            document.getElementById('deleteFileName').textContent = fileName;
            showModal('deleteConfirmModal');
        }

        hideDeleteConfirmModal() {
            hideModal('deleteConfirmModal');
            this.currentDeleteId = null;
        }
    }

    // Función para obtener token de sesión de manera confiable
    async function setupSessionToken() {
        try {
            // Intentar usar token generado por PHP primero
            <?php if ($jwtToken && $userData): ?>
            localStorage.setItem('token', '<?= $jwtToken ?>');
            localStorage.setItem('userInfo', '<?= addslashes(json_encode($userData)) ?>');
            console.log('Token JWT configurado desde PHP');
            return true;
            <?php endif; ?>

            // Si no hay token PHP, obtenerlo via API (fallback para producción)
            console.log('Obteniendo token via API como fallback...');
            const response = await fetch('/api/auth/session-token', {
                method: 'GET',
                credentials: 'include', // Importante para incluir cookies de sesión
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.success && data.token && data.user) {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('userInfo', JSON.stringify(data.user));
                    console.log('Token configurado via API');
                    return true;
                }
            } else {
                console.error('Error obteniendo token:', await response.text());
            }

            return false;
        } catch (error) {
            console.error('Error configurando token:', error);
            return false;
        }
    }

    // Inicializar dashboard después de configurar el token
    setupSessionToken().then(tokenReady => {
        if (!tokenReady) {
            console.warn('No se pudo configurar el token, redirigiendo al login...');
            window.location.href = '/';
            return;
        }

        // Initialize client dashboard
        const clientDashboard = new ClientDashboard();

        // Make clientDashboard globally available
        window.clientDashboard = clientDashboard;
    }).catch(error => {
        console.error('Error inicializando dashboard:', error);
        window.location.href = '/';
    });

    // Welcome message auto-hide functionality
    function hideWelcomeMessage() {
        const welcomeMsg = document.getElementById('welcomeMessage');
        if (welcomeMsg) {
            welcomeMsg.classList.add('fade-out');
            setTimeout(() => {
                welcomeMsg.style.display = 'none';
            }, 500);
        }
    }

    // Auto-hide welcome message after 5 seconds
    document.addEventListener('DOMContentLoaded', function() {
        const welcomeMsg = document.getElementById('welcomeMessage');
        if (welcomeMsg) {
            // Auto-hide after 5 seconds
            setTimeout(() => {
                hideWelcomeMessage();
            }, 5000);
        }
    });
    </script>
</body>
</html>