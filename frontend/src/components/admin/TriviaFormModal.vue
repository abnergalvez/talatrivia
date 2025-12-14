<template>
  <div class="modal d-block modal-open" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ item.id ? 'Editar Trivia' : 'Crear Nueva Trivia' }}</h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>
        <form @submit.prevent="emit('save')">
          <div class="modal-body">
            
            <div class="mb-3">
              <label for="name" class="form-label">Nombre</label>
              <input type="text" class="form-control" id="name" :value="item.name" @input="$emit('update:name', $event.target.value)" required>
            </div>

            <div class="mb-3">
              <label for="description" class="form-label">Descripción</label>
              <textarea class="form-control" id="description" :value="item.description" @input="$emit('update:description', $event.target.value)" rows="3" required></textarea>
            </div>

            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" id="isActive" :checked="item.is_active" @change="$emit('update:is_active', $event.target.checked)">
              <label class="form-check-label" for="isActive">Activa</label>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
              Guardar
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
});

const emit = defineEmits(['close', 'save', 'update:name', 'update:description', 'update:is_active']);
</script>