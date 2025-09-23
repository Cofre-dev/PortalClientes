<?php
require_once __DIR__ . '/../../config/Auth.php';
$auth = new Auth();

// Verificar autenticación
if (!$auth->checkSessionTimeout()) {
    header('Location: /portal-php/client/login');
    exit;
}

$csrfToken = $auth->getCSRFToken();
?>

<div class="client-dashboard">
    <nav class="navbar">
        <div class="nav-brand">
            <div class="brand-logo">
                <i class="fas fa-calculator"></i>
                <span>ARA & BUSTAMANTE</span>
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
                <span id="userInfo">Cliente</span>
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

    <!-- Document Modal -->
    <div id="documentModal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h3><i class="fas fa-files-o"></i> <span id="modalCategoryName">Documentos</span></h3>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="documents-header">
                    <div class="upload-area" id="uploadArea">
                        <div class="upload-content">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <p>Arrastra archivos aquí o haz clic para seleccionar</p>
                            <input type="file" id="fileInput" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                        </div>
                    </div>
                </div>
                <div class="documents-list" id="documentsList">
                    <!-- Los documentos se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Cliente Dashboard App
class ClientDashboard {
    constructor() {
        this.baseURL = '/portal-php/api';
        this.userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}');
        this.categories = [];
        this.currentCategory = null;
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
            window.location.href = '/portal-php/client/login';
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
        setupDragAndDrop(
            document.getElementById('uploadArea'),
            document.getElementById('fileInput'),
            (files) => this.handleFileSelection(files)
        );
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
            window.location.href = '/portal-php/client/login';
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
        } finally {
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
    }

    createCategoryCard(category) {
        return `
            <div class="category-card" onclick="clientDashboard.showDocumentModal(${JSON.stringify(category).replace(/"/g, '&quot;')})">
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
        } finally {
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

        if (documents.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: var(--text-muted);">
                    <i class="fas fa-file-plus" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p>No hay documentos en esta categoría.<br>Sube tu primer archivo usando el área de arriba.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = documents.map(doc => this.createDocumentItem(doc)).join('');
    }

    createDocumentItem(document) {
        const canDelete = document.subido_por_cliente && document.subido_por_cliente === this.userInfo.cliente_id;
        const fileIcon = getFileIcon(document.nombre_archivo);
        const fileSize = formatFileSize(document.tamano);

        return `
            <div class="document-item">
                <div class="document-icon">
                    <i class="fas ${fileIcon}"></i>
                </div>
                <div class="document-info">
                    <div class="document-name">${escapeHtml(document.nombre_archivo)}</div>
                    <div class="document-meta">
                        <span><i class="fas fa-weight-hanging"></i> ${fileSize}</span>
                        <span><i class="fas fa-calendar"></i> ${formatDate(document.fecha_subida)}</span>
                        <span><i class="fas fa-user"></i> ${document.subido_por_cliente ? 'Cliente' : 'Consultora'}</span>
                    </div>
                </div>
                <div class="document-actions">
                    <button class="btn-icon" onclick="clientDashboard.downloadDocument('${document.ruta_archivo}')" title="Descargar">
                        <i class="fas fa-download"></i>
                    </button>
                    ${canDelete ? `
                        <button class="btn-icon danger" onclick="clientDashboard.deleteDocument(${document.id})" title="Eliminar">
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

            showLoading();

            const response = await fetch(`${this.baseURL}/documentos/upload`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('token')}`
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Error al subir archivo');
            }

            showNotification('Archivo subido exitosamente');
            this.loadDocumentsForCategory(this.currentCategory.id);
        } catch (error) {
            showNotification(error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    async deleteDocument(documentId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este documento?')) {
            return;
        }

        showLoading();

        try {
            await makeRequest(`${this.baseURL}/documentos/delete/${documentId}`, {
                method: 'DELETE'
            });

            showNotification('Documento eliminado exitosamente');
            this.loadDocumentsForCategory(this.currentCategory.id);
        } catch (error) {
            showNotification(error.message, 'error');
        } finally {
            hideLoading();
        }
    }

    downloadDocument(filePath) {
        const link = document.createElement('a');
        link.href = `${this.baseURL}/documentos/download?file=${encodeURIComponent(filePath)}`;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    loadProfile() {
        if (!this.userInfo || !this.userInfo.id) return;

        document.getElementById('profileRazonSocial').textContent = this.userInfo.razon_social || '-';
        document.getElementById('profileRut').textContent = this.userInfo.rut_empresa || '-';
        document.getElementById('profileEmail').textContent = this.userInfo.email || '-';
        document.getElementById('profileUsername').textContent = this.userInfo.username || '-';
    }
}

// Initialize client dashboard
const clientDashboard = new ClientDashboard();
</script>