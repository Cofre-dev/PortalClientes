// ARA & Bustamante Consultores - JavaScript Principal
// Funciones globales y utilidades compartidas

// Funciones globales de UI
function showLoading() {
    const loadingElement = document.getElementById('loadingOverlay');
    if (loadingElement) {
        loadingElement.classList.add('show');
    }
}

function hideLoading() {
    const loadingElement = document.getElementById('loadingOverlay');
    if (loadingElement) {
        loadingElement.classList.remove('show');
    }
}

function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const notificationText = document.getElementById('notificationText');

    if (notification && notificationText) {
        notificationText.textContent = message;
        notification.className = `notification ${type} show`;

        setTimeout(() => {
            hideNotification();
        }, 5000);
    }
}

function hideNotification() {
    const notification = document.getElementById('notification');
    if (notification) {
        notification.classList.remove('show');
    }
}

// Funciones de validación
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validateRUT(rut) {
    // Validación básica de RUT chileno
    const re = /^\d{1,8}-[\dkK]$/;
    return re.test(rut);
}

// Funciones de formato
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Función para obtener el icono de archivo
function getFileIcon(filename) {
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

// Función para hacer peticiones HTTP
async function makeRequest(url, options = {}) {
    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json'
        }
    };

    // Agregar token si existe
    const token = localStorage.getItem('token') || localStorage.getItem('admin_token');
    if (token) {
        // Usar múltiples métodos para enviar el token (compatibilidad con diferentes servidores)
        defaultOptions.headers.Authorization = `Bearer ${token}`;
        defaultOptions.headers['X-Authorization'] = `Bearer ${token}`;
        defaultOptions.headers['X-Access-Token'] = token;
    }

    const finalOptions = { ...defaultOptions, ...options };

    try {
        const response = await fetch(url, finalOptions);

        // Si es una descarga de archivo, retornar la respuesta directamente
        if (options.isDownload) {
            return response;
        }

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

// Función para manejar autenticación
function checkAuth(redirectTo = '/portal-php/login') {
    const token = localStorage.getItem('token');
    const userInfo = JSON.parse(localStorage.getItem('userInfo') || '{}');

    if (!token || !userInfo.username) {
        window.location.href = redirectTo;
        return false;
    }

    return { token, userInfo };
}

function checkAdminAuth(redirectTo = '/portal-php/admin') {
    const token = localStorage.getItem('admin_token');
    const userInfo = JSON.parse(localStorage.getItem('admin_userInfo') || '{}');

    if (!token || !userInfo.username || userInfo.role !== 'admin') {
        window.location.href = redirectTo;
        return false;
    }

    return { token, userInfo };
}

// Función para logout
function logout(isAdmin = false) {
    if (isAdmin) {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_userInfo');
        showNotification('Sesión administrativa cerrada');
        setTimeout(() => {
            window.location.href = '/portal-php/admin';
        }, 1000);
    } else {
        localStorage.removeItem('token');
        localStorage.removeItem('userInfo');
        showNotification('Sesión cerrada correctamente');
        setTimeout(() => {
            window.location.href = '/portal-php/login';
        }, 1000);
    }
}

// Funciones de validación de archivos
function validateFile(file, maxSize = 10 * 1024 * 1024) { // 10MB por defecto
    const allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/jpeg',
        'image/png'
    ];

    if (file.size > maxSize) {
        throw new Error('El archivo es demasiado grande. Máximo 10MB.');
    }

    if (!allowedTypes.includes(file.type)) {
        throw new Error('Tipo de archivo no permitido. Solo se permiten PDF, Word, Excel e imágenes.');
    }

    return true;
}

// Función para manejar drag and drop
function setupDragAndDrop(dropZone, fileInput, onFilesSelected) {
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('drag-over');
    });

    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        onFilesSelected(files);
    });

    dropZone.addEventListener('click', () => {
        fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        onFilesSelected(e.target.files);
    });
}

// Función para mostrar/ocultar modales
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');

        // Focus en el primer input si existe
        const firstInput = modal.querySelector('input, textarea, select');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');

        // Limpiar formularios dentro del modal
        const forms = modal.querySelectorAll('form');
        forms.forEach(form => form.reset());
    }
}

// Setup para cerrar modales
function setupModalCloseHandlers() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal') ||
                e.target.classList.contains('modal-close') ||
                e.target.dataset.action === 'cancel') {
                hideModal(modal.id);
            }
        });
    });
}

// Función para cambiar tabs
function switchTab(tabName, tabsSelector = '.nav-tab', contentSelector = '.tab-content') {
    // Update tab buttons
    document.querySelectorAll(tabsSelector).forEach(tab => {
        tab.classList.remove('active');
    });

    const activeTab = document.querySelector(`${tabsSelector}[data-tab="${tabName}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }

    // Update tab content
    document.querySelectorAll(contentSelector).forEach(content => {
        content.classList.remove('active');
    });

    const activeContent = document.getElementById(`${tabName}Tab`) || document.getElementById(`${tabName}-tab`);
    if (activeContent) {
        activeContent.classList.add('active');
    }
}

// Función para debounce (útil para búsquedas)
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Función para escapar HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Función para generar ID únicos
function generateUniqueId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Función para copiar al portapapeles
async function copyToClipboard(text) {
    try {
        await navigator.clipboard.writeText(text);
        showNotification('Copiado al portapapeles');
    } catch (err) {
        console.error('Error al copiar: ', err);
        showNotification('Error al copiar al portapapeles', 'error');
    }
}

// Setup inicial cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Setup para cerrar notificaciones
    const closeNotificationBtn = document.getElementById('closeNotification');
    if (closeNotificationBtn) {
        closeNotificationBtn.addEventListener('click', hideNotification);
    }

    // Setup para modales
    setupModalCloseHandlers();

    // Setup para formularios con loading
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                submitBtn.disabled = true;

                // Re-habilitar después de 3 segundos como fallback
                setTimeout(() => {
                    submitBtn.disabled = false;
                }, 3000);
            }
        });
    });

    // Auto-hide de notificaciones después de 5 segundos
    const notification = document.getElementById('notification');
    if (notification) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                    if (notification.classList.contains('show')) {
                        setTimeout(() => {
                            hideNotification();
                        }, 5000);
                    }
                }
            });
        });

        observer.observe(notification, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
});

// Exportar funciones para uso global
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.showNotification = showNotification;
window.hideNotification = hideNotification;
window.showModal = showModal;
window.hideModal = hideModal;
window.makeRequest = makeRequest;
window.checkAuth = checkAuth;
window.checkAdminAuth = checkAdminAuth;
window.logout = logout;
window.validateFile = validateFile;
window.setupDragAndDrop = setupDragAndDrop;
window.switchTab = switchTab;
window.formatFileSize = formatFileSize;
window.formatDate = formatDate;
window.getFileIcon = getFileIcon;
window.validateEmail = validateEmail;
window.validateRUT = validateRUT;
window.debounce = debounce;
window.escapeHtml = escapeHtml;
window.generateUniqueId = generateUniqueId;
window.copyToClipboard = copyToClipboard;