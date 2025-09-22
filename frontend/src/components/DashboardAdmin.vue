<template>
  <div class="admin-dashboard">
    <!-- Hero Section -->
    <div class="hero-section">
      <div class="hero-content">

        <div class="admin-welcome">
          <h1 class="hero-title">
            <i class="fas fa-shield-alt"></i>
            Panel de Administración
          </h1>
          <p class="hero-subtitle">
            {{ profile.full_name || profile.username }}
            <span class="role-badge">{{ profile.role_detail }}</span>
          </p>

          <div class="admin-actions">
            <button @click="refreshData" class="action-btn refresh" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              <span>Actualizar</span>
            </button>

            <button @click="isLogout = true" class="action-btn logout">
              <i class="fas fa-sign-out-alt"></i>
              <span>Salir</span>
            </button>

          </div>
        </div>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="navigation-tabs">
      <button
        class="nav-tab"
        :class="{ active: activeTab === 'dashboard' }"
        @click="activeTab = 'dashboard'"
      >
        <i class="fas fa-chart-line"></i>
        <span>Dashboard</span>
      </button>
      <button
        class="nav-tab"
        :class="{ active: activeTab === 'management' }"
        @click="activeTab = 'management'"
      >
        <i class="fas fa-users-cog"></i>
        <span>Gestión de Usuarios</span>
      </button>
      <button
        class="nav-tab"
        :class="{ active: activeTab === 'documents' }"
        @click="activeTab = 'documents'"
      >
        <i class="fas fa-folder-open"></i>
        <span>Documentos</span>
      </button>
      <button
        class="nav-tab"
        :class="{ active: activeTab === 'assignments' }"
        @click="activeTab = 'assignments'"
      >
        <i class="fas fa-tasks"></i>
        <span>Asignaciones</span>
      </button>
    </div>

    <!-- Main Content -->
    <div class="main-content">

      <!-- Dashboard Tab -->
      <div v-if="activeTab === 'dashboard'" class="tab-content dashboard-tab">
        <div class="section-header">
          <h2 class="section-title">
            <i class="fas fa-chart-bar"></i>
            Estadísticas del Sistema
          </h2>
          <p class="section-subtitle">Resumen general de la plataforma</p>
        </div>

        <div class="stats-grid">
          <div class="stat-card modern" data-type="clients">
            <div class="stat-background">
              <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ adminStats.total_clientes || 0 }}</div>
              <div class="stat-label">Clientes Totales</div>
              <div class="stat-detail">
                <span class="with-docs">{{ adminStats.clientes_con_documentos || 0 }}</span> con documentos
              </div>
            </div>
          </div>

          <div class="stat-card modern" data-type="documents">
            <div class="stat-background">
              <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ adminStats.total_archivos || 0 }}</div>
              <div class="stat-label">Archivos Totales</div>
              <div class="stat-detail">
                <span class="recent">{{ adminStats.archivos_ultimos_6_meses || 0 }}</span> últimos 6 meses
              </div>
            </div>
          </div>

          <div class="stat-card modern" data-type="categories">
            <div class="stat-background">
              <i class="fas fa-folder"></i>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ adminStats.total_categorias || 0 }}</div>
              <div class="stat-label">Categorías Activas</div>
              <div class="stat-detail">
                <span class="types">{{ adminStats.total_tipos_documento || 0 }}</span> tipos diferentes
              </div>
            </div>
          </div>

          <div class="stat-card modern" data-type="admins">
            <div class="stat-background">
              <i class="fas fa-user-shield"></i>
            </div>
            <div class="stat-content">
              <div class="stat-number">{{ adminStats.total_administradores || 0 }}</div>
              <div class="stat-label">Administradores</div>
              <div class="stat-detail">
                <span class="active">Activos</span> en el sistema
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions-section">
          <h3 class="section-subtitle">Acciones Rápidas</h3>
          <div class="quick-actions-grid">
            <button @click="activeTab = 'management'" class="quick-action-card">
              <i class="fas fa-user-plus"></i>
              <h4>Crear Usuario</h4>
              <p>Agregar nuevos clientes o administradores</p>
            </button>
            <button @click="activeTab = 'documents'" class="quick-action-card">
              <i class="fas fa-file-plus"></i>
              <h4>Nuevo Documento</h4>
              <p>Crear tipo de documento</p>
            </button>
            <button @click="activeTab = 'assignments'" class="quick-action-card">
              <i class="fas fa-link"></i>
              <h4>Asignar Documentos</h4>
              <p>Vincular documentos a clientes</p>
            </button>
            <button @click="refreshData" class="quick-action-card">
              <i class="fas fa-sync-alt"></i>
              <h4>Actualizar Datos</h4>
              <p>Refrescar estadísticas</p>
            </button>
          </div>
        </div>
      </div>

      <!-- Management Tab -->
      <div v-if="activeTab === 'management'" class="tab-content management-tab">
        <div class="section-header">
          <h2 class="section-title">
            <i class="fas fa-users-cog"></i>
            Gestión de Usuarios
          </h2>
          <p class="section-subtitle">Crear y administrar clientes y administradores</p>
        </div>

        <div class="management-sections">
          <!-- Existing Users Lists -->
          <div class="existing-users-section">
            <div class="users-tabs">
              <button
                class="users-tab"
                :class="{ active: usersSubTab === 'clients' }"
                @click="usersSubTab = 'clients'"
              >
                <i class="fas fa-building"></i>
                Clientes ({{ clients.length }})
              </button>
              <button
                class="users-tab"
                :class="{ active: usersSubTab === 'admins' }"
                @click="usersSubTab = 'admins'"
              >
                <i class="fas fa-user-shield"></i>
                Administradores ({{ admins.length }})
              </button>
            </div>

            <!-- Existing Clients -->
            <div v-if="usersSubTab === 'clients'" class="users-list">
              <h3 class="list-title">
                <i class="fas fa-users"></i>
                Clientes Registrados
              </h3>
              <div class="users-table-container">
                <table class="users-table">
                  <thead>
                    <tr>
                      <th>Razón Social</th>
                      <th>RUT</th>
                      <th>Usuario</th>
                      <th>Nombre</th>
                      <th>Email</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="cliente in clients" :key="cliente.id">
                      <td>{{ cliente.razon_social }}</td>
                      <td>{{ cliente.rut_empresa }}</td>
                      <td>{{ cliente.username || 'N/A' }}</td>
                      <td>{{ cliente.full_name || 'N/A' }}</td>
                      <td>{{ cliente.email || 'N/A' }}</td>
                      <td class="actions-column">
                        <button @click="editarCliente(cliente)" class="action-btn edit-btn">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button @click="eliminarCliente(cliente)" class="action-btn delete-btn">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                    <tr v-if="clients.length === 0">
                      <td colspan="6" class="no-data">No hay clientes registrados</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Existing Admins -->
            <div v-if="usersSubTab === 'admins'" class="users-list">
              <h3 class="list-title">
                <i class="fas fa-user-shield"></i>
                Administradores del Sistema
              </h3>
              <div class="users-table-container">
                <table class="users-table">
                  <thead>
                    <tr>
                      <th>Usuario</th>
                      <th>Nombre</th>
                      <th>Email</th>
                      <th>Rol</th>
                      <th>Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="admin in admins" :key="admin.id">
                      <td>{{ admin.username || 'N/A' }}</td>
                      <td>{{ admin.full_name || 'N/A' }}</td>
                      <td>{{ admin.email || 'N/A' }}</td>
                      <td>
                        <span class="role-badge">{{ admin.rol }}</span>
                      </td>
                      <td class="actions-column">
                        <button @click="editarAdmin(admin)" class="action-btn edit-btn">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button @click="eliminarAdmin(admin)" class="action-btn delete-btn">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                    <tr v-if="admins.length === 0">
                      <td colspan="5" class="no-data">No hay administradores registrados</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Create Client Section -->
          <div class="create-section">
            <h3 class="create-title">
              <i class="fas fa-building"></i>
              Crear Cliente
            </h3>
            <form @submit.prevent="createClient" class="create-form">
              <div class="form-row">
                <div class="form-group">
                  <label>Razón Social</label>
                  <input
                    v-model="newClient.razon_social"
                    type="text"
                    placeholder="Nombre de la empresa"
                    required
                  />
                </div>
                <div class="form-group">
                  <label>RUT Empresa</label>
                  <input
                    v-model="newClient.rut_empresa"
                    type="text"
                    placeholder="12345678-9"
                    required
                  />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Usuario</label>
                  <input
                    v-model="newClient.username"
                    type="text"
                    placeholder="usuario_empresa"
                    required
                  />
                </div>
                <div class="form-group">
                  <label>Contraseña</label>
                  <input
                    v-model="newClient.password"
                    type="password"
                    placeholder="Mínimo 8 caracteres"
                    required
                  />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Email (Opcional)</label>
                  <input
                    v-model="newClient.email"
                    type="email"
                    placeholder="contacto@empresa.com"
                  />
                </div>
                <div class="form-group">
                  <label>Nombre Contacto (Opcional)</label>
                  <input
                    v-model="newClient.first_name"
                    type="text"
                    placeholder="Juan"
                  />
                </div>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn-primary" :disabled="isCreatingClient">
                  <i class="fas fa-plus" v-if="!isCreatingClient"></i>
                  <i class="fas fa-spinner fa-spin" v-else></i>
                  {{ isCreatingClient ? 'Creando...' : 'Crear Cliente' }}
                </button>
              </div>
            </form>
          </div>

          <!-- Create Admin Section -->
          <div class="create-section">
            <h3 class="create-title">
              <i class="fas fa-user-shield"></i>
              Crear Administrador
            </h3>
            <form @submit.prevent="createAdmin" class="create-form">
              <div class="form-row">
                <div class="form-group">
                  <label>Usuario</label>
                  <input
                    v-model="newAdmin.username"
                    type="text"
                    placeholder="admin_usuario"
                    required
                  />
                </div>
                <div class="form-group">
                  <label>Contraseña</label>
                  <input
                    v-model="newAdmin.password"
                    type="password"
                    placeholder="Mínimo 8 caracteres"
                    required
                  />
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label>Rol</label>
                  <select v-model="newAdmin.rol" required>
                    <option value="">Seleccionar rol</option>
                    <option value="Contador">Contador</option>
                    <option value="TI">TI</option>
                    <option value="Tributaria">Tributaria</option>
                    <option value="Supervisor">Supervisor</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Email (Opcional)</label>
                  <input
                    v-model="newAdmin.email"
                    type="email"
                    placeholder="admin@araybustamante.com"
                  />
                </div>
              </div>
              <div class="form-actions">
                <button type="submit" class="btn-secondary" :disabled="isCreatingAdmin">
                  <i class="fas fa-plus" v-if="!isCreatingAdmin"></i>
                  <i class="fas fa-spinner fa-spin" v-else></i>
                  {{ isCreatingAdmin ? 'Creando...' : 'Crear Administrador' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Documents Tab -->
      <div v-if="activeTab === 'documents'" class="tab-content documents-tab">
        <div class="section-header">
          <h2 class="section-title">
            <i class="fas fa-folder-open"></i>
            Gestión de Documentos
          </h2>
          <p class="section-subtitle">Administrar documentos de todos los clientes</p>
        </div>

        <!-- Documents Management Tabs -->
        <div class="sub-navigation">
          <button
            class="sub-nav-btn"
            :class="{ active: documentsSubTab === 'view' }"
            @click="documentsSubTab = 'view'"
          >
            <i class="fas fa-eye"></i>
            Ver Documentos
          </button>
          <button
            class="sub-nav-btn"
            :class="{ active: documentsSubTab === 'upload' }"
            @click="documentsSubTab = 'upload'"
          >
            <i class="fas fa-upload"></i>
            Subir Documentos
          </button>
          <button
            class="sub-nav-btn"
            :class="{ active: documentsSubTab === 'types' }"
            @click="documentsSubTab = 'types'"
          >
            <i class="fas fa-tags"></i>
            Tipos de Documento
          </button>
        </div>

        <!-- View Documents Sub-tab -->
        <div v-if="documentsSubTab === 'view'" class="documents-view-section">
          <!-- Filters -->
          <div class="filters-section">
            <div class="filter-group">
              <label>Filtrar por Cliente:</label>
              <select v-model="documentFilters.cliente">
                <option value="">Todos los clientes</option>
                <option v-for="client in clients" :key="client.id" :value="client.id">
                  {{ client.razon_social }}
                </option>
              </select>
            </div>
            <div class="filter-group">
              <label>Filtrar por Tipo:</label>
              <select v-model="documentFilters.tipo">
                <option value="">Todos los tipos</option>
                <option v-for="type in documentTypes" :key="type.id" :value="type.id">
                  {{ type.nombre }}
                </option>
              </select>
            </div>
            <div class="filter-group">
              <label>Buscar:</label>
              <input
                v-model="documentFilters.search"
                type="text"
                placeholder="Nombre de archivo..."
              />
            </div>
            <button @click="refreshDocuments" class="refresh-btn">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loadingDocuments }"></i>
              Actualizar
            </button>
          </div>

          <!-- Documents Table -->
          <div class="documents-table-container">
            <div v-if="loadingDocuments" class="loading-state">
              <i class="fas fa-spinner fa-spin"></i>
              <p>Cargando documentos...</p>
            </div>
            <div v-else-if="filteredDocuments.length === 0" class="empty-state">
              <i class="fas fa-folder-open"></i>
              <p>No se encontraron documentos</p>
            </div>
            <table v-else class="documents-table">
              <thead>
                <tr>
                  <th>Cliente</th>
                  <th>Tipo de Documento</th>
                  <th>Archivo</th>
                  <th>Subido por</th>
                  <th>Fecha</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="doc in filteredDocuments" :key="doc.id" class="document-row">
                  <td>
                    <div class="client-info">
                      <strong>{{ doc.cliente.razon_social }}</strong>
                      <small>{{ doc.cliente.rut_empresa }}</small>
                    </div>
                  </td>
                  <td>
                    <span class="document-type">{{ doc.tipo_documento.nombre }}</span>
                  </td>
                  <td>
                    <div class="file-info">
                      <i class="fas fa-file-alt"></i>
                      <span>{{ doc.nombre_archivo }}</span>
                      <small>#{{ doc.correlativo }}</small>
                    </div>
                  </td>
                  <td>
                    <span class="uploaded-by" :class="doc.subido_por === 'cliente' ? 'client' : 'admin'">
                      {{ doc.subido_por === 'cliente' ? 'Cliente' : 'Ara y Bustamante' }}
                    </span>
                  </td>
                  <td>
                    <span class="date">{{ formatDate(doc.fecha_subida) }}</span>
                  </td>
                  <td>
                    <div class="document-actions">
                      <button @click="downloadDocument(doc)" class="action-btn download" title="Descargar">
                        <i class="fas fa-download"></i>
                      </button>
                      <button @click="deleteDocument(doc)" class="action-btn delete" title="Eliminar">
                        <i class="fas fa-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Upload Documents Sub-tab -->
        <div v-if="documentsSubTab === 'upload'" class="upload-section">
          <h3 class="create-title">
            <i class="fas fa-cloud-upload-alt"></i>
            Subir Documento a Cliente
          </h3>
          <form @submit.prevent="uploadDocumentToClient" class="upload-form">
            <div class="form-row">
              <div class="form-group">
                <label>Cliente</label>
                <select v-model="uploadForm.cliente_id" required @change="loadClientCategories">
                  <option value="">Seleccionar cliente</option>
                  <option v-for="client in clients" :key="client.id" :value="client.id">
                    {{ client.razon_social }} ({{ client.rut_empresa }})
                  </option>
                </select>
              </div>
              <div class="form-group">
                <label>Categoría de Documento</label>
                <select v-model="uploadForm.categoria_id" required :disabled="!uploadForm.cliente_id">
                  <option value="">Seleccionar categoría</option>
                  <option v-for="categoria in clientCategories" :key="categoria.id" :value="categoria.id">
                    {{ categoria.tipo_documento.nombre }}
                  </option>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Archivo</label>
              <div class="file-upload-area" @drop.prevent="handleFileDrop" @dragover.prevent>
                <input
                  ref="fileInput"
                  type="file"
                  @change="handleFileSelect"
                  accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xlsx,.xls"
                  style="display: none"
                />
                <div v-if="!uploadForm.file" class="upload-placeholder" @click="fileInput.click()">
                  <i class="fas fa-cloud-upload-alt"></i>
                  <p>Arrastra un archivo aquí o haz clic para seleccionar</p>
                  <small>PDF, DOC, IMG, XLS (máx. 10MB)</small>
                </div>
                <div v-else class="file-selected">
                  <i class="fas fa-file-check"></i>
                  <span>{{ uploadForm.file.name }}</span>
                  <button type="button" @click="clearFile" class="clear-file">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="form-actions">
              <button type="submit" class="btn-primary" :disabled="isUploadingDoc">
                <i class="fas fa-upload" v-if="!isUploadingDoc"></i>
                <i class="fas fa-spinner fa-spin" v-else></i>
                {{ isUploadingDoc ? 'Subiendo...' : 'Subir Documento' }}
              </button>
            </div>
          </form>
        </div>

        <!-- Document Types Sub-tab -->
        <div v-if="documentsSubTab === 'types'" class="document-types-section">
          <!-- Create Document Type -->
          <div class="create-section">
            <h3 class="create-title">
              <i class="fas fa-file-plus"></i>
              Crear Tipo de Documento
            </h3>
            <form @submit.prevent="createDocumentType" class="create-form">
              <div class="form-group">
                <label>Nombre del Tipo de Documento</label>
                <input
                  v-model="newDocumentType.nombre"
                  type="text"
                  placeholder="Ej: Certificado de Renta, Balance General, etc."
                  required
                />
              </div>
              <div class="form-actions">
                <button type="submit" class="btn-success" :disabled="isCreatingDocType">
                  <i class="fas fa-plus" v-if="!isCreatingDocType"></i>
                  <i class="fas fa-spinner fa-spin" v-else></i>
                  {{ isCreatingDocType ? 'Creando...' : 'Crear Tipo de Documento' }}
                </button>
              </div>
            </form>
          </div>

          <!-- List Document Types -->
          <div class="list-section">
            <h3 class="list-title">
              <i class="fas fa-list"></i>
              Tipos de Documentos Existentes
            </h3>
            <div class="document-types-grid">
              <div
                v-for="docType in documentTypes"
                :key="docType.id"
                class="document-type-card"
              >
                <i class="fas fa-file-alt"></i>
                <h4>{{ docType.nombre }}</h4>
                <div class="card-actions">
                  <button @click="editDocumentType(docType)" class="btn-edit">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button @click="deleteDocumentType(docType.id)" class="btn-delete">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Assignments Tab -->
      <div v-if="activeTab === 'assignments'" class="tab-content assignments-tab">
        <div class="section-header">
          <h2 class="section-title">
            <i class="fas fa-tasks"></i>
            Asignación de Documentos
          </h2>
          <p class="section-subtitle">Vincular tipos de documentos a clientes</p>
        </div>

        <div class="assignment-section">
          <form @submit.prevent="assignDocuments" class="assignment-form">
            <div class="form-row">
              <div class="form-group">
                <label>Cliente</label>
                <select v-model="assignment.cliente_id" required>
                  <option value="">Seleccionar cliente</option>
                  <option
                    v-for="client in clients"
                    :key="client.id"
                    :value="client.id"
                  >
                    {{ client.razon_social }} ({{ client.rut_empresa }})
                  </option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Tipos de Documentos</label>
              <div class="document-types-selector">
                <div
                  v-for="docType in documentTypes"
                  :key="docType.id"
                  class="document-type-option"
                    >
                  <input
                    :id="`doctype-${docType.id}`"
                    v-model="assignment.tipo_documento_ids"
                    :value="docType.id"
                    type="checkbox"
                  />
                  <label :for="`doctype-${docType.id}`" class="checkbox-label">
                    <i class="fas fa-file-alt"></i>
                    {{ docType.nombre }}
                  </label>
                </div>
              </div>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary" :disabled="isAssigning">
                <i class="fas fa-link" v-if="!isAssigning"></i>
                <i class="fas fa-spinner fa-spin" v-else></i>
                {{ isAssigning ? 'Asignando...' : 'Asignar Documentos' }}
              </button>
            </div>
          </form>
        </div>
      </div>

    </div>
    <!-- Fin Main Content -->

    <!-- Logout Modal -->
    <transition name="modal-fade">
      <div v-if="isLogout" class="modal-overlay" @click.self="isLogout = false">
        <div class="modal-content logout-modal">
          <div class="modal-icon">
            <i class="fas fa-sign-out-alt fa-3x"></i>
          </div>
          <h4>¿Cerrar sesión?</h4>
          <p>¿Está seguro de que desea salir del panel de administración?</p>
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
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const props = defineProps({
  profile: Object
})

// Reactive data
const router = useRouter()
const loading = ref(false)
const activeTab = ref('dashboard')
const isLogout = ref(false)

// Admin Stats
const adminStats = ref({
  total_clientes: 0,
  total_administradores: 0,
  total_tipos_documento: 0,
  total_categorias: 0,
  total_archivos: 0,
  clientes_con_documentos: 0,
  clientes_sin_documentos: 0,
  archivos_ultimos_6_meses: 0
})

// Form data
const newClient = ref({
  razon_social: '',
  rut_empresa: '',
  username: '',
  password: '',
  email: '',
  first_name: '',
  last_name: ''
})

const newAdmin = ref({
  username: '',
  password: '',
  rol: '',
  email: '',
  first_name: '',
  last_name: ''
})

const newDocumentType = ref({
  nombre: ''
})

const assignment = ref({
  cliente_id: '',
  tipo_documento_ids: []
})

// Lists data
const clients = ref([])
const admins = ref([])
const documentTypes = ref([])

// Sub tabs
const usersSubTab = ref('clients')

// Loading states
const isCreatingClient = ref(false)
const isCreatingAdmin = ref(false)
const isCreatingDocType = ref(false)
const isAssigning = ref(false)
const loadingDocuments = ref(false)
const isUploadingDoc = ref(false)

// Documents management
const documentsSubTab = ref('view')
const allDocuments = ref([])
const clientCategories = ref([])
const documentFilters = ref({
  cliente: '',
  tipo: '',
  search: ''
})

const uploadForm = ref({
  cliente_id: '',
  categoria_id: '',
  file: null
})

// API Client
const apiClient = axios.create({
  baseURL: import.meta.env.VITE_API_URL + '/api'
})

// Add token to requests
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('accessToken')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Computed properties
const filteredDocuments = computed(() => {
  let filtered = allDocuments.value

  if (documentFilters.value.cliente) {
    filtered = filtered.filter(doc => doc.cliente.id == documentFilters.value.cliente)
  }

  if (documentFilters.value.tipo) {
    filtered = filtered.filter(doc => doc.tipo_documento.id == documentFilters.value.tipo)
  }

  if (documentFilters.value.search) {
    const search = documentFilters.value.search.toLowerCase()
    filtered = filtered.filter(doc =>
      doc.nombre_archivo.toLowerCase().includes(search) ||
      doc.cliente.razon_social.toLowerCase().includes(search) ||
      doc.tipo_documento.nombre.toLowerCase().includes(search)
    )
  }

  return filtered
})

// Methods
async function fetchAdminStats() {
  try {
    const response = await apiClient.get('/admin-stats/')
    adminStats.value = response.data
  } catch (error) {
    console.error('Error fetching admin stats:', error)
  }
}

async function fetchClients() {
  try {
    const response = await apiClient.get('/clientes/')
    clients.value = response.data
  } catch (error) {
    console.error('Error fetching clients:', error)
  }
}

async function fetchAdmins() {
  try {
    const response = await apiClient.get('/administradores/')
    admins.value = response.data
  } catch (error) {
    console.error('Error fetching admins:', error)
  }
}

async function fetchDocumentTypes() {
  try {
    const response = await apiClient.get('/tipos-documento/')
    documentTypes.value = response.data
  } catch (error) {
    console.error('Error fetching document types:', error)
  }
}

async function fetchAllDocuments() {
  try {
    const response = await apiClient.get('/admin-documents/')
    allDocuments.value = response.data
  } catch (error) {
    console.error('Error fetching documents:', error)
  }
}

async function refreshDocuments() {
  loadingDocuments.value = true
  try {
    await fetchAllDocuments()
  } finally {
    loadingDocuments.value = false
  }
}

async function loadClientCategories() {
  if (!uploadForm.value.cliente_id) {
    clientCategories.value = []
    return
  }

  try {
    const response = await apiClient.get('/categorias/', {
      params: { cliente: uploadForm.value.cliente_id }
    })
    clientCategories.value = response.data
  } catch (error) {
    console.error('Error loading client categories:', error)
    clientCategories.value = []
  }
}

function handleFileSelect(event) {
  const file = event.target.files[0]
  if (file && file.size <= 10 * 1024 * 1024) { // 10MB limit
    uploadForm.value.file = file
  } else {
    alert('El archivo debe ser menor a 10MB')
  }
}

function handleFileDrop(event) {
  const file = event.dataTransfer.files[0]
  if (file && file.size <= 10 * 1024 * 1024) {
    uploadForm.value.file = file
  } else {
    alert('El archivo debe ser menor a 10MB')
  }
}

const fileInput = ref(null)

function clearFile() {
  uploadForm.value.file = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

async function uploadDocumentToClient() {
  if (!uploadForm.value.categoria_id || !uploadForm.value.file) {
    alert('Debe seleccionar una categoría y un archivo')
    return
  }

  isUploadingDoc.value = true
  try {
    const formData = new FormData()
    formData.append('categoria_id', uploadForm.value.categoria_id)
    formData.append('file', uploadForm.value.file)

    await apiClient.post('/admin-upload/', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    // Reset form
    uploadForm.value = {
      cliente_id: '',
      categoria_id: '',
      file: null
    }
    clientCategories.value = []

    alert('Documento subido exitosamente')
    await refreshDocuments()
    await fetchAdminStats()

  } catch (error) {
    console.error('Error uploading document:', error)
    alert('Error al subir documento: ' + (error.response?.data?.error || 'Error desconocido'))
  } finally {
    isUploadingDoc.value = false
  }
}

function downloadDocument(doc) {
  if (doc.url_archivo) {
    const link = document.createElement('a')
    link.href = doc.url_archivo
    link.download = doc.nombre_archivo
    link.target = '_blank'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } else {
    alert('URL del archivo no disponible')
  }
}

async function deleteDocument(doc) {
  if (confirm(`¿Está seguro de eliminar el archivo "${doc.nombre_archivo}"?`)) {
    try {
      await apiClient.delete(`/archivos/${doc.id}/`)
      alert('Documento eliminado exitosamente')
      await refreshDocuments()
      await fetchAdminStats()
    } catch (error) {
      console.error('Error deleting document:', error)
      alert('Error al eliminar documento')
    }
  }
}

function formatDate(dateString) {
  const date = new Date(dateString)
  return date.toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

async function refreshData() {
  loading.value = true
  try {
    await Promise.all([
      fetchAdminStats(),
      fetchClients(),
      fetchAdmins(),
      fetchDocumentTypes(),
      fetchAllDocuments()
    ])
  } finally {
    loading.value = false
  }
}

async function createClient() {
  isCreatingClient.value = true
  try {
    await apiClient.post('/clientes/', newClient.value)

    // Reset form
    newClient.value = {
      razon_social: '',
      rut_empresa: '',
      username: '',
      password: '',
      email: '',
      first_name: '',
      last_name: ''
    }

    alert('Cliente creado exitosamente')
    await fetchClients()
    await fetchAdmins()
    await fetchAdminStats()

  } catch (error) {
    console.error('Error creating client:', error)

    let errorMessage = 'Error al crear cliente: '

    if (error.response?.data) {
      const data = error.response.data
      if (data.username) {
        errorMessage += `Usuario: ${data.username[0]}`
      } else if (data.rut_empresa) {
        errorMessage += `RUT: ${data.rut_empresa[0]}`
      } else if (data.detail) {
        errorMessage += data.detail
      } else if (data.non_field_errors) {
        errorMessage += data.non_field_errors[0]
      } else {
        errorMessage += 'Verifique los datos ingresados'
      }
    } else if (error.message) {
      errorMessage += error.message
    } else {
      errorMessage += 'Error de conexión'
    }

    alert(errorMessage)
  } finally {
    isCreatingClient.value = false
  }
}

async function createAdmin() {
  isCreatingAdmin.value = true
  try {
    await apiClient.post('/administradores/', newAdmin.value)

    // Reset form
    newAdmin.value = {
      username: '',
      password: '',
      rol: '',
      email: '',
      first_name: '',
      last_name: ''
    }

    alert('Administrador creado exitosamente')
    await fetchAdmins()
    await fetchAdminStats()

  } catch (error) {
    console.error('Error creating admin:', error)

    let errorMessage = 'Error al crear administrador: '

    if (error.response?.data) {
      const data = error.response.data
      if (data.username) {
        errorMessage += `Usuario: ${data.username[0]}`
      } else if (data.rol) {
        errorMessage += `Rol: ${data.rol[0]}`
      } else if (data.detail) {
        errorMessage += data.detail
      } else if (data.non_field_errors) {
        errorMessage += data.non_field_errors[0]
      } else {
        errorMessage += 'Verifique los datos ingresados'
      }
    } else if (error.message) {
      errorMessage += error.message
    } else {
      errorMessage += 'Error de conexión'
    }

    alert(errorMessage)
  } finally {
    isCreatingAdmin.value = false
  }
}

async function createDocumentType() {
  isCreatingDocType.value = true
  try {
    await apiClient.post('/tipos-documento/', newDocumentType.value)

    // Reset form
    newDocumentType.value = { nombre: '' }

    alert('Tipo de documento creado exitosamente')
    await fetchDocumentTypes()
    await fetchAdminStats()

  } catch (error) {
    console.error('Error creating document type:', error)
    alert('Error al crear tipo de documento: ' + (error.response?.data?.detail || 'Error desconocido'))
  } finally {
    isCreatingDocType.value = false
  }
}

async function assignDocuments() {
  if (!assignment.value.cliente_id || assignment.value.tipo_documento_ids.length === 0) {
    alert('Debe seleccionar un cliente y al menos un tipo de documento')
    return
  }

  isAssigning.value = true
  try {
    const response = await apiClient.post('/asignar-documentos/', assignment.value)

    // Reset form
    assignment.value = {
      cliente_id: '',
      tipo_documento_ids: []
    }

    alert(`Documentos asignados exitosamente: ${response.data.message}`)
    await fetchAdminStats()

  } catch (error) {
    console.error('Error assigning documents:', error)
    alert('Error al asignar documentos: ' + (error.response?.data?.error || 'Error desconocido'))
  } finally {
    isAssigning.value = false
  }
}

function editDocumentType(docType) {
  // TODO: Implement edit functionality
  console.log('Edit document type:', docType)
  alert('Funcionalidad de edición próximamente')
}

async function deleteDocumentType(id) {
  if (confirm('¿Está seguro de eliminar este tipo de documento?')) {
    try {
      await apiClient.delete(`/tipos-documento/${id}/`)
      alert('Tipo de documento eliminado exitosamente')
      await fetchDocumentTypes()
      await fetchAdminStats()
    } catch (error) {
      console.error('Error deleting document type:', error)
      alert('Error al eliminar tipo de documento')
    }
  }
}

function editarCliente(cliente) {
  // TODO: Implement edit functionality
  console.log('Edit client:', cliente)
  alert('Funcionalidad de edición próximamente')
}

async function eliminarCliente(cliente) {
  if (confirm(`¿Está seguro de eliminar el cliente "${cliente.razon_social}"?`)) {
    try {
      await apiClient.delete(`/clientes/${cliente.id}/`)
      alert('Cliente eliminado exitosamente')
      await fetchClients()
      await fetchAdminStats()
    } catch (error) {
      console.error('Error deleting client:', error)
      alert('Error al eliminar cliente')
    }
  }
}

function editarAdmin(admin) {
  // TODO: Implement edit functionality
  console.log('Edit admin:', admin)
  alert('Funcionalidad de edición próximamente')
}

async function eliminarAdmin(admin) {
  if (confirm(`¿Está seguro de eliminar el administrador "${admin.username}"?`)) {
    try {
      await apiClient.delete(`/administradores/${admin.id}/`)
      alert('Administrador eliminado exitosamente')
      await fetchAdmins()
      await fetchAdminStats()
    } catch (error) {
      console.error('Error deleting admin:', error)
      alert('Error al eliminar administrador')
    }
  }
}

function confirmLogout() {
  localStorage.removeItem('accessToken')
  localStorage.removeItem('refreshToken')
  router.push('/')
  isLogout.value = false
}

// Initialize data
onMounted(() => {
  refreshData()
})
</script>

<style scoped>
/* Variables CSS */
:root {
  --primary-blue: #021144;
  --secondary-blue: #061656;
  --primary-green: #2bd17b;
  --secondary-green: #36d85c;
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

/* Layout principal */
.admin-dashboard {
  min-height: 100vh;
  background: linear-gradient(135deg, var(--primary-green) 0%, var(--primary-blue) 100%);
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  overflow-x: hidden;
}

/* Hero Section */
.hero-section {
  padding: 2rem;
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 2rem;
  align-items: center;
  min-height: 25vh;
  background: linear-gradient(135deg, rgba(43, 209, 123, 0.2) 0%, rgba(6, 22, 86, 0.3) 100%);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border-light);
}

.hero-content {
  z-index: 2;
}

.admin-welcome {
  color: var(--text-light);
}

.hero-title {
  font-size: clamp(2rem, 5vw, 2.8rem);
  font-weight: 700;
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
  font-size: clamp(1.1rem, 3vw, 1.4rem);
  color: var(--text-secondary);
  margin: 0 0 1.5rem 0;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.role-badge {
  background: var(--bg-glass);
  backdrop-filter: blur(10px);
  padding: 0.3rem 0.8rem;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
  font-size: 0.85rem;
  font-weight: 500;
}

.admin-actions {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.action-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: var(--bg-glass);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  font-weight: 500;
}

.action-btn:hover {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
  box-shadow: 0 8px 25px var(--shadow-medium);
}

.action-btn.refresh {
  background: var(--primary-green);
  color: white;
  border-color: var(--primary-green);
}

.action-btn.logout {
  background: rgba(231, 76, 60, 0.2);
  border-color: #e74c3c;
}

.action-btn.logout:hover {
  background: #e74c3c;
  color: white;
}

/* Hero Decoration */
.hero-decoration {
  display: flex;
  gap: 1rem;
  flex-direction: column;
}

.floating-stat {
  background: var(--bg-glass);
  backdrop-filter: blur(10px);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  color: var(--text-light);
  animation: float 6s ease-in-out infinite;
}

.floating-stat.delay-1 {
  animation-delay: 2s;
}

.floating-stat.delay-2 {
  animation-delay: 4s;
}

.floating-stat i {
  font-size: 1.5rem;
  color: var(--primary-green);
}

.floating-stat span {
  font-size: 1.2rem;
  font-weight: 600;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

/* Navigation Tabs */
.navigation-tabs {
  display: flex;
  gap: 0.5rem;
  padding: 1.5rem 2rem;
  background: var(--bg-glass);
  backdrop-filter: blur(15px);
  border-bottom: 1px solid var(--border-light);
  overflow-x: auto;
}

.nav-tab {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.25rem;
  background: transparent;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  white-space: nowrap;
  font-weight: 500;
}

.nav-tab:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-tab.active {
  background: var(--primary-green);
  color: white;
  border-color: var(--primary-green);
}

.nav-tab i {
  font-size: 1rem;
}

/* Main Content */
.main-content {
  padding: 2rem;
}

.tab-content {
  background: var(--bg-glass);
  backdrop-filter: blur(15px);
  border-radius: var(--radius-lg);
  padding: 2rem;
  border: 1px solid var(--border-light);
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
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 3rem;
}

.stat-card.modern {
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
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
  border-color: var(--primary-green);
}

.stat-card.modern:hover::before {
  opacity: 1;
}

.stat-background {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 60px;
  height: 60px;
  background: var(--bg-glass);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0.3;
  transition: var(--transition);
}

.stat-card.modern:hover .stat-background {
  opacity: 0.6;
  transform: scale(1.1);
}

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
  background: linear-gradient(135deg, var(--primary-green), var(--primary-blue));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.stat-label {
  font-size: 1rem;
  color: var(--text-secondary);
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.stat-detail {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.stat-detail span {
  color: var(--primary-green);
  font-weight: 600;
}

/* Quick Actions */
.quick-actions-section {
  background: var(--bg-glass);
  border-radius: var(--radius-lg);
  padding: 2rem;
  border: 1px solid var(--border-light);
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1.5rem;
}

.quick-action-card {
  background: var(--bg-glass);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  text-align: center;
  cursor: pointer;
  transition: var(--transition);
  color: var(--text-light);
}

.quick-action-card:hover {
  transform: translateY(-5px);
  background: rgba(255, 255, 255, 0.15);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.quick-action-card i {
  font-size: 2rem;
  color: var(--primary-green);
  margin-bottom: 1rem;
}

.quick-action-card h4 {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  color: var(--text-light);
}

.quick-action-card p {
  margin: 0;
  font-size: 0.9rem;
  color: var(--text-muted);
}

/* Forms */
.management-sections,
.documents-section {
  display: grid;
  gap: 2rem;
}

.create-section {
  background: var(--bg-glass);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-lg);
  padding: 2rem;
}

.create-title {
  color: var(--text-light);
  margin: 0 0 1.5rem 0;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1.3rem;
}

.create-title i {
  color: var(--primary-green);
}

.create-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  color: var(--text-secondary);
  font-weight: 500;
  font-size: 0.9rem;
}

.form-group input,
.form-group select {
  padding: 0.75rem;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-sm);
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-light);
  font-size: 0.95rem;
  transition: var(--transition);
}

.form-group input::placeholder {
  color: var(--text-muted);
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: var(--primary-green);
  box-shadow: 0 0 0 3px rgba(43, 209, 123, 0.1);
  background: rgba(255, 255, 255, 0.15);
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

/* Buttons */
.btn-primary,
.btn-secondary,
.btn-success {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--radius-sm);
  font-weight: 600;
  cursor: pointer;
  transition: var(--transition);
  font-size: 0.95rem;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
  color: white;
}

.btn-secondary {
  background: linear-gradient(135deg, #6c757d, #495057);
  color: white;
}

.btn-success {
  background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
  color: white;
}

.btn-primary:hover,
.btn-secondary:hover,
.btn-success:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}

.btn-primary:disabled,
.btn-secondary:disabled,
.btn-success:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Document Types */
.document-types-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.document-type-card {
  background: var(--bg-glass);
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  padding: 1.5rem;
  text-align: center;
  transition: var(--transition);
}

.document-type-card:hover {
  transform: translateY(-3px);
  background: rgba(255, 255, 255, 0.15);
}

.document-type-card i {
  font-size: 2rem;
  color: var(--primary-green);
  margin-bottom: 1rem;
}

.document-type-card h4 {
  color: var(--text-light);
  margin: 0 0 1rem 0;
  font-size: 1rem;
}

.card-actions {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
}

.btn-edit,
.btn-delete {
  padding: 0.5rem;
  border: none;
  border-radius: 50%;
  width: 2.5rem;
  height: 2.5rem;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-edit {
  background: rgba(52, 152, 219, 0.2);
  color: #3498db;
}

.btn-delete {
  background: rgba(231, 76, 60, 0.2);
  color: #e74c3c;
}

.btn-edit:hover,
.btn-delete:hover {
  transform: scale(1.1);
}

/* Assignment Section */
.document-types-selector {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.75rem;
  max-height: 300px;
  overflow-y: auto;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-light);
}

.document-type-option {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.document-type-option input[type="checkbox"] {
  width: 1.2rem;
  height: 1.2rem;
  accent-color: var(--primary-green);
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: var(--text-secondary);
  cursor: pointer;
  font-size: 0.9rem;
}

.checkbox-label i {
  color: var(--primary-green);
}

/* Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
  border-radius: var(--radius-lg);
  padding: 2rem;
  text-align: center;
  color: var(--text-light);
  min-width: 400px;
  border: 2px solid var(--primary-green);
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-icon {
  margin-bottom: 1rem;
}

.modal-icon i {
  color: var(--primary-green);
}

.modal-content h4 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
}

.modal-content p {
  margin: 0 0 2rem 0;
  color: var(--text-secondary);
  font-size: 1.1rem;
}

.modal-actions {
  display: flex;
  justify-content: center;
  gap: 1rem;
}

.button-secondary,
.button-danger {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  font-weight: 600;
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.button-secondary {
  background: rgba(255, 255, 255, 0.2);
  color: var(--text-light);
  border: 1px solid var(--border-light);
}

.button-danger {
  background: #e74c3c;
  color: white;
}

.button-secondary:hover,
.button-danger:hover {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .hero-section {
    grid-template-columns: 1fr;
    text-align: center;
    gap: 1.5rem;
  }

  .hero-decoration {
    flex-direction: row;
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }
}

@media (max-width: 968px) {
  .admin-dashboard {
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

  .navigation-tabs {
    padding: 1rem 1.5rem;
  }

  .hero-title {
    flex-direction: column;
    gap: 0.5rem;
  }

  .admin-actions {
    justify-content: center;
  }

  .stats-grid {
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }

  .quick-actions-grid {
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}

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

  .navigation-tabs {
    padding: 1rem;
    gap: 0.25rem;
  }

  .nav-tab {
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
  }

  .nav-tab span {
    display: none;
  }

  .stats-grid {
    grid-template-columns: 1fr;
  }

  .section-title {
    flex-direction: column;
    gap: 0.5rem;
  }

  .modal-content {
    margin: 1rem;
    min-width: auto;
    width: calc(100% - 2rem);
  }
}

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

  .floating-stat {
    padding: 0.75rem;
    font-size: 0.9rem;
  }

  .create-section {
    padding: 1.5rem;
  }

  .document-types-selector {
    grid-template-columns: 1fr;
  }
}

/* ===== DOCUMENT MANAGEMENT STYLES ===== */

/* Sub Navigation */
.sub-navigation {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: var(--bg-glass);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
}

.sub-nav-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  background: transparent;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-sm);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  font-weight: 500;
  white-space: nowrap;
}

.sub-nav-btn:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.sub-nav-btn.active {
  background: var(--primary-green);
  color: white;
  border-color: var(--primary-green);
}

/* Filters Section */
.filters-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: var(--bg-glass);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-group label {
  color: var(--text-secondary);
  font-weight: 500;
  font-size: 0.9rem;
}

.filter-group select,
.filter-group input {
  padding: 0.6rem;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-sm);
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-light);
  font-size: 0.9rem;
}

.refresh-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.6rem 1rem;
  background: var(--primary-green);
  color: white;
  border: none;
  border-radius: var(--radius-sm);
  cursor: pointer;
  transition: var(--transition);
  font-weight: 500;
  align-self: end;
}

.refresh-btn:hover {
  background: var(--secondary-green);
  transform: translateY(-2px);
}

/* Documents Table */
.documents-table-container {
  background: var(--bg-glass);
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
  overflow: hidden;
}

.loading-state,
.empty-state {
  padding: 3rem;
  text-align: center;
  color: var(--text-secondary);
}

.loading-state i,
.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--primary-green);
}

