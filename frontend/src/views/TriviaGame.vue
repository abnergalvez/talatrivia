<template>
  <div class="container mt-5">
    
    <h2 class="mb-4 text-primary">Trivia: {{ trivia ? trivia.name : 'Cargando...' }}</h2>

    <div v-if="isLoading" class="text-center p-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando trivia...</span>
      </div>
      <p class="mt-2 text-muted">Preparando la trivia...</p>
    </div>

    <div v-else-if="error" class="alert alert-danger p-4">
      <h4 class="alert-heading">Error al cargar la Trivia</h4>
      <p>{{ error }}</p>
      <hr>
      <router-link to="/" class="btn btn-danger">Volver a Trivias Asignadas</router-link>
    </div>

    <div v-else-if="trivia" class="card shadow-lg">
      <div class="card-body p-4">
        
        <div v-if="!isGameStarted && !isGameFinished">
            <h4 class="card-title mb-3">{{ trivia.name }}</h4>
            <p class="card-text text-muted">{{ trivia.description }}</p>

            <div class="alert alert-info mt-4">
                <p class="mb-1">**Total de Preguntas:** {{ trivia.questions.length }}</p>
                </div>

            <button @click="startGame" class="btn btn-lg btn-success mt-4 d-block w-100">
                <i class="bi bi-play-fill me-2"></i> ¡Comenzar Trivia Ahora!
            </button>
        </div>

        <div v-else-if="isGameStarted && !isGameFinished">
            
            <QuestionDisplay
              :question="currentQuestion"
              :questionIndex="currentQuestionIndex"
              :totalQuestions="trivia.questions.length"
              :selectedOptionId="userAnswers[currentQuestion.id]"
              @select="selectOption"
            />

            <div class="d-flex justify-content-between mt-4">
                
                <button 
                    @click="currentQuestionIndex--" 
                    :disabled="currentQuestionIndex === 0" 
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left"></i> Anterior
                </button>
                
                <button 
                    v-if="currentQuestionIndex < trivia.questions.length - 1"
                    @click="currentQuestionIndex++" 
                    :disabled="!userAnswers[currentQuestion.id]"
                    class="btn btn-primary"
                >
                    Siguiente Pregunta <i class="bi bi-arrow-right"></i>
                </button>

                <button 
                    v-else 
                    @click="submitTrivia" 
                    :disabled="isSubmitting"
                    class="btn btn-success"
                >
                    <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2"></span>
                    {{ isSubmitting ? 'Enviando...' : 'Finalizar y Enviar Respuestas' }}
                </button>
            </div>
            <p class="text-end text-muted small mt-2">
                Respuestas dadas: {{ Object.keys(userAnswers).length }} / {{ trivia.questions.length }}
            </p>
        </div>

        <div v-else-if="isGameFinished">
            <h4 class="text-success mb-3">¡Trivia Completada!</h4>
            <div class="alert alert-success p-4">
                <h5 class="mb-3">Resultados del Envío:</h5>
                <p><strong>Puntuación Total:</strong> <span class="badge bg-primary fs-5">{{ finalResults.total_points_earned }}</span></p>
                <p><strong>Respuestas Correctas:</strong> <span class="badge bg-success">{{ finalResults.correct_answers }}</span> de {{ finalResults.total_answers }}</p>
                <p class="mt-3">Tu puntuación ha sido registrada. Ahora puedes volver al listado de trivias o ver el ranking.</p>
            </div>
            
            <router-link to="/" class="btn btn-primary me-2">
                <i class="bi bi-house-fill me-1"></i> Volver a Trivias Asignadas
            </router-link>
            </div>

      </div>
      
      <div v-if="!isGameStarted || isGameFinished" class="card-footer text-end">
        <router-link to="/" class="btn btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i> Volver
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, onUnmounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import http from '@/http';
import QuestionDisplay from '@/components/QuestionDisplay.vue';

const route = useRoute();
const router = useRouter();

const trivia = ref(null);
const isLoading = ref(true);
const error = ref(null);

const isGameStarted = ref(false);
const isGameFinished = ref(false);
const isSubmitting = ref(false);
const currentQuestionIndex = ref(0);
const userAnswers = ref({});
const remainingTime = ref(0);
let globalTimerInterval = null;
const finalResults = ref({});

const currentQuestion = computed(() => {
    if (trivia.value && trivia.value.questions.length > 0) {
        return trivia.value.questions[currentQuestionIndex.value];
    }
    return null;
});

const formatTime = (totalSeconds) => {
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;
    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
};

const startGame = () => {
    isGameStarted.value = true;
    currentQuestionIndex.value = 0;
    userAnswers.value = {};
};

const selectOption = (questionId, optionId) => {
    userAnswers.value = {
        ...userAnswers.value,
        [questionId]: optionId
    };
};

const submitTrivia = async () => {
    if (isGameFinished.value || isSubmitting.value) return; 
    
    isSubmitting.value = true;
    
    const answersPayload = {
        answers: Object.entries(userAnswers.value).map(([question_id, option_id]) => ({
            question_id: parseInt(question_id), 
            option_id: option_id
        }))
    };
    
    if (answersPayload.answers.length === 0) {
        alert("Debes responder al menos una pregunta antes de enviar la trivia.");
        isSubmitting.value = false;
        return;
    }

    try {
        const response = await http.post(`/trivias/${trivia.value.id}/answer_all`, answersPayload);

        if (response.data.result) {
            finalResults.value = response.data.result;
        } else {
            finalResults.value = {
                total_points_earned: 'N/A',
                correct_answers: 'N/A',
                total_answers: answersPayload.answers.length
            };
        }
        
        isGameFinished.value = true;
        
    } catch (err) {
        console.error("Error al enviar respuestas:", err);
        let msg = 'Hubo un problema al enviar tus respuestas. Intenta de nuevo.';
        
        if (err.response && err.response.data && err.response.data.message) {
            msg = `Error: ${err.response.data.message}`;
        }
        
        alert(msg);
        isSubmitting.value = false;
    }
};

const fetchTriviaData = async () => {
    const triviaId = route.params.id;
    isLoading.value = true;
    error.value = null;

    if (!triviaId) {
        error.value = 'ID de Trivia no encontrado en la URL.';
        isLoading.value = false;
        return;
    }

    try {
        const response = await http.get(`/trivias/${triviaId}/get_full`);
        
        if (response.data && response.data.trivia) {
            trivia.value = {
                ...response.data.trivia, 
                questions: response.data.questions || [],
                total_questions: response.data.total_questions,
                answered_questions: response.data.answered_questions,
                remaining_questions: response.data.remaining_questions,
            };
            
            if (trivia.value.answered_questions > 0 && trivia.value.answered_questions === trivia.value.total_questions) {
                 isGameFinished.value = true;
                 error.value = "Esta trivia ya fue completada por ti."
            }
        } else {
            throw new Error('La respuesta del servidor no contiene datos de trivia válidos.');
        }

    } catch (err) {
        if (err.response && (err.response.status === 404 || err.response.status === 403 || err.response.status === 401)) {
            error.value = 'No se pudo cargar la trivia. Verifica que esté asignada a tu usuario y se encuentre activa.';
        } else {
            error.value = `Error de red o servidor: ${err.message}`;
        }
        console.error("Error al cargar trivia:", err);

    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchTriviaData();
});

onUnmounted(() => {
    if (globalTimerInterval) {
        clearInterval(globalTimerInterval);
    }
});
</script>

<style scoped>
.active.bg-primary {
    border-color: #0d6efd !important;
}
</style>