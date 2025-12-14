<template>
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
                    @click="fetchRanking(trivia.id)"
                >
                    <i class="bi bi-trophy-fill me-2 text-warning"></i> 
                    Ver Ranking de la Trivia
                    <span v-if="rankingState.data" class="badge bg-secondary ms-auto me-2">
                        Tu Posición: #{{ rankingState.data.your_position || '-' }}
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
                    
                    <div v-if="rankingState.isLoading" class="text-center">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="small mt-2 mb-0">Cargando ranking...</p>
                    </div>
                    
                    <div v-else-if="rankingState.error" class="alert alert-warning small mb-0">
                        {{ rankingState.error }}
                    </div>

                    <div v-else-if="rankingState.data">
                        
                        <p class="mb-2 small">
                            <strong>Tu mejor resultado:</strong> <strong :class="['badge', rankingState.data.your_position === 1 ? 'bg-warning text-dark' : 'bg-info']">
                                #{{ rankingState.data.your_position || 'N/A' }}
                            </strong>
                            | Puntuación: 
                            <strong class="badge bg-primary">
                                {{ rankingState.data.your_score }} pts
                            </strong>
                        </p>

                        <ul class="list-group list-group-flush border rounded small">
                            <li v-for="rankEntry in processedRanking" 
                                :key="rankEntry.user.id" 
                                :class="['list-group-item', 'd-flex', 'justify-content-between', 'align-items-center', 
                                         {'bg-light fw-bold': rankEntry.user.id === authUser.id}]"
                            >
                                <span :class="['me-2', rankEntry.displayRank === 1 ? 'text-warning' : 'text-secondary']">
                                    #{{ rankEntry.displayRank || '-' }}
                                </span>
                                <span class="ms-2 flex-grow-1">
                                    {{ rankEntry.user.name }}
                                    <span v-if="rankEntry.user.id === authUser.id" class="badge bg-primary">Tú</span>
                                </span>
                                <span class="badge bg-dark rounded-pill">{{ rankEntry.score }} pts</span>
                            </li>
                        </ul>
                        
                        <div v-if="processedRanking.length === 0" class="alert alert-secondary small mt-2 mb-0">
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
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    trivia: Object,
    rankingState: Object,
    processedRanking: Array,
    fetchRanking: Function,
    formatCompletionDate: Function,
    authUser: Object,
});
</script>

<style scoped>
.card-header {
    font-weight: bold;
}
.accordion-item:first-of-type .accordion-header .accordion-button {
    border-top-left-radius: 0;
    border-top-right-radius: 0;
}
</style>