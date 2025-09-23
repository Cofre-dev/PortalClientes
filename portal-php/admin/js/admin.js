class AdminApp {
    constructor() {
        this.baseURL = 'http://localhost/portal-php/api';
        this.token = localStorage.getItem('admin_token');
        this.init();
    }

    init() {
        this.bindEvents();
        this.checkAuth();
    }

    bindEvents() {
        // Login admin
        document.getElementById('adminLoginForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.adminLogin();
        });

        // Logout admin
        document.getElementById('adminLogoutBtn').addEventListener('click', () => {
            this.adminLogout();
        });

        // Forms
        document.getElementById('createUserForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.createUser();
        });

        document.getElementById('createDocumentForm').addEventListener('submit', (e) => {
            e.preventDefault();
            this.createDocument();
        });

        // Close notification
        document.getElementById('closeNotification').addEventListener('click', () => {
            this.hideNotification();
        });
    }

    checkAuth() {
        if (this.token) {
            this.showScreen('adminPanelScreen');
            this.loadAdminData();
        } else {
            this.showScreen('adminLoginScreen');
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

    async adminLogin() {
        const username = document.getElementById('adminUsername').value;
        const password = document.getElementById('adminPassword').value;

        this.showLoading();

        try {
            const data = await this.makeRequest(`${this.baseURL}/auth.php?action=login`, {
                method: 'POST',
                body: JSON.stringify({ username, password })
            });

            // Verificar que sea admin (esto lo haríamos en el backend en producción)
            if (username === 'admin') {
                this.token = data.token;
                localStorage.setItem('admin_token', this.token);

                this.showNotification('Acceso administrativo exitoso');
                this.showScreen('adminPanelScreen');
                this.loadAdminData();
            } else {
                throw new Error('Acceso denegado. Solo administradores.');
            }
        } catch (error) {
            this.showNotification(error.message, 'error');
        } finally {
            this.hideLoading();
        }
    }

    adminLogout() {
        this.token = null;
        localStorage.removeItem('admin_token');
        this.showScreen('adminLoginScreen');
        this.showNotification('Sesión administrativa cerrada');
    }

    async loadAdminData() {
        this.loadUsuarios();
        this.loadClientes();
        this.loadDocumentos();
        this.loadTipos();
        this.loadSelects();
    }

    // Para simplificar el MVP, simulamos datos directamente
    async loadUsuarios() {
        // En un escenario real, esto vendría de una API
        const usuarios = [
            { id: 1, username: 'admin', email: 'admin@portal.com', role: 'admin', is_active: true },
            { id: 2, username: 'cliente1', email: 'cliente1@empresa.com', role: 'cliente', is_active: true }
        ];

        this.renderUsuarios(usuarios);
    }

    renderUsuarios(usuarios) {
        const tbody = document.getElementById('usuariosTableBody');
        tbody.innerHTML = usuarios.map(user => `
            <tr>
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td><span class="status-badge ${user.role === 'admin' ? 'status-active' : ''}">${user.role}</span></td>
                <td><span class="status-badge ${user.is_active ? 'status-active' : 'status-inactive'}">${user.is_active ? 'Activo' : 'Inactivo'}</span></td>
                <td>
                    <div class="actions">
                        <button class="btn btn-secondary btn-xs" onclick="admin.toggleUserStatus(${user.id})">
                            <i class="fas fa-toggle-${user.is_active ? 'on' : 'off'}"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async loadClientes() {
        const clientes = [
            { id: 1, razon_social: 'Empresa Demo S.A.', rut_empresa: '123456789', username: 'cliente1', created_at: '2024-01-01 10:00:00' }
        ];

        this.renderClientes(clientes);
    }

    renderClientes(clientes) {
        const tbody = document.getElementById('clientesTableBody');
        tbody.innerHTML = clientes.map(cliente => `
            <tr>
                <td>${cliente.id}</td>
                <td>${cliente.razon_social}</td>
                <td>${cliente.rut_empresa}</td>
                <td>${cliente.username}</td>
                <td>${new Date(cliente.created_at).toLocaleDateString()}</td>
                <td>
                    <div class="actions">
                        <button class="btn btn-primary btn-xs" onclick="admin.viewClientDocuments(${cliente.id})">
                            <i class="fas fa-eye"></i>
                            Ver Docs
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async loadDocumentos() {
        const documentos = [
            { id: 1, cliente_razon: 'Empresa Demo S.A.', tipo_nombre: 'Balance General', archivo_consultora: 'balance_demo.pdf', archivo_cliente: null, fecha_actualizacion: '2024-01-15 10:30:00' },
            { id: 2, cliente_razon: 'Empresa Demo S.A.', tipo_nombre: 'Estado de Resultados', archivo_consultora: null, archivo_cliente: null, fecha_actualizacion: '2024-01-10 14:20:00' },
            { id: 3, cliente_razon: 'Empresa Demo S.A.', tipo_nombre: 'Flujo de Efectivo', archivo_consultora: 'flujo_demo.pdf', archivo_cliente: 'flujo_cliente.pdf', fecha_actualizacion: '2024-01-20 09:15:00' }
        ];

        this.renderDocumentos(documentos);
    }

    renderDocumentos(documentos) {
        const tbody = document.getElementById('documentosTableBody');
        tbody.innerHTML = documentos.map(doc => `
            <tr>
                <td>${doc.id}</td>
                <td>${doc.cliente_razon}</td>
                <td>${doc.tipo_nombre}</td>
                <td>${doc.archivo_consultora ? `<i class="fas fa-file text-success"></i> ${doc.archivo_consultora}` : '<i class="fas fa-minus text-muted"></i> Sin archivo'}</td>
                <td>${doc.archivo_cliente ? `<i class="fas fa-file text-success"></i> ${doc.archivo_cliente}` : '<i class="fas fa-minus text-muted"></i> Sin archivo'}</td>
                <td>${new Date(doc.fecha_actualizacion).toLocaleString()}</td>
                <td>
                    <div class="actions">
                        <button class="btn btn-primary btn-xs" onclick="admin.uploadConsultoraFile(${doc.id})">
                            <i class="fas fa-upload"></i>
                            Subir
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    async loadTipos() {
        const tipos = [
            { id: 1, nombre: 'Balance General', codigo: 'BAL_GEN' },
            { id: 2, nombre: 'Estado de Resultados', codigo: 'EST_RES' },
            { id: 3, nombre: 'Flujo de Efectivo', codigo: 'FLU_EFE' },
            { id: 4, nombre: 'Declaración de Impuestos', codigo: 'DEC_IMP' },
            { id: 5, nombre: 'Análisis Financiero', codigo: 'ANA_FIN' }
        ];

        this.renderTipos(tipos);
    }

    renderTipos(tipos) {
        const tbody = document.getElementById('tiposTableBody');
        tbody.innerHTML = tipos.map(tipo => `
            <tr>
                <td>${tipo.id}</td>
                <td>${tipo.nombre}</td>
                <td>${tipo.codigo}</td>
            </tr>
        `).join('');
    }

    loadSelects() {
        // Cargar opciones en los selects
        const clienteSelect = document.getElementById('clienteSelect');
        clienteSelect.innerHTML = '<option value="1">Empresa Demo S.A.</option>';

        const tipoSelect = document.getElementById('tipoSelect');
        tipoSelect.innerHTML = `
            <option value="1">Balance General</option>
            <option value="2">Estado de Resultados</option>
            <option value="3">Flujo de Efectivo</option>
            <option value="4">Declaración de Impuestos</option>
            <option value="5">Análisis Financiero</option>
        `;
    }

    async createUser() {
        const formData = new FormData(document.getElementById('createUserForm'));
        const data = Object.fromEntries(formData);

        this.showNotification('Usuario creado exitosamente (simulado para MVP)');
        document.getElementById('createUserForm').reset();
        this.loadUsuarios();
    }

    async createDocument() {
        const formData = new FormData(document.getElementById('createDocumentForm'));
        const data = Object.fromEntries(formData);

        this.showNotification('Documento creado exitosamente (simulado para MVP)');
        document.getElementById('createDocumentForm').reset();
        this.loadDocumentos();
    }

    toggleUserStatus(userId) {
        this.showNotification(`Estado de usuario ${userId} cambiado (simulado para MVP)`);
        this.loadUsuarios();
    }

    viewClientDocuments(clienteId) {
        this.showNotification(`Viendo documentos del cliente ${clienteId}`);
        showTab('documentos');
    }

    uploadConsultoraFile(documentoId) {
        this.showNotification(`Funcionalidad de subida para documento ${documentoId} (por implementar)`);
    }
}

// Función global para cambiar tabs
function showTab(tabName) {
    // Ocultar todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });

    document.querySelectorAll('.admin-tab').forEach(tab => {
        tab.classList.remove('active');
    });

    // Mostrar el tab seleccionado
    document.getElementById(`${tabName}-tab`).classList.add('active');

    // Activar el botón del tab
    event.target.classList.add('active');
}

// Initialize the admin app
const admin = new AdminApp();