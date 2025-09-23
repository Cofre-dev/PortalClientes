class PortalApp {
    constructor() {
        this.baseURL = 'http://localhost/portal-php/api';
        this.token = localStorage.getItem('token');
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

        document.getElementById('showLogin').addEventListener('click', (e) => {
            e.preventDefault();
            this.showScreen('loginScreen');
        });

        // Dashboard
        document.getElementById('logoutBtn').addEventListener('click', () => {
            this.logout();
        });

        document.getElementById('refreshBtn').addEventListener('click', () => {
            this.loadDocuments();
        });

        // Close notification
        document.getElementById('closeNotification').addEventListener('click', () => {
            this.hideNotification();
        });
    }

    checkAuth() {
        if (this.token) {
            this.showScreen('dashboardScreen');
            this.loadDocuments();
        } else {
            this.showScreen('loginScreen');
        }
    }

    showScreen(screenId) {
        document.querySelectorAll('.screen').forEach(screen => {
            screen.classList.remove('active');
        });
        document.getElementById(screenId).classList.add('active');
    }

    showLoading() {
        document.getElementById('loadingOverlay').classList.add('active');
    }

    hideLoading() {
        document.getElementById('loadingOverlay').classList.remove('active');
    }

    showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const notificationText = document.getElementById('notificationText');

        notificationText.textContent = message;
        notification.className = `notification ${type} active`;

        setTimeout(() => {
            this.hideNotification();
        }, 5000);
    }

    hideNotification() {
        document.getElementById('notification').classList.remove('active');
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
            localStorage.setItem('token', this.token);

            this.showNotification('Inicio de sesión exitoso');
            this.showScreen('dashboardScreen');
            this.loadDocuments();

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
        localStorage.removeItem('token');
        this.showScreen('loginScreen');
        this.showNotification('Sesión cerrada correctamente');
    }

    async loadDocuments() {
        this.showLoading();

        try {
            const documentos = await this.makeRequest(`${this.baseURL}/documentos.php`);
            this.renderDocuments(documentos);
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    renderDocuments(documentos) {
        const container = document.getElementById('documentsContainer');

        if (documentos.length === 0) {
            container.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <i class="fas fa-folder-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <h3 style="color: #666;">No hay documentos disponibles</h3>
                    <p style="color: #999;">Los documentos aparecerán aquí cuando estén disponibles.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = documentos.map(doc => this.createDocumentCard(doc)).join('');
    }

    createDocumentCard(documento) {
        const hasConsultora = documento.archivo_consultora;
        const hasCliente = documento.archivo_cliente;

        return `
            <div class="document-card">
                <div class="document-header">
                    <div class="document-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="document-info">
                        <h3>${documento.tipo_nombre}</h3>
                        <p>Código: ${documento.tipo_codigo}</p>
                    </div>
                </div>

                <div class="document-files">
                    <div class="file-item ${hasConsultora ? 'available' : 'missing'}">
                        <div class="file-info">
                            <i class="fas ${hasConsultora ? 'fa-check-circle' : 'fa-clock'}"></i>
                            <span>Archivo Consultora</span>
                        </div>
                        <span class="file-status ${hasConsultora ? 'available' : 'missing'}">
                            ${hasConsultora ? 'Disponible' : 'Pendiente'}
                        </span>
                    </div>

                    <div class="file-item ${hasCliente ? 'available' : 'missing'}">
                        <div class="file-info">
                            <i class="fas ${hasCliente ? 'fa-check-circle' : 'fa-upload'}"></i>
                            <span>Mi Archivo</span>
                        </div>
                        <span class="file-status ${hasCliente ? 'available' : 'missing'}">
                            ${hasCliente ? 'Subido' : 'Por subir'}
                        </span>
                    </div>
                </div>

                <div class="document-actions">
                    ${hasConsultora ? `
                        <a href="${documento.archivo_consultora_url}" target="_blank" class="btn btn-success btn-sm">
                            <i class="fas fa-download"></i>
                            Descargar
                        </a>
                    ` : ''}

                    ${!hasCliente ? `
                        <div class="file-upload">
                            <input type="file" id="file-${documento.id}" onchange="app.uploadFile(${documento.id}, this.files[0])">
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-upload"></i>
                                Subir Archivo
                            </button>
                        </div>
                    ` : `
                        <a href="${documento.archivo_cliente_url}" target="_blank" class="btn btn-secondary btn-sm">
                            <i class="fas fa-eye"></i>
                            Ver Mi Archivo
                        </a>
                    `}
                </div>

                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #eee; font-size: 0.85rem; color: #666;">
                    <i class="fas fa-clock"></i>
                    Última actualización: ${new Date(documento.fecha_actualizacion).toLocaleString()}
                </div>
            </div>
        `;
    }

    async uploadFile(documentoId, file) {
        if (!file) {
            this.showNotification('Por favor selecciona un archivo', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('file', file);

        this.showLoading();

        try {
            const response = await fetch(`${this.baseURL}/documentos.php?action=subir-cliente&id=${documentoId}`, {
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
            this.loadDocuments(); // Recargar documentos
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }
}

// Initialize the app
const app = new PortalApp();