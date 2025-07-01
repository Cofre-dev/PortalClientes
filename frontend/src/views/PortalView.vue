<template>
  <div class="portal-background">
    <!-- Muestra el componente del Dashboard de Administrador si el rol es 'administrador' -->
    <DashboardAdmin v-if="profile.role_type === 'administrador'" :profile="profile" />

    <!-- Muestra el componente del Dashboard de Cliente si el rol es 'cliente' -->
    <DashboardCliente v-else-if="profile.role_type === 'cliente'" :profile="profile" />

    <!-- Mientras se carga el perfil, muestra un mensaje -->
    <div v-else-if="loading" class="loading-container">
      <p>Cargando perfil...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

// Importa los componentes de dashboard
import DashboardAdmin from '@/components/DashboardAdmin.vue';
import DashboardCliente from '@/components/DashboardCliente.vue';

const profile = ref({});
const loading = ref(true);
const router = useRouter();

onMounted(async () => {
  // --- INICIO DEL CAMBIO ---
  // Creamos la instancia de Axios DENTRO del onMounted.
  // Esto asegura que se lee el token MÁS RECIENTE desde localStorage,
  // justo después de que el usuario ha iniciado sesión.
  const apiClient = axios.create({
    baseURL: 'http://127.0.0.1:8000/api',
    headers: {
      'Authorization': `Bearer ${localStorage.getItem('accessToken')}`
    }
  });
  // --- FIN DEL CAMBIO ---

  try {
    // Llama al endpoint /me/ para obtener los datos del perfil
    const response = await apiClient.get('/me/');
    profile.value = response.data; // Guarda los datos del perfil
  } catch (error) {
    console.error("No se pudo obtener el perfil del usuario. Redirigiendo al login.", error);
    router.push('/'); // Si hay un error (ej: token inválido), vuelve al login
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
/* Estilos para el fondo y el mensaje de carga */
.portal-background {
  background-color: #0A2240; /* El mismo azul marino para consistencia */
  min-height: 100vh;
  color: #EFEFEF;
}

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  font-size: 1.5em;
}
</style>
