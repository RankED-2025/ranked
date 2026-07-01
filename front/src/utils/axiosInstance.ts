import axios, { type AxiosInstance } from 'axios'

const API_URL = (import.meta.env.VITE_API_URL ?? '').replace(/\/$/, '')

const axiosInstance: AxiosInstance = axios.create({
  baseURL: API_URL || undefined,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
})

// Intercepteur pour ajouter le token JWT à chaque requête
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('access_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Intercepteur pour gérer le refresh token
axiosInstance.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error.config

    const isLoginEndpoint = originalRequest.url?.endsWith('/api/login')
    const refreshToken = localStorage.getItem('refresh_token')

    if (
      error.response?.status === 401 &&
      !originalRequest._retry &&
      !isLoginEndpoint &&
      refreshToken
    ) {
      originalRequest._retry = true

      try {
        const response = await axios.post(`${API_URL}/api/token/refresh`, {
          refresh_token: refreshToken,
        })

        const { token, refresh_token } = response.data
        localStorage.setItem('access_token', token)
        localStorage.setItem('refresh_token', refresh_token)

        originalRequest.headers.Authorization = `Bearer ${token}`
        return axiosInstance(originalRequest)
      } catch (refreshError) {
        localStorage.removeItem('access_token')
        localStorage.removeItem('refresh_token')
        window.location.href = '/login'
        return Promise.reject(refreshError)
      }
    }

    return Promise.reject(error)
  }
)

export default axiosInstance
