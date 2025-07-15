import axios from 'axios';

// const API_BASE_URL = import.meta.env.VITE_API_URL || 'https://127.0.0.1:8000';
const API_BASE_URL = 'https://127.0.0.1:8000';

// 1. Creamos una instancia de Axios con la configuración base.
const apiClient = axios.create({
  // Apunta a la raíz de tu API
  baseURL: 'http://127.0.0.1:8000/api', 
  // baseURL: 'https://portalclientes.onrender.com'
  // baseURL: `${API_BASE_URL}/api`,
});

// 2. Usamos un "interceptor" para añadir el token de autenticación a CADA petición.
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
    return axios.post(`${API_BASE_URL}/api/token/`, credentials);
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
  
  // La descarga usa axios porque la url esta completa:)
  downloadFile(fileUrl) {
    return axios.get(fileUrl, {
      responseType: 'blob',
    });
  }
};
