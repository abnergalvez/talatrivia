<template>
  <div class="modal d-block modal-open" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ form.id ? 'Editar Usuario' : 'Crear Usuario' }}</h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>
        <form @submit.prevent="emit('save')" autocomplete="off">
          <div class="modal-body">

            <div v-if="Object.keys(validationErrors).length" class="alert alert-warning">
              <ul class="mb-0">
                <li v-for="(errors, field) in validationErrors" :key="field">
                  <span class="fw-bold text-capitalize">{{ field.replace('_', ' ') }}:</span>
                  <ul class="ms-3 my-1">
                    <li v-for="errorMsg in errors" :key="errorMsg">{{ errorMsg }}</li>
                  </ul>
                </li>
              </ul>
            </div>

            <div class="mb-3">
              <label for="name" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="name" v-model="form.name" required>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" v-model="form.email" autocomplete="off" required>
            </div>

            <div class="mb-3">
              <label for="role" class="form-label">Rol</label>
              <p v-if="isRolesLoading" class="text-muted">Cargando roles...</p>

              <select v-else class="form-select" id="role" v-model="form.role_id" required>
                <option v-if="roles.length === 0" disabled value="">No hay roles disponibles</option>
                
                <option v-for="role in roles" :key="role.id" :value="role.id">
                  {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }} ({{ role.name }})
                </option>
              </select>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Contraseña {{ form.id ? '(Dejar en blanco para no cambiar)' : '*' }}</label>
              <input type="password" class="form-control" id="password" v-model="form.password" :required="!form.id" autocomplete="new-password">
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
              Guardar Usuario
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade show"></div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    form: Object,
    roles: Array,
    isSaving: Boolean,
    isRolesLoading: Boolean,
    validationErrors: Object,
});

const emit = defineEmits(['close', 'save']);
</script>