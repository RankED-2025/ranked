import { passwordResetService } from '@/services/passwordResetService'
import axiosInstance from '@/utils/axiosInstance'
import { beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('@/utils/axiosInstance', () => {
	const instance = { post: vi.fn() }
	return {
		default: instance,
		axiosInstance: instance,
	}
})

const mockedAxios = vi.mocked(axiosInstance)

describe('passwordResetService', () => {
	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('requestReset envoie la demande de réinitialisation sur le bon endpoint et retourne la réponse', async () => {
		const requestData = {
			email: 'student@example.com',
		}
		const response = {
			data: {
				message: 'reset email sent',
			},
		}

		mockedAxios.post.mockResolvedValue(response)

		await expect(passwordResetService.requestReset(requestData)).resolves.toEqual(response.data)

		expect(mockedAxios.post).toHaveBeenCalledWith('/api/password-reset/request', requestData)
	})

	it('confirmReset envoie le token et le nouveau mot de passe sur le bon endpoint et retourne la réponse', async () => {
		const confirmData = {
			token: 'reset-token',
			password: 'new-secret-password',
			password_confirmation: 'new-secret-password',
		}
		const response = {
			data: {
				message: 'password reset confirmed',
			},
		}

		mockedAxios.post.mockResolvedValue(response)

		await expect(passwordResetService.confirmReset(confirmData)).resolves.toEqual(response.data)

		expect(mockedAxios.post).toHaveBeenCalledWith('/api/password-reset/confirm', confirmData)
	})
})
