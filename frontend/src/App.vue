<script setup>
import { RouterView } from 'vue-router';
import NavBar from '@/components/NavBar.vue';
</script>

<template>
  <div class="main-layout-wrapper"> 
    
    <NavBar /> 
    
    <main class="content-area">
      <RouterView /> 
    </main>
    
  </div>
</template>

<style>
/* Estilos globales si son necesarios, ya tienes bootstrap cargado */
body {
  background-color: #f8f9fa; /* Color de fondo ligero de Bootstrap */
}
.content-area { 
  padding: 20px; 
  /* Asegura que el contenido esté debajo del NavBar si es fijo */
}
</style>