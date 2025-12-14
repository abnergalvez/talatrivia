<template>
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg">
          <div class="card-header bg-success text-white text-center">
            <h4 class="mb-0">Registro de Usuario</h4>
          </div>
          <div class="card-body">

            <div v-if="authStore.error" class="alert alert-danger" role="alert">
              {{ authStore.error }}
            </div>

            <form @submit.prevent="handleRegister">
              <div class="mb-3">
                <label for="name" class="form-label">Nombre de Usuario</label>
                <input type="text" class="form-control" id="name" v-model="name" required :disabled="authStore.isLoading">
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="email" v-model="email" required :disabled="authStore.isLoading">
              </div>
              <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="password" v-model="password" required :disabled="authStore.isLoading">
              </div>
              
              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-success btn-lg mt-3" :disabled="authStore.isLoading">
                  <span v-if="authStore.isLoading" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                  {{ authStore.isLoading ? 'Cargando...' : 'Crear Cuenta' }}
                </button>
              </div>
            </form>
          </div>
          <div class="card-footer text-center">
            <router-link to="/login" class="text-decoration-none">
              ¿Ya tienes cuenta? Inicia Sesión.
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const name = ref('');
const email = ref('');
const password = ref('');

const handleRegister = () => {
    authStore.register(name.value, email.value, password.value);
};
</script>