import { defineStore } from 'pinia'
import { authService } from '@/services/authService'
import type { LoginData, User, UserData } from '@/types'
import { AxiosError } from 'axios'

export interface UserStoreState {
  /** The current logged-in user */
  user: User | null

  /** The JWT access token */
  token: string | null

  /** The JWT refresh token */
  refreshToken: string | null
}

export const useUserStore = defineStore('user', {
  state: (): UserStoreState => {
    return {
      user: null,
      token: null,
      refreshToken: null,
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
        
        return true
      } catch (error) {
        this.forceDisconnect()
        console.error('Login error:', error)
        return false
      }
    },

    /**
     * Will attempt to register the user from the backend.
     * Returns true if the registration is successful, or false otherwise
     */
    async registerAttempt(registerData: UserData, userType?: 'eleve' | 'professeur'): Promise<boolean> {
      try {
        if (userType === 'eleve') {
          await authService.registerEleve(registerData as any)
        } else if (userType === 'professeur') {
          await authService.registerProfesseur(registerData as any)
        } else {
          throw new Error('Type d\'utilisateur non spécifié')
        }
        return true
      } catch (error) {
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
          const userData = await authService.getCurrentUser()
          this.user = userData as User
        } catch (error) {
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
     * Initialize authentication from localStorage
     */
    async initAuth() {
      await this.initializeFromStorage()
    },
  },
  getters: {
    activeUser: (state) => state.user! as User,
    userToken: (state) => state.token,
    accessToken: (state) => state.token,
    isAuthenticated: (state) => !!state.user && !!state.token,
  },
})
