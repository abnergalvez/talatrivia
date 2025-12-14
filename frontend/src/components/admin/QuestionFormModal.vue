<template>
  <div class="modal d-block modal-open" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ item.id ? 'Editar Pregunta' : 'Crear Nueva Pregunta' }}</h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>
        <form @submit.prevent="emit('save')">
          <div class="modal-body modal-body-scroll">
            
            <div class="mb-3">
              <label for="questionContent" class="form-label">Contenido de la Pregunta</label>
              <textarea 
                class="form-control" 
                id="questionContent" 
                :value="item.description" 
                @input="$emit('update:description', $event.target.value)" 
                rows="3" 
                required
              ></textarea>
            </div>

            <div class="mb-3">
              <label for="levelId" class="form-label">Nivel (Level ID)</label>
              <select 
                class="form-select" 
                id="levelId" 
                :value="item.level_id" 
                @change="$emit('update:level_id', parseInt($event.target.value))" 
                required
              >
                <option :value="null" disabled>Selecciona un nivel</option>
                <option v-for="level in levels" :key="level.id" :value="level.id">
                  {{ level.name }} (ID: {{ level.id }} - {{ level.points }} pts)
                </option>
              </select>
              <small v-if="!levels.length" class="text-danger">Cargando niveles. Asegúrate de que los niveles existan en el sistema.</small>
            </div>

            <hr>
            
            <h5 class="mt-4 mb-3">Opciones (max. 8)</h5>
            <div v-for="(option, index) in item.options" :key="index" class="input-group mb-3">
              <span class="input-group-text">{{ index + 1 }}</span>
              <input 
                type="text" 
                class="form-control" 
                :value="option.text"
                @input="$emit('update-option-text', index, $event.target.value)"
                required 
                placeholder="Contenido de la opción"
              > 
              
              <div class="input-group-text">
                <input 
                  class="form-check-input mt-0" 
                  type="radio" 
                  :name="'correct-option-' + item.id" 
                  :checked="option.is_correct" 
                  @change="emit('set-correct-option', index)"
                >
                <label class="ms-2">Correcta</label>
              </div>

              <button 
                v-if="item.options.length > 2" 
                type="button" 
                class="btn btn-outline-danger" 
                @click="emit('remove-option', index)"
              >
                <i class="bi bi-x-lg"></i>
              </button>
            </div>

            <div class="d-flex justify-content-between">
              <button 
                type="button" 
                class="btn btn-sm btn-outline-secondary" 
                @click="emit('add-option')" 
                :disabled="item.options.length >= 8"
              >
                <i class="bi bi-plus-circle me-1"></i> Añadir Opción
              </button>
              <div v-if="optionError" class="text-danger small">{{ optionError }}</div>
            </div>

          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="emit('close')">Cancelar</button>
            <button type="submit" class="btn btn-primary" :disabled="isSaving">
              <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
              Guardar Pregunta
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
    levels: Array,
    isSaving: Boolean,
    optionError: String,
});

const emit = defineEmits([
    'close', 
    'save', 
    'update:description', 
    'update:level_id', 
    'update-option-text',
    'add-option',
    'remove-option',
    'set-correct-option'
]);
</script>

<style scoped>
.modal-body-scroll {
    max-height: 75vh; 
    overflow-y: auto; 
}
.modal-open {
    overflow: hidden;
}
</style>