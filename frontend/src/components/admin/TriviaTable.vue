<template>
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle shadow-sm">
      <thead class="table-dark">
        <tr>
          <th style="width: 5%">ID</th>
          <th style="width: 25%">Nombre</th>
          <th style="width: 15%" class="text-center">Preguntas</th>
          <th style="width: 15%" class="text-center">Usuarios Asignados</th>
          <th style="width: 10%" class="text-center">Activa</th>
          <th style="width: 30%" class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="trivia in trivias" :key="trivia.id">
          <td>{{ trivia.id }}</td>
          <td>
            <span class="fw-bold">{{ trivia.name }}</span>
            <p class="text-muted small mb-0">{{ trivia.description.substring(0, 50) }}...</p>
          </td>
          <td class="text-center">
            <span class="badge bg-secondary">{{ trivia.questions_count }}</span>
          </td>
          <td class="text-center">
            <span class="badge bg-info text-dark">{{ trivia.users_count }}</span>
          </td>
          <td class="text-center">
            <i v-if="trivia.is_active" class="bi bi-check-circle-fill text-success fs-5"></i>
            <i v-else class="bi bi-x-octagon-fill text-danger fs-5"></i>
          </td>
          <td class="text-center">
            <button 
              @click="emit('edit', trivia)" 
              class="btn btn-sm btn-primary me-2" 
              title="Editar Trivia"
            >
              <i class="bi bi-pencil-fill"></i>
            </button>

            <button 
              @click="emit('questions', trivia.id)" 
              class="btn btn-sm btn-info text-white me-2" 
              title="Gestionar Preguntas y Opciones"
            >
              <i class="bi bi-patch-question-fill"></i> ({{ trivia.questions_count }})
            </button>

            <button 
              @click="emit('assign', trivia)" 
              class="btn btn-sm btn-warning me-2" 
              title="Asignar/Desasignar Usuarios"
            >
              <i class="bi bi-person-lines-fill"></i>
            </button>
            <button 
              @click="emit('delete', trivia.id, trivia.name)" 
              class="btn btn-sm btn-danger" 
              title="Eliminar Trivia"
              :disabled="(isDeleting || {})[trivia.id]"
            >
              <span v-if="(isDeleting || {})[trivia.id]" class="spinner-border spinner-border-sm"></span>
              <i v-else class="bi bi-trash-fill"></i>
            </button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    trivias: Array,
    isDeleting: Object,
});

const emit = defineEmits(['edit', 'questions', 'assign', 'delete']);
</script>