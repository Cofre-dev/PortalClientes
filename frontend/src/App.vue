<script setup>
import { RouterLink, RouterView } from 'vue-router'
import { useRouter } from 'vue-router'
import { ref, onMounted } from 'vue'
import HelloWorld from './components/HelloWorld.vue'

const router = useRouter()
const currentRoute = ref('')

onMounted(() => {
  currentRoute.value = router.currentRoute.value.name
  router.afterEach((to) => {
    currentRoute.value = to.name
  })
})
</script>

<template>
  <div class="app-container">
    <header v-if="currentRoute !== 'portal'" class="main-header">
      <div class="header-content">
        <div class="logo-container">
          <img alt="Ara y Bustamante Logo" class="logo" src="@/assets/image.png" />
        </div>
        <div class="company-info">
          <HelloWorld msg="Ara y Bustamante Consultores" />
        </div>
      </div>
    </header>

    <main class="main-content" :class="{ 'full-width': currentRoute === 'portal' }">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
/* Reset y variables globales */
.app-container {
  min-height: 100vh;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f4f7fb;
  overflow-x: hidden;
}

/* Header principal */
.main-header {
  background: linear-gradient(135deg, #021144 0%, #061656 100%);
  min-height: 100vh;
  width: 50%;
  position: fixed;
  top: 0;
  left: 0;
  z-index: 100;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  box-sizing: border-box;
}

.header-content {
  text-align: center;
  max-width: 600px;
  width: 100%;
}

.logo-container {
  margin-bottom: 2rem;
}

.logo {
  max-width: 100%;
  height: auto;
  width: clamp(200px, 50vw, 400px);
  transition: all 0.3s ease;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
  border-radius: 15px;
  filter: brightness(1.1);
}

.logo:hover {
  transform: scale(1.05);
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.4);
}

.company-info {
  animation: fadeInUp 0.8s ease-out 0.3s both;
}

/* Contenido principal */
.main-content {
  margin-left: 50%;
  width: 50%;
  min-height: 100vh;
  position: relative;
  transition: all 0.3s ease;
}

.main-content.full-width {
  margin-left: 0;
  width: 100%;
}

/* Animaciones */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive Design */
@media (max-width: 1200px) {
  .main-header {
    width: 45%;
  }

  .main-content {
    margin-left: 45%;
    width: 55%;
  }
}

@media (max-width: 968px) {
  .main-header {
    position: relative;
    width: 100%;
    min-height: 60vh;
    padding: 1.5rem;
  }

  .main-content {
    margin-left: 0;
    width: 100%;
  }

  .logo {
    width: clamp(150px, 40vw, 300px);
  }
}

@media (max-width: 768px) {
  .main-header {
    min-height: 50vh;
    padding: 1rem;
  }

  .logo {
    width: clamp(120px, 35vw, 250px);
  }

  .header-content {
    max-width: 90%;
  }
}

@media (max-width: 480px) {
  .main-header {
    min-height: 40vh;
    padding: 0.8rem;
  }

  .logo {
    width: clamp(100px, 30vw, 200px);
  }

  .logo-container {
    margin-bottom: 1rem;
  }
}

/* Estados específicos para login - Aplicar siempre que no sea full-width */
.main-content:not(.full-width) {
  background: linear-gradient(135deg, #2bd17b, #061656);
}

@media (max-width: 968px) {
  .main-content:not(.full-width) {
    background: linear-gradient(135deg, #2bd17b, #061656);
  }
}
</style>
