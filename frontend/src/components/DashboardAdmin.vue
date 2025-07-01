<template>
  <div class="dashboard-container">
    <div class="header">
      <h1>Panel de Administrador</h1>
      <div class="profile-info">
        <span>{{ profile.full_name || profile.username }} (<strong>{{ profile.role_detail }}</strong>)</span>
        <button @click="logout" class="logout-button">
          <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
      </div>
    </div>
    <p class="subtitle">Revise y gestione los documentos de todos los clientes.</p>

    <!-- Indicador de Carga Principal -->
    <div v-if="loading" class="loading-spinner">
      <i class="fas fa-spinner fa-spin"></i> Cargando documentos...
    </div>

    <div v-else class="documents-table-container">
      <table>
        <thead>
          <tr>
            <th style="width: 5%;"></th> <!-- Columna para el ícono de desplegar -->
            <th>Cliente</th>
            <th>Tipo de Documento</th>
            <th class="text-center">Archivos</th>
            <th class="text-center">Acción Rápida</th>
          </tr>
        </thead>
        <!-- Usamos un template para poder tener dos <tr> por cada categoría -->
        <template v-for="categoria in categorias" :key="categoria.id">
          <!-- Fila principal de la categoría -->
          <tr class="category-row" @click="toggleCategory(categoria.id)">
            <td class="text-center">
              <i class="fas chevron-icon" :class="isCategoryOpen(categoria.id) ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </td>
            <td>{{ categoria.cliente.razon_social }}</td>
            <td>{{ categoria.tipo_documento.nombre }}</td>
            <td class="text-center">
              <span class="file-count-badge">{{ categoria.archivos.length }}</span>
            </td>
            <td class="text-center">
              <label class="action-button upload small">
                <i class="fas fa-upload"></i> Subir
                <input type="file" @change="handleUpload($event, categoria.id)" class="file-input">
              </label>
            </td>
          </tr>
          <!-- Fila desplegable con la lista de archivos -->
          <tr v-if="isCategoryOpen(categoria.id)" class="files-row">
            <td colspan="5">
              <div class="files-list">
                <div v-if="categoria.archivos.length === 0" class="no-files">
                  <i class="fas fa-folder-open"></i> No hay archivos en esta categoría.
                </div>
                <ul>
                  <li v-for="archivo in categoria.archivos" :key="archivo.id">
                    <span class="file-info">
                      <i class="fas fa-file-alt"></i>
                      {{ archivo.archivo.split('/').pop() }}
                      <small> (subido por: {{ archivo.subido_por }})</small>
                    </span>
                    <div class="file-actions">
                      <button @click="downloadFile(archivo.archivo)" class="action-button download small">
                        <i class="fas fa-download"></i> Descargar
                      </button>
                      <button @click="confirmDelete(archivo.id)" class="action-button delete small">
                        <i class="fas fa-trash-alt"></i> Borrar
                      </button>
                    </div>
                  </li>
                </ul>
              </div>
            </td>
          </tr>
        </template>
      </table>
    </div>

    <!-- Modal de confirmación para borrar (mejorado) -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-content">
        <h4><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h4>
        <p>¿Está seguro de que desea borrar este archivo? Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
          <button @click="showDeleteModal = false" class="button-secondary">Cancelar</button>
          <button @click="deleteFile" class="button-danger" :disabled="isDeleting">
            <i v-if="isDeleting" class="fas fa-spinner fa-spin"></i>
            <span v-else>Sí, Borrar</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

// NOTA: Para que los íconos (fas fa-...) funcionen,
// asegúrate de haber añadido el link de Font Awesome en tu archivo principal `index.html`
// <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />

const props = defineProps({
  profile: Object
});

const categorias = ref([]);
const loading = ref(true);
const router = useRouter();
const backendUrl = 'http://127.0.0.1:8000';

const openCategories = ref(new Set());
const showDeleteModal = ref(false);
const fileToDeleteId = ref(null);
const isDeleting = ref(false); // Nuevo estado para el feedback de borrado

