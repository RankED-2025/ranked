import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useUserStore } from '@/stores/userStore'
import type { LoginData, RegisterData } from '@/types'

/**
 * Composable pour la gestion de l'authentification
 * Centralise toute la logique d'authentification de l'application
 */
export function useAuth() {
  const userStore = useUserStore()
  const router = useRouter()

  // État réactif
  const isAuthenticated = computed(() => userStore.isAuthenticated)
  const user = computed(() => userStore.user)
  const accessToken = computed(() => userStore.token)

  /**
   * Connexion de l'utilisateur
   */
  const login = async (credentials: LoginData) => {
    await userStore.loginAttempt(credentials)
  }

  /**
   * Inscription d'un élève
   */
  const register = async (data: RegisterData) => {
    await userStore.registerAttempt(data)
  }

  /**
   * Déconnexion de l'utilisateur
   */
  const logout = async () => {
    await userStore.logout()
    await router.push('/login')
  }

  /**
   * Initialisation de l'authentification au démarrage
   */
  const initialize = () => {
    userStore.initializeFromStorage();
  }

  return {
    // État
    isAuthenticated,
    user,
    accessToken,
    // Actions
    login,
    logout,
    initialize,
    register
  }
}
