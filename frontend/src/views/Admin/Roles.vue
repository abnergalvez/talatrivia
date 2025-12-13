<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="text-dark fw-bold">Roles</h3>
      <button @click="openCreateModal" class="btn btn-success btn-lg">
        <i class="bi bi-plus-circle-fill me-2"></i> Crear Nuevo
      </button>
    </div>

    <div v-if="error" class="alert alert-danger" role="alert">
      Error: <strong>{{ error }}</strong>
    </div>

    <div v-if="isLoading" class="text-center p-5">
      <div class="spinner-border text-info" role="status"></div>
      <p class="mt-2 text-muted">Cargando roles...</p>
    </div>

    <div v-else-if="items.length" class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-dark">
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in items" :key="item.id">
                <td>{{ item.id }}</td>
                <td>
                  <span 
                    :class="['badge', item.name === 'admin' ? 'bg-primary' : 'bg-secondary']"
                  >
                    {{ item.name }}
                  </span>
                </td>
                <td>
                  <button @click="openEditModal(item)" class="btn btn-sm btn-primary me-2">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <button 
                    @click="deleteItem(item.id, item.name)" 
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

    <div v-else-if="!isLoading" class="alert alert-info text-center">
      No hay roles registrados.
    </div>

    <div v-if="isModalOpen" class="modal d-block modal-open" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ currentItem.id ? 'Editar Rol' : 'Crear Rol' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="saveItem" autocomplete="off">
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
                <input type="text" class="form-control" id="name" v-model="currentItem.name" required autocomplete="off">
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                Guardar Rol
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div v-if="isModalOpen" class="modal-backdrop fade show"></div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '@/http'; 
import { useAuthStore } from '@/stores/auth'; 

const endpoint = '/roles';

// --- ESTADOS DE DATOS ---
const items = ref([]);
const isLoading = ref(false);
const error = ref(null);

// Modal y Formulario
const isModalOpen = ref(false);
const currentItem = ref({
    id: null,
    name: '',
});
const isSaving = ref(false);
const isDeleting = {}; // Usamos un objeto para estados de eliminación
const validationErrors = ref({}); 

// ----------------------------------------------------------------------
// --- FETCHING / LISTADO DE ROLES (Lógica de extracción mejorada) ---
// ----------------------------------------------------------------------

const fetchItems = async () => {
    const authStore = useAuthStore();
    
    if (!authStore.token) {
        error.value = 'Acceso denegado. Token no encontrado.';
        return; 
    }
    
    isLoading.value = true;
    error.value = null;
    try {
        const response = await http.get(endpoint); 
        
        let dataArray = [];
        
        // ¡LA CORRECCIÓN CLAVE! Extraer del campo 'roles'
        if (response.data && Array.isArray(response.data.roles)) {
            dataArray = response.data.roles;
        } else {
             // Mantener la verificación por si hay otros formatos, pero 'roles' es la clave
             dataArray = response.data.data || response.data || [];
        }
        
        items.value = dataArray;
        console.log("Roles cargados:", items.value.length); // DEBUG

    } catch (err) {
        console.error("Error al obtener roles:", err);
        error.value = err.response?.data?.message || 'Error de conexión o permisos con el backend.';
    } finally {
        isLoading.value = false;
    }
};

// ----------------------------------------------------------------------
// --- MODAL Y FORMULARIO (Sin cambios) ---
// ----------------------------------------------------------------------

const resetForm = () => {
    currentItem.value = { id: null, name: '' };
    validationErrors.value = {};
};

const openCreateModal = () => {
    resetForm();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    resetForm();
    currentItem.value = { 
        id: item.id,
        name: item.name,
    }; 
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    resetForm();
};

// ----------------------------------------------------------------------
// --- CRUD: GUARDAR ROL (CREATE/UPDATE) (Sin cambios) ---
// ----------------------------------------------------------------------

const saveItem = async () => {
    isSaving.value = true;
    validationErrors.value = {};
    const isEditing = !!currentItem.value.id;
    
    try {
        let response;
        
        const data = { name: currentItem.value.name };

        if (isEditing) {
            response = await http.put(`${endpoint}/${currentItem.value.id}`, data);
        } else {
            response = await http.post(endpoint, data);
        }

        alert(response.data.message || `Rol "${currentItem.value.name}" guardado con éxito.`);
        closeModal();
        await fetchItems(); 

    } catch (err) {
        if (err.response && err.response.status === 422) {
            validationErrors.value = err.response.data.errors || {};
            document.querySelector('.modal-body').scrollTo(0, 0); 

        } else {
            const apiMessage = err.response?.data?.message || 'Error de conexión o servidor.';
            alert(`Error al guardar el rol: ${apiMessage}`);
        }
    } finally {
        isSaving.value = false;
    }
};

// ----------------------------------------------------------------------
// --- CRUD: ELIMINAR ROL (Sin cambios) ---
// ----------------------------------------------------------------------

const deleteItem = async (itemId, itemName) => {
    if (!confirm(`¿Estás seguro de eliminar el rol: "${itemName}" (ID: ${itemId})? Esto podría causar errores si hay usuarios asignados.`)) {
        return;
    }
    
    isDeleting[itemId] = true;
    
    try {
        const response = await http.delete(`${endpoint}/${itemId}`);
        
        alert(response.data.message || `Rol eliminado con éxito.`);
        
        await fetchItems(); // Recargar

    } catch (err) {
        console.error("Error al eliminar rol:", err);
        let msg = `No se pudo eliminar el rol.`;

        if (err.response && err.response.data && err.response.data.message) {
            msg = `Error: ${err.response.data.message}`;
        }
        
        alert(msg);
    } finally {
        isDeleting[itemId] = false;
    }
};


// --- LIFECYCLE HOOKS ---
onMounted(() => {
    fetchItems(); 
});
</script>

<style scoped>
/* Estilos necesarios para el modal */
.modal-open {
    overflow: hidden;
}
.modal-open .modal {
    overflow-x: hidden;
    overflow-y: auto;
}
.modal-backdrop.show {
    opacity: 0.5;
}
</style>