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
      Error al cargar preguntas: <strong>{{ error }}</strong>
    </div>

    <QuestionList
        v-else-if="questions.length"
        :questions="questions"
        :isDeleting="isDeleting"
        @edit="openEditQuestionModal"
        @delete="deleteQuestion"
    />

    <div v-else class="alert alert-info text-center">
      Esta trivia aún no tiene preguntas.
    </div>

    <QuestionFormModal
      v-if="isQuestionModalOpen"
      :item="currentQuestion"
      :levels="levels"
      :isSaving="isSaving"
      :optionError="optionError"
      @close="closeQuestionModal"
      @save="saveQuestion"
      @update:description="currentQuestion.description = $event"
      @update:level_id="currentQuestion.level_id = $event"
      @update-option-text="updateOptionText"
      @add-option="addOption"
      @remove-option="removeOption"
      @set-correct-option="setCorrectOption"
    />

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import http from '@/http'; 
import QuestionList from '@/components/admin/QuestionList.vue'; 
import QuestionFormModal from '@/components/admin/QuestionFormModal.vue'; 

const props = defineProps({
    triviaId: {
        type: [String, Number],
        required: true
    }
});

const router = useRouter();

const triviaName = ref('');
const questions = ref([]);
const levels = ref([]); 
const isLoading = ref(false);
const error = ref(null);

const isQuestionModalOpen = ref(false);
const currentQuestion = ref({
    id: null,
    description: '', 
    time: null, 
    level_id: null,
    options: [] 
});
const isSaving = ref(false);
const isDeleting = ref({});
const optionError = ref(null);

const createEmptyOption = () => ({ text: '', is_correct: false }); 

const fetchLevels = async () => {
    try {
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
        const response = await http.get(`/trivias/${props.triviaId}/questions`); 
        
        if (response.data.trivia) {
            triviaName.value = response.data.trivia.name;
        }

        questions.value = (response.data.questions || []).map(q => ({
            ...q,
            description: q.description,
            time: q.time, 
            options: (q.options || []).map(opt => ({
               ...opt,
               is_correct: !!opt.is_correct, 
               text: opt.text
            }))
        }));
        
    } catch (err) {
        error.value = err.response?.data?.message || 'No se pudieron cargar las preguntas o los datos de la trivia.';
    } finally {
        isLoading.value = false;
    }
};

const openCreateQuestionModal = () => {
    currentQuestion.value = {
        id: null,
        description: '', 
        time: null,
        level_id: levels.value.length > 0 ? levels.value[0].id : null, 
        options: [createEmptyOption(), createEmptyOption()],
    };
    optionError.value = null;
    isQuestionModalOpen.value = true;
};

const openEditQuestionModal = (question) => {
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

const updateOptionText = (index, newText) => {
  currentQuestion.value.options[index].text = newText;
};

const addOption = () => {
    if (currentQuestion.value.options.length < 8) {
        currentQuestion.value.options.push(createEmptyOption());
    }
};

const removeOption = (index) => {
    if (currentQuestion.value.options.length > 2) {
        const wasCorrect = currentQuestion.value.options[index].is_correct;
        currentQuestion.value.options.splice(index, 1);
        
        if (wasCorrect && currentQuestion.value.options.length > 0) {
            setCorrectOption(0);
        }
    }
};

const setCorrectOption = (selectedIndex) => {
    currentQuestion.value.options.forEach((option, index) => {
        option.is_correct = (index === selectedIndex);
    });
};

const validateOptions = () => {
    const optionsForValidation = currentQuestion.value.options;

    const hasEmptyContent = optionsForValidation.some(opt => !opt.text || !String(opt.text).trim()); 
    
    if (hasEmptyContent) {
        optionError.value = 'Todas las opciones deben tener contenido.';
        return false;
    }
    
    const correctOptionsCount = optionsForValidation.filter(opt => opt.is_correct).length;
    if (correctOptionsCount !== 1) {
        optionError.value = 'Debe haber exactamente una opción correcta.';
        return false;
    }

    optionError.value = null;
    return true;
};

const saveQuestion = async () => {
    if (!validateOptions()) {
        return;
    }

    isSaving.value = true;
    const isEditing = !!currentQuestion.value.id;
    
    try {
        let response;
        
        const data = {
            description: currentQuestion.value.description, 
            time: currentQuestion.value.time, 
            level_id: currentQuestion.value.level_id,
            options: currentQuestion.value.options.map(opt => ({
                text: opt.text,
                is_correct: opt.is_correct ? 1 : 0, 
                ...(opt.id && { id: opt.id }) 
            })),
        };

        if (isEditing) {
            response = await http.put(`/questions/${currentQuestion.value.id}`, data);
        } else {
            response = await http.post(`/trivias/${props.triviaId}/questions`, data);
        }

        alert(response.data.message || 'Pregunta guardada con éxito.');
        closeQuestionModal();
        await fetchQuestions(); 

    } catch (err) {
        const apiMessage = err.response?.data?.message || err.response?.data?.error || 'Error de conexión.';
        alert(`Error al guardar la pregunta: ${apiMessage}`);
    } finally {
        isSaving.value = false;
    }
};

const deleteQuestion = async (questionId, questionDescription) => {
    if (!confirm(`¿Estás seguro de eliminar la pregunta: "${questionDescription.substring(0, 40)}..."?`)) {
        return;
    }
    
    isDeleting.value = { ...isDeleting.value, [questionId]: true };
    
    try {
        const response = await http.delete(`/questions/${questionId}`);
        
        alert(response.data.message || `Pregunta eliminada con éxito.`);
        
        questions.value = questions.value.filter(q => q.id !== questionId);

    } catch (err) {
        let msg = `No se pudo eliminar la pregunta.`;

        if (err.response && err.response.data && err.response.data.message) {
            msg = `Error: ${err.response.data.message}`;
        }
        
        alert(msg);
    } finally {
        isDeleting.value = { ...isDeleting.value, [questionId]: false };
    }
};

onMounted(() => {
    fetchLevels(); 
    fetchQuestions(); 
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