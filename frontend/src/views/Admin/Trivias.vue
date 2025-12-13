<template>
  <div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="text-dark fw-bold">Trivias</h3>
      <button @click="openCreateModal" class="btn btn-success btn-lg">
        <i class="bi bi-plus-circle me-2"></i> Crear Nueva
      </button>
    </div>

    <div v-if="isLoading" class="text-center p-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando...</span>
      </div>
      <p class="mt-2 text-muted">Cargando listado de trivias...</p>
    </div>
    
    <div v-else-if="error" class="alert alert-danger" role="alert">
      Error al cargar trivias: **{{ error }}**
    </div>

    <div v-else-if="trivias.length" class="table-responsive">
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
                @click="openEditModal(trivia)" 
                class="btn btn-sm btn-primary me-2" 
                title="Editar Trivia"
              >
                <i class="bi bi-pencil-fill"></i>
              </button>

              <button 
                @click="goToQuestionManagement(trivia.id)" 
                class="btn btn-sm btn-info text-white me-2" 
                title="Gestionar Preguntas y Opciones"
              >
                <i class="bi bi-patch-question-fill"></i> ({{ trivia.questions_count }})
              </button>

              <button 
                @click="openAssignmentModal(trivia)" 
                class="btn btn-sm btn-warning me-2" 
                title="Asignar/Desasignar Usuarios"
              >
                <i class="bi bi-person-lines-fill"></i>
              </button>
              <button 
                @click="deleteTrivia(trivia.id, trivia.name)" 
                class="btn btn-sm btn-danger" 
                title="Eliminar Trivia"
                :disabled="isDeleting[trivia.id]"
              >
                <span v-if="isDeleting[trivia.id]" class="spinner-border spinner-border-sm"></span>
                <i v-else class="bi bi-trash-fill"></i>
              </button>
              
              </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="alert alert-info text-center">
      No se encontraron trivias. ¡Crea la primera!
    </div>
    
    <div v-if="isFormModalOpen" class="modal d-block modal-open" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ currentTrivia.id ? 'Editar Trivia' : 'Crear Nueva Trivia' }}</h5>
            <button type="button" class="btn-close" @click="closeFormModal"></button>
          </div>
          <form @submit.prevent="saveTrivia">
            <div class="modal-body">
              
              <div class="mb-3">
                <label for="name" class="form-label">Nombre</label>
                <input type="text" class="form-control" id="name" v-model="currentTrivia.name" required>
              </div>

              <div class="mb-3">
                <label for="description" class="form-label">Descripción</label>
                <textarea class="form-control" id="description" v-model="currentTrivia.description" rows="3" required></textarea>
              </div>

              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="isActive" v-model="currentTrivia.is_active">
                <label class="form-check-label" for="isActive">Activa</label>
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeFormModal">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                Guardar
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div v-if="isFormModalOpen" class="modal-backdrop fade show"></div>


    <div v-if="isAssignmentModalOpen" class="modal d-block modal-open" tabindex="-1">
      <div class="modal-dialog modal-lg"> <div class="modal-content">
          <div class="modal-header bg-warning text-dark">
            <h5 class="modal-title">Gestión de Usuarios para: {{ currentTrivia.name }}</h5>
            <button type="button" class="btn-close" @click="closeAssignmentModal"></button>
          </div>
          <div class="modal-body">
            <p class="text-muted">Gestiona los usuarios asignados a la trivia: <strong>{{ currentTrivia.name }}</strong></p>
            
            <div v-if="isAssignmentLoading" class="text-center p-4">
                <div class="spinner-border text-warning" role="status"></div>
                <p class="mt-2 text-warning">Cargando listas de usuarios...</p>
            </div>

            <div v-else class="row">
                <div class="col-6">
                    <h6 class="text-danger">Usuarios Asignados ({{ assignedUsers.length }})</h6>
                    <p class="text-muted small">Selecciona usuarios de aquí para **desasignar**.</p>
                    <select multiple class="form-select" size="10" v-model="selectedAssignedUsers">
                        <option v-for="user in assignedUsers" :key="user.id" :value="user.email">
                            {{ user.name }} ({{ user.email }})
                            <span v-if="user.role_id === 1" class="text-muted fw-bold">(Admin)</span>
                            <span v-else-if="user.role_id === 2" class="text-muted fw-bold">(Player)</span>
                            <span v-else class="text-muted fw-bold">(Rol #{{ user.role_id }})</span>
                        </option>
                    </select>
                    <button 
                        @click="unassignSelectedUsers" 
                        class="btn btn-sm btn-outline-danger w-100 mt-2" 
                        :disabled="isAssigning || selectedAssignedUsers.length === 0"
                    >
                        <span v-if="isAssigning && assignmentAction === 'unassign'" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-person-dash-fill me-1"></i> Desasignar Seleccionados
                    </button>
                </div>

                <div class="col-6">
                    <h6 class="text-success">Usuarios Disponibles ({{ availableUsers.length }})</h6>
                    <p class="text-muted small">Selecciona usuarios de aquí para **asignar**.</p>
                    <select multiple class="form-select" size="10" v-model="selectedAvailableUsers">
                        <option v-for="user in availableUsers" :key="user.id" :value="user.email">
                            {{ user.name }} ({{ user.email }})
                            <span v-if="user.role_id === 1" class="text-muted fw-bold">(Admin)</span>
                            <span v-else-if="user.role_id === 2" class="text-muted fw-bold">(Player)</span>
                            <span v-else class="text-muted fw-bold">(Rol #{{ user.role_id }})</span>
                        </option>
                    </select>
                    <button 
                        @click="assignSelectedUsers" 
                        class="btn btn-sm btn-success w-100 mt-2" 
                        :disabled="isAssigning || selectedAvailableUsers.length === 0"
                    >
                        <span v-if="isAssigning && assignmentAction === 'assign'" class="spinner-border spinner-border-sm me-2"></span>
                        <i v-else class="bi bi-person-plus-fill me-1"></i> Asignar Seleccionados
                    </button>
                </div>
            </div>
            
            <div v-if="assignmentError" class="alert alert-danger small mt-3">{{ assignmentError }}</div>
            <div v-if="assignmentSuccess" class="alert alert-success small mt-3">{{ assignmentSuccess }}</div>
            
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeAssignmentModal" :disabled="isAssigning">Cerrar</button>
          </div>
        </div>
      </div>
    </div>
    <div v-if="isAssignmentModalOpen" class="modal-backdrop fade show"></div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import http from '@/http'; 

const router = useRouter();
// --- ESTADOS LOCALES ---
const trivias = ref([]);
const isLoading = ref(false);
const error = ref(null);

// CRUD Modal States
const isFormModalOpen = ref(false);
const currentTrivia = ref({
    id: null,
    name: '',
    description: '',
    is_active: true,
});
const isSaving = ref(false);

// Deleting State
const isDeleting = ref({}); 

// Assignment Modal States
const isAssignmentModalOpen = ref(false);
const isAssignmentLoading = ref(false);
const assignedUsers = ref([]);
const availableUsers = ref([]);
const selectedAvailableUsers = ref([]); // Emails para asignar
const selectedAssignedUsers = ref([]);  // Emails para desasignar
const isAssigning = ref(false);
const assignmentAction = ref(null); // 'assign' or 'unassign'
const assignmentError = ref(null);
const assignmentSuccess = ref(null);


// --- FETCHING DE DATOS (Listar Trivias) ---
const fetchTrivias = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    // GET /api/trivias
    const response = await http.get('/trivias'); 
    
    if (response.data && response.data.trivias) {
        // Asegurar que el conteo de users y questions exista para evitar errores
        trivias.value = response.data.trivias.map(t => ({
            ...t,
            questions_count: t.questions_count || 0,
            users_count: t.users_count || 0
        }));
    } else {
        throw new Error('Formato de respuesta inválido.');
    }
    
  } catch (err) {
    console.error("Error al obtener trivias:", err);
    if (err.response && err.response.status === 403) {
         error.value = 'Acceso Denegado. Solo los administradores pueden ver este contenido.';
    } else {
         error.value = 'No se pudieron cargar las trivias.';
    }
  } finally {
    isLoading.value = false;
  }
};


