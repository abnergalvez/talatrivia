// src/router/index.js (Actualizado)

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

// --- 1. IMPORTACIÓN DE VISTAS ---

// Vistas Principales
import HomeView from '@/views/Home.vue';
import LoginView from '@/views/Login.vue';
import RegisterView from '@/views/Register.vue';
import TriviaGameView from '@/views/TriviaGame.vue';
// ¡NUEVA IMPORTACIÓN!
import RankingView from '@/views/Ranking.vue'; 

// Vistas de Administración (Asegúrate de que existan en src/views/Admin/)
import AdminTrivias from '@/views/Admin/Trivias.vue'; 
import AdminUsers from '@/views/Admin/Users.vue';

// --- 2. CONFIGURACIÓN DEL ROUTER ---

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: true }, 
    },
    // ... rutas de login, register, trivia-game ...
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }, 
    },
    {
      path: '/register',
      name: 'register',
      component: RegisterView,
      meta: { requiresGuest: true }, 
    },
    {
      path: '/trivia/:id',
      name: 'trivia-game',
      component: TriviaGameView,
      meta: { 
        requiresAuth: true
      },
    },
    // ¡NUEVA RUTA!
    {
        path: '/ranking',
        name: 'general-ranking',
        component: RankingView,
        meta: { requiresAuth: true }, // Generalmente, el ranking requiere autenticación
    },
    
    // --- RUTAS DE ADMINISTRACIÓN ---
    {
      path: '/admin/trivias',
      name: 'admin-trivias',
      component: AdminTrivias,
      meta: { 
        requiresAuth: true, 
        requiresAdmin: true
      },
    },
    {
      path: '/admin/users',
      name: 'admin-users',
      component: AdminUsers,
      meta: { 
        requiresAuth: true, 
        requiresAdmin: true 
      },
    },
    // Ruta comodín para capturar URLs no encontradas (404)
    { path: '/:pathMatch(.*)*', name: 'NotFound', component: HomeView }, 
  ],
});

// ... (El router.beforeEach sigue igual)

router.beforeEach((to, from, next) => {
  // Inicializa el store para acceder al estado de autenticación
  const authStore = useAuthStore();
  
  const requiresAuth = to.meta.requiresAuth;
  const requiresGuest = to.meta.requiresGuest;
  const requiresAdmin = to.meta.requiresAdmin;
  
  // 1. Requerir Autenticación (Rutas de Juego y Admin)
  if (requiresAuth && !authStore.isAuthenticated) {
    // Si la ruta requiere un usuario logueado, y no hay token:
    next('/login');
    return;
  } 
  
  // 2. Requerir ser Invitado (Rutas de Login y Register)
  if (requiresGuest && authStore.isAuthenticated) {
    // Si la ruta requiere ser invitado (Login/Register), y el usuario SÍ está logueado:
    next('/'); // Ir a la página principal
    return;
  } 
  
  // 3. Requerir Rol de Administrador (Rutas de Admin/*)
  if (requiresAdmin && !authStore.isAdmin) {
    // Si la ruta requiere ser admin, y el usuario NO tiene ese rol:
    alert('Acceso Denegado: Se requiere rol de Administrador.');
    next('/'); // Redirige a la página principal de juego
    return;
  }
  
  // 4. Si pasa todas las comprobaciones: continuar con la navegación.
  next();
});

export default router;