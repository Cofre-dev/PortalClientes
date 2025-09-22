<template>
  <div class="dashboard-container">
    <div class="hero-section">
      <div class="hero-content">

        <div class="welcome-message">
          <h1 class="hero-title">
            <i class="fas fa-building">Bienvenido:</i>
            {{ profile.company_name }}
          </h1>
        </div>

        <div class="quick-actions">
          <button @click="activeTab = 'dashboard'" class="quick-action-btn" :class="{ active: activeTab === 'dashboard' }">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard</span>
          </button>

          <button @click="activeTab = 'documents'" class="quick-action-btn" :class="{ active: activeTab === 'documents' }">
            <i class="fas fa-folder-open"></i>
            <span>Documentos</span>
          </button>

          <button @click="isLogout = true" class="quick-action-btn logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Salir</span>
          </button>

        </div>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">

      <!-- Dashboard Tab -->
      <div v-if="activeTab === 'dashboard'" class="tab-content dashboard-tab">
        <div class="section-header">
          <h2 class="section-title">
            <i class="fas fa-chart-line"></i>
            Mi Dashboard
          </h2>
          <p class="section-subtitle">Resumen completo de documentos y actividad</p>
        </div>

        <div class="stats-grid">
          <div class="stat-card modern" data-stat="total">
            <div class="stat-background">
              <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ clientStats.totalDocumentos }}</div>
              <div class="stat-label">Documentos Totales</div>
              <div class="stat-trend">
                <i class="fas fa-chart-line"></i>
                Total acumulado
              </div>
            </div>
          </div>

          <div class="stat-card modern" data-stat="completed">
            <div class="stat-background">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ clientStats.categoriasCompletas }}</div>
              <div class="stat-label">Categorías Completas</div>
              <div class="stat-trend">
                <i class="fas fa-trophy"></i>
                Estado completado
              </div>
            </div>
          </div>

          <div class="stat-card modern" data-stat="recent">
            <div class="stat-background">
              <i class="fas fa-upload"></i>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ clientStats.documentosEsteMes }}</div>
              <div class="stat-label">Subidos Este Mes</div>
              <div class="stat-trend">
                <i class="fas fa-calendar-alt"></i>
                Actividad reciente
              </div>
            </div>
          </div>

          <div class="stat-card modern" data-stat="pending">
            <!-- <div class="stat-background">
              <i class="fas fa-clock"></i>
            </div> -->
            <div class="stat-content">
              <div class="stat-number">{{ clientStats.categoriasPendientes }}</div>
              <div class="stat-label">Categorías Pendientes</div>
              <div class="stat-trend">
                <i class="fas fa-tasks"></i>
                Por completar
              </div>
            </div>
          </div>
        </div>

        <!-- Progreso por Categoría -->
        <div class="progress-section">
          <h3>Estado por Categoría de Documento</h3>
          <div v-if="categorias.length === 0" class="no-categories">
            <i class="fas fa-folder-plus"></i>
            <p>No hay categorías asignadas. Contacte con su administrador.</p>
          </div>
          <div v-else class="categories-progress">
            <div 
              v-for="categoria in categorias" 
              :key="categoria.id" 
              class="category-progress-card"
              :class="{ 'has-files': categoria.archivos.length > 0 }"
            >
              <div class="category-header">
                <h4>{{ categoria.tipo_documento.nombre }}</h4>
                <div class="category-status">
                  <span class="file-count">{{ categoria.archivos.length }} archivos</span>
                  <i 
                    class="fas status-icon"
                    :class="categoria.archivos.length > 0 ? 'fa-check-circle complete' : 'fa-clock pending'"
                  ></i>
                </div>
              </div>
              
              <div class="category-details">
                <div v-if="categoria.archivos.length === 0" class="empty-state">
                  <i class="fas fa-folder-plus"></i>
                  <p>No hay documentos. ¡Sube el primero!</p>
                </div>
                <div v-else class="files-summary">
                  <div class="recent-files">
                    <p><strong>Últimos archivos:</strong></p>
                    <ul>
                      <li v-for="archivo in categoria.archivos.slice(-2)" :key="archivo.id">
                        <i class="fas fa-file-alt"></i>
                        {{ archivo.archivo.split('/').pop() }}
                        <small>({{ formatDate(archivo.fecha_subida) }})</small>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actividad Reciente -->
        <div class="recent-activity">
          <h3>Mi Actividad Reciente</h3>
          <div v-if="recentClientActivity.length === 0" class="empty-activity">
            <i class="fas fa-history"></i>
            <p>No hay actividad reciente</p>
          </div>
          <div v-else class="activity-timeline">
            <div v-for="activity in recentClientActivity" :key="activity.id" class="activity-item">
              <div class="activity-icon">
                <i class="fas fa-upload"></i>
              </div>
              <div class="activity-content">
                <p>Subiste <strong>{{ activity.documento }}</strong></p>
                <p class="activity-category">en {{ activity.categoria }}</p>
                <small>{{ formatDate(activity.fecha) }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Documents Tab -->
      <div v-if="activeTab === 'documents'" class="tab-content">
        <p class="subtitle">Revise y gestione sus documentos.</p>

        <!-- Filtro de búsqueda -->
        <div class="filters-container">
          <div class="search-container">
            <i class="fas fa-search"></i>
            <input 
              v-model="searchFilter" 
              type="text" 
              placeholder="Buscar documentos..."
              class="search-input"
            >
          </div>
          <select v-model="statusFilter" class="filter-select">
            <option value="">Todas las categorías</option>
            <option value="with-files">Con archivos</option>
            <option value="empty">Sin archivos</option>
          </select>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="loading-spinner">
          <i class="fas fa-spinner fa-spin"></i> Cargando...
        </div>
        
        <!-- Documents Table -->
        <div v-else class="documents-table-container">
          <table>
            <thead>
              <tr>
                <th style="width: 5%;"></th>
                <th>Tipo de Documento</th>
                <th class="text-center">Estado</th>
                <th class="text-center">Archivos</th>
                <th class="text-center">Acción Rápida</th>
              </tr>
            </thead>
            <tbody>
              <!-- Mensaje si no hay categorías -->
              <tr v-if="filteredCategorias.length === 0">
                <td colspan="5" class="no-categories-message">
                  <i class="fas fa-search"></i>
                  No se encontraron categorías con los filtros aplicados.
                </td>
              </tr>
              <!-- Bucle para mostrar las categorías -->
              <template v-else v-for="categoria in filteredCategorias" :key="categoria.id">
                <!-- Fila principal de la categoría -->
                <tr class="category-row" @click="toggleCategory(categoria.id)">
                  <td class="text-center">
                    <i class="fas chevron-icon" :class="isCategoryOpen(categoria.id) ? 'fa-chevron-down' : 'fa-chevron-right'"></i>
                  </td>

                  <td>{{ categoria.tipo_documento.nombre }}</td>
                  
                  <td class="text-center">
                    <span 
                      class="status-badge"
                      :class="categoria.archivos.length > 0 ? 'complete' : 'pending'"
                    >
                      <i class="fas" :class="categoria.archivos.length > 0 ? 'fa-check-circle' : 'fa-clock'"></i>
                      {{ categoria.archivos.length > 0 ? 'Completo' : 'Pendiente' }}
                    </span>
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
                      <div v-if="categoria.archivos.length === 0" class="no-files">
                        <i class="fas fa-folder-open"></i> No hay archivos en esta categoría.
                      </div>
                      <table v-else class="files-table">
                        <thead>
                          <tr>
                            <th>Nombre del archivo</th>
                            <th>Subido por</th>
                            <th class="text-center">Descargar</th>
                            <th class="text-center">Borrar</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr v-for="archivo in categoria.archivos" :key="archivo.id">
                            <td>
                              <i class="fas fa-file-alt"></i> {{ archivo.archivo.split('/').pop() }}
                            </td>
                            
                            <td class="text-center">
                              <span class="uploader-badge" :class="archivo.subido_por">
                                {{ archivo.subido_por}}
                              </span>
                            </td>

                            <td class="text-center">
                              <button @click="triggerDownload(archivo.archivo)" class="action-button download" title="Descargar archivo">
                                <i class="fas fa-download"></i> Descargar
                              </button>
                            </td>
                            <td class="text-center">
                              <button v-if="archivo.subido_por === 'cliente'" @click="confirmDelete(archivo.id)" class="action-button delete" title="Borrar archivo">
                                <i class="fas fa-trash-alt"></i> Borrar
                              </button>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
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
    
    <!-- Modal de logout -->
    <transition name="modal-fade">
      <div v-if="isLogout" class="modal-overlay" @click.self="isLogout = false">
        <div class="modal-content logout-modal">
          <div class="modal-icon">
            <i class="fas fa-sign-out-alt fa-3x"></i>
          </div>
          <h4>¿Cerrar sesión?</h4>
          <p>¿Está seguro de que desea salir de su cuenta?</p>
          <div class="modal-actions">
            <button @click="isLogout = false" class="button-secondary">
              Cancelar
            </button>
            <button @click="confirmLogout" class="button-danger">
              <i class="fas fa-check"></i> Sí, cerrar sesión
            </button>
          </div>
        </div>
      </div>
    </transition>

    </div>
    <!-- Fin Main Content Area -->

  </div>
  <!-- Fin Dashboard Container -->
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const props = defineProps({
  profile: Object
});

// Reactive data
const activeTab = ref('documents'); // Empezar en documents como el original
const categorias = ref([]);
const clientStats = ref({
  totalDocumentos: 0,
  categoriasCompletas: 0,
  documentosEsteMes: 0,
  categoriasPendientes: 0
});
const recentClientActivity = ref([]);

const loading = ref(true);
const router = useRouter();

// Filters
const searchFilter = ref('');
const statusFilter = ref('');

// Modal states
const openCategories = ref(new Set());
const showDeleteModal = ref(false);
const fileToDeleteId = ref(null);
const isDeleting = ref(false);
const isLogout = ref(false);

const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL + "/api",
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('accessToken')}`
  }
});

// Computed
const filteredCategorias = computed(() => {
  let filtered = categorias.value;
  
  if (searchFilter.value) {
    const search = searchFilter.value.toLowerCase();
    filtered = filtered.filter(cat => 
      cat.tipo_documento.nombre.toLowerCase().includes(search)
    );
  }
  
  if (statusFilter.value === 'with-files') {
    filtered = filtered.filter(cat => cat.archivos.length > 0);
  } else if (statusFilter.value === 'empty') {
    filtered = filtered.filter(cat => cat.archivos.length === 0);
  }
  
  return filtered;
});

// Methods
function toggleCategory(id) {
  openCategories.value.has(id) ? openCategories.value.delete(id) : openCategories.value.add(id);
}

function isCategoryOpen(id) {
  return openCategories.value.has(id);
}

function calculateClientStats() {
  const totalDocumentos = categorias.value.reduce((sum, cat) => sum + cat.archivos.length, 0);
  const categoriasCompletas = categorias.value.filter(cat => cat.archivos.length > 0).length;
  const categoriasPendientes = categorias.value.filter(cat => cat.archivos.length === 0).length;
  
  // Calcular documentos de este mes
  const thisMonth = new Date();
  const documentosEsteMes = categorias.value.reduce((sum, cat) => {
    const thisMonthFiles = cat.archivos.filter(archivo => {
      const fileDate = new Date(archivo.fecha_subida);
      return fileDate.getMonth() === thisMonth.getMonth() && 
             fileDate.getFullYear() === thisMonth.getFullYear();
    });
    return sum + thisMonthFiles.length;
  }, 0);

  clientStats.value = {
    totalDocumentos,
    categoriasCompletas,
    documentosEsteMes,
    categoriasPendientes
  };
}

function generateRecentActivity() {
  const allFiles = [];
  categorias.value.forEach(categoria => {
    categoria.archivos.forEach(archivo => {
      if (archivo.subido_por === 'cliente') {
        allFiles.push({
          id: archivo.id,
          documento: archivo.archivo.split('/').pop(),
          categoria: categoria.tipo_documento.nombre,
          fecha: archivo.fecha_subida
        });
      }
    });
  });
  
  // Ordenar por fecha y tomar los 5 más recientes
  recentClientActivity.value = allFiles
    .sort((a, b) => new Date(b.fecha) - new Date(a.fecha))
    .slice(0, 5);
}

async function fetchCategorias() {
  loading.value = true;
  try {
    const response = await apiClient.get('/categorias/');
    categorias.value = response.data;
    calculateClientStats();
    generateRecentActivity();
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
    openCategories.value.add(categoriaId);
    
    // Show success message
    const categoria = categorias.value.find(c => c.id === categoriaId);
    if (categoria) {
      alert(`Archivo subido exitosamente a ${categoria.tipo_documento.nombre}`);
    }
  } catch (error) {
    alert('Error al subir el archivo. Inténtalo de nuevo.');
  }
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
    alert('Archivo eliminado correctamente');
  } catch (error) {
    alert('Error al borrar el archivo.');
  } finally {
    isDeleting.value = false;
    showDeleteModal.value = false;
    fileToDeleteId.value = null;
  }
}

function confirmLogout() {
  localStorage.removeItem('accessToken');
  router.push('/');
  isLogout.value = false;
}

function formatDate(dateString) {
  if (!dateString) return 'Fecha no disponible';
  const options = { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric'
  };
  return new Date(dateString).toLocaleDateString('es-CL', options);
}

onMounted(fetchCategorias);
</script>

<style scoped>
/* Variables CSS elegantes */
:root {
  --primary-blue: #021144;
  --secondary-blue: #061656;
  --primary-green: #2bd17b;
  --secondary-green: #36d85c;
  --success-color: #27ae60;
  --warning-color: #f39c12;
  --danger-color: #e74c3c;
  --info-color: #3498db;
  --text-light: #ffffff;
  --text-secondary: rgba(255, 255, 255, 0.8);
  --text-muted: rgba(255, 255, 255, 0.6);
  --border-light: rgba(255, 255, 255, 0.2);
  --bg-glass: rgba(255, 255, 255, 0.1);
  --shadow-light: rgba(0, 0, 0, 0.1);
  --shadow-medium: rgba(0, 0, 0, 0.2);
  --radius-sm: 8px;
  --radius-md: 12px;
  --radius-lg: 16px;
  --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Layout principal responsive */
.dashboard-container {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-blue) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  position: relative;
  overflow-x: hidden;
}

/* Hero Section */
.hero-section {
  padding: 2rem;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 2rem;
  align-items: center;
  min-height: 30vh;
  position: relative;
  background: linear-gradient(135deg, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.3) 100%);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border-light);
}

.hero-content {
  text-align: center;
  z-index: 2;
}

.welcome-message {
  /* text-align: center; */
  margin-bottom: 2rem;
}

.hero-title {
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 700;
  color: var(--text-light);
  margin: 0 0 1rem 0;
  display: flex;
  align-items: center;
  gap: 1rem;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.hero-title i {
  color: var(--primary-green);
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
}

.hero-subtitle {
  text-align: center;
  font-size: clamp(1.2rem, 3vw, 1.8rem);
  color: var(--text-secondary);
  margin: 0 0 1rem 0;
  font-weight: 500;
}

.company-tag {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: var(--bg-glass);
  backdrop-filter: blur(10px);
  padding: 0.5rem 1rem;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border-light);
  color: var(--text-secondary);
  font-size: 0.9rem;
  font-weight: 500;
}

.quick-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.quick-action-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  background: var(--bg-glass);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  min-width: 80px;
}

.quick-action-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px var(--shadow-medium);
}

.quick-action-btn.active {
  background: var(--primary-green);
  color: white;
  border-color: var(--primary-green);
}

.quick-action-btn.logout {
  background: rgba(231, 76, 60, 0.2);
  border-color: var(--danger-color);
}

.quick-action-btn.logout:hover {
  background: var(--danger-color);
  color: white;
}

.quick-action-btn i {
  font-size: 1.2rem;
}

.quick-action-btn span {
  font-size: 0.85rem;
  font-weight: 500;
}

/* Hero Decoration */
.hero-decoration {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
}

.floating-card {
  position: absolute;
  width: 60px;
  height: 60px;
  background: var(--bg-glass);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-light);
  font-size: 1.5rem;
  animation: float 6s ease-in-out infinite;
}

.floating-card:nth-child(1) {
  top: -20px;
  right: 20px;
  animation-delay: 0s;
}

.floating-card:nth-child(2) {
  top: 20px;
  right: -10px;
  animation-delay: 2s;
}

.floating-card:nth-child(3) {
  top: 60px;
  right: 40px;
  animation-delay: 4s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

/* Content Sections */
.main-content {
  padding: 2rem;
}

.tab-content {
  background: var(--bg-glass);
  backdrop-filter: blur(15px);
  border-radius: var(--radius-lg);
  padding: 2rem;
  border: 1px solid var(--border-light);
  margin-bottom: 2rem;
  animation: fadeInUp 0.6s ease-out;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.subtitle {
  color: var(--text-secondary);
  font-size: 1.1rem;
  margin-bottom: 2rem;
  text-align: center;
}

/* Section Headers */
.section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-title {
  font-size: clamp(1.5rem, 4vw, 2rem);
  color: var(--text-light);
  margin: 0 0 0.5rem 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  font-weight: 600;
}

.section-title i {
  color: var(--primary-green);
}

.section-subtitle {
  color: var(--text-secondary);
  font-size: 1rem;
  margin: 0;
  font-weight: 400;
}

/* Modern Stats Grid */
.stats-grid {
  /* border: 5px solid black; */
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card.modern {
  /* border: 5px solid black; */
  position: relative;
  background: var(--bg-glass);
  backdrop-filter: blur(20px);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: 2rem;
  overflow: hidden;
  transition: var(--transition);
  cursor: pointer;
}

.stat-card.modern::before {
  border: 5px solid rgb(12, 12, 49);
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary-green), var(--primary-blue));
  opacity: 0;
  transition: var(--transition);
}

.stat-card.modern:hover {
  border: 5px solid;
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border-color: var(--primary-green);
}

.stat-card.modern:hover::before {
  opacity: 1;
}


/* .stat-card.modern:hover .stat-background {
  opacity: 0.6;
  transform: scale(1.1);
} */

.stat-background i {
  font-size: 1.5rem;
  color: var(--text-light);
}

.stat-content {
  position: relative;
  z-index: 2;
  
}

.stat-number {
  font-size: clamp(2rem, 5vw, 3rem);
  font-weight: 700;
  color: var(--text-light);
  line-height: 1;
  margin-bottom: 0.5rem;
  background: #021144;
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-label {
  font-size: 1rem;
  color: var(--text-secondary);
  font-weight: 500;
  margin-bottom: 0.75rem;
}

.stat-trend {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.85rem;
  color: var(--text-muted);
  font-weight: 400;
}

.stat-trend i {
  color: var(--primary-green);
  opacity: 0.7;
}

/* Color variations for stat cards */
.stat-card.modern[data-stat="total"] .stat-background {
  background: linear-gradient(135deg, var(--info-color), #2980b9);
}

.stat-card.modern[data-stat="completed"] .stat-background {
  background: linear-gradient(135deg, var(--success-color), #1e8449);
}

/* .stat-card.modern[data-stat="recent"] .stat-background {
  background: linear-gradient(135deg, #8e44ad, #6c3483);
} */

.stat-card.modern[data-stat="pending"] .stat-background {
  background: linear-gradient(135deg, var(--warning-color), #d68910);
}

/* Progress Section */
.progress-section {
  background: rgba(255, 255, 255, 0.05);
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 25px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.progress-section h3 {
  color: white;
  margin-bottom: 18px;
  font-size: 1.1em;
}

.no-categories {
  text-align: center;
  padding: 40px;
  color: rgba(255, 255, 255, 0.6);
}

.no-categories i {
  font-size: 3em;
  margin-bottom: 15px;
  color: rgba(255, 255, 255, 0.4);
}

.categories-progress {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.category-progress-card {
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 10px;
  padding: 16px;
  transition: all 0.3s;
}

.category-progress-card.has-files {
  border-color: #27ae60;
  background: rgba(39, 174, 96, 0.1);
}

.category-progress-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 12px;
}

.category-header h4 {
  margin: 0;
  color: white;
  font-size: 1em;
  font-weight: 600;
}

.category-status {
  display: flex;
  align-items: center;
  gap: 8px;
}

.file-count {
  font-size: 0.8em;
  color: rgba(255, 255, 255, 0.7);
}

.status-icon.complete { color: #27ae60; }
.status-icon.pending { color: #f39c12; }

.empty-state {
  text-align: center;
  padding: 15px;
  color: rgba(255, 255, 255, 0.6);
}

.empty-state i {
  font-size: 1.8em;
  margin-bottom: 8px;
  color: rgba(255, 255, 255, 0.4);
}

.files-summary .recent-files ul {
  list-style: none;
  padding: 0;
  margin: 5px 0 0 0;
}

.files-summary .recent-files li {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 4px 0;
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.8em;
}

.files-summary .recent-files li i {
  color: #3498db;
}

/* Recent Activity */
.recent-activity {
  background: rgba(255, 255, 255, 0.05);
  padding: 20px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.recent-activity h3 {
  color: white;
  margin-bottom: 15px;
  font-size: 1.1em;
}

.empty-activity {
  text-align: center;
  padding: 25px;
  color: rgba(255, 255, 255, 0.6);
}

.empty-activity i {
  font-size: 2.5em;
  margin-bottom: 12px;
  color: rgba(255, 255, 255, 0.4);
}

.activity-timeline {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.activity-item {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 12px;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 8px;
  border-left: 3px solid #3498db;
}

.activity-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #27ae60;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
  font-size: 0.8em;
}

.activity-content p {
  margin: 0 0 4px 0;
  color: white;
  font-size: 0.9em;
}

.activity-category {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.85em;
}

.activity-content small {
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.75em;
}

/* Filters */
.filters-container {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
  flex-wrap: wrap;
  align-items: center;
}

.search-container {
  position: relative;
  flex: 1;
  min-width: 220px;
}

.search-container i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: rgba(3, 3, 3, 0.6);
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 35px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.1);
  color: white;
  backdrop-filter: blur(10px);
}

.search-input::placeholder {
  color: rgba(255, 255, 255, 0.6);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.3);
}

.filter-select {
  padding: 10px 12px;
  border-radius: 6px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.1);
  color: rgb(0, 0, 0);
  backdrop-filter: blur(10px);
  min-width: 160px;
}

.filter-select:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.3);
}

/* Mantener estilos originales para la tabla de documentos */
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
  overflow: visible;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

table {
  width: 100%;
  border-collapse: collapse;
}

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

.category-row:hover {
  background-color: #030c44;
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

.status-badge {
  padding: 5px 10px;
  border-radius: 12px;
  font-size: 0.75em;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.status-badge.complete {
  background: rgba(25, 135, 84, 0.2);
  color: #198754;
  border: 1px solid #198754;
}

.status-badge.pending {
  background: rgba(255, 193, 7, 0.2);
  color: #ffc107;
  border: 1px solid #ffc107;
}

.file-count-badge {
  background-color: var(--dark-grey);
  color: white;
  padding: 3px 10px;
  border-radius: 0px;
  font-size: 0.85em;
  font-weight: bold;
}

.files-row td {
  background-color: #201183;
  padding: 0;
}

.files-list {
  padding: 15px 30px;
}

.no-files {
  text-align: center;
  padding: 20px;
  color: #888;
}

.no-categories-message {
  text-align: center;
  padding: 30px;
  color: rgba(255, 255, 255, 0.6);
  font-style: italic;
}

.files-table {
  background-color: #092c61;
  width: 100%;
  color: white;
}

.files-table th {
  background-color: #092c61;
  font-size: 0.75em;
  padding: 10px 15px;
  color: #a0b3d1;
  border-bottom: 1px solid #0c3d7a;
}

.files-table td {
  padding: 12px 15px;
  font-size: 0.9em;
  vertical-align: middle;
  border-bottom: 1px solid #0c3d7a;
}

.files-table tbody tr:last-child td {
  border-bottom: none;
}

.files-table td .fas {
  margin-right: 10px;
  color: #a0b3d1;
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
  color: #030c44;
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 7px 35px;
  border-radius: 5px;
  text-decoration: none;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  text-align: center;
  border: none;
  font-family: inherit;
  font-size: 0.9em;
  margin-left: 2px;
}

.action-button.download {
  background-color: #0600aa;
  color: rgb(235, 230, 230);
}

.action-button.download:hover {
  background-color: #13c825;
}

.action-button.delete {
  background-color: #0600aa;
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

/* Modal Styles */
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
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
  width: 90%;
  max-width: 450px;
  text-align: center;
  color: white;
}

.logout-modal {
  background: linear-gradient(135deg, #0d6efd 0%, #051f46 100%);
  border: 2px solid #0d6efd;
  box-shadow: 0 8px 32px rgba(13, 110, 253, 0.2);
  animation: modalPop 0.3s;
}

@keyframes modalPop {
  0% { transform: scale(0.8); opacity: 0; }
  100% { transform: scale(1); opacity: 1; }
}

.modal-icon {
  margin-bottom: 15px;
}

.logout-modal h4 {
  color: #fff;
  font-size: 1.5em;
  margin-bottom: 10px;
}

.logout-modal p {
  color: #e0e0e0;
  font-size: 1.1em;
  margin-bottom: 25px;
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
  transition: all 0.3s;
}

.button-secondary {
  background-color: #ccc;
  color: #333;
}

.button-secondary:hover {
  background-color: #bbb;
}

.button-danger {
  background-color: var(--danger-color);
  color: white;
}

.button-danger:hover {
  background-color: #c82333;
}

.button-danger:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.modal-fade-enter-active, .modal-fade-leave-active {
  transition: opacity 0.3s;
}

.modal-fade-enter, .modal-fade-leave-to {
  opacity: 0;
}

/* ===== RESPONSIVE DESIGN ===== */

/* Large tablets y laptops pequeñas */
@media (max-width: 1200px) {
  .hero-section {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 1.5rem;
  }

  .hero-decoration {
    order: -1;
    margin-bottom: 1rem;
  }

  .floating-card {
    position: relative;
    display: inline-flex;
    margin: 0 0.5rem;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }
}

/* Tablets */
@media (max-width: 968px) {
  .dashboard-container {
    padding: 0;
  }

  .hero-section {
    padding: 1.5rem;
    min-height: auto;
  }

  .main-content {
    padding: 1.5rem;
  }

  .tab-content {
    padding: 1.5rem;
  }

  .hero-title {
    font-size: clamp(1.8rem, 6vw, 2.5rem);
    flex-direction: column;
    gap: 0.5rem;
  }

  .quick-actions {
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }

  .stat-card.modern {
    padding: 1.5rem;
  }

  .section-title {
    flex-direction: column;
    gap: 0.5rem;
  }
}

/* Mobile phones */
@media (max-width: 768px) {
  .hero-section {
    padding: 1rem;
  }

  .main-content {
    padding: 1rem;
  }

  .tab-content {
    padding: 1rem;
  }

  .hero-title {
    font-size: 1.8rem;
  }

  .hero-subtitle {
    font-size: 1.2rem;
  }

  .quick-actions {
    gap: 0.75rem;
  }

  .quick-action-btn {
    min-width: 70px;
    padding: 0.75rem;
  }

  .quick-action-btn span {
    font-size: 0.8rem;
  }

  .stats-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .stat-number {
    font-size: 2.5rem;
  }

  .section-header {
    margin-bottom: 2rem;
  }

  .documents-table-container {
    overflow-x: auto;
  }

  .modal-content {
    margin: 1rem;
    width: calc(100% - 2rem);
    max-width: none;
  }
}

/* Small mobile phones */
@media (max-width: 480px) {
  .hero-section {
    padding: 0.75rem;
  }

  .main-content {
    padding: 0.75rem;
  }

  .tab-content {
    padding: 0.75rem;
  }

  .hero-title {
    font-size: 1.5rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .company-tag {
    font-size: 0.8rem;
    padding: 0.4rem 0.8rem;
  }

  .quick-action-btn {
    min-width: 60px;
    padding: 0.6rem;
  }

  .quick-action-btn i {
    font-size: 1rem;
  }

  .quick-action-btn span {
    font-size: 0.75rem;
  }

  .stat-card.modern {
    padding: 1rem;
  }

  .stat-background {
    width: 50px;
    height: 50px;
    top: 0.75rem;
    right: 0.75rem;
  }

  .stat-background i {
    font-size: 1.2rem;
  }

  .stat-number {
    font-size: 2rem;
  }

  .section-title {
    font-size: 1.3rem;
  }

  .section-subtitle {
    font-size: 0.9rem;
  }
}

/* Landscape mode para móviles */
@media (max-height: 500px) and (orientation: landscape) {
  .hero-section {
    min-height: auto;
    padding: 1rem;
  }

  .hero-title {
    font-size: 1.5rem;
  }

  .floating-card {
    display: none;
  }
}

/* Estados de alta accesibilidad */
@media (prefers-reduced-motion: reduce) {
  .floating-card {
    animation: none;
  }

  .stat-card.modern:hover {
    transform: none;
  }

  .quick-action-btn:hover {
    transform: none;
  }

  * {
    transition-duration: 0.01ms !important;
  }
}

/* Modo oscuro preferido del usuario */
@media (prefers-color-scheme: dark) {
  :root {
    --bg-glass: rgba(255, 255, 255, 0.05);
    --border-light: rgba(255, 255, 255, 0.15);
  }
}
</style>