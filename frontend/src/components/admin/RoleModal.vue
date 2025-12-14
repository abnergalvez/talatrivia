<template>
  <div class="modal d-block modal-open" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ item.id ? 'Editar Rol' : 'Crear Rol' }}</h5>
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
              <label for="name" class="form-label">Nombre del Rol (ej: admin, player)</label>
              <input type="text" class="form-control" id="name" v-model="item.name" required autocomplete="off">
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
              Guardar Rol
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
    item: Object,
    isSaving: Boolean,
    validationErrors: Object,
});

const emit = defineEmits(['close', 'save']);
</script>