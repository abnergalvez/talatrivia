<template>
  <div class="container mt-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <button @click="router.back()" class="btn btn-secondary me-3">
          <i class="bi bi-arrow-left"></i> Volver a Trivias
        </button>
        <h3 class="text-dark d-inline-block fw-bold">
            Preguntas de Trivia #{{ triviaId }}
        </h3>
        <span v-if="triviaName" class="badge bg-primary ms-3 fs-6">{{ triviaName }}</span>
      </div>
      <button @click="openCreateQuestionModal" class="btn btn-success btn-lg">
        <i class="bi bi-plus-circle me-2"></i> Crear Nueva Pregunta
      </button>
    </div>

    <div v-if="levels.length" class="alert alert-light border shadow-sm">
        <i class="bi bi-info-circle-fill text-primary me-2"></i>
        Niveles Disponibles: 
        <span v-for="level in levels" :key="level.id" class="badge bg-primary me-2">
            {{ level.name }} (ID: {{ level.id }})
        </span>
    </div>

    <div v-if="isLoading" class="text-center p-5">
      <div class="spinner-border text-info" role="status"></div>
      <p class="mt-2 text-muted">Cargando preguntas y datos de la trivia...</p>
    </div>
    
    <div v-else-if="error" class="alert alert-danger" role="alert">
      Error al cargar preguntas: **{{ error }}**
    </div>

    <div v-else-if="questions.length" class="row">
        <div class="col-12">
            <div v-for="question in questions" :key="question.id" class="card shadow-sm mb-3">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 text-primary">
                        P. #{{ question.id }} 
                        (Nivel: <span class="fw-bold text-success">{{ question.level?.name || question.level_id }}</span> / Puntos: {{ question.level?.points || '?' }})
                        <!--<span v-if="question.time" class="badge bg-secondary ms-2">Tiempo: {{ question.time }}s</span>-->
                    </h5>
                    <div>
                        <button @click="openEditQuestionModal(question)" class="btn btn-sm btn-primary me-2">
                            <i class="bi bi-pencil-fill"></i> Editar
                        </button>
                        <button @click="deleteQuestion(question.id, question.description)" class="btn btn-sm btn-danger" :disabled="isDeleting[question.id]">
                             <span v-if="isDeleting[question.id]" class="spinner-border spinner-border-sm"></span>
                             <i v-else class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text fw-bold">{{ question.description }}</p> 

                    <h6 class="mt-3">Opciones:</h6>
                    <ul class="list-group">
                        <li v-for="option in question.options" :key="option.id" class="list-group-item d-flex justify-content-between align-items-center" :class="{ 'list-group-item-success': option.is_correct }">
                            <span :class="{ 'fw-bold': option.is_correct }">
                                {{ option.text }} 
                            </span>
                            <span v-if="option.is_correct" class="badge bg-success">Correcta</span>
                            <span v-else class="badge bg-secondary">Incorrecta</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div v-else class="alert alert-info text-center">
      Esta trivia aún no tiene preguntas.
    </div>

    <div v-if="isQuestionModalOpen" class="modal d-block modal-open" tabindex="-1">
      <div class="modal-dialog modal-xl">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ currentQuestion.id ? 'Editar Pregunta' : 'Crear Nueva Pregunta' }}</h5>
            <button type="button" class="btn-close" @click="closeQuestionModal"></button>
          </div>
          <form @submit.prevent="saveQuestion">
            <div class="modal-body modal-body-scroll">
              
              <div class="mb-3">
                <label for="questionContent" class="form-label">Contenido de la Pregunta</label>
                <textarea class="form-control" id="questionContent" v-model="currentQuestion.description" rows="3" required></textarea>
              </div>

              <!--<div class="mb-3">
                <label for="time" class="form-label">Tiempo Límite (segundos)</label>
                <input type="number" class="form-control" id="time" v-model="currentQuestion.time" placeholder="Opcional. Ej: 30" min="5" max="180">
              </div>-->

              <div class="mb-3">
                <label for="levelId" class="form-label">Nivel (Level ID)</label>
                <select class="form-select" id="levelId" v-model="currentQuestion.level_id" required>
                    <option :value="null" disabled>Selecciona un nivel</option>
                    <option v-for="level in levels" :key="level.id" :value="level.id">
                        {{ level.name }} (ID: {{ level.id }} - {{ level.points }} pts)
                    </option>
                </select>
                <small v-if="!levels.length" class="text-danger">Cargando niveles. Asegúrate de que los niveles existan en el sistema.</small>
              </div>

              <hr>
              
              <h5 class="mt-4 mb-3">Opciones  (max. 8)</h5>
              <div v-for="(option, index) in currentQuestion.options" :key="index" class="input-group mb-3">
                <span class="input-group-text">{{ index + 1 }}</span>
                <input type="text" class="form-control" v-model="option.text" required placeholder="Contenido de la opción"> 
                
                <div class="input-group-text">
                    <input 
                        class="form-check-input mt-0" 
                        type="radio" 
                        :name="'correct-option-' + currentQuestion.id" 
                        :checked="option.is_correct" 
                        @change="setCorrectOption(index)"
                    >
                    <label class="ms-2">Correcta</label>
                </div>

                <button 
                    v-if="currentQuestion.options.length > 2" 
                    type="button" 
                    class="btn btn-outline-danger" 
                    @click="removeOption(index)"
                >
                    <i class="bi bi-x-lg"></i>
                </button>
              </div>

              <div class="d-flex justify-content-between">
                <button 
                    type="button" 
                    class="btn btn-sm btn-outline-secondary" 
                    @click="addOption" 
                    :disabled="currentQuestion.options.length >= 8"
                >
                    <i class="bi bi-plus-circle me-1"></i> Añadir Opción
                </button>
                <div v-if="optionError" class="text-danger small">{{ optionError }}</div>
              </div>

            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" @click="closeQuestionModal">Cancelar</button>
              <button type="submit" class="btn btn-primary" :disabled="isSaving">
                <span v-if="isSaving" class="spinner-border spinner-border-sm me-2"></span>
                Guardar Pregunta
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div v-if="isQuestionModalOpen" class="modal-backdrop fade show"></div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import http from '@/http'; 

