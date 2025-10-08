<?php
// Client Login Page
require_once __DIR__ . '/../../backend/config/Auth.php';

$auth = new Auth();
$csrfToken = $auth->getCSRFToken();

$title = 'Portal Cliente - ARA & Bustamante';
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
</head>

<body class="<?= $bodyClass ?>">
    <div class="login-screen">
        <div class="login-background">
            <div class="login-container">
                <div class="login-form">
                    <div class="company-logo">
                        <div class="logo-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <h1>ARA & BUSTAMANTE</h1>
                        <p>Consultores Contables y Tributarios</p>
                    </div>

                    <div class="form-section">
                        <h2>Acceso Cliente</h2>
                        <form id="clientLoginForm">
                            <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">

                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-user"></i>
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

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i>
                                Acceder al Portal
                            </button>
                        </form>

                        <div class="admin-access" style="margin-top: 2rem; text-align: center;">
                            <a href="/admin/login/" class="link-secondary" style="color: var(--text-muted); font-size: var(--font-size-sm);">
                                <i class="fas fa-shield-alt"></i>
                                Acceso Administrativo
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="login-sidebar">
                <div class="sidebar-content">
                    <h3>Portal de Clientes</h3>
                    <ul class="feature-list">
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
    document.getElementById('clientLoginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const csrfToken = document.getElementById('csrfToken').value;

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

            // Verificar que es un cliente
            if (data.user.role !== 'cliente') {
                throw new Error('Este portal es solo para clientes. Use el acceso administrativo.');
            }

            // Guardar datos de sesión
            localStorage.setItem('token', data.token);
            localStorage.setItem('userInfo', JSON.stringify(data.user));

            showNotification('Acceso exitoso');

            // Redirigir al dashboard
            setTimeout(() => {
                window.location.href = '/client/dashboard/';
            }, 1000);

        } catch (error) {
            showNotification(error.message, 'error');
        } finally {
            hideLoading();
            // Re-habilitar formulario
            this.querySelector('button[type="submit"]').disabled = false;
        }
    });
    </script>
</body>
</html>