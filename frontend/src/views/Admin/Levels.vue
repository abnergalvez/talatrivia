<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="text-dark fw-bold">Niveles</h3>
      <button @click="openCreateModal" class="btn btn-success btn-lg">
        <i class="bi bi-plus-circle-fill me-2"></i> Crear Nuevo
      </button>
    </div>

    <div v-if="error" class="alert alert-danger" role="alert">
      Error: <strong>{{ error }}</strong>
    </div>

    <div v-if="isLoading" class="text-center p-5">
      <div class="spinner-border text-info" role="status"></div>
      <p class="mt-2 text-muted">Cargando niveles...</p>
    </div>

    <LevelTable
      v-else-if="items.length"
      :items="items"
      :isDeleting="isDeleting"
      @edit="openEditModal"
      @delete="deleteItem"
    />

    <div v-else-if="!isLoading" class="alert alert-info text-center">
      No hay niveles registrados.
    </div>

    <LevelModal
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
import LevelTable from '@/components/admin/LevelTable.vue'; 
import LevelModal from '@/components/admin/LevelModal.vue'; 

const endpoint = '/levels';

const items = ref([]);
const isLoading = ref(false);
const error = ref(null);

const isModalOpen = ref(false);
const currentItem = ref({
    id: null,
    name: '',
    points: 0, 
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
        
        if (response.data && Array.isArray(response.data.levels)) {
            dataArray = response.data.levels;
        } else {
             dataArray = response.data.data || response.data || [];
        }
        
        items.value = dataArray;

    } catch (err) {
        console.error("Error al obtener niveles:", err);
        error.value = err.response?.data?.message || 'Error de conexión o permisos con el backend.';
    } finally {
        isLoading.value = false;
    }
};

const resetForm = () => {
    currentItem.value = { id: null, name: '', points: 0 };
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
        points: item.points, 
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
        
        const data = { 
            name: currentItem.value.name,
            points: currentItem.value.points,
        };

        if (isEditing) {
            response = await http.put(`${endpoint}/${currentItem.value.id}`, data);
        } else {
            response = await http.post(endpoint, data);
        }

        alert(response.data.message || `Nivel "${currentItem.value.name}" guardado con éxito.`);
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
            alert(`Error al guardar el nivel: ${apiMessage}`);
        }
    } finally {
        isSaving.value = false;
    }
};

const deleteItem = async (itemId, itemName) => {
    if (!confirm(`¿Estás seguro de eliminar el nivel: "${itemName}" (ID: ${itemId})? Esto podría afectar la experiencia de los jugadores.`)) {
        return;
    }
    
    isDeleting.value = { ...isDeleting.value, [itemId]: true };
    
    try {
        const response = await http.delete(`${endpoint}/${itemId}`);
        
        alert(response.data.message || `Nivel eliminado con éxito.`);
        
        await fetchItems();

    } catch (err) {
        console.error("Error al eliminar nivel:", err);
        let msg = `No se pudo eliminar el nivel.`;

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