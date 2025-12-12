<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
      <router-link class="navbar-brand" to="/">TalaTrivia</router-link>
      
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <router-link class="nav-link" to="/">Jugar Trivia</router-link>
          </li>

          <li class="nav-item" v-if="authStore.isAdmin">
            <router-link class="nav-link text-warning" to="/admin/trivias">Gestionar Trivias</router-link>
          </li>
          <li class="nav-item" v-if="authStore.isAdmin">
            <router-link class="nav-link text-warning" to="/admin/users">Gestionar Usuarios</router-link>
          </li>
        </ul>

        <div class="d-flex">
          <template v-if="!authStore.isAuthenticated">
            <router-link to="/login" class="btn btn-outline-light me-2">Login</router-link>
            <router-link to="/register" class="btn btn-light">Register</router-link>
          </template>
          <template v-else>
            <span class="navbar-text me-3">
              Hola, {{ authStore.user?.name || authStore.role }}!
            </span>
            <button @click="authStore.logout" class="btn btn-danger">Cerrar Sesión</button>
          </template>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
const authStore = useAuthStore();
</script>