.documents-table {
  width: 100%;
  border-collapse: collapse;
}

.documents-table th {
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-light);
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  border-bottom: 1px solid var(--border-light);
}

.documents-table td {
  padding: 1rem;
  border-bottom: 1px solid var(--border-light);
  color: var(--text-secondary);
}

.document-row:hover {
  background: rgba(255, 255, 255, 0.05);
}

.client-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.client-info strong {
  color: var(--text-light);
  font-size: 0.95rem;
}

.client-info small {
  color: var(--text-muted);
  font-size: 0.8rem;
}

.document-type {
  background: var(--bg-glass);
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border-light);
  font-size: 0.85rem;
  color: var(--text-light);
}

.file-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.file-info i {
  color: var(--primary-green);
}

.file-info small {
  color: var(--text-muted);
  font-size: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  padding: 0.2rem 0.5rem;
  border-radius: var(--radius-sm);
}

.uploaded-by {
  padding: 0.25rem 0.75rem;
  border-radius: var(--radius-sm);
  font-size: 0.85rem;
  font-weight: 500;
}

.uploaded-by.client {
  background: rgba(52, 152, 219, 0.2);
  color: #3498db;
}

.uploaded-by.admin {
  background: rgba(43, 209, 123, 0.2);
  color: var(--primary-green);
}

