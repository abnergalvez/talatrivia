<template>
  <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="text-dark fw-bold">Usuarios</h3>
      <button @click="openCreateModal" class="btn btn-success btn-lg">
        <i class="bi bi-person-plus-fill me-2"></i> Crear Nuevo
      </button>
    </div>

    <div v-if="error" class="alert alert-danger" role="alert">
      Error: <strong>{{ error }}</strong>
    </div>

    <div v-if="isLoading || isRolesLoading" class="text-center p-5">
      <div class="spinner-border text-info" role="status"></div>
      <p class="mt-2 text-muted">Cargando {{ isLoading ? 'usuarios' : 'roles' }}...</p>
    </div>

    <UserTable
      v-else-if="users.length"
      :users="users"
      :isDeleting="isDeleting"
      @edit="openEditModal"
      @delete="deleteUser"
    />

    <div v-else-if="!isLoading && !isRolesLoading" class="alert alert-info text-center">
      No hay usuarios registrados.
    </div>

    <UserModal
      v-if="isModalOpen"
      :form="currentForm"
      :roles="roles"
      :isSaving="isSaving"
      :isRolesLoading="isRolesLoading"
      :validationErrors="validationErrors"
      @close="closeModal"
      @save="saveUser"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import http from '@/http'; 
import { useAuthStore } from '@/stores/auth'; 
import UserTable from '@/components/admin/UserTable.vue'; 
import UserModal from '@/components/admin/UserModal.vue'; 

const users = ref([]);
const roles = ref([]);
const isLoading = ref(false);
const isRolesLoading = ref(false);
const error = ref(null);

const isModalOpen = ref(false);
const currentForm = ref({
    id: null,
    name: '',
    email: '',
    role_id: null,
    password: '',
});
const isSaving = ref(false);
const isDeleting = ref({});
const validationErrors = ref({}); 


const fetchRoles = async () => {
    isRolesLoading.value = true;
    error.value = null;
    try {
        const response = await http.get(`/roles`); 
        
        let rolesArray = [];

        if (Array.isArray(response.data)) {
            rolesArray = response.data;
        } 
        else if (response.data && Array.isArray(response.data.roles)) {
             rolesArray = response.data.roles;
        } 
        else if (response.data && Array.isArray(response.data.data)) {
             rolesArray = response.data.data;
        } 
        
        roles.value = rolesArray;
        
        if (currentForm.value.role_id === null && roles.value.length > 0) {
              const defaultRole = roles.value.find(r => r.name === 'player') || roles.value[0];
              currentForm.value.role_id = defaultRole.id;
        }

    } catch (err) {
        console.error("Error crítico al obtener roles desde /api/roles:", err);
        error.value = `Fallo al cargar los roles. Status: ${err.response?.status || 'N/A'}.`;
    } finally {
        isRolesLoading.value = false;
    }
}


const fetchUsers = async (page = 1) => {
    const authStore = useAuthStore();
    
    if (!authStore.token) {
        error.value = 'Acceso denegado. No se encontró el Token de Administrador.';
        isLoading.value = false;
        return; 
    }
    
    isLoading.value = true;
    error.value = null;
    try {
        const response = await http.get(`/users?page=${page}`); 
        
        if (response.data && Array.isArray(response.data.data)) {
            users.value = response.data.data;
        } else if (Array.isArray(response.data)) {
            users.value = response.data;
        } else {
            users.value = [];
        }

    } catch (err) {
        console.error("Error al obtener usuarios:", err);
        
        if (err.response && (err.response.status === 401 || err.response.status === 403)) {
            error.value = 'Acceso denegado. El token no es válido o no tiene permisos de administrador.';
        } else {
            error.value = err.response?.data?.message || 'Error de conexión con el backend.';
        }
    } finally {
        isLoading.value = false;
    }
};


const resetForm = () => {
    const defaultRole = roles.value.find(r => r.name === 'player') || roles.value[0];

    currentForm.value = {
        id: null,
        name: '',
        email: '',
        role_id: defaultRole ? defaultRole.id : null,
        password: '',
    };
    validationErrors.value = {};
};

const openCreateModal = () => {
    resetForm();
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    resetForm();
    
    currentForm.value = { 
        id: user.id,
        name: user.name,
        email: user.email,
        role_id: user.role.id,
        password: '', 
    }; 
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    resetForm();
};

const saveUser = async () => {
    isSaving.value = true;
    validationErrors.value = {};
    const isEditing = !!currentForm.value.id;
    
    try {
        let response;
        
        const data = {
            name: currentForm.value.name,
            email: currentForm.value.email,
            role_id: currentForm.value.role_id, 
            ...(currentForm.value.password && { password: currentForm.value.password }),
            ...(currentForm.value.password && isEditing && { password_confirmation: currentForm.value.password }),
        };

        if (isEditing) {
            response = await http.put(`/users/${currentForm.value.id}`, data);
        } else {
            response = await http.post(`/users`, data);
        }

        alert(response.data.message || 'Usuario guardado con éxito.');
        closeModal();
        await fetchUsers(1); 

    } catch (err) {
        if (err.response && err.response.status === 422) {
            validationErrors.value = err.response.data.errors || {};
            const modalBody = document.querySelector('.modal-body');
            if(modalBody) {
                modalBody.scrollTo(0, 0); 
            }

        } else {
            const apiMessage = err.response?.data?.message || 'Error de conexión o servidor.';
            alert(`Error al guardar el usuario: ${apiMessage}`);
        }
    } finally {
        isSaving.value = false;
    }
};


const deleteUser = async (userId, userName) => {
    if (!confirm(`¿Estás seguro de eliminar el usuario: "${userName}" (ID: ${userId})?`)) {
        return;
    }
    
    isDeleting.value = { ...isDeleting.value, [userId]: true };
    
    try {
        const response = await http.delete(`/users/${userId}`);
        
        alert(response.data.message || `Usuario eliminado con éxito.`);
        
        await fetchUsers(1);

    } catch (err) {
        console.error("Error al eliminar usuario:", err);
        let msg = `No se pudo eliminar el usuario.`;

        if (err.response && err.response.data && err.response.data.message) {
            msg = `Error: ${err.response.data.message}`;
        }
        
        alert(msg);
    } finally {
        isDeleting.value = { ...isDeleting.value, [userId]: false };
    }
};


onMounted(() => {
    fetchRoles(); 
    fetchUsers(); 
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