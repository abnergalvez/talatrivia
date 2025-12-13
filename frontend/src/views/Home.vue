<template>
  <div class="container py-4">
    
    <h2 class="mb-4">👋 ¡Bienvenido, <span class="text-primary">{{ authStore.user?.name || 'Jugador' }}</span>!</h2>
    <h3 class="mb-4 text-secondary">Trivias Asignadas</h3>

    <div v-if="isLoading" class="text-center p-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando trivias...</span>
      </div>
      <p class="mt-2 text-muted">Cargando trivias...</p>
    </div>
    
    <div v-else-if="error" class="alert alert-danger" role="alert">
      Error al cargar trivias: **{{ error }}**
    </div>

    <div v-else-if="trivias.length" class="row">
      <div v-for="trivia in trivias" :key="trivia.id" class="col-md-6 mb-4">
        
        <div :class="['card shadow-sm', trivia.status === 'completed' ? 'border-success' : 'border-primary']">
          
          <div :class="['card-header', trivia.status === 'completed' ? 'bg-success text-white' : 'bg-primary text-white']">
            <h5 class="mb-0">{{ trivia.name }}</h5>
          </div>

          <div class="card-body">
            <p class="card-text text-muted mb-3">{{ trivia.description }}</p>
            <p class="mb-2 fw-bold">Total de Preguntas: {{ trivia.questions_count }}</p>

            <div v-if="trivia.status === 'completed'" class="p-2 border border-success rounded bg-light">
              <span class="badge bg-success me-2">Completada</span>
              <p class="mt-2 mb-0 ">
                Puntuación: <strong class="text-success">{{ trivia.score ?? 'N/A' }}</strong>
              </p>
              <p class="mb-1 small">
                Finalizada: {{ formatCompletionDate(trivia.completed_at) }}
              </p>
            </div>

            <div v-else class="p-2 border border-warning rounded bg-light">
              <span class="badge bg-warning text-dark me-3">Pendiente de Jugar</span>
              
              <router-link :to="{ name: 'trivia-game', params: { id: trivia.id } }" class="btn btn-primary btn-sm mt-2 d-block">
                <i class="bi bi-play-fill me-1"></i> Ir a la Trivia
              </router-link>
            </div>
          </div>
          
          <div class="accordion accordion-flush" :id="`ranking-accordion-${trivia.id}`">
              <div class="accordion-item">
                  <h2 class="accordion-header" :id="`heading${trivia.id}`">
                      <button 
                          class="accordion-button collapsed py-2" 
                          type="button" 
                          data-bs-toggle="collapse" 
                          :data-bs-target="`#collapse${trivia.id}`" 
                          aria-expanded="false" 
                          :aria-controls="`collapse${trivia.id}`"
                          @click="fetchTriviaRanking(trivia.id)"
                      >
                          <i class="bi bi-trophy-fill me-2 text-warning"></i> 
                          Ver Ranking de la Trivia
                          <span v-if="getRankingState(trivia.id).data" class="badge bg-secondary ms-auto me-2">
                              Tu Posición: #{{ getRankingState(trivia.id).data.your_position || '-' }}
                          </span>
                      </button>
                  </h2>
                  <div 
                      :id="`collapse${trivia.id}`" 
                      class="accordion-collapse collapse" 
                      :aria-labelledby="`heading${trivia.id}`" 
                      :data-bs-parent="`#ranking-accordion-${trivia.id}`"
                  >
                      <div class="accordion-body p-3">
                          
                          <div v-if="getRankingState(trivia.id).isLoading" class="text-center">
                              <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                              <p class="small mt-2 mb-0">Cargando ranking...</p>
                          </div>
                          
                          <div v-else-if="getRankingState(trivia.id).error" class="alert alert-warning small mb-0">
                              {{ getRankingState(trivia.id).error }}
                          </div>

                          <div v-else-if="getRankingState(trivia.id).data">
                              
                              <p class="mb-2 small">
                                  <strong>Tu mejor resultado:</strong> <strong :class="['badge', getRankingState(trivia.id).data.your_position === 1 ? 'bg-warning text-dark' : 'bg-info']">
                                      #{{ getRankingState(trivia.id).data.your_position || 'N/A' }}
                                  </strong>
                                  | Puntuación: 
                                  <strong class="badge bg-primary">
                                      {{ getRankingState(trivia.id).data.your_score }} pts
                                  </strong>
                              </p>

                              <ul class="list-group list-group-flush border rounded small">
                                  <li v-for="rankEntry in getProcessedRanking(trivia.id)" 
                                      :key="rankEntry.user.id" 
                                      :class="['list-group-item', 'd-flex', 'justify-content-between', 'align-items-center', 
                                               {'bg-light fw-bold': rankEntry.user.id === authStore.user?.id}]"
                                  >
                                      <span :class="['me-2', rankEntry.displayRank === 1 ? 'text-warning' : 'text-secondary']">
                                          #{{ rankEntry.displayRank || '-' }}
                                      </span>
                                      <span class="ms-2 flex-grow-1">
                                          {{ rankEntry.user.name }}
                                          <span v-if="rankEntry.user.id === authStore.user?.id" class="badge bg-primary">Tú</span>
                                      </span>
                                      <span class="badge bg-dark rounded-pill">{{ rankEntry.score }} pts</span>
                                  </li>
                              </ul>
                              
                              <div v-if="getProcessedRanking(trivia.id).length === 0" class="alert alert-secondary small mt-2 mb-0">
                                  Aún no hay participantes. ¡Sé el primero!
                              </div>
                          </div>
                          
                          <div v-else class="alert alert-secondary small mb-0">
                              Haz clic en el título para cargar el ranking.
                          </div>

                      </div>
                  </div>
              </div>
          </div>
          </div>
      </div>
    </div>
    
    <div v-else class="alert alert-info text-center">
      No tienes trivias asignadas por el momento.
    </div>

    <div class="mt-5 d-flex justify-content-between align-items-center">
      <button @click="authStore.logout()" class="btn btn-danger">
        <i class="bi bi-box-arrow-right me-1"></i> Cerrar Sesión
      </button>
      <router-link to="/ranking" class="btn btn-outline-secondary">
        <i class="bi bi-bar-chart-fill me-1"></i> Ver Ranking General
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import http from '@/http'; // Asumimos que estás usando un cliente HTTP configurado con Axios

