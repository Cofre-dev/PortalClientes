<template>
  <div v-if="profile.role_type === 'administrador'">
    <DashboardAdmin :profile="profile" /> 
  </div>

  <div v-else-if="profile.role_type === 'cliente'">
    <DashboardCliente :profile="profile" />
  </div>

  <div v-else>
    <p>Cargando perfil...</p>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router'; // Asegúrate de importar useRouter si lo usas en logout
import DashboardAdmin from '@/components/DashboardAdmin.vue';
import DashboardCliente from '@/components/DashboardCliente.vue';

const profile = ref({});
const loading = ref(true); // Añadimos un estado de carga
const router = useRouter(); // Inicializa el router

// 1. Definir el apiClient con la URL COMPLETA del backend
const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api', 
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('accessToken')}`
  }
});

// 2. Usar el apiClient para la llamada
onMounted(async () => {
  try {
    const response = await apiClient.get('/me/');
    profile.value = response.data;
  } catch (error) {
    console.error("No se pudo obtener el perfil del usuario", error);
    // Si falla, podrías redirigir al login
    router.push('/');
  } finally {
    loading.value = false; // Oculta el mensaje de "Cargando"
  }
});
</script>