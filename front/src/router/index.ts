import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import { useUserStore } from '@/stores/userStore'

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
      component: () => import('../views/LoginView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/RegisterView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/forgot-password',
      name: 'forgot-password',
      component: () => import('../views/ForgotPasswordView.vue'),
      meta: { requiresGuest: true },
    },
    {
      path: '/reset-password',
      name: 'reset-password',
      component: () => import('../views/ResetPasswordView.vue'),
      meta: { requiresGuest: true },
    },
  ],
})

// Navigation guard pour protéger les routes
router.beforeEach((to, from, next) => {
  const userStore = useUserStore()
  
  // Initialiser l'authentification depuis le localStorage
  if (!userStore.accessToken) {
    userStore.initAuth()
  }

  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiresGuest = to.matched.some(record => record.meta.requiresGuest)

  if (requiresAuth && !userStore.isAuthenticated) {
    // Route protégée mais utilisateur non authentifié
    next({ name: 'login' })
  } else if (requiresGuest && userStore.isAuthenticated) {
    // Route pour invités mais utilisateur authentifié
    next({ name: 'home' })
  } else {
    // Continuer normalement
    next()
  }
})

export default router
