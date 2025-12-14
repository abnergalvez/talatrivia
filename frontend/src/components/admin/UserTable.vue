<template>
  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Rol</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.id }}</td>
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>
                <span 
                  :class="['badge', user.role.name === 'admin' ? 'bg-primary' : 'bg-secondary']"
                >
                  {{ user.role.name }}
                </span>
              </td>
              <td>
                <button @click="emit('edit', user)" class="btn btn-sm btn-primary me-2">
                  <i class="bi bi-pencil-fill"></i>
                </button>
                <button 
                  @click="emit('delete', user.id, user.name)" 
                  class="btn btn-sm btn-danger" 
                  :disabled="isDeleting[user.id]"
                >
                  <span v-if="isDeleting[user.id]" class="spinner-border spinner-border-sm"></span>
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
    users: Array,
    isDeleting: Object,
});

const emit = defineEmits(['edit', 'delete']);
</script>