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

    <RoleTable
      v-else-if="items.length"
      :items="items"
      :isDeleting="isDeleting.value"
      @edit="openEditModal"
      @delete="deleteItem"
    />

    <div v-else-if="!isLoading" class="alert alert-info text-center">
      No hay roles registrados.
    </div>

    <RoleModal
      v-if="isModalOpen"
      :item="currentItem"
      :isSaving="isSaving"
      :validationErrors="validationErrors"
      @close="closeModal"
      @save="saveItem"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '@/http'; 
import { useAuthStore } from '@/stores/auth'; 
import RoleTable from '@/components/admin/RoleTable.vue'; 
import RoleModal from '@/components/admin/RoleModal.vue'; 

const endpoint = '/roles';

const items = ref([]);
const isLoading = ref(false);
const error = ref(null);

const isModalOpen = ref(false);
const currentItem = ref({
    id: null,
    name: '',
});
const isSaving = ref(false);
const isDeleting = ref({});
const validationErrors = ref({}); 

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
        
        if (response.data && Array.isArray(response.data.roles)) {
            dataArray = response.data.roles;
        } else {
            dataArray = response.data.data || response.data || [];
        }
        
        items.value = dataArray;

    } catch (err) {
        error.value = err.response?.data?.message || 'Error de conexión o permisos con el backend.';
    } finally {
        isLoading.value = false;
    }
};

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
            const modalBody = document.querySelector('.modal-body');
            if(modalBody) {
                modalBody.scrollTo(0, 0); 
            }
        } else {
            const apiMessage = err.response?.data?.message || 'Error de conexión o servidor.';
            alert(`Error al guardar el rol: ${apiMessage}`);
        }
    } finally {
        isSaving.value = false;
    }
};

const deleteItem = async (itemId, itemName) => {
    if (!confirm(`¿Estás seguro de eliminar el rol: "${itemName}" (ID: ${itemId})? Esto podría causar errores si hay usuarios asignados.`)) {
        return;
    }
    
    isDeleting.value = { ...isDeleting.value, [itemId]: true };
    
    try {
        const response = await http.delete(`${endpoint}/${itemId}`);
        
        alert(response.data.message || `Rol eliminado con éxito.`);
        
        await fetchItems();

    } catch (err) {
        let msg = `No se pudo eliminar el rol.`;

        if (err.response && err.response.data && err.response.data.message) {
            msg = `Error: ${err.response.data.message}`;
        }
        
        alert(msg);
    } finally {
        isDeleting.value = { ...isDeleting.value, [itemId]: false };
    }
};


onMounted(() => {
    fetchItems(); 
});
</script>

<style scoped>
.modal-open {
    overflow: hidden;
}
.modal-open .modal {
    overflow-x: hidden;
    overflow-y: auto;
}
</style>