.date {
  font-size: 0.85rem;
  color: var(--text-muted);
}

.document-actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.5rem;
  border: none;
  border-radius: 50%;
  width: 2.5rem;
  height: 2.5rem;
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  justify-content: center;
}

.action-btn.download {
  background: rgba(52, 152, 219, 0.2);
  color: #3498db;
}

.action-btn.delete {
  background: rgba(231, 76, 60, 0.2);
  color: #e74c3c;
}

.action-btn:hover {
  transform: scale(1.1);
}

/* File Upload */
.file-upload-area {
  border: 2px dashed var(--border-light);
  border-radius: var(--radius-md);
  padding: 2rem;
  text-align: center;
  transition: var(--transition);
  cursor: pointer;
}

.file-upload-area:hover {
  border-color: var(--primary-green);
  background: rgba(43, 209, 123, 0.05);
}

.upload-placeholder {
  color: var(--text-secondary);
}

.upload-placeholder i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: var(--primary-green);
}

.upload-placeholder p {
  margin: 0 0 0.5rem 0;
  font-size: 1.1rem;
  color: var(--text-light);
}

.upload-placeholder small {
  color: var(--text-muted);
  font-size: 0.9rem;
}

.file-selected {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: var(--text-light);
  background: rgba(43, 209, 123, 0.1);
  border-radius: var(--radius-sm);
  padding: 1rem;
}

