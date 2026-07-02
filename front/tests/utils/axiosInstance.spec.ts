import { beforeEach, describe, expect, it, vi } from 'vitest'
import axiosInstance from '@utils/axiosInstance'

const { mockUseRequest, mockUseResponse, mockAxiosInstance, mockAxiosPost } = vi.hoisted(() => {
	const mockUseRequest = vi.fn()
	const mockUseResponse = vi.fn()
	const mockAxiosInstance = vi.fn()
	const mockAxiosPost = vi.fn()

	mockAxiosInstance.interceptors = {
		request: { use: mockUseRequest },
		response: { use: mockUseResponse },
	}

	return {
		mockUseRequest,
		mockUseResponse,
		mockAxiosInstance,
		mockAxiosPost,
	}
})

vi.mock('axios', () => ({
	default: {
		create: vi.fn(() => mockAxiosInstance),
		post: mockAxiosPost,
	},
}))

describe('axiosInstance', () => {
	beforeEach(() => {
		localStorage.clear()
	})

	it('creates the axios instance with JSON headers and registers interceptors', () => {
		expect(axiosInstance).toBe(mockAxiosInstance)
		expect(mockUseRequest).toHaveBeenCalledTimes(1)
		expect(mockUseResponse).toHaveBeenCalledTimes(1)
	})

	it('adds the JWT token to request headers when it exists', () => {
		const requestInterceptor = mockUseRequest.mock.calls[0][0]
		const config = { headers: {} as Record<string, string> }

		localStorage.setItem('access_token', 'access-token')

		const returnedConfig = requestInterceptor(config)

		expect(returnedConfig.headers.Authorization).toBe('Bearer access-token')
	})

	it('refreshes tokens and retries the original request after a 401 response', async () => {
		const responseInterceptor = mockUseResponse.mock.calls[0][1]
		const originalRequest = {
			headers: {} as Record<string, string>,
			_retry: false,
		}

		localStorage.setItem('refresh_token', 'refresh-token')
		mockAxiosPost.mockResolvedValue({
			data: {
				token: 'new-access-token',
				refresh_token: 'new-refresh-token',
			},
		})
		mockAxiosInstance.mockResolvedValue({ data: 'retried' })

		await expect(
			Promise.resolve().then(() =>
				responseInterceptor({
					config: originalRequest,
					response: { status: 401 },
				}),
			),
		).resolves.toEqual({ data: 'retried' })

		expect(mockAxiosPost).toHaveBeenCalledWith('/api/token/refresh', {
			refresh_token: 'refresh-token',
		})
		expect(localStorage.getItem('access_token')).toBe('new-access-token')
		expect(localStorage.getItem('refresh_token')).toBe('new-refresh-token')
		expect(originalRequest.headers.Authorization).toBe('Bearer new-access-token')
		expect(mockAxiosInstance).toHaveBeenCalledWith(originalRequest)
	})
})