const props = defineProps({
    triviaId: {
        type: [String, Number],
        required: true
    }
});

const router = useRouter();

// --- ESTADOS LOCALES ---
const triviaName = ref('');
const questions = ref([]);
const levels = ref([]); // Los niveles se usarán para el <select> del modal.
const isLoading = ref(false);
const error = ref(null);

// CRUD Modal States
const isQuestionModalOpen = ref(false);
const currentQuestion = ref({
    id: null,
    // Cambiado de 'content' a 'description' para coincidir con el API
    description: '', 
    time: null, // Nuevo campo
    level_id: null,
    options: [] // [{ text: '', is_correct: false }, ...] (usamos 'text' y no 'content')
});
const isSaving = ref(false);
const isDeleting = ref({});
const optionError = ref(null);

// --- UTILIDADES ---

// Usamos 'text' para el contenido de la opción
const createEmptyOption = () => ({ text: '', is_correct: 0 }); 

// ----------------------------------------------------------------------
// --- FETCHING DE DATOS (CONSOLIDADO) ---
// ----------------------------------------------------------------------

// **Se elimina fetchTriviaName y se integra en fetchQuestions**
// **Se elimina fetchLevels y se integra en fetchQuestions, pero se mantiene la llamada separada si es necesario.**

const fetchLevels = async () => {
    try {
        // GET /api/levels (Esto es para asegurar que tengamos todos los niveles para el <select>)
        const response = await http.get('/levels'); 
        levels.value = response.data.levels || [];
    } catch (err) {
        console.error("Error al obtener niveles:", err);
    }
};

const fetchQuestions = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        // Endpoint: GET /api/trivias/{trivia_id}/questions
        const response = await http.get(`/trivias/${props.triviaId}/questions`); 
        
        // 1. OBTENER INFO DE LA TRIVIA
        if (response.data.trivia) {
            triviaName.value = response.data.trivia.name;
        }

        // 2. OBTENER PREGUNTAS
        // Mapear los datos para asegurar campos consistentes y booleans
        questions.value = (response.data.questions || []).map(q => ({
            ...q,
            // Usamos 'description' y 'time' según el ejemplo de API
            description: q.description,
            time: q.time, 
            options: (q.options || []).map(opt => ({
                 ...opt,
                 // Usamos 'text' y convertimos is_correct a boolean (o numérico 0/1 si el v-model lo prefiere, 
                 // pero el radio button con :value="true" funciona mejor con booleano puro)
                 is_correct: !!opt.is_correct, 
                 text: opt.text // Usamos 'text' según el ejemplo de API
            }))
        }));
        
    } catch (err) {
        console.error("Error al obtener preguntas:", err);
        error.value = err.response?.data?.message || 'No se pudieron cargar las preguntas o los datos de la trivia.';
    } finally {
        isLoading.value = false;
    }
};

// ----------------------------------------------------------------------
// --- MODAL DE PREGUNTAS ---
// ----------------------------------------------------------------------

const openCreateQuestionModal = () => {
    currentQuestion.value = {
        id: null,
        description: '', // Usar description
        time: null,
        level_id: levels.value.length > 0 ? levels.value[0].id : null, 
        options: [createEmptyOption(), createEmptyOption()],
    };
    optionError.value = null;
    isQuestionModalOpen.value = true;
};

const openEditQuestionModal = (question) => {
    // Clonar la pregunta y sus opciones. Aseguramos que is_correct sea boolean para el radio button.
    currentQuestion.value = { 
        ...question,
        options: question.options.map(opt => ({ ...opt, is_correct: !!opt.is_correct }))
    }; 
    optionError.value = null;
    isQuestionModalOpen.value = true;
};