.file-selected i {
  color: var(--primary-green);
  font-size: 1.5rem;
}

.clear-file {
  background: rgba(231, 76, 60, 0.2);
  color: #e74c3c;
  border: none;
  border-radius: 50%;
  width: 2rem;
  height: 2rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition);
}

.clear-file:hover {
  background: #e74c3c;
  color: white;
}

/* Responsive Design for Documents */
@media (max-width: 768px) {
  .sub-navigation {
    flex-direction: column;
    gap: 0.25rem;
  }

  .filters-section {
    grid-template-columns: 1fr;
    padding: 1rem;
  }

  .documents-table-container {
    overflow-x: auto;
  }

  .documents-table {
    min-width: 800px;
  }

  .documents-table th,
  .documents-table td {
    padding: 0.75rem 0.5rem;
    font-size: 0.85rem;
  }

  .file-upload-area {
    padding: 1.5rem 1rem;
  }

  .upload-placeholder i {
    font-size: 2rem;
  }
}

@media (max-width: 480px) {
  .document-actions {
    flex-direction: column;
    gap: 0.25rem;
  }

  .action-btn {
    width: 2rem;
    height: 2rem;
    font-size: 0.8rem;
  }

  .file-upload-area {
    padding: 1rem;
  }

  .upload-placeholder p {
    font-size: 0.95rem;
  }
}

