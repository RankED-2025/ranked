import { User, LoginData, RegisterData } from "../../src/types/user"

export const mockUser: User = {
	id: 1,
	email: 'user@example.com',
	roles: ['ROLE_USER'],
	name: 'Doe',
	firstname: 'Jane',
}

export const mockLoginData: LoginData = {
	email: 'user@example.com',
	password: 'password123',
}

export const mockRegisterData: RegisterData = {
	name: 'Doe',
	firstname: 'Jane',
	email: 'user@example.com',
	password: 'password123',
}