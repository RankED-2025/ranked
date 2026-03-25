import { defineStore } from 'pinia'
import { authService } from '@/services/authService'
import type { LoginData, RegisterData, User } from '@/types'

export interface UserStoreState {
  /** The current logged-in user */
  user: User | null

  /** The JWT access token */
  token: string | null

  /** The JWT refresh token */
  refreshToken: string | null

  loading: boolean
}

export const useUserStore = defineStore('user', {
  state: (): UserStoreState => {
    return {
      user: null,
      token: null,
      refreshToken: null,
      loading: false,
    }
  },
  actions: {
    /**
     * Returns true if the current user is logged in
     */
    isLoggedIn(): boolean {
      return !!this.user && !!this.token
    },

    /**
     * Will attempt to log in the user from the backend.
     * Returns true if the login is successful, or false otherwise
     */
    async loginAttempt(loginData: LoginData): Promise<boolean> {
      try {
        this.loading = true
        // Étape 1: Authentification et récupération des tokens
        const response = await authService.login(loginData)

        // Sauvegarder les tokens dans localStorage
        localStorage.setItem('access_token', response.token)
        localStorage.setItem('refresh_token', response.refresh_token)

        this.token = response.token
        this.refreshToken = response.refresh_token

        // Étape 2: Récupérer les informations utilisateur
        const userData = await authService.getCurrentUser()
        this.user = userData as User
        this.loading = false

        return true
      } catch (error) {
        this.forceDisconnect()
        this.loading = false
        console.error('Login error:', error)
        return false
      }
    },

    /**
     * Will attempt to register the user from the backend.
     * Returns true if the registration is successful, or false otherwise
     */
    async registerAttempt(registerData: RegisterData, userType?: 'eleve' | 'professeur'): Promise<boolean> {
      try {
        this.loading = true
        if (userType === 'eleve') {
          await authService.register(registerData)
        } else {
          throw new Error('Type d\'utilisateur non spécifié')
        }
        this.loading = false
        return true
      } catch (error) {
        this.loading = false
        console.error('Registration error:', error)
        return false
      }
    },

    /**
     * Attempt to connect the user from localStorage.
     */
    async initializeFromStorage() {
      const token = localStorage.getItem('access_token')
      const refreshToken = localStorage.getItem('refresh_token')

      if (token && refreshToken) {
        this.token = token
        this.refreshToken = refreshToken

        try {
          this.loading = true
          const userData = await authService.getCurrentUser()
          this.user = userData as User
          this.loading = false
        } catch (error) {
          this.loading = false
          console.error('Failed to load user:', error)
          this.forceDisconnect()
        }
      }
    },

    /**
     * Disconnects the user.
     */
    forceDisconnect() {
      this.user = null
      this.token = null
      this.refreshToken = null
      localStorage.removeItem('access_token')
      localStorage.removeItem('refresh_token')
    },

    /**
     * Logout the user
     */
    async logout() {
      try {
        if (this.refreshToken) {
          await authService.logout(this.refreshToken)
        }
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.forceDisconnect()
      }
    },

    /**
     * Check if the user has both tokens
     */
    hasValidTokens(): boolean {
      return !!localStorage.getItem('access_token') && !!localStorage.getItem('refresh_token')
    }
  },
  getters: {
    activeUser: (state) => state.user! as User,
    userToken: (state) => state.token,
    getRefreshToken: (state) => state.refreshToken,
    isAuthenticated: (state) => !!state.user && !!state.token,
    isLoading: (state) => state.loading,
  },
})
