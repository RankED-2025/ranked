import { axiosInstance } from '@/utils/axiosInstance'
import type {
  PasswordResetRequestData,
  PasswordResetConfirmData,
  PasswordResetResponse
} from '@/types'

export const passwordResetService = {
  /**
   * Demande de réinitialisation de mot de passe
   * Envoie un email avec un lien de réinitialisation
   */
  async requestReset(data: PasswordResetRequestData): Promise<PasswordResetResponse> {
    const response = await axiosInstance.post('/api/password-reset/request', data)
    return response.data
  },

  /**
   * Confirmation de la réinitialisation avec le token et le nouveau mot de passe
   */
  async confirmReset(data: PasswordResetConfirmData): Promise<PasswordResetResponse> {
    const response = await axiosInstance.post('/api/password-reset/confirm', data)
    return response.data
  },
}
