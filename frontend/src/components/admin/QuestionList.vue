<template>
  <div class="row">
    <div class="col-12">
      <div v-for="question in questions" :key="question.id" class="card shadow-sm mb-3">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="mb-0 text-primary">
            P. #{{ question.id }} 
            (Nivel: <span class="fw-bold text-success">{{ question.level?.name || question.level_id }}</span> / Puntos: {{ question.level?.points || '?' }})
          </h5>
          <div>
            <button @click="emit('edit', question)" class="btn btn-sm btn-primary me-2">
              <i class="bi bi-pencil-fill"></i> Editar
            </button>
            <button @click="emit('delete', question.id, question.description)" class="btn btn-sm btn-danger" :disabled="(isDeleting || {})[question.id]">
              <span v-if="(isDeleting || {})[question.id]" class="spinner-border spinner-border-sm"></span>
              <i v-else class="bi bi-trash-fill"></i>
            </button>
          </div>
        </div>
        <div class="card-body">
          <p class="card-text fw-bold">{{ question.description }}</p> 

          <h6 class="mt-3">Opciones:</h6>
          <ul class="list-group">
            <li v-for="option in question.options" :key="option.id" class="list-group-item d-flex justify-content-between align-items-center" :class="{ 'list-group-item-success': option.is_correct }">
              <span :class="{ 'fw-bold': option.is_correct }">
                {{ option.text }} 
              </span>
              <span v-if="option.is_correct" class="badge bg-success">Correcta</span>
              <span v-else class="badge bg-secondary">Incorrecta</span>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    questions: Array,
    isDeleting: Object,
});

const emit = defineEmits(['edit', 'delete']);
</script>