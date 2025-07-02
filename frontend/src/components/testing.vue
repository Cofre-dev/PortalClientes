<template>
  <div class="dashboard-container">
    <div class="header">
      <h1>Portal de Cliente</h1>
      <div class="profile-info">
        <span>Bienvenido, <strong>{{ profile.company_name }}</strong></span>
        <button @click="logout" class="logout-button">
          <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
      </div>
    </div>

    <p class="subtitle">Revise y gestione sus documentos.</p>

    <div v-if="loading" class="loading-spinner">
      <i class="fas fa-spinner fa-spin"></i> Cargando...
    </div>

    <div v-else class="documents-table-container">
      <table>
        <thead>
          <tr>
            <th style="width: 5%;"></th>
            <th>Tipo de Documento</th>
            <th class="text-center">Archivos</th>
            <th class="text-center">Acción Rápida</th>
          </tr>
        </thead>

        <template v-for="categoria in categorias" :key="categoria.id">
          <tr class="category-row" @click="toggleCategory(categoria.id)">

            <td class="text-center">
              <i class="fas chevron-icon" :class="isCategoryOpen(categoria.id) ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </td>

            <td>
                {{ categoria.tipo_documento.nombre }}
            </td>

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

          <tr v-if="isCategoryOpen(categoria.id)" class="files-row">
            <td colspan="4">
              <div class="files-list">

                <div v-if="categoria.archivos.length === 0" class="no-files">
                  <i class="fas fa-folder-open"></i> No hay archivos en esta categoría.
                </div>

                <table v-else class="files-table">
                  <thead>
                    <tr>
                      <th>Nombre del Archivo</th>
                      <th>Subido Por</th>
                      <th class="text-center">Descargar</th>
                      <th class="text-center">Borrar</th>
                    </tr>
                  </thead>

                  <tbody>
                    <tr v-for="archivo in categoria.archivos" :key="archivo.id">
                      <td>
                        <i class="fas fa-file-alt"></i> {{ archivo.archivo.split('/').pop() }}
                      </td>

                      <td>
                        <span class="uploader-badge" :class="archivo.subido_por">
                          {{ archivo.subido_por }}
                        </span>
                      </td>

                      <td class="text-center">
                        <button @click="triggerDownload(archivo.archivo)" class="action-button download small" title="Descargar archivo">
                          <i class="fas fa-download"></i>
                        </button>
                      </td>

                      <td class="text-center">
                        <!-- El cliente solo puede borrar los archivos que él mismo subió -->
                        <button v-if="archivo.subido_por === 'cliente'" @click="confirmDelete(archivo.id)" class="action-button delete small" title="Borrar archivo">
                          <i class="fas fa-trash-alt"></i>
                        </button>
                      </td>
                      
                    </tr>
                  </tbody>

                </table>
              </div>
            </td>
          </tr>
        </template>
      </table>
    </div>

    <!-- Modal de confirmación para borrar -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-content">
        <h4><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h4>
        <p>¿Está seguro de que desea borrar este archivo? Esta acción no se puede deshacer.</p>
        <div class="modal-actions">
          <button @click="showDeleteModal = false" class="button-secondary">Cancelar</button>
          <button @click="executeDelete" class="button-danger" :disabled="isDeleting">
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
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  profile: Object
});

const categorias = ref([]);
const loading = ref(true);
const router = useRouter();

const openCategories = ref(new Set());
const showDeleteModal = ref(false);
const fileToDeleteId = ref(null);
const isDeleting = ref(false);

const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api',
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

async function triggerDownload(fileUrl) {
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
    // La API ya sabe que es un cliente, así que solo le devolverá sus categorías
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
    await apiClient.post(`/categorias/${categoriaId}/upload-file/`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    await fetchCategorias();
    openCategories.value.add(categoriaId);
  } catch (error) {
    alert('Error al subir el archivo.');
  }
}

function confirmDelete(id) {
  fileToDeleteId.value = id;
  showDeleteModal.value = true;
}

async function executeDelete() {
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
/* Los estilos son idénticos a los del DashboardAdmin para mantener la consistencia */
:root {
  --primary-color: #007BFF;
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
h1 {
  color: #FFFFFF;
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
  background-color: #FFFFFF;
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
  background-color: #fafafa;
  padding: 10px 20px;
}
.files-list {
  padding: 0;
}
.no-files {
  text-align: center;
  padding: 20px;
  color: #888;
}

/* --- ESTILOS PARA LA TABLA ANIDADA --- */
.files-table {
  width: 100%;
  margin-top: 10px;
}
.files-table th {
  background-color: #e9ecef;
  font-size: 0.75em;
  padding: 10px;
}
.files-table td {
  padding: 10px;
  font-size: 0.9em;
  vertical-align: middle;
  /* CAMBIO: Se añade un borde inferior para separar cada archivo */
  border-bottom: 1px solid #dee2e6;
}
/* CAMBIO: Se elimina el borde de la última fila para un acabado limpio */
.files-table tbody tr:last-child td {
    border-bottom: none;
}
.files-table td .fas {
  margin-right: 8px;
  color: var(--dark-grey);
}
.uploader-badge {
  padding: 3px 8px;
  border-radius: 4px;
  color: white;
  font-size: 0.8em;
  text-transform: capitalize;
}
.uploader-badge.cliente {
  background-color: #28a745;
}
.uploader-badge.consultora {
  background-color: #17a2b8;
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
  background-color: #28a745;
  color: white;
}
.action-button.upload:hover {
  background-color: #1e7e34;
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
  background: white;
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
