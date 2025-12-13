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

    <div v-else-if="users.length" class="card shadow-sm">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead  class="table-dark">
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
                  <button @click="openEditModal(user)" class="btn btn-sm btn-primary me-2">
                    <i class="bi bi-pencil-fill"></i>
                  </button>
                  <button 
                    @click="deleteUser(user.id, user.name)" 
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

    <div v-else-if="!isLoading && !isRolesLoading" class="alert alert-info text-center">
      No hay usuarios registrados.
    </div>

    <div v-if="isModalOpen" class="modal d-block modal-open" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ currentForm.id ? 'Editar Usuario' : 'Crear Usuario' }}</h5>
            <button type="button" class="btn-close" @click="closeModal"></button>
          </div>
          <form @submit.prevent="saveUser" autocomplete="off">
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
                <label for="name" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="name" v-model="currentForm.name" required>
              </div>

              <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" v-model="currentForm.email" autocomplete="off" required >
              </div>

              <div class="mb-3">
                <label for="role" class="form-label">Rol</label>
                <p v-if="isRolesLoading" class="text-muted">Cargando roles...</p>

                <select v-else class="form-select" id="role" v-model="currentForm.role_id" required>
                    <option v-if="roles.length === 0" disabled value="">No hay roles disponibles</option>
                    
                    <option v-for="role in roles" :key="role.id" :value="role.id">
                        {{ role.name.charAt(0).toUpperCase() + role.name.slice(1) }} ({{ role.name }})
                    </option>
                </select>
              </div>

              <div class="mb-3">
                <label for="password" class="form-label">Contraseña {{ currentForm.id ? '(Dejar en blanco para no cambiar)' : '*' }}</label>
                <input type="password" class="form-control" id="password" v-model="currentForm.password" :required="!currentForm.id" autocomplete="new-password">
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeModal">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                Guardar Usuario
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

// --- ESTADOS DE DATOS ---
const users = ref([]);
const roles = ref([]); // NUEVO: Para guardar la lista dinámica de roles
const isLoading = ref(false); // Carga de usuarios
const isRolesLoading = ref(false); // NUEVO: Carga de roles
const error = ref(null);

// Paginación
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 10,
    total: 0,
});

// Modal y Formulario
const isModalOpen = ref(false);
const currentForm = ref({
    id: null,
    name: '',
    email: '',
    role_id: null, // Inicialmente null o el ID del rol 'player' si se conoce
    password: '',
});
const isSaving = ref(false);
const isDeleting = ref({});
const validationErrors = ref({}); 

// ----------------------------------------------------------------------
// --- FETCHING / OBTENER ROLES ---
// ----------------------------------------------------------------------

const fetchRoles = async () => {
    isRolesLoading.value = true;
    error.value = null; // Limpiamos el error global
    try {
        const response = await http.get(`/roles`); 
        
        // --- DEBUG CLAVE: Mira la consola del navegador aquí ---
        console.log("Respuesta completa de /api/roles:", response.data);
        
        let rolesArray = [];

        // Caso 1: Array directo (ideal)
        if (Array.isArray(response.data)) {
            rolesArray = response.data;
        } 
        // Caso 2: Array anidado (común en Laravel/Lumen: { roles: [...] } o { data: [...] })
        else if (response.data && Array.isArray(response.data.roles)) {
             rolesArray = response.data.roles;
        } 
        else if (response.data && Array.isArray(response.data.data)) {
             rolesArray = response.data.data;
        } 
        else {
            console.error("Estructura de roles inesperada. No se encontró un array en response.data.");
        }
        
        roles.value = rolesArray;
        
        // Si el formulario está vacío, inicializa role_id con un valor por defecto
        if (currentForm.value.role_id === null && roles.value.length > 0) {
             const defaultRole = roles.value.find(r => r.name === 'player') || roles.value[0];
             currentForm.value.role_id = defaultRole.id;
        }

    } catch (err) {
        console.error("Error crítico al obtener roles desde /api/roles:", err);
        // Muestra un error visible si la petición falla
        error.value = `Fallo al cargar los roles. Status: ${err.response?.status || 'N/A'}. ¿Token o ruta incorrecta?`;
    } finally {
        isRolesLoading.value = false;
    }
}

// ----------------------------------------------------------------------
// --- FETCHING / LISTADO DE USUARIOS ---
// ----------------------------------------------------------------------

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
        
        if (Array.isArray(response.data)) {
            users.value = response.data;
            // Manejo simple de paginación
            pagination.value.current_page = 1;
            pagination.value.last_page = 1;
            pagination.value.total = response.data.length;
        } 
        // ... (el fallback de paginación se mantiene por si el backend cambia) ...
        else {
            users.value = [];
        }


    } catch (err) {
        console.error("Error al obtener usuarios:", err);
        
        if (err.response && (err.response.status === 401 || err.response.status === 403)) {
            error.value = 'Acceso denegado. El token no es válido o no tiene permisos de administrador.';
        } else {
            error.value = err.response?.data?.message || 'Error de conexión con el backend (Servidor caído o CORS).';
        }
    } finally {
        isLoading.value = false;
    }
};

// ----------------------------------------------------------------------
// --- MODAL Y FORMULARIO ---
// ----------------------------------------------------------------------

const resetForm = () => {
    // Busca el ID del rol 'player' o usa el ID del primer rol si no existe 'player'
    const defaultRole = roles.value.find(r => r.name === 'player') || roles.value[0];

    currentForm.value = {
        id: null,
        name: '',
        email: '',
        role_id: defaultRole ? defaultRole.id : null, // Asignación dinámica
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
    
    // CRÍTICO: El ID del rol del usuario es user.role.id
    currentForm.value = { 
        id: user.id,
        name: user.name,
        email: user.email,
        role_id: user.role.id, // ¡Asignamos directamente el ID recibido en los datos del usuario!
        password: '', 
    }; 
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    resetForm();
};

// ----------------------------------------------------------------------
// --- CRUD: GUARDAR USUARIO (CREATE/UPDATE) ---
// ----------------------------------------------------------------------

const saveUser = async () => {
    isSaving.value = true;
    validationErrors.value = {};
    const isEditing = !!currentForm.value.id;
    
    try {
        let response;
        
        const data = {
            name: currentForm.value.name,
            email: currentForm.value.email,
            role_id: currentForm.value.role_id, // Se sigue enviando el ID
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
            document.querySelector('.modal-body').scrollTo(0, 0); 

        } else {
            const apiMessage = err.response?.data?.message || 'Error de conexión o servidor.';
            alert(`Error al guardar el usuario: ${apiMessage}`);
        }
    } finally {
        isSaving.value = false;
    }
};

// ----------------------------------------------------------------------
// --- CRUD: ELIMINAR USUARIO ---
// ----------------------------------------------------------------------

const deleteUser = async (userId, userName) => {
    if (!confirm(`¿Estás seguro de eliminar el usuario: "${userName}" (ID: ${userId})?`)) {
        return;
    }
    
    isDeleting.value[userId] = true;
    
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
        isDeleting.value[userId] = false;
    }
};


// --- LIFECYCLE HOOKS ---
onMounted(() => {
    // CRÍTICO: Llamamos a fetchRoles primero, luego fetchUsers.
    fetchRoles(); 
    fetchUsers(); 
});
</script>

<style scoped>
/* Estilos para que los modales se muestren correctamente */
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