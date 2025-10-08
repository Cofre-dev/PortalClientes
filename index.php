<?php
// Portal de Acceso Unificado - ARA & Bustamante
require_once __DIR__ . '/backend/config/Auth.php';

$auth = new Auth();

// Si ya hay sesión activa, redirigir al dashboard correspondiente
if ($auth->isLoggedIn()) {
    $userRole = $auth->getCurrentUserRole();
    if ($userRole === 'admin') {
        header('Location: /admin/dashboard/');
        exit;
    } else {
        header('Location: /client/dashboard/');
        exit;
    }
}

$csrfToken = $auth->getCSRFToken();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Acceso - ARA & Bustamante</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏢</text></svg>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="/assets/styles.css">

    <style>
        .access-type-toggle {
            display: flex;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 25px;
            padding: 4px;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .toggle-option {
            flex: 1;
            padding: 12px 20px;
            text-align: center;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
        }

        .toggle-option.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .login-form-container {
            transition: all 0.4s ease;
        }

        .client-interface .company-logo h1 {
            color: #007cba;
        }

        .admin-interface .company-logo h1 {
            color: #dc3545;
        }

        .client-interface .company-logo .logo-icon {
            color: #007cba;
        }

        .admin-interface .company-logo .logo-icon {
            color: #dc3545;
        }

        .admin-interface .company-logo .logo-icon i {
            content: "\f3ed"; /* fa-shield-alt */
        }

        .admin-interface .form-section h2 {
            color: #dc3545;
        }

        .client-interface .form-section h2 {
            color: #007cba;
        }

        .admin-interface {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        .client-interface {
            background: linear-gradient(135deg, #007cba 0%, #005a8b 100%);
        }
    </style>
</head>

<body>
    <div class="login-screen">
        <div class="login-background client-interface" id="loginBackground">
            <div class="login-container">
                <div class="login-form">
                    <div class="company-logo">
                        <div class="logo-icon">
                            <i class="fas fa-calculator" id="logoIcon"></i>
                        </div>
                        <h1>ARA & BUSTAMANTE</h1>
                        <p>Consultores Contables y Tributarios</p>
                    </div>

                    <div class="form-section">
                        <!-- Toggle para tipo de acceso -->
                        <div class="access-type-toggle">
                            <div class="toggle-option active" data-type="client">
                                <i class="fas fa-user"></i> Cliente
                            </div>
                            <div class="toggle-option" data-type="admin">
                                <i class="fas fa-shield-alt"></i> Administrador
                            </div>
                        </div>

                        <h2 id="accessTitle">Acceso Cliente</h2>

                        <form id="unifiedLoginForm">
                            <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">
                            <input type="hidden" id="accessType" value="client">

                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-user" id="usernameIcon"></i>
                                    <input type="text" id="username" name="username" placeholder="Usuario" required
                                           autocomplete="username" maxlength="50">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" id="password" name="password" placeholder="Contraseña" required
                                           autocomplete="current-password" maxlength="100">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" id="submitButton">
                                <i class="fas fa-sign-in-alt"></i>
                                <span id="submitText">Acceder al Portal</span>
                            </button>
                        </form>

                        <div class="additional-info" id="additionalInfo" style="margin-top: 2rem; text-align: center;">
                            <p style="color: rgba(255, 255, 255, 0.8); font-size: 0.9rem;">
                                Portal seguro para la gestión de documentos contables
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="login-sidebar">
                <div class="sidebar-content">
                    <h3 id="sidebarTitle">Portal de Clientes</h3>
                    <ul class="feature-list" id="featureList">
                        <li><i class="fas fa-check"></i> Gestión completa de documentos</li>
                        <li><i class="fas fa-check"></i> Acceso 24/7 a sus archivos</li>
                        <li><i class="fas fa-check"></i> Comunicación directa con consultores</li>
                        <li><i class="fas fa-check"></i> Seguridad y confidencialidad garantizada</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Validando credenciales...</p>
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
        // Variables para controlar la interfaz
        const loginBackground = document.getElementById('loginBackground');
        const logoIcon = document.getElementById('logoIcon');
        const accessTitle = document.getElementById('accessTitle');
        const usernameIcon = document.getElementById('usernameIcon');
        const submitText = document.getElementById('submitText');
        const accessTypeInput = document.getElementById('accessType');
        const sidebarTitle = document.getElementById('sidebarTitle');
        const featureList = document.getElementById('featureList');

        // Configuraciones de interfaz
        const interfaceConfig = {
            client: {
                title: 'Acceso Cliente',
                logoIcon: 'fas fa-calculator',
                usernameIcon: 'fas fa-user',
                submitText: 'Acceder al Portal',
                sidebarTitle: 'Portal de Clientes',
                features: [
                    'Gestión completa de documentos',
                    'Acceso 24/7 a sus archivos',
                    'Comunicación directa con consultores',
                    'Seguridad y confidencialidad garantizada'
                ]
            },
            admin: {
                title: 'Acceso Administrativo',
                logoIcon: 'fas fa-shield-alt',
                usernameIcon: 'fas fa-user-shield',
                submitText: 'Acceder al Panel',
                sidebarTitle: 'Panel de Administración',
                features: [
                    'Gestión completa de clientes',
                    'Control de usuarios y permisos',
                    'Estadísticas y reportes avanzados',
                    'Configuración del sistema'
                ]
            }
        };

        // Función para cambiar interfaz
        function switchInterface(type) {
            const config = interfaceConfig[type];

            // Remover clases anteriores y agregar nueva
            loginBackground.className = `login-background ${type}-interface`;

            // Actualizar elementos de la interfaz
            logoIcon.className = config.logoIcon;
            accessTitle.textContent = config.title;
            usernameIcon.className = config.usernameIcon;
            submitText.textContent = config.submitText;
            sidebarTitle.textContent = config.sidebarTitle;
            accessTypeInput.value = type;

            // Actualizar lista de características
            featureList.innerHTML = config.features.map(feature =>
                `<li><i class="fas fa-check"></i> ${feature}</li>`
            ).join('');

            // Actualizar placeholder del usuario
            const usernameInput = document.getElementById('username');
            usernameInput.placeholder = type === 'admin' ? 'Usuario Administrador' : 'Usuario';

            // Limpiar campos
            usernameInput.value = '';
            document.getElementById('password').value = '';
        }

        // Event listeners para el toggle
        document.querySelectorAll('.toggle-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remover active de todos
                document.querySelectorAll('.toggle-option').forEach(opt => opt.classList.remove('active'));

                // Agregar active al seleccionado
                this.classList.add('active');

                // Cambiar interfaz
                const type = this.dataset.type;
                switchInterface(type);
            });
        });

        // Manejar envío del formulario
        document.getElementById('unifiedLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const csrfToken = document.getElementById('csrfToken').value;
            const accessType = document.getElementById('accessType').value;

            if (!username || !password) {
                showNotification('Por favor completa todos los campos', 'error');
                return;
            }

            showLoading();

            try {
                const response = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        username,
                        password,
                        csrf_token: csrfToken
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Error en la solicitud');
                }

                // Verificar que el rol coincida con el tipo de acceso seleccionado
                if (accessType === 'admin' && data.user.role !== 'admin') {
                    throw new Error('Credenciales de administrador requeridas para acceso administrativo.');
                }

                if (accessType === 'client' && data.user.role !== 'cliente') {
                    throw new Error('Credenciales de cliente requeridas para acceso de cliente.');
                }

                // Guardar datos de sesión según el tipo
                if (data.user.role === 'admin') {
                    localStorage.setItem('admin_token', data.token);
                    localStorage.setItem('admin_userInfo', JSON.stringify(data.user));
                } else {
                    localStorage.setItem('token', data.token);
                    localStorage.setItem('userInfo', JSON.stringify(data.user));
                }

                showNotification(`Acceso ${data.user.role === 'admin' ? 'administrativo' : 'de cliente'} exitoso`);

                // Redirigir al dashboard correspondiente
                setTimeout(() => {
                    if (data.user.role === 'admin') {
                        window.location.href = '/admin/dashboard/';
                    } else {
                        window.location.href = '/client/dashboard/';
                    }
                }, 1000);

            } catch (error) {
                showNotification(error.message, 'error');
            } finally {
                hideLoading();
                this.querySelector('button[type="submit"]').disabled = false;
            }
        });

        // Función para prellenar campos de demo (solo en desarrollo)
        function fillDemoCredentials(type) {
            if (type === 'admin') {
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'admin123';
            } else {
                document.getElementById('username').value = 'cliente1';
                document.getElementById('password').value = 'cliente123';
            }
        }

        // Solo para desarrollo - doble click en el logo para autocompletar
        document.querySelector('.company-logo').addEventListener('dblclick', function() {
            const currentType = document.getElementById('accessType').value;
            fillDemoCredentials(currentType);
            showNotification('Credenciales demo cargadas (solo desarrollo)', 'info');
        });
    </script>
</body>
</html>