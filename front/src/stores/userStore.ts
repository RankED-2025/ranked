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

  /** True only while the app is restoring the session from localStorage on startup */
  initializing: boolean

  /** Last error message, null if no error */
  error: string | null
}

export const useUserStore = defineStore('user', {
  state: (): UserStoreState => {
    return {
      user: null,
      token: null,
      refreshToken: null,
      loading: false,
      initializing: false,
      error: null,
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
     * Throws on failure so the caller can display the actual error.
     */
    async loginAttempt(loginData: LoginData): Promise<void> {
      this.loading = true
      try {
        const response = await authService.login(loginData)

        localStorage.setItem('access_token', response.token)
        localStorage.setItem('refresh_token', response.refresh_token)

        this.token = response.token
        this.refreshToken = response.refresh_token

        const userData = await authService.getCurrentUser()
        this.user = userData as User
      } catch (error) {
        this.forceDisconnect()
        throw error
      } finally {
        this.loading = false
      }
    },

    /**
     * Will attempt to register the user from the backend.
     * Throws on failure so the caller can display the actual error.
     */
    async registerAttempt(registerData: RegisterData): Promise<void> {
      this.loading = true
      try {
        await authService.register(registerData)
      } catch (error) {
        throw error
      } finally {
        this.loading = false
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
          this.initializing = true
          const userData = await authService.getCurrentUser()
          this.user = userData as User
          this.initializing = false
        } catch (error) {
          this.initializing = false
          console.error(error)
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
      } catch {
        // Logout silently even if the API call fails
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
    isInitializing: (state) => state.initializing,
    getError: (state) => state.error,
  },
})
