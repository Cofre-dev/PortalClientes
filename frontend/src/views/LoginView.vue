<template>
  <div class="login-wrapper">
    <div class="login-container">
      <div class="login-card">
        <div class="card-header">
          <h1 class="title">Acceso al Portal</h1>
          <p class="subtitle">Portal de Clientes</p>
        </div>

        <form @submit.prevent="login" class="login-form">
          <div class="input-group">
            <div class="input-wrapper">
              <i class="fas fa-user input-icon"></i>
              <input
                v-model="username"
                type="text"
                placeholder="Nombre de usuario"
                class="input"
                :class="{ 'error': error && !username }"
                required
                autocomplete="username"
              />
            </div>
          </div>

          <div class="input-group">
            <div class="input-wrapper">
              <i class="fas fa-lock input-icon"></i>
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Contraseña"
                class="input"
                :class="{ 'error': error && !password }"
                required
                autocomplete="current-password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="password-toggle"
              >
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>

          <div class="form-actions">
            <button
              type="submit"
              class="login-button"
              :disabled="loading || !username || !password"
              :class="{ 'loading': loading }"
            >
              <span v-if="!loading">
                <i class="fas fa-sign-in-alt"></i>
                Ingresar
              </span>
              <span v-else class="loading-content">
                <i class="fas fa-spinner fa-spin"></i>
                Ingresando...
              </span>
            </button>
          </div>

          <div v-if="error" class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            {{ error }}
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue';
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  import apiService from '@/services/apiService';

  const loading = ref(false);
  const error = ref(null);
  const username = ref('');
  const password = ref('');
  const showPassword = ref(false);
  const router = useRouter();

  // Verificar si ya está logueado
  onMounted(() => {
    const token = localStorage.getItem('accessToken');
    if (token) {
      router.push('/portal');
    }
  });

  async function login() {
    error.value = null;
    loading.value = true;

    // Validación de campos vacíos
    if (!username.value.trim() || !password.value.trim()) {
      error.value = "Por favor, complete todos los campos";
      loading.value = false;
      return;
    }

    try {
      await apiService.login({
        username: username.value.trim(),
        password: password.value
      });

      // Redireccionar al portal con animación suave
      await router.push('/portal');

    } catch (err) {
      console.error('Error de login:', err);

      if (err.response?.status === 401) {
        error.value = 'Usuario o contraseña incorrectos';
      } else if (err.response?.status >= 500) {
        error.value = 'Error del servidor. Intente más tarde';
      } else if (!navigator.onLine) {
        error.value = 'Sin conexión a internet';
      } else {
        error.value = 'Error de conexión. Verifique su red';
      }
    } finally {
      loading.value = false;
    }
  }
</script>

<style scoped>
/* Variables CSS */
:root {
  --primary-blue: #021144;
  --secondary-blue: #061656;
  --primary-green: #2bd17b;
  --secondary-green: #36d85c;
  --text-dark: #333;
  --text-light: #666;
  --error-color: #e74c3c;
  --success-color: #27ae60;
  --card-bg: #ffffff;
  --shadow-light: rgba(0, 0, 0, 0.1);
  --shadow-medium: rgba(0, 0, 0, 0.15);
  --shadow-heavy: rgba(0, 0, 0, 0.25);
  --border-radius: 12px;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Layout principal */
.login-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  box-sizing: border-box;
  position: relative;
  overflow: hidden;
}

.login-wrapper::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(circle at 30% 70%, rgba(43, 209, 123, 0.3) 0%, transparent 50%),
              radial-gradient(circle at 70% 30%, rgba(6, 22, 86, 0.3) 0%, transparent 50%);
  pointer-events: none;
}

.login-container {
  position: relative;
  z-index: 1;
  width: 100%;
  max-width: 450px;
  animation: slideInUp 0.6s ease-out;
}

/* Tarjeta de login */
.login-card {
  background: var(--card-bg);
  padding: 2.5rem;
  border-radius: var(--border-radius);
  box-shadow: 0 20px 60px var(--shadow-heavy);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: var(--transition);
}

.login-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 25px 80px var(--shadow-heavy);
}

