<template>
  <div class="dashboard-container">
    <div class="header">
      <!-- Muestra el nombre y rol del administrador -->
      <h1>Panel de Administrador</h1>
      <div class="profile-info">
        <span>{{ profile.full_name || profile.username }} (<strong>{{ profile.role_detail }}</strong>)</span>
        <button @click="logout" class="logout-button">Cerrar Sesión</button>
      </div>
    </div>
    <p class="subtitle">Revise y gestione los documentos de sus clientes asignados.</p>

    <div v-if="loading" class="loading-spinner">Cargando documentos...</div>

    <div v-else class="documents-table-container">
      <table>
        <thead>
          <tr>
            <!-- Columna extra para el nombre del cliente -->
            <th>Cliente</th>
            <th>Tipo de Documento</th>
            <th>Archivo del Cliente</th>
            <th>Subir (Consultora)</th>
          </tr>
        </thead>
        <tbody>
          <!-- El v-for ahora itera sobre los documentos de todos los clientes -->
          <tr v-for="doc in documentos" :key="doc.id">
            <!-- Mostramos la razón social del cliente asociado al documento -->
            <td>{{ doc.cliente.razon_social }}</td>
            <td>{{ doc.tipo_documento.nombre }}</td>
            <td>
              <a :href="doc.archivo_cliente" target="_blank" class="action-button download" v-if="doc.archivo_cliente">
                Descargar
              </a>
              <span class="status-text" v-else>Pendiente</span>
            </td>
            <td>
              <!-- La lógica para subir archivos como consultor se añadiría aquí -->
              <label class="action-button upload">
                Subir
                <input type="file" @change="handleConsultantUpload($event, doc.id)" class="file-input">
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

// Define las "props" que este componente espera recibir
const props = defineProps({
  profile: Object
});

const documentos = ref([]);
const loading = ref(true);
const router = useRouter();

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('accessToken')}`
  }
});

// Serializer para Cliente en Django
// Necesitarás crear este serializer en `portal/serializers.py`
// para que la información del cliente venga completa.
//
// class ClienteSerializer(serializers.ModelSerializer):
//     class Meta:
//         model = Cliente
//         fields = ['razon_social', 'rut_empresa']
//
// Y en DocumentoSerializer, añadir:
// cliente = ClienteSerializer(read_only=True)

async function fetchDocuments() {
  // La API ya sabe que es un admin y devolverá los documentos de sus clientes
  try {
    const response = await apiClient.get('/documentos/');
    documentos.value = response.data;
  } catch (error) {
    console.error("Error al cargar documentos:", error);
  } finally {
    loading.value = false;
  }
}

function handleConsultantUpload(event, docId) {
  // Aquí iría la lógica para que el consultor suba un archivo
  // al campo `archivo_consultora` del documento.
  alert(`Funcionalidad de subida para el documento ${docId} pendiente.`);
}

function logout() {
  localStorage.removeItem('accessToken');
  router.push('/');
}

onMounted(fetchDocuments);
</script>

<style scoped>
/* Los estilos son muy similares al del cliente para mantener la consistencia */
.dashboard-container { padding: 40px; font-family: 'Segoe UI', sans-serif; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
h1 { color: #FFFFFF; font-weight: 300; }
.profile-info { display: flex; align-items: center; gap: 20px; color: white; }
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
.action-button.upload { background-color: #5A6A7A; color: white; }
.action-button.upload:hover { background-color: #434f5c; }
.status-text { color: #888; font-style: italic; }
.file-input { display: none; }
</style>