const closeQuestionModal = () => {
    isQuestionModalOpen.value = false;
    currentQuestion.value = { id: null, description: '', time: null, level_id: null, options: [] };
};

// ----------------------------------------------------------------------
// --- GESTIÓN DE OPCIONES ---
// ----------------------------------------------------------------------

const addOption = () => {
    if (currentQuestion.value.options.length < 8) {
        currentQuestion.value.options.push(createEmptyOption());
    }
};

const removeOption = (index) => {
    if (currentQuestion.value.options.length > 2) {
        currentQuestion.value.options.splice(index, 1);
    }
};

const setCorrectOption = (selectedIndex) => {
    // 1. Recorrer todas las opciones
    currentQuestion.value.options.forEach((option, index) => {
        // 2. Establecer 'is_correct' en true solo para el índice seleccionado
        // y en false para todos los demás.
        option.is_correct = (index === selectedIndex);
    });
};

const validateOptions = () => {
    // Convertir a 0/1 si es necesario para el envío, pero validar con boolean
    const optionsForValidation = currentQuestion.value.options;

    const hasCorrectOption = optionsForValidation.some(opt => opt.is_correct);
    // Usamos 'text' para validar contenido
    const hasEmptyContent = optionsForValidation.some(opt => !opt.text || !String(opt.text).trim()); 
    
    if (hasEmptyContent) {
        optionError.value = 'Todas las opciones deben tener contenido.';
        return false;
    }
    
    const correctOptionsCount = optionsForValidation.filter(opt => opt.is_correct).length;
    if (correctOptionsCount !== 1) {
        optionError.value = 'Debe al menos haber una opción correcta.';
        return false;
    }

    optionError.value = null;
    return true;
};

// ----------------------------------------------------------------------
// --- CRUD: GUARDAR PREGUNTA ---
// ----------------------------------------------------------------------

const saveQuestion = async () => {
    if (!validateOptions()) {
        return;
    }

    isSaving.value = true;
    const isEditing = !!currentQuestion.value.id;
    
    try {
        let response;
        
        // Preparamos los datos para el backend
        const data = {
            description: currentQuestion.value.description, // Usar description
            time: currentQuestion.value.time, // Enviar tiempo
            level_id: currentQuestion.value.level_id,
            // Convertir 'is_correct' de boolean a 0 o 1 para el backend si es necesario
            options: currentQuestion.value.options.map(opt => ({
                text: opt.text,
                is_correct: opt.is_correct ? 1 : 0, 
                // Añadir el id de la opción si estamos editando
                ...(opt.id && { id: opt.id }) 
            })),
        };

        if (isEditing) {
            // PUT /api/questions/{id}
            response = await http.put(`/questions/${currentQuestion.value.id}`, data);
        } else {
            // POST /api/trivias/{trivia_id}/questions
            response = await http.post(`/trivias/${props.triviaId}/questions`, data);
        }

        alert(response.data.message || 'Pregunta guardada con éxito.');
        closeQuestionModal();
        await fetchQuestions(); // Recargar la lista

    } catch (err) {
        console.error("Error al guardar pregunta:", err);
        // Mostrar errores de validación si existen
        const apiMessage = err.response?.data?.message || err.response?.data?.error || 'Error de conexión.';
        alert(`Error al guardar la pregunta: ${apiMessage}`);
    } finally {
        isSaving.value = false;
    }
};

// ----------------------------------------------------------------------
// --- CRUD: ELIMINAR PREGUNTA ---
// ----------------------------------------------------------------------

const deleteQuestion = async (questionId, questionDescription) => {
    if (!confirm(`¿Estás seguro de eliminar la pregunta: "${questionDescription.substring(0, 40)}..."?`)) {
        return;
    }
    
    isDeleting.value[questionId] = true;
    
    try {
        // DELETE /api/questions/{id}
        const response = await http.delete(`/questions/${questionId}`);
        
        alert(response.data.message || `Pregunta eliminada con éxito.`);
        
        // Actualizar la lista localmente
        questions.value = questions.value.filter(q => q.id !== questionId);

    } catch (err) {
        console.error("Error al eliminar pregunta:", err);
        let msg = `No se pudo eliminar la pregunta.`;

        if (err.response && err.response.data && err.response.data.message) {
            msg = `Error: ${err.response.data.message}`;
        }
        
        alert(msg);
    } finally {
        isDeleting.value[questionId] = false;
    }
};


// --- LIFECYCLE HOOKS ---
onMounted(() => {
    // Mantenemos esta llamada separada para cargar los niveles disponibles, 
    // incluso si la trivia no tiene preguntas (así el <select> del modal se llena).
    fetchLevels(); 
    
    // Esta función ahora carga: TriviaName, Questions, y los datos de Level dentro de Questions.
    fetchQuestions(); 
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
.modal-body-scroll {
    /* Altura máxima (ajusta este valor si es necesario, vh = viewport height) */
    max-height: 75vh; 
    /* Habilita el scroll vertical cuando el contenido excede la altura */
    overflow-y: auto; 
}
</style>