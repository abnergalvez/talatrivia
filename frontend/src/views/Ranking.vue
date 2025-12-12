<template>
  <div class="container py-4">
    
    <h2 class="mb-4 text-primary">🏆 Ranking General de Jugadores</h2>
    <p class="text-muted">Puntuación total acumulada de todas las trivias completadas.</p>
    
    <div v-if="generalRankingData" class="alert alert-info shadow-sm p-3 mb-4">
        <h5 class="alert-heading mb-1">Tu Posición Actual:</h5>
        <div class="d-flex justify-content-between align-items-center">
            <p class="mb-0">
                <span class="fs-4 fw-bold me-2" :class="[generalRankingData.your_position === 1 ? 'text-warning' : 'text-primary']">
                    #{{ generalRankingData.your_position || 'N/A' }}
                </span>
                <span class="text-muted">({{ generalRankingData.your_trivias_completed }} trivias completadas)</span>
            </p>
            <p class="mb-0">
                Puntuación Total: 
                <span class="badge bg-success fs-5">{{ generalRankingData.your_total_score }} pts</span>
            </p>
        </div>
    </div>
    
    <div v-if="isLoading" class="text-center p-5">
      <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Cargando ranking...</span>
      </div>
      <p class="mt-2 text-muted">Cargando ranking general...</p>
    </div>
    
    <div v-else-if="error" class="alert alert-danger" role="alert">
      Error al cargar ranking: **{{ error }}**
    </div>

    <div v-else-if="processedRanking.length" class="table-responsive">
      <table class="table table-striped table-hover table-bordered shadow-sm"> 
        <thead class="table-primary">
          <tr>
            <th class="text-center" style="width: 10%">#</th>
            <th style="width: 40%">Jugador</th>
            <th class="text-center" style="width: 20%">Trivias Comp.</th>
            <th class="text-center" style="width: 30%">Puntaje Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="player in processedRanking" :key="player.user.id"
              :class="{'table-success fw-bold': player.user.id === authStore.user?.id}">
            
            <td class="text-center fs-5">
                <span v-if="player.displayRank === 1" class="text-warning">🥇</span>
                <span v-else-if="player.displayRank === 2" class="text-secondary">🥈</span>
                <span v-else-if="player.displayRank === 3" class="text-danger">🥉</span>
                <span v-else class="text-muted">#{{ player.displayRank }}</span>
            </td>
            
            <td>
                {{ player.user.name }}
                <span v-if="player.user.id === authStore.user?.id" class="badge bg-primary ms-1">Tú</span>
            </td>
            
            <td class="text-center">{{ player.trivias_completed }}</td>
            
            <td class="text-center">
                <span class="badge bg-dark fs-6">{{ player.total_score }}</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div v-else class="alert alert-info text-center">
      No hay datos de ranking disponibles.
    </div> 
    
    <router-link to="/" class="btn btn-outline-secondary mt-3">
        <i class="bi bi-arrow-left me-1"></i> Volver a Trivias Asignadas
    </router-link>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import http from '@/http';
import { useAuthStore } from '@/stores/auth'; // Importamos el store para identificar al usuario

const authStore = useAuthStore();
const generalRankingData = ref(null);
const isLoading = ref(false);
const error = ref(null);

// --- LÓGICA DE PROCESAMIENTO DEL RANKING CON EMPATES ---
const getProcessedRanking = (rawRanking) => {
    if (!rawRanking || rawRanking.length === 0) {
        return [];
    }
    
    // 1. Clonar y ordenar por total_score descendente.
    const sortedRanking = [...rawRanking].sort((a, b) => b.total_score - a.total_score);
    
    let currentRank = 0;
    let lastScore = null;
    
    return sortedRanking.map((entry, index) => {
        const score = entry.total_score;
        
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

// --- COMPUTED PROPERTY para la tabla ---
const processedRanking = computed(() => {
    return generalRankingData.value ? getProcessedRanking(generalRankingData.value.ranking) : [];
});


// --- FETCHING DE DATOS ---
const fetchGeneralRanking = async () => {
  isLoading.value = true;
  error.value = null;
  try {
    // Endpoint: /api/all_trivias_ranking
    const response = await http.get('/all_trivias_ranking');
    
    if (response.data) {
        // Almacenamos toda la data de la respuesta, incluyendo los stats del usuario
        generalRankingData.value = response.data;
    } else {
        throw new Error("Respuesta de API inválida.");
    }
    
  } catch (err) {
    console.error("Error al obtener ranking:", err);
    error.value = 'No se pudo cargar el ranking general.';
  } finally {
    isLoading.value = false;
  }
};

onMounted(fetchGeneralRanking);
</script>

<style scoped>
/* Estilos adicionales de Bootstrap para el componente */
.ranking-table th {
    font-weight: bold;
}
.ranking-table tbody tr.table-success {
    border: 2px solid #198754; /* Borde más notorio para tu propia fila */
}
</style>