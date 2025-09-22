<template>
  <div class="portal-view">
    <!-- Loading state -->
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Cargando perfil...</p>
      </div>
    </div>

    <!-- Error state -->
    <div v-else-if="error" class="error-container">
      <div class="error-content">
        <i class="fas fa-exclamation-triangle"></i>
        <h2>Error de Autenticación</h2>
        <p>{{ error }}</p>
        <button @click="redirectToLogin" class="retry-button">
          <i class="fas fa-sign-in-alt"></i> Ir al Login
        </button>
      </div>
    </div>

    <!-- Main portal content -->
    <div v-else class="portal-content">
      <!-- Admin Dashboard -->
      <DashboardAdmin 
        v-if="profile && profile.role_type === 'administrador'" 
        :profile="profile" 
      />
      
      <!-- Client Dashboard -->
      <DashboardCliente 
        v-else-if="profile && profile.role_type === 'cliente'" 
        :profile="profile" 
      />
      
      <!-- Unknown role fallback -->
      <div v-else class="unknown-role">
        <i class="fas fa-user-question"></i>
        <h2>Rol no reconocido</h2>
        <p>Su cuenta no tiene un rol válido asignado. Contacte al administrador.</p>
        <button @click="logout" class="logout-btn">
          <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import DashboardAdmin from '@/components/DashboardAdmin.vue'
import DashboardCliente from '@/components/DashboardCliente.vue'

const router = useRouter()
const profile = ref(null)
const loading = ref(true)
const error = ref('')

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api',
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('accessToken')}`
  }
})

async function fetchProfile() {
  try {
    loading.value = true
    error.value = ''
    
    // Verificar si hay token
    const token = localStorage.getItem('accessToken')
    if (!token) {
      throw new Error('No hay token de acceso. Inicie sesión nuevamente.')
    }

    const response = await apiClient.get('/me/')
    profile.value = response.data
    
    console.log('Perfil cargado:', profile.value)
    
  } catch (err) {
    console.error('Error al cargar perfil:', err)
    
    if (err.response?.status === 401) {
      error.value = 'Sesión expirada. Inicie sesión nuevamente.'
      // Limpiar tokens
      localStorage.removeItem('accessToken')
      localStorage.removeItem('refreshToken')
    } else if (err.response?.status === 403) {
      error.value = 'No tiene permisos para acceder al portal.'
    } else if (err.message) {
      error.value = err.message
    } else {
      error.value = 'Error de conexión. Verifique su conexión a internet.'
    }
  } finally {
    loading.value = false
  }
}

function redirectToLogin() {
  localStorage.removeItem('accessToken')
  localStorage.removeItem('refreshToken')
  router.push('/')
}

function logout() {
  localStorage.removeItem('accessToken')
  localStorage.removeItem('refreshToken')
  router.push('/')
}

// Interceptor para manejar respuestas 401
apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      redirectToLogin()
    }
    return Promise.reject(error)
  }
)

onMounted(() => {
  fetchProfile()
})
</script>

<style scoped>
.portal-view {
  min-height: 100vh;
  position: relative;
}

/* Loading States */
.loading-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #2bd17b 0%, #061656 100%);
}

.loading-spinner {
  text-align: center;
  color: white;
  font-size: 1.2em;
}

.loading-spinner i {
  font-size: 3em;
  margin-bottom: 20px;
  animation: spin 1s linear infinite;
}

.loading-spinner p {
  margin: 0;
  font-weight: 500;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Error States */
.error-container {
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
  padding: 20px;
}

.error-content {
  text-align: center;
  color: white;
  background: rgba(255, 255, 255, 0.1);
  padding: 40px;
  border-radius: 15px;
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
  max-width: 500px;
}

.error-content i {
  font-size: 4em;
  margin-bottom: 20px;
  color: #ffffff;
}

.error-content h2 {
  margin: 0 0 15px 0;
  font-size: 1.8em;
  font-weight: 600;
}

.error-content p {
  margin: 0 0 25px 0;
  font-size: 1.1em;
  line-height: 1.5;
  color: rgba(255, 255, 255, 0.9);
}

.retry-button {
  background: white;
  color: #e74c3c;
  border: none;
  padding: 15px 30px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 1em;
}

.retry-button:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

/* Unknown Role */
.unknown-role {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background: linear-gradient(135deg, #f39c12 0%, #d35400 100%);
  color: white;
  text-align: center;
  padding: 40px;
}

.unknown-role i {
  font-size: 5em;
  margin-bottom: 25px;
  opacity: 0.9;
}

.unknown-role h2 {
  font-size: 2.2em;
  margin: 0 0 15px 0;
  font-weight: 600;
}

.unknown-role p {
  font-size: 1.2em;
  margin: 0 0 30px 0;
  max-width: 600px;
  line-height: 1.6;
  opacity: 0.95;
}

.logout-btn {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  border: 2px solid white;
  padding: 15px 30px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-size: 1em;
}

.logout-btn:hover {
  background: white;
  color: #f39c12;
  transform: translateY(-2px);
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
}

/* Portal Content */
.portal-content {
  /* El contenido principal no necesita estilos especiales 
     ya que cada dashboard maneja su propio diseño */
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .error-content,
  .unknown-role {
    padding: 30px 20px;
  }
  
  .error-content h2,
  .unknown-role h2 {
    font-size: 1.5em;
  }
  
  .error-content p,
  .unknown-role p {
    font-size: 1em;
  }
  
  .retry-button,
  .logout-btn {
    padding: 12px 25px;
    font-size: 0.9em;
  }
}
</style>