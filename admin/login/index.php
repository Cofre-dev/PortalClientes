<?php
// Admin Login Page
require_once __DIR__ . '/../../backend/config/Auth.php';

$auth = new Auth();
$csrfToken = $auth->getCSRFToken();

$title = 'Admin - ARA & Bustamante';
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
    <div class="admin-login-screen">
        <div class="admin-login-background">
            <div class="admin-login-container">
                <div class="admin-login-form">
                    <div class="admin-logo">
                        <div class="admin-logo-icon">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h1>ARA & BUSTAMANTE CONSULORES</h1>
                        <p>Panel de Administración</p>
                    </div>

                    <div class="admin-form-section">
                        <h2>Acceso Administrativo</h2>
                        <form id="adminLoginForm">
                            <input type="hidden" id="adminCsrfToken" value="<?= htmlspecialchars($csrfToken) ?>">

                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-user-shield"></i>
                                    <input type="text" id="adminUsername" name="username" placeholder="Usuario Administrador"
                                           required autocomplete="username" maxlength="50">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-wrapper">
                                    <i class="fas fa-key"></i>
                                    <input type="password" id="adminPassword" name="password" placeholder="Contraseña"
                                           required autocomplete="current-password" maxlength="100">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt"></i>
                                Acceder al Panel
                            </button>
                        </form>

                        <div class="client-access" style="margin-top: 2rem; text-align: center;">
                            <a href="/client/login/" class="link-secondary" style="color: var(--text-muted); font-size: var(--font-size-sm);">
                                <i class="fas fa-user"></i>
                                Portal de Clientes
                            </a>
                        </div>
                    </div>
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
    document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const username = document.getElementById('adminUsername').value.trim();
        const password = document.getElementById('adminPassword').value;
        const csrfToken = document.getElementById('adminCsrfToken').value;

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

            // Verificar que es un administrador
            if (data.user.role !== 'admin') {
                throw new Error('Acceso denegado. Solo administradores pueden acceder a este panel.');
            }

            // Guardar datos de sesión
            localStorage.setItem('admin_token', data.token);
            localStorage.setItem('admin_userInfo', JSON.stringify(data.user));

            showNotification('Acceso administrativo exitoso');

            // Redirigir al dashboard admin
            setTimeout(() => {
                window.location.href = '/admin/dashboard/';
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