// ----------------------------------------------------------------------
// --- CRUD: CREAR / EDITAR TRIVIA ---
// ----------------------------------------------------------------------

const openCreateModal = () => {
    currentTrivia.value = {
        id: null,
        name: '',
        description: '',
        is_active: true,
    };
    isFormModalOpen.value = true;
};

const openEditModal = (trivia) => {
    // Clonar la trivia para que el modal no edite el estado de la lista directamente
    currentTrivia.value = { ...trivia }; 
    isFormModalOpen.value = true;
};

const closeFormModal = () => {
    isFormModalOpen.value = false;
    currentTrivia.value = { id: null, name: '', description: '', is_active: true };
};

const saveTrivia = async () => {
    isSaving.value = true;
    const isEditing = !!currentTrivia.value.id;
    
    try {
        let response;
        const data = {
            name: currentTrivia.value.name,
            description: currentTrivia.value.description,
            is_active: currentTrivia.value.is_active,
        };

        if (isEditing) {
            // PUT /api/trivias/{id}
            response = await http.put(`/trivias/${currentTrivia.value.id}`, data);
        } else {
            // POST /api/trivias
            response = await http.post('/trivias', data);
        }

        alert(response.data.message || 'Trivia guardada con éxito.');
        closeFormModal();
        await fetchTrivias(); // Recargar la lista

    } catch (err) {
        console.error("Error al guardar trivia:", err);
        alert(`Error al guardar la trivia: ${err.response?.data?.message || 'Error de conexión.'}`);
    } finally {
        isSaving.value = false;
    }
};

// ----------------------------------------------------------------------
// --- CRUD: ELIMINAR TRIVIA ---
// ----------------------------------------------------------------------

