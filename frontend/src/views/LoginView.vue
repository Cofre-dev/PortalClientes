<template>
  <div class="login-container">
    <h2>Acceso Portal Clientes</h2>
    <input v-model="username" type="text" placeholder="RUT de la empresa" />
    <input v-model="password" type="password" placeholder="Contraseña" />
    <button @click="login">Ingresar</button>
    <p v-if="error" class="error-message">{{ error }}</p>
  </div>
</template>

<script setup>
    import { ref } from 'vue';
    import axios from 'axios';
    import { useRouter } from 'vue-router';

    const username = ref(''); // Este será el RUT
    const password = ref('');
    const error = ref(null);
    const router = useRouter();

    async function login() {
    error.value = null;
    try {
        const response = await axios.post('http://127.0.0.1:8000/api/token/', {
        username: username.value,
        password: password.value,
        });

        // ¡Éxito! Guarda el token en el almacenamiento local del navegador
        localStorage.setItem('accessToken', response.data.access);

        // Redirige al portal
        router.push('/portal');

    } catch (err) {
        error.value = 'RUT o contraseña incorrectos.';
        console.error('Error de autenticación:', err);
    }
    }
</script>

<style scoped>
    .login-container { max-width: 400px; margin: 50px auto; text-align: center; }
    .error-message { color: red; }
</style>