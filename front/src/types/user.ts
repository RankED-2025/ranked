/**
 * Types et interfaces pour l'authentification et les utilisateurs
 */

export interface User {
  id: number
  email: string
  roles: string[]
  name?: string
  firstname?: string
}

export interface LoginData {
  email: string
  password: string
}

export interface LoginResponse {
  token: string
  refresh_token: string
  user: User
}

export interface RegisterEleveData {
  name: string
  firstname: string
  email: string
  password: string
}

export interface RegisterProfesseurData {
  name: string
  firstname: string
  email: string
  password: string
}

export interface RegisterResponse {
  message: string
  user: {
    id: number
    email: string
  }
}

export interface UserData {
  name?: string
  firstname?: string
  nickname?: string
  email: string
  password?: string
  roles?: string[]
}

export interface AuthResponse {
  token: string
  refresh_token: string
  user: User
}

export interface PasswordResetRequestData {
  email: string
}

export interface PasswordResetConfirmData {
  token: string
  password: string
}

export interface PasswordResetResponse {
  message: string
}
