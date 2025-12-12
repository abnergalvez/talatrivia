import { defineStore } from 'pinia';
import router from '@/router'; // Para la redirección
import axios from 'axios';     // Usaremos Axios por ser la librería estándar para peticiones HTTP

// 1. Instalar Axios
// Desde la terminal: pnpm install axios

// 2. Obtener la URL base de la API
// Esto lee la variable VITE_API_URL definida en tu docker-compose.yml
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8082/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        // Inicializa el estado leyendo de localStorage
        token: localStorage.getItem('token') || null,
        user: JSON.parse(localStorage.getItem('user')) || null,
        role: localStorage.getItem('role') || 'player', // Por defecto 'player'
        
        // Determina si el usuario está autenticado si hay un token
        isAuthenticated: !!localStorage.getItem('token'),
        
        isLoading: false,
        error: null,
    }),

    getters: {
        // Getter simple para el Guardia de Ruta (Route Guard)
        isAdmin: (state) => state.role === 'admin',
    },

    actions: {
        // Función auxiliar para configurar axios y guardar el estado
        setAuthData(token, userData) {
            this.token = token;
            this.user = userData;
            this.role = userData?.role || 'player'; // Asegura que el rol se establezca
            this.isAuthenticated = true;

            // Persistencia
            localStorage.setItem('token', token);
            localStorage.setItem('user', JSON.stringify(userData));
            localStorage.setItem('role', this.role);
            
            // Configurar el token Bearer globalmente en Axios
            if (token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
            } else {
                delete axios.defaults.headers.common['Authorization'];
            }
        },

        clearAuthData() {
            this.token = null;
            this.user = null;
            this.role = 'player';
            this.isAuthenticated = false;
            
            // Limpieza de persistencia
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            localStorage.removeItem('role');

            // Limpiar cabecera de Axios
            delete axios.defaults.headers.common['Authorization'];
        },
        
        // --- AUTH ACTIONS ---

        async login(email, password) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await axios.post(`${API_BASE_URL}/login`, {
                    email,
                    password
                });

                const { token, user } = response.data;
                
                // El backend de Login devuelve el objeto 'user' completo (incluyendo el rol)
                this.setAuthData(token, user);
                
                router.push('/'); // Redirigir a la página principal después del login
                
            } catch (err) {
                this.error = err.response?.data?.error || 'Credenciales inválidas o error de red.';
                this.clearAuthData();
            } finally {
                this.isLoading = false;
            }
        },

        async register(name, email, password) {
            this.isLoading = true;
            this.error = null;
            try {
                const response = await axios.post(`${API_BASE_URL}/register`, {
                    name,
                    email,
                    password
                });
                
                const { token, user } = response.data;
                
                // El backend AHORA devuelve el objeto 'user' completo con el rol
                this.setAuthData(token, user); // Guarda el token, el user y el rol en localStorage
                
                router.push('/'); // Redirigir a la página principal inmediatamente
                
            } catch (err) {
                // ... (manejo de errores)
            } finally {
                this.isLoading = false;
            }
        },

        async fetchUser() {
             // Esta acción asume que el token ya está en localStorage/Axios headers
            if (!this.token) return; 

            this.isLoading = true;
            try {
                const response = await axios.get(`${API_BASE_URL}/me`);
                
                // El endpoint /me devuelve el objeto de usuario completo con el rol
                this.setAuthData(this.token, response.data);
                
            } catch (err) {
                // Si el token es inválido o expiró
                if (err.response?.status === 401) {
                    this.logout(false); // Limpiar sesión sin llamar al API de logout
                }
            } finally {
                this.isLoading = false;
            }
        },
        
        async logout(callApi = true) {
            this.isLoading = true;
            this.error = null;

            if (callApi && this.token) {
                try {
                    // Llama al backend para invalidar el token (opcional, tu backend lo pone en null)
                    await axios.post(`${API_BASE_URL}/logout`);
                } catch (err) {
                    // Simplemente ignoramos el error, el logout de todas formas se limpiará localmente
                    console.error("Error al llamar al API de logout:", err);
                }
            }
            
            this.clearAuthData();
            
            // Redirigir siempre a login
            if (router.currentRoute.value.path !== '/login') {
                router.push('/login');
            }
            
            this.isLoading = false;
        },
    }
});