<template>
  <div class="login-wrapper">
    <div class="login-card">

    <div class="right-panel">
      <div class="login-card">
        <h1 class="title">Acceso al Portal De Clientes</h1>
          <input v-model="username" type="text" placeholder="Nombre de usuario" class="input" required/>

          <input v-model="password" type="password" placeholder="Contraseña" class="input" required />

          <button @click="login" class="login-button">Ingresar</button>

          <p v-if="error" class="error-message">{{ error }}</p>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue';
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  // import { loadEnvFile } from 'process';

  const loading = ref(false);
  const error = ref(null);
  const username = ref('');
  const password = ref('');
  const router = useRouter();

  async function login() {
    console.log("1.-")

    error.value = null;
    loading.value = true;

    if (username.value === "" || password.value === ""){
      error.value = "Credenciales vacías";
      alert("Debe ingresar sus credenciales")
      loading.value = false
      return; //These step stoped the execition if credentials are empty
    }

    try {
      console.log("2.-");
      const baseUrl = import.meta.env.VITE_API_URL.replace(/\/$/, '');
      const tokenPath = `${baseUrl}/api/token/`
      // const path = `${baseUrl}/api/token/`;
      console.log("3.-", tokenPath);

      const response = await axios.post(tokenPath, {
        username: username.value,
        password: password.value
      },
      {
        headers: {
          "Content-Type": 'application/json',
          "Accept":'application/json',
        },
        withCredentials: true,
      });

      console.log("4.- si vemos esto, la api responde bien 200")

      localStorage.setItem('accessToken', response.data.access);
      // Cuando se obtenga un 200 como respuesta se reocaliza a la vista dependiendo de su nivel de usuario
      router.push('/portal');

      // Implementando nueva logica
     } catch (err) {
      // Robust error handling for Axios errors
      if (axios.isAxiosError(err)) {
        if (err.response) {
          if (err.response.status === 400 || err.response.status === 401) {
            error.value = "Usuario o contraseña incorrectos.";
            console.error("Error de autenticación (credenciales/solicitud):", err.response.data);
          } else {
            error.value = `Error del servidor: ${err.response.status}. Intente de nuevo.`;
            console.error("Error del servidor inesperado:", err.response.data);
          }
        } else if (err.request) {
          error.value = `No se pudo conectar al servidor. Verifique su conexión o la configuración CORS.`. err.message;
          console.error("Error de red/conexión (Axios err.request):", err.message);
        } else {
          error.value = 'Error al configurar la solicitud. Intente de nuevo.';
          console.error("Error de Axios (configuración):", err.message);
        }
      } else {
        error.value = 'Ocurrió un error inesperado.';
        console.error("Error inesperado (no-Axios):", err);
      }
     } finally {
        console.log("Fin de la función login");
        loading.value = false;
    }
  }

</script>

<style scoped>

html, body {
  margin: 0;
  padding: 0;
  height: 100%;
  width: 100%;
  background-color: #f4f7fb; /* o tu color base */
  font-family: 'Segoe UI', sans-serif;
}

  :root {
    --primary: #0074b6;
    --secondary: #6cbd45;
    --text: #333;
    --danger: #dc3545;
    --bg-light: #f4f7fb;
    --card-bg: #ffffff;
    --shadow: rgba(0, 0, 0, 0.1);
  }

  .login-wrapper {
    height: 75px;
    width: 380%;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    box-sizing: border-box;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background: linear-gradient(to right, #2bd17b, rgb(6, 22, 94));
  }

  .login-card {
    background-color: var(--card-bg);
    padding: 40px 30px;
    border-radius: 10px;
    box-shadow: 0 10px 25px var(--shadow);
    text-align: center;
    max-width: 400px;
    width: 100%;
  }

  .title {
    color: black;
    font-weight: 400;
    font-size: 2.5em;
    margin-bottom: 30px;
  }

  .input {
    display: block;
    width: 100%;
    padding: 12px 15px;
    margin-bottom: 20px;
    border: 1px solid #ccc;
    border-radius: 20px;
    font-size: 1em;
    transition: border 0.3s ease;
  }

  .input:focus {
    border-color: #101011;
    outline: none;
  }

  .login-button {
    background-color: #101011;
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 1.4em;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .login-button:hover {
    background-color: #36d85c;
  }

  .error-message {
    color: #590303;
    margin-top: 15px;
    font-size: 1.2em;
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: center;
  }

</style>