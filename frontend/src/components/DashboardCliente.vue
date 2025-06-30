<template>
  <div class="dashboard-container">
    <div class="header">
      <h1>Bienvenido, {{ profile.company_name }}</h1>
      <button @click="logout" class="logout-button">Cerrar Sesión</button>
    </div>
    <p class="subtitle">Aquí puede descargar los documentos preparados por la consultora y subir los suyos.</p>
    <div v-if="loading" class="loading-spinner">Cargando documentos...</div>
    <div v-else class="documents-table-container">
      <table>
        <thead>
          <tr>
            <th>Tipo de Documento</th>
            <th>Descargar (Consultora)</th>
            <th>Mi Archivo (Cliente)</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="doc in documentos" :key="doc.id">
            <td>{{ doc.tipo_documento.nombre }}</td>
            <td>
              <!-- CAMBIO: Añadimos 'backendUrl' al principio del href -->
              <a :href="backendUrl + doc.archivo_consultora" target="_blank" class="action-button download" v-if="doc.archivo_consultora">
                Descargar
              </a>
              <span class="status-text" v-else>No disponible</span>
            </td>
            <td>
              <!-- CAMBIO: Añadimos 'backendUrl' al principio del href -->
              <a :href="backendUrl + doc.archivo_cliente" target="_blank" class="action-button download" v-if="doc.archivo_cliente">
                Ver mi archivo
              </a>
              <label class="action-button upload" v-else>
                Subir Archivo
                <input type="file" @change="handleFileUpload($event, doc.id)" class="file-input">
              </label>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
    import { ref, onMounted } from 'vue';
    import axios from 'axios';
    import { useRouter } from 'vue-router';

    const props = defineProps({
    profile: Object
    });

    const documentos = ref([]);
    const loading = ref(true);
    const router = useRouter();

    // CAMBIO: Definimos la URL base del backend para usarla en los enlaces
    const backendUrl = 'http://127.0.0.1:8000';

    const apiClient = axios.create({
    baseURL: `${backendUrl}/api`, // Usamos la constante para construir la URL de la API
    headers: {
        'Authorization': `Bearer ${localStorage.getItem('accessToken')}`
    }
    });

    // ... (El resto de tus funciones fetchDocuments, handleFileUpload, logout)
    async function fetchDocuments() {
    try {
        const response = await apiClient.get('/documentos/');
        documentos.value = response.data;
    } catch (error) {
        console.error("Error al cargar documentos:", error);
    } finally {
        loading.value = false;
    }
    }

    async function handleFileUpload(event, docId) {
    const file = event.target.files?.[0];
    if (!file) return;
    const formData = new FormData();
    formData.append('file', file);
    try {
        loading.value = true;
        await apiClient.post(`/documentos/${docId}/subir-cliente/`, formData);
        await fetchDocuments();
    } catch (error) {
        alert('Hubo un error al subir el archivo.');
    } finally {
        loading.value = false;
    }
    }

    function logout() {
    localStorage.removeItem('accessToken');
    router.push('/');
    }


    onMounted(fetchDocuments);
</script>

<style scoped>
/* Tus estilos se mantienen igual */
.dashboard-container { padding: 40px; font-family: 'Segoe UI', sans-serif; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
h1 { color: #FFFFFF; font-weight: 300; }
.subtitle { color: #B0C4DE; margin-bottom: 30px; }
.logout-button { background-color: #E53935; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
.logout-button:hover { background-color: #C62828; }
.documents-table-container { background-color: #FFFFFF; color: #333; border-radius: 8px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2); overflow: hidden; }
table { width: 100%; border-collapse: collapse; }
thead th { background-color: #F5F7FA; color: #5A6A7A; padding: 15px 20px; text-align: left; font-weight: 600; text-transform: uppercase; font-size: 0.8em; border-bottom: 2px solid #EAEFF5; }
tbody td { padding: 15px 20px; border-bottom: 1px solid #EAEFF5; }
tbody tr:last-child td { border-bottom: none; }
.action-button { display: inline-block; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: 500; cursor: pointer; transition: all 0.3s ease; text-align: center; }
.action-button.download { background-color: #007BFF; color: white; }
.action-button.download:hover { background-color: #0056b3; }
.action-button.upload { background-color: #28a745; color: white; }
.action-button.upload:hover { background-color: #1e7e34; }
.status-text { color: #888; font-style: italic; }
.file-input { display: none; }
</style>