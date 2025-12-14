<template>
  <div class="modal d-block modal-open" tabindex="-1">
    <div class="modal-dialog modal-lg"> 
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title">Gestión de Usuarios para: {{ trivia.name }}</h5>
          <button type="button" class="btn-close" @click="emit('close')"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">Gestiona los usuarios asignados a la trivia: <strong>{{ trivia.name }}</strong></p>
          
          <div v-if="isLoading" class="text-center p-4">
              <div class="spinner-border text-warning" role="status"></div>
              <p class="mt-2 text-warning">Cargando listas de usuarios...</p>
          </div>

          <div v-else class="row">
              <div class="col-6">
                  <h6 class="text-danger">Usuarios Asignados ({{ assignedUsers.length }})</h6>
                  <p class="text-muted small">Selecciona usuarios de aquí para **desasignar**.</p>
                  <select multiple class="form-select" size="10" 
                    :value="selectedAssignedEmails"
                    @change="emit('update:selectedAssignedEmails', Array.from($event.target.options).filter(o => o.selected).map(o => o.value))"
                  >
                      <option v-for="user in assignedUsers" :key="user.id" :value="user.email">
                          {{ user.name }} ({{ user.email }})
                          <span v-if="user.role_id === 1" class="text-muted fw-bold">(Admin)</span>
                          <span v-else-if="user.role_id === 2" class="text-muted fw-bold">(Player)</span>
                          <span v-else class="text-muted fw-bold">(Rol #{{ user.role_id }})</span>
                      </option>
                  </select>
                  <button 
                      @click="emit('unassign')" 
                      class="btn btn-sm btn-outline-danger w-100 mt-2" 
                      :disabled="isAssigning || selectedAssignedEmails.length === 0"
                  >
                      <span v-if="isAssigning && action === 'unassign'" class="spinner-border spinner-border-sm me-2"></span>
                      <i v-else class="bi bi-person-dash-fill me-1"></i> Desasignar Seleccionados
                  </button>
              </div>

              <div class="col-6">
                  <h6 class="text-success">Usuarios Disponibles ({{ availableUsers.length }})</h6>
                  <p class="text-muted small">Selecciona usuarios de aquí para **asignar**.</p>
                  <select multiple class="form-select" size="10" 
                    :value="selectedAvailableEmails"
                    @change="emit('update:selectedAvailableEmails', Array.from($event.target.options).filter(o => o.selected).map(o => o.value))"
                  >
                      <option v-for="user in availableUsers" :key="user.id" :value="user.email">
                          {{ user.name }} ({{ user.email }})
                          <span v-if="user.role_id === 1" class="text-muted fw-bold">(Admin)</span>
                          <span v-else-if="user.role_id === 2" class="text-muted fw-bold">(Player)</span>
                          <span v-else class="text-muted fw-bold">(Rol #{{ user.role_id }})</span>
                      </option>
                  </select>
                  <button 
                      @click="emit('assign')" 
                      class="btn btn-sm btn-success w-100 mt-2" 
                      :disabled="isAssigning || selectedAvailableEmails.length === 0"
                  >
                      <span v-if="isAssigning && action === 'assign'" class="spinner-border spinner-border-sm me-2"></span>
                      <i v-else class="bi bi-person-plus-fill me-1"></i> Asignar Seleccionados
                  </button>
              </div>
          </div>
          
          <div v-if="error" class="alert alert-danger small mt-3">{{ error }}</div>
          <div v-if="success" class="alert alert-success small mt-3">{{ success }}</div>
          
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="emit('close')" :disabled="isAssigning">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
  <div class="modal-backdrop fade show"></div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    trivia: Object,
    isLoading: Boolean,
    assignedUsers: Array,
    availableUsers: Array,
    selectedAvailableEmails: Array,
    selectedAssignedEmails: Array,
    isAssigning: Boolean,
    action: String,
    error: String,
    success: String,
});

const emit = defineEmits([
    'close', 
    'assign', 
    'unassign', 
    'update:selectedAvailableEmails', 
    'update:selectedAssignedEmails'
]);
</script>