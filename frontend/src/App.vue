<script setup>
import { RouterLink, RouterView } from 'vue-router'
import HelloWorld from './components/HelloWorld.vue'
</script>

<template>
  <header>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" -->
    
    <img alt="Vue logo" class="logo" src="@/assets/image.png" width="325" height="325" />

    <div class="wrapper">
      <HelloWorld msg="Ara y Bustamante Consultores" />

      <nav>
        <RouterLink to="/" class="Home">Home</RouterLink>
      </nav>
    </div>
  </header>

  <RouterView />
</template>

<style scoped>
html{
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

body{
  margin: 0;
  padding: 0;
  display: flex;
}

header {
  position: fixed;
  top: 0;
  left: 0;
  height: 42vh;
  background-color: #021144;
  line-height: 1.4;
  padding: 25px; 
  min-height: 100vh;
  width: 790px;
  box-sizing: border-box;
  z-index: 100;
}

.Home{
  border-radius: 100%;
  font-size: 1.6rem;
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