/* Header de la tarjeta */
.card-header {
  text-align: center;
  margin-bottom: 2rem;
}

.title {
  color: var(--text-dark);
  font-weight: 700;
  font-size: clamp(1.8rem, 4vw, 2.2rem);
  margin: 0 0 0.5rem 0;
  background: linear-gradient(135deg, var(--primary-blue), var(--primary-green));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.subtitle {
  color: var(--text-light);
  font-size: 1.1rem;
  font-weight: 400;
  margin: 0;
  opacity: 0.8;
}

/* Formulario */
.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.input-group {
  position: relative;
}

.input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.input-icon {
  position: absolute;
  left: 1rem;
  color: var(--text-light);
  z-index: 2;
  transition: var(--transition);
}

.input {
  width: 100%;
  padding: 1rem 1rem 1rem 3rem;
  border: 2px solid #e0e6ed;
  border-radius: var(--border-radius);
  font-size: 1rem;
  background: #f8f9fa;
  transition: var(--transition);
  box-sizing: border-box;
}

.input:focus {
  outline: none;
  border-color: var(--primary-green);
  background: white;
  box-shadow: 0 0 0 3px rgba(43, 209, 123, 0.1);
}

.input:focus + .input-icon,
.input:focus ~ .input-icon {
  color: var(--primary-green);
}

.input.error {
  border-color: var(--error-color);
  background: #fff5f5;
}

.input.error:focus {
  box-shadow: 0 0 0 3px rgba(231, 76, 60, 0.1);
}

.password-toggle {
  position: absolute;
  right: 1rem;
  background: none;
  border: none;
  color: var(--text-light);
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: var(--transition);
  z-index: 2;
}

.password-toggle:hover {
  color: var(--primary-green);
  background: rgba(43, 209, 123, 0.1);
}

/* Botón de login */
.form-actions {
  margin-top: 1rem;
}

.login-button {
  width: 100%;
  background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
  color: white;
  border: none;
  padding: 1rem 2rem;
  border-radius: var(--border-radius);
  font-weight: 600;
  font-size: 1.1rem;
  cursor: pointer;
  transition: var(--transition);
  position: relative;
  overflow: hidden;
  min-height: 3.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.login-button:hover:not(:disabled) {
  background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(43, 209, 123, 0.3);
}

.login-button:active {
  transform: translateY(0);
}

.login-button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
}

.login-button.loading {
  pointer-events: none;
}

.loading-content {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Mensaje de error */
.error-message {
  background: #fff5f5;
  color: var(--error-color);
  padding: 1rem;
  border-radius: var(--border-radius);
  border-left: 4px solid var(--error-color);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  margin-top: 1rem;
  animation: fadeInDown 0.3s ease-out;
}

/* Animaciones */
@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.fa-spin {
  animation: spin 1s linear infinite;
}

/* Responsive Design */
@media (max-width: 768px) {
  .login-card {
    padding: 2rem 1.5rem;
    margin: 1rem;
  }

  .title {
    font-size: 1.8rem;
  }

  .subtitle {
    font-size: 1rem;
  }

  .input {
    padding: 0.9rem 0.9rem 0.9rem 2.8rem;
    font-size: 0.95rem;
  }

  .login-button {
    padding: 0.9rem 1.5rem;
    font-size: 1rem;
    min-height: 3.2rem;
  }
}

@media (max-width: 480px) {
  .login-wrapper {
    padding: 0.5rem;
  }

  .login-card {
    padding: 1.5rem 1rem;
  }

  .title {
    font-size: 1.6rem;
  }

  .input {
    padding: 0.8rem 0.8rem 0.8rem 2.6rem;
    font-size: 0.9rem;
  }

  .login-button {
    padding: 0.8rem 1.2rem;
    font-size: 0.95rem;
    min-height: 3rem;
  }
}

/* Mejoras de accesibilidad */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}

/* Estado de enfoque mejorado para navegación por teclado */
.login-button:focus,
.input:focus,
.password-toggle:focus {
  outline: 2px solid var(--primary-green);
  outline-offset: 2px;
}
</style>