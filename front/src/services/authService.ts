import axiosInstance from '@/utils/axiosInstance'
import type {
  LoginData,
  RegisterEleveData,
  RegisterProfesseurData,
  RegisterResponse,
  AuthResponse
} from '@/types'

export const authService = {
  async login(credentials: LoginData): Promise<AuthResponse> {
    const response = await axiosInstance.post('/api/login', credentials)
    return response.data
  },

  async registerEleve(data: RegisterEleveData): Promise<RegisterResponse> {
    const response = await axiosInstance.post('/api/register/eleve', data)
    return response.data
  },

  async registerProfesseur(data: RegisterProfesseurData): Promise<RegisterResponse> {
    const response = await axiosInstance.post('/api/register/professeur', data)
    return response.data
  },

  async logout(refreshToken: string): Promise<void> {
    await axiosInstance.post('/api/logout', { refresh_token: refreshToken })
  },

  async getCurrentUser() {
    const response = await axiosInstance.get('/api/me')
    return response.data
  },
}
