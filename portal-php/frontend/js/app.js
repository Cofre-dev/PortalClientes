class PortalApp {
    constructor() {
        this.baseURL = 'http://localhost/portal-php/api';
        this.token = localStorage.getItem('token');
        this.userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}');
        this.categories = [];
        this.currentCategory = null;
        this.init();
    }

    init() {
        this.bindEvents();
        this.checkAuth();
    }

    bindEvents() {
        // Login form
        document.getElementById('loginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.login();
        });

        // Register form
        document.getElementById('registerForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.register();
        });

        // Navigation
        document.getElementById('showRegister').addEventListener('click', (e) => {
            e.preventDefault();
            this.showScreen('registerScreen');
        });

        document.getElementById('backToLogin').addEventListener('click', (e) => {
            e.preventDefault();
            this.showScreen('loginScreen');
        });

        // Dashboard
        document.getElementById('logoutBtn').addEventListener('click', () => {
            this.logout();
        });

        document.getElementById('refreshBtn').addEventListener('click', () => {
            this.loadCategories();
        });

        // Create category
        document.getElementById('createCategoryBtn').addEventListener('click', () => {
            this.showCategoryModal();
        });

        // Category form
        document.getElementById('categoryForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.createCategory();
        });

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

        // File upload
        document.getElementById('uploadArea').addEventListener('click', () => {
            document.getElementById('fileInput').click();
        });

        document.getElementById('fileInput').addEventListener('change', (e) => {
            this.handleFileSelection(e.target.files);
        });

        // Drag and drop
        const uploadArea = document.getElementById('uploadArea');
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('drag-over');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('drag-over');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('drag-over');
            this.handleFileSelection(e.dataTransfer.files);
        });

        // Navigation tabs
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                this.switchTab(tab.dataset.tab);
            });
        });

        // Close notification
        document.getElementById('closeNotification').addEventListener('click', () => {
            this.hideNotification();
        });
    }

    checkAuth() {
        if (this.token && this.userInfo.username) {
            this.showScreen('dashboardScreen');
            this.updateUserInfo();
            this.loadCategories();
            this.loadProfile();
        } else {
            this.showScreen('loginScreen');
        }
    }

    updateUserInfo() {
        document.getElementById('userInfo').textContent = this.userInfo.razon_social || this.userInfo.username;
        document.getElementById('userRole').textContent = this.userInfo.role === 'admin' ? 'Administrador' : 'Cliente';
    }

    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.nav-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}Tab`).classList.add('active');

        // Load appropriate content
        if (tabName === 'documents') {
            this.loadCategories();
        } else if (tabName === 'profile') {
            this.loadProfile();
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

    showCategoryModal() {
        document.getElementById('categoryModal').classList.add('show');
        document.getElementById('categoryName').focus();
    }

    hideCategoryModal() {
        document.getElementById('categoryModal').classList.remove('show');
        document.getElementById('categoryForm').reset();
    }

    showDocumentModal(category) {
        this.currentCategory = category;
        document.getElementById('modalCategoryName').textContent = category.nombre;
        document.getElementById('documentModal').classList.add('show');
        this.loadDocumentsForCategory(category.id);
    }

    hideDocumentModal() {
        document.getElementById('documentModal').classList.remove('show');
        this.currentCategory = null;
        document.getElementById('documentsList').innerHTML = '';
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
            throw error;
        }
    }

    async login() {
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;

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

            this.token = data.token;
            this.userInfo = data.user;
            localStorage.setItem('token', this.token);
            localStorage.setItem('userInfo', JSON.stringify(this.userInfo));

            this.showNotification('Inicio de sesión exitoso');
            this.showScreen('dashboardScreen');
            this.updateUserInfo();
            this.loadCategories();

            // Clear form
            document.getElementById('loginForm').reset();
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    async register() {
        const formData = new FormData(document.getElementById('registerForm'));
        const data = Object.fromEntries(formData);

        if (!data.username || !data.password || !data.razon_social || !data.rut_empresa) {
            this.showNotification('Por favor completa todos los campos', 'error');
            return;
        }

        this.showLoading();

        try {
            await this.makeRequest(`${this.baseURL}/auth.php?action=register`, {
                method: 'POST',
                body: JSON.stringify(data)
            });

            this.showNotification('Registro exitoso. Ahora puedes iniciar sesión.');
            this.showScreen('loginScreen');

            // Clear form
            document.getElementById('registerForm').reset();
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    logout() {
        this.token = null;
        this.userInfo = {};
        localStorage.removeItem('token');
        localStorage.removeItem('userInfo');
        this.showScreen('loginScreen');
        this.showNotification('Sesión cerrada correctamente');
    }

    async loadCategories() {
        this.showLoading();

        try {
            const categories = await this.makeRequest(`${this.baseURL}/tipos-documento.php`);
            this.categories = categories;
            this.renderCategories(categories);
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
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
            <div class="category-card" onclick="app.showDocumentModal(${JSON.stringify(category).replace(/"/g, '&quot;')})">
                <div class="category-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <h3>${category.nombre}</h3>
                <p>${category.descripcion || 'Sin descripción'}</p>
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
        const nombre = document.getElementById('categoryName').value;
        const descripcion = document.getElementById('categoryDescription').value;

        if (!nombre.trim()) {
            this.showNotification('El nombre de la categoría es requerido', 'error');
            return;
        }

        this.showLoading();

        try {
            await this.makeRequest(`${this.baseURL}/tipos-documento.php`, {
                method: 'POST',
                body: JSON.stringify({
                    nombre: nombre.trim(),
                    descripcion: descripcion.trim() || null,
                    codigo: nombre.trim().toUpperCase().replace(/\s+/g, '_')
                })
            });

            this.showNotification('Categoría creada exitosamente');
            this.hideCategoryModal();
            this.loadCategories();
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    async loadDocumentsForCategory(categoryId) {
        try {
            const documents = await this.makeRequest(`${this.baseURL}/documentos.php?categoria_id=${categoryId}`);
            this.renderDocuments(documents);
        } catch (error) {
            this.showNotification(error.message, 'error');
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
        const canDelete = document.subido_por_cliente && document.subido_por_cliente === this.userInfo.id;
        const fileIcon = this.getFileIcon(document.nombre_archivo);
        const fileSize = this.formatFileSize(document.tamano);

        return `
            <div class="document-item">
                <div class="document-icon">
                    <i class="fas ${fileIcon}"></i>
                </div>
                <div class="document-info">
                    <div class="document-name">${document.nombre_archivo}</div>
                    <div class="document-meta">
                        <span><i class="fas fa-weight-hanging"></i> ${fileSize}</span>
                        <span><i class="fas fa-calendar"></i> ${this.formatDate(document.fecha_subida)}</span>
                        <span><i class="fas fa-user"></i> ${document.subido_por_cliente ? 'Cliente' : 'Consultora'}</span>
                    </div>
                </div>
                <div class="document-actions">
                    <button class="btn-icon" onclick="app.downloadDocument('${document.ruta_archivo}')" title="Descargar">
                        <i class="fas fa-download"></i>
                    </button>
                    ${canDelete ? `
                        <button class="btn-icon danger" onclick="app.deleteDocument(${document.id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
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
            'png': 'fa-file-image',
            'txt': 'fa-file-alt'
        };
        return iconMap[extension] || 'fa-file';
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    }

    handleFileSelection(files) {
        if (!this.currentCategory) {
            this.showNotification('Error: No hay categoría seleccionada', 'error');
            return;
        }

        if (files.length === 0) return;

        Array.from(files).forEach(file => {
            this.uploadFile(file);
        });
    }

    async uploadFile(file) {
        if (!file) {
            this.showNotification('Por favor selecciona un archivo', 'error');
            return;
        }

        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            this.showNotification('El archivo es demasiado grande. Máximo 10MB.', 'error');
            return;
        }

        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                             'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                             'image/jpeg', 'image/png'];

        if (!allowedTypes.includes(file.type)) {
            this.showNotification('Tipo de archivo no permitido', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('archivo', file);
        formData.append('categoria_id', this.currentCategory.id);

        this.showLoading();

        try {
            const response = await fetch(`${this.baseURL}/documentos.php?action=subir`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${this.token}`
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Error al subir archivo');
            }

            this.showNotification('Archivo subido exitosamente');
            this.loadDocumentsForCategory(this.currentCategory.id);
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    async deleteDocument(documentId) {
        if (!confirm('¿Estás seguro de que quieres eliminar este documento?')) {
            return;
        }

        this.showLoading();

        try {
            await this.makeRequest(`${this.baseURL}/documentos.php?action=eliminar&id=${documentId}`, {
                method: 'DELETE'
            });

            this.showNotification('Documento eliminado exitosamente');
            this.loadDocumentsForCategory(this.currentCategory.id);
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    downloadDocument(filePath) {
        const link = document.createElement('a');
        link.href = `${this.baseURL}/documentos.php?action=descargar&archivo=${encodeURIComponent(filePath)}`;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    async loadProfile() {
        if (!this.userInfo || !this.userInfo.id) return;

        document.getElementById('profileRazonSocial').textContent = this.userInfo.razon_social || '-';
        document.getElementById('profileRut').textContent = this.userInfo.rut_empresa || '-';
        document.getElementById('profileEmail').textContent = this.userInfo.email || '-';
        document.getElementById('profileUsername').textContent = this.userInfo.username || '-';
    }
}

// Initialize the app
const app = new PortalApp();