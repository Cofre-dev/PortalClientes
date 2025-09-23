<?php
require_once __DIR__ . '/../../config/Auth.php';
$auth = new Auth();
$csrfToken = $auth->getCSRFToken();
?>

<div class="register-screen">
    <div class="register-background">
        <div class="register-container">
            <div class="register-form">
                <div class="form-header">
                    <a href="/portal-php/client/login" class="btn-back">
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
        const response = await fetch('/portal-php/api/auth/register', {
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
            window.location.href = '/portal-php/client/login';
        }, 2000);

    } catch (error) {
        showNotification(error.message, 'error');
    } finally {
        hideLoading();
        // Re-habilitar formulario
        this.querySelector('button[type="submit"]').disabled = false;
    }
});

// Formatear RUT mientras se escribe
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