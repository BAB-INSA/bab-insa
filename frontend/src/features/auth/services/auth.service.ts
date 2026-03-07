import apiClient from '@/core/lib/axios.ts'
import axios from 'axios'
import type {User} from "@/features/auth/types/auth.ts";

// Create a standalone axios instance for refresh token requests
// This bypasses all interceptors to avoid Authorization header conflicts
const refreshTokenClient = axios.create({
    baseURL: apiClient.defaults.baseURL,
    headers: {
        'Content-Type': 'application/json'
    }
})

export interface LoginCredentials {
    email: string
    password: string
}

export interface LoginResponse {
    token: string
    refresh_token: string
    user: User
}

export interface RefreshTokenResponse {
    token: string
}

class AuthService {
    async login(credentials: LoginCredentials): Promise<LoginResponse> {
        const response = await apiClient.post('/auth/login', credentials)
        return response.data
    }

    async getCurrentUser(): Promise<User> {
        const response = await apiClient.get('/api/users/me')
        return response.data
    }

    async updateUser(userId: number, userData: Partial<User>): Promise<User> {
        const response = await apiClient.put(`/api/users/${userId}`, userData)
        return response.data
    }

    async refreshToken(refreshToken: string): Promise<RefreshTokenResponse> {
        const response = await refreshTokenClient.post('/auth/refresh', { refresh_token: refreshToken })
        return response.data
    }
}

export default new AuthService()