const authStore = useAuthStore();

// --- ESTADOS LOCALES ---
const trivias = ref([]);
const isLoading = ref(false);
const error = ref(null);
const triviaRankings = ref({}); 

// --- FUNCIÓN DE FORMATO DE FECHA ---
const formatCompletionDate = (dateString) => {
    if (!dateString) return 'Fecha no disponible';
    const date = new Date(dateString.replace(' ', 'T')); 
    if (isNaN(date)) return 'Formato inválido';
    
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: false
    };

    return date.toLocaleString('es-ES', options);
};

// Helper para obtener el estado del ranking de una trivia
const getRankingState = (triviaId) => {
    return triviaRankings.value[triviaId] || { data: null, isLoading: false, error: null };
};

// --- LÓGICA CLAVE: PROCESAMIENTO DEL RANKING CON EMPATES ---
const getProcessedRanking = (triviaId) => {
    const rankingState = getRankingState(triviaId);
    if (!rankingState.data || !rankingState.data.ranking) {
        return [];
    }
    
    // 1. Clonar y ordenar por score descendente.
    // Esto asegura que la lista esté ordenada correctamente ANTES de asignar el rank.
    const sortedRanking = [...rankingState.data.ranking].sort((a, b) => b.score - a.score);
    
    let currentRank = 0;
    let lastScore = null;
    
    return sortedRanking.map((entry, index) => {
        const score = entry.score;
        
        if (score !== lastScore) {
            // Si el score cambia, el rank es el índice actual + 1
            currentRank = index + 1;
            lastScore = score;
        } else {
            // Si el score es el mismo, se mantiene el rank anterior (manejo de empate)
            // currentRank ya contiene el rank correcto.
        }
        
        return {
            ...entry,
            displayRank: currentRank, // Rank calculado para mostrar
        };
    });
};


// --- FETCHING DE RANKING ---
const fetchTriviaRanking = async (triviaId) => {
    // Si ya hay datos cargados, o si está cargando, salir.
    if (triviaRankings.value[triviaId].data || triviaRankings.value[triviaId].isLoading) {
        return;
    }
    
    triviaRankings.value[triviaId].isLoading = true;
    triviaRankings.value[triviaId].error = null;

    try {
        const response = await http.get(`/trivias/${triviaId}/ranking`);
        triviaRankings.value[triviaId].data = response.data;
        
    } catch (err) {
        console.error(`Error al obtener ranking de trivia ${triviaId}:`, err);
        triviaRankings.value[triviaId].error = 'No se pudo cargar el ranking de esta trivia.';
        triviaRankings.value[triviaId].data = null; 
    } finally {
        triviaRankings.value[triviaId].isLoading = false;
    }
};


// --- FETCHING DE TRIVIAS ASIGNADAS ---
const fetchAssignedTrivias = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    const response = await http.get('/assigned_trivias');
    
    trivias.value = response.data.trivias.map(trivia => {
        // Inicializar el estado del ranking para cada trivia
        if (!triviaRankings.value[trivia.id]) {
            triviaRankings.value[trivia.id] = {
                data: null,
                isLoading: false,
                error: null,
            };
        }
        return {
            ...trivia,
            status: trivia.status,
        };
    }) || [];
    
  } catch (err) {
    console.error("Error al obtener trivias asignadas:", err);
    error.value = 'No se pudieron cargar tus trivias.';
  } finally {
    isLoading.value = false;
  }
};

// --- LIFECYCLE HOOKS ---
onMounted(() => {
  if (authStore.isAuthenticated) {
    fetchAssignedTrivias();
  }
});
</script>

<style scoped>
.card-header {
    font-weight: bold;
}
/* Asegura que el acordeón se vea adjunto al cuerpo de la tarjeta */
.accordion-item:first-of-type .accordion-header .accordion-button {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}
</style>