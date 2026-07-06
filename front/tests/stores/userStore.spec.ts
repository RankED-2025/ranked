import { useUserStore } from './../../src/stores/userStore';
import { authService } from './../../src/services/authService';
import { beforeEach, describe, expect, it, vi } from 'vitest'
import { createPinia, setActivePinia } from 'pinia'
import { mockLoginData, mockRegisterData, mockUser } from '../mocks/user'

const mockedAuthService = vi.mocked(authService)

vi.mock('@/services/authService', () => ({
	authService: {
		login: vi.fn(),
		register: vi.fn(),
		logout: vi.fn(),
		getCurrentUser: vi.fn(),
	},
}))

describe('useUserStore', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
		localStorage.clear()
		vi.clearAllMocks()
	})

	it('initializes with the expected default state', () => {
		const store = useUserStore()

		expect(store.user).toBeNull()
		expect(store.token).toBeNull()
		expect(store.refreshToken).toBeNull()
		expect(store.loading).toBe(false)
	})

	it('reports the authentication status from user and token presence', () => {
		const store = useUserStore()

		expect(store.isLoggedIn()).toBe(false)

		store.user = mockUser
		store.token = 'access-token'

		expect(store.isLoggedIn()).toBe(true)
	})

	it('loginAttempt stores tokens and loads the current user on success', async () => {
		const store = useUserStore()

		mockedAuthService.login.mockResolvedValue({
			token: 'access-token',
			refresh_token: 'refresh-token',
			user: mockUser,
		})
		mockedAuthService.getCurrentUser.mockResolvedValue(mockUser)

		await expect(store.loginAttempt(mockLoginData)).resolves.toBeUndefined()

		expect(mockedAuthService.login).toHaveBeenCalledWith(mockLoginData)
		expect(mockedAuthService.getCurrentUser).toHaveBeenCalledTimes(1)
		expect(store.user).toEqual(mockUser)
		expect(store.token).toBe('access-token')
		expect(store.refreshToken).toBe('refresh-token')
		expect(store.loading).toBe(false)
		expect(localStorage.getItem('access_token')).toBe('access-token')
		expect(localStorage.getItem('refresh_token')).toBe('refresh-token')
	})

	it('loginAttempt disconnects the user when the authentication flow fails', async () => {
		const store = useUserStore()

		mockedAuthService.login.mockRejectedValue(new Error('invalid credentials'))

		await expect(store.loginAttempt(mockLoginData)).rejects.toThrow('invalid credentials')

		expect(store.user).toBeNull()
		expect(store.token).toBeNull()
		expect(store.refreshToken).toBeNull()
		expect(store.loading).toBe(false)
		expect(localStorage.getItem('access_token')).toBeNull()
		expect(localStorage.getItem('refresh_token')).toBeNull()
	})

	it('registerAttempt registers an eleve account successfully', async () => {
		const store = useUserStore()

		mockedAuthService.register.mockResolvedValue({
			message: 'registered',
			user: {
				id: 2,
				email: mockRegisterData.email,
			},
		})

		await expect(store.registerAttempt(mockRegisterData)).resolves.toBeUndefined()

		expect(mockedAuthService.register).toHaveBeenCalledWith(mockRegisterData)
		expect(store.loading).toBe(false)
	})

	it('registerAttempt throws when the service fails', async () => {
		const store = useUserStore()

		mockedAuthService.register.mockRejectedValue(new Error('email already in use'))

		await expect(store.registerAttempt(mockRegisterData)).rejects.toThrow('email already in use')

		expect(store.loading).toBe(false)
	})

	it('initializeFromStorage loads the user when tokens are available', async () => {
		const store = useUserStore()
		localStorage.setItem('access_token', 'stored-access-token')
		localStorage.setItem('refresh_token', 'stored-refresh-token')

		mockedAuthService.getCurrentUser.mockResolvedValue(mockUser)

		await store.initializeFromStorage()

		expect(store.token).toBe('stored-access-token')
		expect(store.refreshToken).toBe('stored-refresh-token')
		expect(store.user).toEqual(mockUser)
		expect(store.loading).toBe(false)
		expect(mockedAuthService.getCurrentUser).toHaveBeenCalledTimes(1)
	})

	it('initializeFromStorage disconnects the user when loading the current user fails', async () => {
		const store = useUserStore()
		const errorSpy = vi.spyOn(console, 'error').mockImplementation(() => undefined)
		localStorage.setItem('access_token', 'stored-access-token')
		localStorage.setItem('refresh_token', 'stored-refresh-token')

		mockedAuthService.getCurrentUser.mockRejectedValue(new Error('cannot load user'))

		await store.initializeFromStorage()

		expect(store.user).toBeNull()
		expect(store.token).toBeNull()
		expect(store.refreshToken).toBeNull()
		expect(localStorage.getItem('access_token')).toBeNull()
		expect(localStorage.getItem('refresh_token')).toBeNull()
		expect(errorSpy).toHaveBeenCalled()

		errorSpy.mockRestore()
	})

	it('forceDisconnect clears the store state and localStorage', () => {
		const store = useUserStore()
		store.user = mockUser
		store.token = 'access-token'
		store.refreshToken = 'refresh-token'
		localStorage.setItem('access_token', 'access-token')
		localStorage.setItem('refresh_token', 'refresh-token')

		store.forceDisconnect()

		expect(store.user).toBeNull()
		expect(store.token).toBeNull()
		expect(store.refreshToken).toBeNull()
		expect(localStorage.getItem('access_token')).toBeNull()
		expect(localStorage.getItem('refresh_token')).toBeNull()
	})

	it('logout calls the service when a refresh token exists and always disconnects', async () => {
		const store = useUserStore()
		store.user = mockUser
		store.token = 'access-token'
		store.refreshToken = 'refresh-token'
		localStorage.setItem('access_token', 'access-token')
		localStorage.setItem('refresh_token', 'refresh-token')

		mockedAuthService.logout.mockResolvedValue(undefined)

		await store.logout()

		expect(mockedAuthService.logout).toHaveBeenCalledWith('refresh-token')
		expect(store.user).toBeNull()
		expect(store.token).toBeNull()
		expect(store.refreshToken).toBeNull()
		expect(localStorage.getItem('access_token')).toBeNull()
		expect(localStorage.getItem('refresh_token')).toBeNull()
	})

	it('hasValidTokens reflects the localStorage token state', () => {
		const store = useUserStore()

		expect(store.hasValidTokens()).toBe(false)

		localStorage.setItem('access_token', 'access-token')
		localStorage.setItem('refresh_token', 'refresh-token')

		expect(store.hasValidTokens()).toBe(true)
	})

	it('exposes the computed getters from the current state', () => {
		const store = useUserStore()
		store.user = mockUser
		store.token = 'access-token'
		store.refreshToken = 'refresh-token'
		store.loading = true

		expect(store.activeUser).toEqual(mockUser)
		expect(store.userToken).toBe('access-token')
		expect(store.getRefreshToken).toBe('refresh-token')
		expect(store.isAuthenticated).toBe(true)
		expect(store.isLoading).toBe(true)
	})
})
