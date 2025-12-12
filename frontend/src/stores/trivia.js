import { defineStore } from 'pinia';
import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8082/api';

export const useTriviaStore = defineStore('trivia', {
    state: () => ({
        assignedTrivias: [],
        isLoading: false,
        error: null,
    }),

    actions: {
        async fetchAssignedTrivias() {
            this.isLoading = true;
            this.error = null;
            try {
                // Axios ya debe tener el token Bearer configurado desde el useAuthStore
                const response = await axios.get(`${API_BASE_URL}/assigned_trivias`);
                
                this.assignedTrivias = response.data.trivias;
                
            } catch (err) {
                this.error = 'Error al cargar las trivias asignadas.';
                console.error('Error fetching assigned trivias:', err);
                this.assignedTrivias = [];
            } finally {
                this.isLoading = false;
            }
        },

    }
});