/* User Management Styles */
.existing-users-section {
  margin-bottom: 2rem;
}

.users-tabs {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
  background: var(--bg-glass);
  padding: 0.5rem;
  border-radius: var(--radius-lg);
  backdrop-filter: blur(10px);
}

.users-tab {
  flex: 1;
  padding: 0.8rem 1.2rem;
  background: transparent;
  border: 1px solid var(--border-light);
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  cursor: pointer;
  transition: var(--transition);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
}

.users-tab:hover {
  background: rgba(255, 255, 255, 0.1);
  color: var(--text-light);
  transform: translateY(-1px);
}

.users-tab.active {
  background: var(--primary-green);
  color: var(--text-light);
  border-color: var(--primary-green);
  box-shadow: 0 4px 15px rgba(43, 209, 123, 0.3);
}

.users-list {
  background: var(--bg-glass);
  backdrop-filter: blur(10px);
  border-radius: var(--radius-lg);
  padding: 1.5rem;
  border: 1px solid var(--border-light);
}

.users-table-container {
  overflow-x: auto;
  border-radius: var(--radius-md);
  background: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(10px);
  margin-top: 1rem;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  background: transparent;
}

.users-table th,
.users-table td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid var(--border-light);
  color: var(--text-light);
}

.users-table th {
  background: rgba(255, 255, 255, 0.1);
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-secondary);
}

