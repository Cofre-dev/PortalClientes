<template>
      <div class="dashboard-container">
        <div class="header">
          <h2>Panel de Administrador</h2>
          <div class="profile-info">
            <span>{{ profile.full_name || profile.username }} (<strong>{{ profile.role_detail }}</strong>)</span>
            <button @click="logout" class="logout-button">Cerrar Sesión</button>
          </div>
        </div>
        <p class="subtitle">Revise y gestione los documentos de los clientes.</p>

        <div v-if="loading" class="loading-spinner">Cargando documentos...</div>

        <div v-else class="documents-table-container">
          <table>
            <thead>
              <tr>
                <th>Cliente</th>
                <th>Tipo de Documento</th>
                <th>Archivo del Cliente</th>
                <th>Subir archivo</th>
                <th>Borrar archivo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="doc in documentos" :key="doc.id">
                <td>{{ doc.cliente.razon_social }}</td>
                <td>{{ doc.tipo_documento.nombre }}</td>
                <td>
                  <!-- Botón para que el admin descargue lo que subió el cliente -->
                  <!-- <a :href="backendUrl + doc.archivo_cliente" download class="action-button download" v-if="doc.archivo_cliente">Descargar</a> -->
                   <button @click="downloadFile(doc.archivo_cliente)" class="action-button download" v-if="doc.archivo_cliente"> Descargar</button>
                  <span class="status-text" v-else>Pendiente</span>
                </td>
                <td>
                  <!-- Si la consultora ya subió un archivo, muestra botón de descarga -->
                  <button @click="downloadFile(doc.archivo_consultora)" class="action-button download" v-if="doc.archivo_cliente"> Descargar</button>
                  <!-- Si no, muestra el botón para subir uno -->
                  <label class="action-button upload" v-else>
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

    const props = defineProps({
      profile: Object
    });

    const documentos = ref([]);
    const loading = ref(true);
    const router = useRouter();
    const backendUrl = 'http://127.0.0.1:8000';

    const apiClient = axios.create({
      baseURL: `${backendUrl}/api`,
      headers: {
        'Authorization': `Bearer ${localStorage.getItem('accessToken')}`
      }
    });

    async function downloadFile(fileUrl) {
  if (!fileUrl) return;
  const fullUrl = fileUrl;
  console.log(`Intentando descargar desde: ${fullUrl}`); // Log para ver la URL

  try {
    const response = await axios.get(fullUrl, {
      responseType: 'blob',
    });

    const url = window.URL.createObjectURL(response.data);
    const link = document.createElement('a');
    link.href = url;
    const fileName = fileUrl.split('/').pop();
    link.setAttribute('download', fileName || 'download');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);

  } catch (error) {
    // --- BLOQUE CATCH MEJORADO ---
    console.error('--- INICIO DEL INFORME DE ERROR DE DESCARGA ---');
    console.error('El objeto de error completo es:', error);

    if (error.response) {
      console.error('Status del error:', error.response.status);
      console.error('Headers del error:', error.response.headers);
      
      // Intentamos leer el cuerpo del error, que puede ser un blob
      if (error.response.data instanceof Blob) {
        const reader = new FileReader();
        reader.onload = function() {
          try {
            // Intenta interpretarlo como JSON
            const errorJson = JSON.parse(this.result);
            console.error('Datos del error (interpretado como JSON):', errorJson);
          } catch (e) {
            // Si falla, lo muestra como texto plano
            console.error('Datos del error (interpretado como texto):', this.result);
          }
        };
        reader.readAsText(error.response.data);
      } else {
        console.error('Datos del error (ya en formato legible):', error.response.data);
      }
    } else {
      console.error('El error no tiene una respuesta del servidor. Puede ser un problema de red, CORS, o una URL inválida.');
    }
    console.error('--- FIN DEL INFORME DE ERROR DE DESCARGA ---');
    alert('No se pudo descargar el archivo. Por favor, revise la consola para más detalles (presione F12).');
  }
}

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

    // --- FUNCIÓN IMPLEMENTADA ---
    async function handleConsultantUpload(event, docId) {
      const file = event.target.files?.[0];
      if (!file) return;

      const formData = new FormData();
      formData.append('file', file);

      try {
        loading.value = true;
        // Llama al nuevo endpoint 'subir-consultora'
        await apiClient.post(`/documentos/${docId}/subir-consultora/`, formData);
        alert('Archivo subido con éxito para el cliente.');
        await fetchDocuments(); // Recarga la lista para mostrar el nuevo archivo
      } catch (error) {
        console.error("Error al subir el archivo:", error);
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
    .profile-info { display: flex; align-items: center; gap: 20px; color: white; }
    .subtitle { color: #B0C4DE; margin-bottom: 30px; }
    .logout-button { background-color: #E53935; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background-color 0.3s; }
    .logout-button:hover { background-color: #C62828; }

    .documents-table-container {
      background-color: #FFFFFF; 
      color: #333; 
      border-radius: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      overflow: hidden;
    }

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
    