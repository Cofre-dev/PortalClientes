<script setup>
import { RouterLink, RouterView } from 'vue-router'
import HelloWorld from './components/HelloWorld.vue'
</script>

<template>
  <header>
    <img alt="Vue logo" class="logo" src="@/assets/image.png" width="325" height="325" />

    <div class="wrapper">
      <HelloWorld msg="Portal de clientes de Ara y Bustamante" />

      <nav>
        <RouterLink to="/">Home</RouterLink>
        <!-- <RouterLink to="/about">About</RouterLink> -->
      </nav>
    </div>
  </header>

  <RouterView />
</template>

<style scoped>
html, body {
  overflow-x: auto;
  margin: 0;
  padding: 0;
  height: 100%;
  width: 100%;
  background-color: #f4f7fb; /* o tu color base */
  font-family: 'Segoe UI', sans-serif;
   line-height: 1.5;
  padding: 40px; 
  min-height: 100vh;
  width: 42vw;
  box-sizing: border-box;
}

header {
  background-color: #000000;
  line-height: 1.5;
  padding: 40px; 
  min-height: 100vh;
  width: 42vw;
  box-sizing: border-box;
}

 .logo {
  max-width: 500px;
  margin-bottom: 20px;
  transition: transform 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  border-radius: 10px;
}

.logo:hover {
  transform: scale(1.1);
}
.logo:delay{
  transition-duration: 2s;
  transition-delay: 2s;
}

nav {
  width: 100%;
  font-size: 12px;
  text-align: center;
  margin-top: 2rem;
}

nav a.router-link-exact-active {
  color: var(--color-text);
}

nav a.router-link-exact-active:hover {
  background-color: transparent;
}

nav a {
  display: inline-block;
  padding: 0 1rem;
  border-left: 1px solid var(--color-border);
}

nav a:first-of-type {
  border: 0;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    padding-right: calc(var(--section-gap) / 2);
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }

  nav {
    text-align: left;
    margin-left: -1rem;
    font-size: 1rem;

    padding: 1rem 0;
    margin-top: 1rem;
  }
}
</style>
