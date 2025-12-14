<template>
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Puntos Asociados</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in items" :key="item.id">
              <td>{{ item.id }}</td>
              <td>{{ item.name }}</td>
              <td>{{ item.points }}</td>
              <td>
                <button @click="emit('edit', item)" class="btn btn-sm btn-primary me-2">
                  <i class="bi bi-pencil-fill"></i>
                </button>
                <button 
                  @click="emit('delete', item.id, item.name)" 
                  class="btn btn-sm btn-danger" 
                  :disabled="isDeleting[item.id]"
                >
                  <span v-if="isDeleting[item.id]" class="spinner-border spinner-border-sm"></span>
                  <i v-else class="bi bi-trash-fill"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    items: Array,
    isDeleting: Object,
});

const emit = defineEmits(['edit', 'delete']);
</script>