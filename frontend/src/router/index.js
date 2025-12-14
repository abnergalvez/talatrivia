// src/router/index.js (Actualizado con Roles y Niveles)

import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/auth';


// --- 1. IMPORTACIÓN DE VISTAS ---

// Vistas Principales
import HomeView from '@/views/Home.vue';
import LoginView from '@/views/Login.vue';
import RegisterView from '@/views/Register.vue';
import TriviaGameView from '@/views/TriviaGame.vue';
import RankingView from '@/views/Ranking.vue'; 

// Vistas de Administración
import AdminTrivias from '@/views/Admin/Trivias.vue'; 
import AdminUsers from '@/views/Admin/Users.vue';
import Questions from '@/views/Admin/Questions.vue';
// ¡NUEVAS IMPORTACIONES!
import AdminRoles from '@/views/Admin/Roles.vue'; 
import AdminLevels from '@/views/Admin/Levels.vue'; 

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
    {
        path: '/ranking',
        name: 'general-ranking',
        component: RankingView,
        meta: { requiresAuth: true },
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
    // NUEVAS RUTAS
    {
      path: '/admin/roles',
      name: 'admin-roles',
      component: AdminRoles,
      meta: { 
        requiresAuth: true, 
        requiresAdmin: true 
      },
    },
    {
      path: '/admin/levels',
      name: 'admin-levels',
      component: AdminLevels,
      meta: { 
        requiresAuth: true, 
        requiresAdmin: true 
      },
    },
    // FIN NUEVAS RUTAS
    {
      path: '/admin/trivias/:triviaId/questions',
      name: 'admin.questions',
      component: Questions,
      meta: { requiresAuth: true, requiresAdmin: true },
      props: true
    },
    // Ruta comodín para capturar URLs no encontradas (404)
    { path: '/:pathMatch(.*)*', name: 'NotFound', component: HomeView }, 
  ],
});

// ... (El router.beforeEach se mantiene sin cambios)

router.beforeEach((to, from, next) => {
  // Inicializa el store para acceder al estado de autenticación
  const authStore = useAuthStore();
  
  const requiresAuth = to.meta.requiresAuth;
  const requiresGuest = to.meta.requiresGuest;
  const requiresAdmin = to.meta.requiresAdmin;
  
  // 1. Requerir Autenticación (Rutas de Juego y Admin)
  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login');
    return;
  } 
  
  // 2. Requerir ser Invitado (Rutas de Login y Register)
  if (requiresGuest && authStore.isAuthenticated) {
    next('/'); 
    return;
  } 
  
  // 3. Requerir Rol de Administrador (Rutas de Admin/*)
  if (requiresAdmin && !authStore.isAdmin) {
    alert('Acceso Denegado: Se requiere rol de Administrador.');
    next('/'); 
    return;
  }
  
  // 4. Si pasa todas las comprobaciones: continuar con la navegación.
  next();
});

export default router;