const apiClient = axios.create({
  baseURL: `${backendUrl}/api`,
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('accessToken')}`
  }
});

function toggleCategory(id) {
  openCategories.value.has(id) ? openCategories.value.delete(id) : openCategories.value.add(id);
}

function isCategoryOpen(id) {
  return openCategories.value.has(id);
}

async function downloadFile(fileUrl) {
  if (!fileUrl) return;
  try {
    const response = await axios.get(fileUrl, { responseType: 'blob' });
    const url = window.URL.createObjectURL(response.data);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', fileUrl.split('/').pop() || 'download');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (error) {
    console.error('Error al descargar el archivo:', error);
    alert('No se pudo descargar el archivo.');
  }
}

async function fetchCategorias() {
  loading.value = true;
  try {
    const response = await apiClient.get('/categorias/');
    categorias.value = response.data;
  } catch (error) {
    console.error("Error al cargar categorías:", error);
  } finally {
    loading.value = false;
  }
}

async function handleUpload(event, categoriaId) {
  const file = event.target.files?.[0];
  if (!file) return;
  const formData = new FormData();
  formData.append('file', file);
  try {
    await apiClient.post(`/categorias/${categoriaId}/upload-file/`, formData);
    await fetchCategorias();
    openCategories.value.add(categoriaId); // Mantiene la categoría abierta después de subir
  } catch (error) {
    alert('Error al subir el archivo.');
  }
}

function confirmDelete(id) {
  fileToDeleteId.value = id;
  showDeleteModal.value = true;
}

async function deleteFile() {
  if (!fileToDeleteId.value) return;
  isDeleting.value = true;
  try {
    await apiClient.delete(`/archivos/${fileToDeleteId.value}/`);
    await fetchCategorias();
  } catch (error) {
    alert('Error al borrar el archivo.');
  } finally {
    isDeleting.value = false;
    showDeleteModal.value = false;
    fileToDeleteId.value = null;
  }
}

function logout() {
  localStorage.removeItem('accessToken');
  router.push('/');
}

onMounted(fetchCategorias);
</script>

<style scoped>
/* Usando variables CSS para un tema consistente y fácil de cambiar */
:root {
  --primary-color: #038eff;
  --danger-color: #dc3545;
  --dark-grey: #5A6A7A;
  --light-grey: #F5F7FA;
  --text-color: #333;
  --border-color: #EAEFF5;
}
.dashboard-container {
   padding: 40px; 
   font-family: 'Segoe UI', sans-serif;
}
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding-bottom: 20px;
}
h1 { color: #FFFFFF;
   font-weight: 300;
    margin: 0;
}
.profile-info {
  display: flex; 
  align-items: center;
  gap: 20px;
  color: white;
}

.subtitle { 
  color: #B0C4DE;
  margin-bottom: 30px;
  font-size: 1.1em;
}

.logout-button {
   background-color: transparent;
   border: 1px solid #E53935;
   color: #E53935;
   padding: 10px 20px;
   border-radius: 5px;
   cursor: pointer;
   transition: all 0.3s;
}

.logout-button:hover {
   background-color: #E53935; 
   color: white;
}

.loading-spinner {
   text-align: center;
   padding: 50px;
   font-size: 1.5em;
   color: white;
}
.loading-spinner .fa-spinner {
   margin-right: 10px;  
}

.documents-table-container { 
  background-color: #152748;
  color: var(--text-color);
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  overflow: hidden;
}

table {
   width: 100%;
   border-collapse: collapse;
}

thead th { 
  background-color: var(--light-grey);
  color: var(--dark-grey);
  padding: 15px 20px; 
  text-align: left;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.8em;
  border-bottom: 2px solid var(--border-color);
}

.category-row {
   cursor: pointer;
   transition: background-color 0.2s;
}

.category-row:hover {
   background-color: var(--light-grey);
}

tbody td {
   padding: 15px 20px;
   border-bottom: 1px solid var(--border-color);
   vertical-align: middle;
}

tbody tr:last-child td {
  border-bottom: none;
}

.chevron-icon {
  transition: transform 0.3s;
}

.file-count-badge {
  background-color: var(--dark-grey);
  color: white;
  padding: 3px 8px;
  border-radius: 10px;
  font-size: 0.8em;
}

.files-row td { 
  background-color: #0e1c67;
  padding: 0;
}

.files-list {
   padding: 15px 30px;
}

.files-list ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.files-list li {
   display: flex;
   justify-content: space-between;
   align-items: center;
   padding: 12px;
   border-bottom: 1px solid #eee;
}

.files-list li:last-child {
   border-bottom: none;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.file-info small { 
  color: #888;
}

.no-files {
  text-align: center;
  padding: 20px;
  color: #888;
}

.file-actions {
  display: flex;
  gap: 10px;
}

.action-button {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 12px;
  border-radius: 5px;
  text-decoration: none;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  border: none;
  font-family: inherit;
  font-size: 0.9em;
}

.action-button.download {
   background-color: var(--primary-color);
   color: white;
}

.action-button.download:hover { 
  background-color: #0056b3;
}

.action-button.upload {
  background-color: #17a2b8;
  color: white;
}

.action-button.upload:hover {
  background-color: #117a8b;
}

.action-button.delete {
  background-color: var(--danger-color); 
  color: white;
}

.action-button.delete:hover {
  background-color: #c82333;
}

.file-input { 
  display: none;
}

.text-center {
  text-align: center;
}

/* Estilos para el Modal de Confirmación */
.modal-overlay { 
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content { 
  background: rgb(5, 14, 77);
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
  width: 90%;
  max-width: 450px;
  text-align: center;
}

.modal-content h4 {
  margin-top: 0;
  color: var(--danger-color);
}

.modal-content p {
  margin-bottom: 25px;
  color: var(--text-color);
}

.modal-actions { 
  display: flex;
  justify-content: center;
  gap: 15px;
}

.modal-actions button {
  padding: 10px 25px;
  border-radius: 5px;
  border: none;
  cursor: pointer;
  font-weight: bold;
}

.button-secondary {
  background-color: #ccc;
  color: #333;
}

.button-danger { 
  background-color: var(--danger-color);
  color: white;
}

.button-danger:disabled {
  background-color: #c82333;
  opacity: 0.7;
  cursor: not-allowed;
}

</style>