.users-table tbody tr {
  transition: var(--transition);
}

.users-table tbody tr:hover {
  background: rgba(255, 255, 255, 0.05);
}

.actions-column {
  width: 120px;
}

.actions-column .action-btn {
  margin: 0 0.2rem;
}

.action-btn {
  background: transparent;
  border: 1px solid var(--border-light);
  color: var(--text-secondary);
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 50%;
  cursor: pointer;
  transition: var(--transition);
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.action-btn.edit-btn:hover {
  background: var(--primary-green);
  border-color: var(--primary-green);
  color: var(--text-light);
  transform: scale(1.1);
}

.action-btn.delete-btn:hover {
  background: #e74c3c;
  border-color: #e74c3c;
  color: var(--text-light);
  transform: scale(1.1);
}

.no-data {
  text-align: center;
  color: var(--text-muted);
  font-style: italic;
  padding: 2rem !important;
}

.role-badge {
  background: var(--primary-green);
  color: var(--text-light);
  padding: 0.3rem 0.8rem;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 500;
}

/* Responsive adjustments for user tables */
@media (max-width: 768px) {
  .users-tabs {
    flex-direction: column;
  }

  .users-tab {
    text-align: center;
  }

  .users-table-container {
    font-size: 0.85rem;
  }

  .users-table th,
  .users-table td {
    padding: 0.7rem 0.5rem;
  }

  .actions-column {
    width: 80px;
  }

  .action-btn {
    width: 2rem;
    height: 2rem;
    font-size: 0.8rem;
  }
}

/* Estados de alta accesibilidad */
@media (prefers-reduced-motion: reduce) {
  .floating-stat {
    animation: none;
  }

  .stat-card.modern:hover,
  .quick-action-card:hover,
  .btn-primary:hover,
  .btn-secondary:hover,
  .btn-success:hover,
  .action-btn:hover {
    transform: none;
  }

  * {
    transition-duration: 0.01ms !important;
  }
}
</style>
