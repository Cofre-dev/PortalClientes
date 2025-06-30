<template>
  <div>
    <h1>Mis Documentos</h1>
    <div v-if="loading">Cargando...</div>
    <table v-else>
      <thead>
        <tr>
          <th>Tipo de Documento</th>
          <th>Descargar (Consultora)</th>
          <th>Subir mi archivo</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="doc in documentos" :key="doc.id">
          <td>{{ doc.tipo_documento.nombre }}</td>
          <td>
            <a :href="doc.archivo_consultora" target="_blank" v-if="doc.archivo_consultora">
              Descargar Archivo
            </a>
            <span v-else>No disponible</span>
          </td>
          <td>
            <input type="file" @change="handleFileUpload($event, doc.id)" />
          </td>
        </tr>
      </tbody>
    </table>
    <button @click="logout">Cerrar Sesión</button>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';

const documentos = ref([]);
const loading = ref(true);
const router = useRouter();

async function fetchDocuments() {
  const token = localStorage.getItem('accessToken');
  if (!token) {
    router.push('/'); // Si no hay token, redirige al login
    return;
  }

  try {
    const response = await axios.get('http://127.0.0.1:8000/api/documentos/', {
      headers: {
        // Así le decimos a Django quiénes somos
        'Authorization': `Bearer ${token}`
      }
    });
    documentos.value = response.data;
  } catch (error) {
    console.error("Error al cargar documentos:", error);
    // Podrías manejar un token expirado aquí
    if (error.response && error.response.status === 401) {
        logout();
    }
  } finally {
    loading.value = false;
  }
}

function logout() {
    localStorage.removeItem('accessToken');
    router.push('/');
}

// Llama a la función cuando el componente se ha montado en la página
onMounted(fetchDocuments);
</script>