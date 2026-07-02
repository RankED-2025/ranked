import { authService } from '@/services/authService'
import axiosInstance from '@/utils/axiosInstance'
import { beforeEach, describe, expect, it, vi } from 'vitest'

vi.mock('@/utils/axiosInstance', () => ({
	default: {
		get: vi.fn(),
		post: vi.fn(),
	},
}))

const mockedAxios = vi.mocked(axiosInstance)

describe('authService', () => {
	beforeEach(() => {
		vi.clearAllMocks()
	})

	it('login envoie les identifiants sur l\'endpoint de connexion et retourne la réponse', async () => {
		const credentials = {
			email: 'student@example.com',
			password: 'secret-password',
		}
		const response = {
			data: {
				token: 'access-token',
				refresh_token: 'refresh-token',
				user: { id: 1, email: credentials.email },
			},
		}

		mockedAxios.post.mockResolvedValue(response)

		await expect(authService.login(credentials)).resolves.toEqual(response.data)

		expect(mockedAxios.post).toHaveBeenCalledWith('/api/login', credentials)
	})

	it('register envoie les données d\'inscription et retourne la réponse', async () => {
		const registerData = {
			email: 'teacher@example.com',
			password: 'secret-password',
			first_name: 'Ada',
			last_name: 'Lovelace',
		}
		const response = {
			data: {
				message: 'registered',
				user: { id: 2, email: registerData.email },
			},
		}

		mockedAxios.post.mockResolvedValue(response)

		await expect(authService.register(registerData)).resolves.toEqual(response.data)

		expect(mockedAxios.post).toHaveBeenCalledWith('/api/register', registerData)
	})

	it('logout transmet le refresh token à l\'endpoint de déconnexion', async () => {
		mockedAxios.post.mockResolvedValue({ data: undefined })

		await expect(authService.logout('refresh-token')).resolves.toBeUndefined()

		expect(mockedAxios.post).toHaveBeenCalledWith('/api/logout', {
			refresh_token: 'refresh-token',
		})
	})

	it('getCurrentUser récupère les informations du profil courant', async () => {
		const response = {
			data: {
				id: 1,
				email: 'student@example.com',
				first_name: 'Ada',
				last_name: 'Lovelace',
			},
		}

		mockedAxios.get.mockResolvedValue(response)

		await expect(authService.getCurrentUser()).resolves.toEqual(response.data)

		expect(mockedAxios.get).toHaveBeenCalledWith('/api/me')
	})
})
