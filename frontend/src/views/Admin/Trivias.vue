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
      Error al cargar trivias: <strong>{{ error }}</strong>
    </div>

    <TriviaTable
      v-else-if="trivias.length"
      :trivias="trivias"
      :isDeleting="isDeleting.value"
      @edit="openEditModal"
      @questions="goToQuestionManagement"
      @assign="openAssignmentModal"
      @delete="deleteTrivia"
    />

    <div v-else class="alert alert-info text-center">
      No se encontraron trivias. ¡Crea la primera!
    </div>
    
    <TriviaFormModal
      v-if="isFormModalOpen"
      :item="currentTrivia"
      :isSaving="isSaving"
      @close="closeFormModal"
      @save="saveTrivia"
      @update:name="currentTrivia.name = $event"
      @update:description="currentTrivia.description = $event"
      @update:is_active="currentTrivia.is_active = $event"
    />

    <TriviaAssignmentModal
      v-if="isAssignmentModalOpen"
      :trivia="currentTrivia"
      :isLoading="isAssignmentLoading"
      :assignedUsers="assignedUsers"
      :availableUsers="availableUsers"
      :selectedAvailableEmails="selectedAvailableUsers"
      :selectedAssignedEmails="selectedAssignedUsers"
      :isAssigning="isAssigning"
      :action="assignmentAction"
      :error="assignmentError"
      :success="assignmentSuccess"
      @close="closeAssignmentModal"
      @assign="assignSelectedUsers"
      @unassign="unassignSelectedUsers"
      @update:selectedAvailableEmails="selectedAvailableUsers = $event"
      @update:selectedAssignedEmails="selectedAssignedUsers = $event"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import http from '@/http'; 
import TriviaTable from '@/components/admin/TriviaTable.vue'; 
import TriviaFormModal from '@/components/admin/TriviaFormModal.vue'; 
import TriviaAssignmentModal from '@/components/admin/TriviaAssignmentModal.vue'; 

const router = useRouter();

const trivias = ref([]);
const isLoading = ref(false);
const error = ref(null);

const isFormModalOpen = ref(false);
const currentTrivia = ref({
    id: null,
    name: '',
    description: '',
    is_active: true,
});
const isSaving = ref(false);
const isDeleting = ref({}); 

const isAssignmentModalOpen = ref(false);
const isAssignmentLoading = ref(false);
const assignedUsers = ref([]);
const availableUsers = ref([]);
const selectedAvailableUsers = ref([]); 
const selectedAssignedUsers = ref([]); 
const isAssigning = ref(false);
const assignmentAction = ref(null); 
const assignmentError = ref(null);
const assignmentSuccess = ref(null);

const fetchTrivias = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await http.get('/trivias'); 
    
    if (response.data && response.data.trivias) {
        trivias.value = response.data.trivias.map(t => ({
            ...t,
            questions_count: t.questions_count || 0,
            users_count: t.users_count || 0
        }));
    } else {
        throw new Error('Formato de respuesta inválido.');
    }
    
  } catch (err) {
    if (err.response && err.response.status === 403) {
        error.value = 'Acceso Denegado. Solo los administradores pueden ver este contenido.';
    } else {
        error.value = 'No se pudieron cargar las trivias.';
    }
  } finally {
    isLoading.value = false;
  }
};

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
            response = await http.put(`/trivias/${currentTrivia.value.id}`, data);
        } else {
            response = await http.post('/trivias', data);
        }

        alert(response.data.message || 'Trivia guardada con éxito.');
        closeFormModal();
        await fetchTrivias(); 

    } catch (err) {
        alert(`Error al guardar la trivia: ${err.response?.data?.message || 'Error de conexión.'}`);
    } finally {
        isSaving.value = false;
    }
};

const deleteTrivia = async (triviaId, triviaName) => {
    if (!confirm(`¿Estás seguro de que quieres eliminar la trivia: "${triviaName}"? \nADVERTENCIA: Fallará si tiene respuestas de juego registradas.`)) {
        return;
    }
    
    isDeleting.value = { ...isDeleting.value, [triviaId]: true };
    
    try {
        const response = await http.delete(`/trivias/${triviaId}`);
        
        alert(response.data.message || `Trivia ${triviaName} eliminada con éxito.`);
        
        trivias.value = trivias.value.filter(t => t.id !== triviaId);

    } catch (err) {
        let msg = `No se pudo eliminar la trivia "${triviaName}".`;

        if (err.response && err.response.data && err.response.data.message) {
            msg = `Error: ${err.response.data.message}`;
        }
        
        alert(msg);
    } finally {
        isDeleting.value = { ...isDeleting.value, [triviaId]: false };
    }
};

const fetchAssignmentData = async (triviaId) => {
    isAssignmentLoading.value = true;
    assignmentError.value = null;
    assignmentSuccess.value = null;

    try {
        const [allUsersResponse, triviaDetailsResponse] = await Promise.all([
            http.get('/users'),
            http.get(`/trivias/${triviaId}`)
        ]);
            
        const allSystemUsers = (allUsersResponse.data || []);
            
        const assignedUsersList = triviaDetailsResponse.data.trivia?.users || [];
        
        const assignedEmails = new Set(assignedUsersList.map(u => u.email));
        
        availableUsers.value = allSystemUsers.filter(user => !assignedEmails.has(user.email));
        assignedUsers.value = assignedUsersList;
        
        selectedAvailableUsers.value = [];
        selectedAssignedUsers.value = [];

    } catch (err) {
        assignmentError.value = err.response?.data?.message || 'Error al cargar la lista de usuarios.';
    } finally {
        isAssignmentLoading.value = false;
    }
};

const openAssignmentModal = async (trivia) => {
    currentTrivia.value = trivia; 
    isAssignmentModalOpen.value = true;
    
    await fetchAssignmentData(trivia.id);
};

const closeAssignmentModal = () => {
    isAssignmentModalOpen.value = false;
    currentTrivia.value = { id: null, name: '', description: '', is_active: true };
};

const assignSelectedUsers = async () => {
    if (selectedAvailableUsers.value.length > 0) {
        await handleAssignmentAction('assign', selectedAvailableUsers.value);
    }
};

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
            ? `/trivias/${currentTrivia.value.id}/assign` 
            : `/trivias/${currentTrivia.value.id}/unassign`; 
        
        const data = { emails: emails }; 
        
        const response = await http.post(endpoint, data);
        
        assignmentSuccess.value = response.data.message;

        await fetchAssignmentData(currentTrivia.value.id);
        
        await fetchTrivias(); 

    } catch (err) {
        let errorMsg = `Error al ejecutar la acción de ${action}.`;
        if (err.response && err.response.data) {
            if (err.response.data.error) {
                errorMsg = err.response.data.error;
            } 
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
    router.push({ name: 'admin.questions', params: { triviaId: triviaId } });
};

onMounted(() => {
    fetchTrivias();
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