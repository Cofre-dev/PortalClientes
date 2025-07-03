<template>
  <div class="dashboard-container">

    <div class="header">
      <h1>Portal para Clientes</h1>

      <button @click="logout" class="logout-button">
        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
      </button>
      
      <br>

      <div class="profile-info">
        <br>
        <span>Bienvenido/a, <strong>{{ profile.company_name }}</strong></span>
        <br>
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
            <th class="text-center" style="width: 5%;"></th>
            <th class="text-center">Cliente</th>
            <th class="text-center">Tipo de Documento</th>
            <th class="text-center">Archivos</th>
            <th class="text-center">Acción Rápida</th>
          </tr>
        </thead>

        <template v-for="categoria in categorias" :key="categoria.id">
          <!-- Fila principal de la categoría -->
          <tr class="category-row" @click="toggleCategory(categoria.id)">

            <td class="text-center">
              <i class="fas chevron-icon" :class="isCategoryOpen(categoria.id) ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
            </td>

            <td class="text-center">
              {{ categoria.cliente.razon_social }}
            </td>
            
            <td class="text-center">
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
          <!-- Fila desplegable con la lista de archivos -->
          <tr v-if="isCategoryOpen(categoria.id)" class="files-row">
            <td colspan="5">
              <div class="files-list">
                <!--Si no hay archivos se mostrará este fragmento de código-->
                 <div v-if="categoria.archivos.length === 0" class="no-files">
                  <i class="fas fa-folder-open"></i> No hay archivos en esta categoría.
                </div>

                <!--Cuando se despliegue la lista, se mostrará este código que renderiza los archivos a mostrar-->
                <ul>
                  <li v-for="archivo in categoria.archivos" :key="archivo.id">
                    <span class="file-info">
                      <i class="fas fa-file-alt"></i>
                        {{ archivo.archivo.split('/').pop() }}
                        <small> (subido por: {{ archivo.subido_por }})</small>
                    </span>
                    
                    <div  class="file-actions">
                      <button @click="triggerDownload(archivo.archivo)" class="action-button download small">
                        <i class="fas fa-download"></i> Descargar
                      </button>

                      <button v-if="archivo.subido_por === 'cliente' " @click="confirmDelete(archivo.id)" class="action-button delete small">
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

    <!-- Modal de confirmación para borrar -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-content">

        <h4><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h4>

        <p>¿Está seguro de que desea borrar este archivo? Esta acción no se puede deshacer.</p>

        <div class="modal-actions">
          <button @click="showDeleteModal = false" class="button-secondary">
              Cancelar
          </button>

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
/* CSS Limpio y con el formato solicitado */
:root {
  --primary-color: #0d6efd;
  --danger-color: #dc3545;
  --success-color: #198754;
  --info-color: #0dcaf0;
  --dark-grey: #6c757d;
  --light-grey: #f8f9fa;
  --text-color: #212529;
  --border-color: #dee2e6;
  --gradient-start: #2bd17b;
  --gradient-end: rgb(6, 22, 94);
  --table-dark-bg: #092c61;
  --table-dark-header-bg: #0c3d7a;
  --table-dark-text: #a0b3d1;
}   

.dashboard-container {
  padding: 50px; 
  justify-content: center;  
  font-family: 'Segoe UI', sans-serif;
  min-height: 100vh;
  width: 850px;
  background: linear-gradient(to right, #2bd17b, rgb(6, 22, 94));
  box-sizing: border-box;
  /* height: 100px; */
  margin-left: 580px;
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
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  gap: 20px;
  color: white;
}
.subtitle {
  font-weight: bold;
  color: #0a0a0a;
  margin-bottom: 30px;
  font-size: 1.1em;
}
.logout-button {
  font-weight: bold;
  background-color: transparent;
  border: 1px solid #ff0000;
  color: white;
  padding: 10px 20px;
  border-radius: 10px;
  cursor:no-drop ;
  transition: all 0.1s;
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
  background-color: #09125f;
  color: white;
  border-radius: 10px;
  box-shadow: linear-gradient(to right, #2bd17b, rgb(6, 22, 94));
  overflow:visible ;
}

table {
  width: 100%;
  border-collapse:collapse ;
}

/* Encabezados de la tabla principal */
thead th {
  background-color: #0b081e69;
  color: var(--dark-grey);
  padding: 15px 20px;
  text-align: left;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 1em;
  border-bottom: 2px solid var(--border-color);
}

.category-row {
  cursor: pointer;
  transition: background-color 0.1s;
}

/*No toma el valor de este código, se comentará */
.category-row:hover {
  background-color: #102380;
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
  transition: transform 0.1s;
}

.file-count-badge {
  background-color: var(--dark-grey);
  color: white;
  padding: 3px 10px;
  border-radius: 0px;
  font-size: 0.85em;
  font-weight: bold;
}

/*Filas de la tabla cuando se despliega */
.files-row td {
  background-color: #201183;
  padding: 0;
}
.files-list{
  padding: 15px 30px;
}

.files-list ul{
  list-style: none;
  padding: 0;
  margin: 0;
}

.files-list li{
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px;
  border-bottom: 1px solid #eee;
}

.files-list li:last-child{
  border-bottom: none;
}

.file-info {
  display: flex;
  align-items: center;
  gap: 10px;
}

.file-info small {
  color: #888
}

.no-files{
  text-align: center;
  padding: 20px;
  color: #888
}

.file-action {
  display: flex;
  gap: 10px;
}


.files-tables tr {
  background-color: #06285c;
  border-radius: 8px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.2);
  overflow: hidden;
}

.files-tables td {
  padding: 12px 16px;
  color: white;
}
.no-files {
  text-align: center;
  padding: 20px;
  color: var(--table-dark-text);
}
.files-table {
  background-color: var(--table-dark-bg);
  width: 100%;
  color: white;
}
.files-table th {
  background-color: var(--table-dark-bg);
  font-size: 0.75em;
  padding: 10px 15px;
  color: var(--table-dark-text);
  border-bottom: 1px solid var(--table-dark-header-bg);
}

.files-table td {
  padding: 12px 15px;
  font-size: 0.9em;
  vertical-align: middle;
  border-bottom: 1px solid var(--table-dark-header-bg);
}

.files-table tbody tr:last-child td {
  border-bottom: none;
}
.files-table td .fas {
  margin-right: 10px;
  color: var(--table-dark-text);
  font-size: 1.1em;
}

.uploader-badge {
  padding: 4px 9px;
  border-radius: 5px;
  color: white;
  font-size: 0.8em;
  font-weight: 500;
  text-transform: capitalize;
}

.uploader-badge.cliente {
  background-color: var(--success-color);
}
.uploader-badge.consultora {
  background-color: var(--info-color);
}
.action-button {
  display:flow-root ;
  align-items: center;
  gap: 5px;
  padding: 7px 15px;
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

.action-button.delete {
 background-color: var(--danger-color); 
  color: white;
}

.action-button.delete:hover {
  background-color: #c82333;
}

.action-button.upload {
  background-color: #0a1a5f;
  color: white;
}

.action-button.upload:hover {
  background-color: #1c44a9;
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
  background: #051f46;
  padding: 30px;
  border-radius: 8px;
  box-shadow: 0 5px 20px rgba(0,0,0,0.3);
  width: 90%;
  max-width: 450px;
  text-align: center;
  color: white;
}
.modal-content h4 {
  margin-top: 0;
  color: var(--danger-color);
}
.modal-content p {
  margin-bottom: 25px;
  color: #d0d0d0;
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