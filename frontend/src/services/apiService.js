import axios from 'axios';

// 1. Creamos una instancia de Axios con la configuración base.
const apiClient = axios.create({
  baseURL: 'http://127.0.0.1:8000/api', // Apunta a la raíz de tu API
});

// 2. Usamos un "interceptor" para añadir el token de autenticación a CADA petición.
// Esto es mucho más limpio que añadirlo manualmente en cada llamada.
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('accessToken');
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// 3. Creamos y exportamos funciones para cada acción de la API.
export default {
  // --- Autenticación ---
  login(credentials) {
    // El login es especial, no usa el apiClient porque no tiene el token aún.
    return axios.post('http://127.0.0.1:8000/api/token/', credentials);
  },
  getProfile() {
    return apiClient.get('/me/');
  },

  // --- Categorías y Archivos ---
  fetchCategorias() {
    return apiClient.get('/categorias/');
  },
  uploadFile(categoriaId, formData) {
    return apiClient.post(`/categorias/${categoriaId}/upload-file/`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
  },
  deleteFile(archivoId) {
    return apiClient.delete(`/archivos/${archivoId}/`);
  },
  
  // La descarga es especial porque necesita la URL completa y una respuesta tipo 'blob'
  downloadFile(fileUrl) {
    return axios.get(fileUrl, {
      responseType: 'blob',
    });
  }
};
