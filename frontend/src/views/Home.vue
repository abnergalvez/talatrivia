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
        <TriviaCard
          :trivia="trivia"
          :rankingState="getRankingState(trivia.id)"
          :processedRanking="getProcessedRanking(trivia.id)"
          :fetchRanking="fetchTriviaRanking"
          :formatCompletionDate="formatCompletionDate"
          :authUser="authStore.user"
        />
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
import http from '@/http';
import TriviaCard from '@/components/TriviaCard.vue';

const authStore = useAuthStore();

const trivias = ref([]);
const isLoading = ref(false);
const error = ref(null);
const triviaRankings = ref({}); 

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

const getRankingState = (triviaId) => {
    return triviaRankings.value[triviaId] || { data: null, isLoading: false, error: null };
};

const getProcessedRanking = (triviaId) => {
    const rankingState = getRankingState(triviaId);
    if (!rankingState.data || !rankingState.data.ranking) {
        return [];
    }
    
    const sortedRanking = [...rankingState.data.ranking].sort((a, b) => b.score - a.score);
    
    let currentRank = 0;
    let lastScore = null;
    
    return sortedRanking.map((entry, index) => {
        const score = entry.score;
        
        if (score !== lastScore) {
            currentRank = index + 1;
            lastScore = score;
        }
        
        return {
            ...entry,
            displayRank: currentRank,
        };
    });
};

const fetchTriviaRanking = async (triviaId) => {
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

const fetchAssignedTrivias = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        const response = await http.get('/assigned_trivias');
        
        trivias.value = response.data.trivias.map(trivia => {
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

onMounted(() => {
    if (authStore.isAuthenticated) {
        fetchAssignedTrivias();
    }
});
</script>