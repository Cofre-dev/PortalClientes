import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL;
// const API_BASE_URL = 'http://127.0.0.1:8000';

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


apiClient.interceptors.response.use(
  (response) => response,

  async (error) => {
    const originalRequest = error.config;

    if (error.response.status === 401 && !originalRequest._retry){
      originalRequest._retry = true; 

      try {
        const refreshToken = localStorage.getItem('refreshToken');
        const response = await axios.post(`${API_BASE_URL}/api/token/refresh/`, {
          refresh: refreshToken,
        });

        const newAccessToken = response.data.access;
        localStorage.setItem('accessToken', newAccessToken);

        originalRequest.headers['Autorization'] = `Bearer ${newAccessToken}`;
        return apiClient(originalRequest); 

      } catch (errorToken) {
        console.log("No se logro refrescar el token", refreshToken) //Para depurar en caso de error
        localStorage.removeItem('accessToken');
        localStorage.removeItem('refreshToken');
        window.location.href = '/';
        return Promise.reject(errorToken);
      }
    }
    return Promise.reject(error);
  }
)

// 3. Creamos y exportamos funciones para cada acción de la API.
export default {
  // --- Autenticación ---
  async login(credentials) {
    // El login es especial, no usa el apiClient porque no tiene el token aún.
    const response = await axios.post(`${API_BASE_URL}/api/token/`, credentials);
    localStorage.setItem('accessToken', response.data.access);
    localStorage.setItem('RefreshToken', response.data.refresh);
    return response;
  },
  logout(){
    localStorage.removeItem('accessToken');
    localStorage.removeItem('refreshToken');
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
