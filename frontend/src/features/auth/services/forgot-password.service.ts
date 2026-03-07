import apiClient from '@/core/lib/axios.ts'

interface ForgotPasswordRequest {
    email: string
}

interface ResetPasswordRequest {
    token: string
    newPassword: string
}

class ForgotPasswordService {
    async sendPasswordResetLink(data: ForgotPasswordRequest): Promise<unknown> {
        const response = await apiClient.post('/auth/reset-password/send-link', data)
        return response.data
    }

    async confirmPasswordReset(data: ResetPasswordRequest): Promise<unknown> {
        const response = await apiClient.post('/auth/reset-password/confirm', data)
        return response.data
    }
}

export default new ForgotPasswordService()