const deleteTrivia = async (triviaId, triviaName) => {
    if (!confirm(`¿Estás seguro de que quieres eliminar la trivia: "${triviaName}"? \nADVERTENCIA: Fallará si tiene respuestas de juego registradas.`)) {
        return;
    }
    
    isDeleting.value[triviaId] = true;
    
    try {
        // DELETE /api/trivias/{id}
        const response = await http.delete(`/trivias/${triviaId}`);
        
        alert(response.data.message || `Trivia ${triviaName} eliminada con éxito.`);
        
        // Actualizar la lista localmente
        trivias.value = trivias.value.filter(t => t.id !== triviaId);

    } catch (err) {
        console.error("Error al eliminar trivia:", err);
        let msg = `No se pudo eliminar la trivia "${triviaName}".`;

        if (err.response && err.response.data && err.response.data.message) {
            msg = `Error: ${err.response.data.message}`;
        }
        
        alert(msg);
    } finally {
        isDeleting.value[triviaId] = false;
    }
};


// ----------------------------------------------------------------------
// --- ASIGNACIÓN / DESASIGNACIÓN DE USUARIOS ---
// ----------------------------------------------------------------------

const fetchAssignmentData = async (triviaId) => {
    isAssignmentLoading.value = true;
    assignmentError.value = null;

    try {
        // 1. Fetch all users and trivia details concurrently
        const [allUsersResponse, triviaDetailsResponse] = await Promise.all([
            http.get('/users'), // GET /api/users
            http.get(`/trivias/${triviaId}`) // GET /api/trivias/{trivia_id} (incluye 'users')
        ]);
        
        // MODIFICACIÓN: Se incluyen TODOS los usuarios del sistema, sin importar su role_id.
        const allSystemUsers = (allUsersResponse.data || []);
            
        const assignedUsersList = triviaDetailsResponse.data.trivia?.users || [];
        
        // 2. Compute available users (set difference)
        const assignedEmails = new Set(assignedUsersList.map(u => u.email));
        
        availableUsers.value = allSystemUsers.filter(user => !assignedEmails.has(user.email));
        assignedUsers.value = assignedUsersList;
        
        // Limpiamos las selecciones
        selectedAvailableUsers.value = [];
        selectedAssignedUsers.value = [];

    } catch (err) {
        console.error("Error al cargar datos de asignación:", err);
        assignmentError.value = err.response?.data?.message || 'Error al cargar la lista de usuarios.';
    } finally {
        isAssignmentLoading.value = false;
    }
};

const openAssignmentModal = async (trivia) => {
    currentTrivia.value = trivia; 
    isAssignmentModalOpen.value = true;
    assignmentError.value = null;
    assignmentSuccess.value = null;
    
    await fetchAssignmentData(trivia.id);
};

const closeAssignmentModal = () => {
    isAssignmentModalOpen.value = false;
    currentTrivia.value = { id: null, name: '', description: '', is_active: true };
};

// Wrapper para asignar
const assignSelectedUsers = async () => {
    if (selectedAvailableUsers.value.length > 0) {
        await handleAssignmentAction('assign', selectedAvailableUsers.value);
    }
};

// Wrapper para desasignar
const unassignSelectedUsers = async () => {
    if (selectedAssignedUsers.value.length > 0) {
        await handleAssignmentAction('unassign', selectedAssignedUsers.value);
    }
};


const handleAssignmentAction = async (action, emails) => {
    
    isAssigning.value = true;
    assignmentAction.value = action;
    assignmentError.value = null;
    assignmentSuccess.value = null;

    try {
        const endpoint = action === 'assign' 
            ? `/trivias/${currentTrivia.value.id}/assign`  // POST /api/trivias/{id}/assign
            : `/trivias/${currentTrivia.value.id}/unassign`; // POST /api/trivias/{id}/unassign
        
        const data = { emails: emails }; // Enviamos la lista de emails seleccionados
        
        const response = await http.post(endpoint, data);
        
        // Mensaje de éxito
        assignmentSuccess.value = response.data.message;

        // Re-fetch data for the modal to update the lists
        await fetchAssignmentData(currentTrivia.value.id);
        
        // Recargar la lista principal para actualizar 'Usuarios Asignados' en la tabla
        await fetchTrivias(); 

    } catch (err) {
        console.error(`Error al ${action} usuarios:`, err);
        
        // --- MANEJO DE ERRORES MEJORADO (Incluye error específico de API) ---
        let errorMsg = `Error al ejecutar la acción de ${action}.`;
        if (err.response && err.response.data) {
            // Priorizamos 'error' (ej. "Some users cannot be unassigned...")
            if (err.response.data.error) {
                errorMsg = err.response.data.error;
            } 
            // Si no hay 'error', usamos 'message' (formato Laravel/genérico)
            else if (err.response.data.message) {
                errorMsg = err.response.data.message;
            }
        }
        
        assignmentError.value = errorMsg;
        assignmentSuccess.value = null;
    } finally {
        isAssigning.value = false;
    }
};

const goToQuestionManagement = (triviaId) => {
    // Redirige a la nueva ruta /admin/trivias/:id/questions
    router.push({ name: 'admin.questions', params: { triviaId: triviaId } });
};

// --- LIFECYCLE HOOKS ---
onMounted(() => {
  fetchTrivias();
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
    opacity: 0.5; /* Default Bootstrap backdrop opacity */
}
.table th {
    font-weight: 700;
}
</style>