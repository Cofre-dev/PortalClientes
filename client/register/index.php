<?php
// Client Register Page
require_once __DIR__ . '/../../backend/config/Auth.php';

$auth = new Auth();
$csrfToken = $auth->getCSRFToken();

$title = 'Registro Cliente - ARA & Bustamante';
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
    .help-text {
        display: block;
        font-size: var(--font-size-xs);
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .form-group input:invalid {
        border-color: var(--danger-color);
    }

    .form-group input:valid {
        border-color: var(--success-color);
    }
    </style>
</head>

<body class="<?= $bodyClass ?>">
    <div class="register-screen">
        <div class="register-background">
            <div class="register-container">
                <div class="register-form">
                    <div class="form-header">
                        <a href="/client/login/" class="btn-back">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h2>Registro de Nueva Empresa</h2>
                        <p>Complete los datos de su empresa para acceder al portal</p>
                    </div>

                    <form id="clientRegisterForm">
                        <input type="hidden" id="csrfToken" value="<?= htmlspecialchars($csrfToken) ?>">

                        <div class="form-row">
                            <div class="form-group">
                                <label for="razonSocial">Razón Social *</label>
                                <input type="text" id="razonSocial" name="razon_social" required
                                       maxlength="200" autocomplete="organization">
                            </div>

                            <div class="form-group">
                                <label for="rutEmpresa">RUT Empresa *</label>
                                <input type="text" id="rutEmpresa" name="rut_empresa" placeholder="12345678-9" required
                                       pattern="[0-9]{7,8}-[0-9kK]" maxlength="12" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="regUsername">Usuario *</label>
                                <input type="text" id="regUsername" name="username" required
                                       maxlength="50" autocomplete="username"
                                       pattern="[a-zA-Z0-9_]{3,50}"
                                       title="Mínimo 3 caracteres, solo letras, números y guiones bajos">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Corporativo *</label>
                                <input type="email" id="email" name="email" required
                                       maxlength="100" autocomplete="email">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="regPassword">Contraseña *</label>
                            <input type="password" id="regPassword" name="password" required
                                   autocomplete="new-password"
                                   pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$"
                                   title="Mínimo 8 caracteres, debe incluir al menos: una mayúscula, una minúscula y un número">
                            <small class="help-text">Mínimo 8 caracteres con al menos una mayúscula, una minúscula y un número</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-building"></i>
                            Registrar Empresa
                        </button>
                    </form>
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
    document.getElementById('clientRegisterForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData);
        data.csrf_token = document.getElementById('csrfToken').value;

        // Validaciones del lado cliente
        if (!data.username || !data.password || !data.razon_social || !data.rut_empresa || !data.email) {
            showNotification('Por favor completa todos los campos requeridos', 'error');
            return;
        }

        // Validar formato de RUT
        if (!/^[0-9]{7,8}-[0-9kK]$/.test(data.rut_empresa)) {
            showNotification('Formato de RUT inválido. Use formato: 12345678-9', 'error');
            return;
        }

        // Validar contraseña
        if (!/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d@$!%*?&]{8,}$/.test(data.password)) {
            showNotification('La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número', 'error');
            return;
        }

        showLoading();

        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.error || 'Error en el registro');
            }

            showNotification('Registro exitoso. Ahora puedes iniciar sesión.');

            // Redirigir al login después de un delay
            setTimeout(() => {
                window.location.href = '/client/login/';
            }, 1000);

        } catch (error) {
            showNotification(error.message, 'error');
        } finally {
            hideLoading();
            this.querySelector('button[type="submit"]').disabled = false;
        }
    });

    document.getElementById('rutEmpresa').addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9kK]/g, '');
        if (value.length > 1) {
            value = value.slice(0, -1) + '-' + value.slice(-1);
        }
        if (value.length > 12) {
            value = value.slice(0, 12);
        }
        e.target.value = value;
    });
    </script>
</body>
</html>