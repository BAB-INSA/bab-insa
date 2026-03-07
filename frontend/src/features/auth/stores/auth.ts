import { defineStore } from 'pinia'
import authService from '@/features/auth/services/auth.service.ts'
import toastService from '@/shared/services/toast.service.ts'
import type { User } from '@/features/auth/types/auth.ts'
import type { ApiError } from '@/core/types';

/**
 * Authentication state interface
 */
interface AuthState {
    token: string | null
    refreshToken: string | null
    user: User | null
    loading: boolean
    error: string | null
    tokenExpiration: number | null
}

/**
 * Authentication store to manage the user session
 * Handles login, logout, token refresh, and user data
 */
export const useAuthStore = defineStore('auth', {
    /**
     * Initial state for authentication
     */
    state: (): AuthState => ({
        token: localStorage.getItem('token') || null,
        refreshToken: localStorage.getItem('refresh_token') || null,
        user: null,
        loading: false,
        error: null,
        tokenExpiration: localStorage.getItem('token_expiration') ? Number(localStorage.getItem('token_expiration')) : null
    }),

    /**
     * Computed properties for authentication state
     */
    getters: {
        /**
         * Check if the user is authenticated
         */
        isAuthenticated: (state) => !!state.token,

        /**
         * Get the current user
         */
        getUser: (state) => state.user,

        /**
         * Get the last error message
         */
        getError: (state) => state.error,

        /**
         * Check if authentication is in progress
         */
        isLoading: (state) => state.loading,

        /**
         * Check if the token is expired
         * Adds a 10-second safety margin
         */
        isTokenExpired: (state) => {
            if (!state.tokenExpiration) return true
            return Date.now() >= (state.tokenExpiration - 10) * 1000
        }
    },

    /**
     * Actions to modify the authentication state
     */
    actions: {
        /**
         * Login with email and password
         * @param email User's email
         * @param password User's password
         * @returns Promise<boolean> True if login was successful
         */
        async login(email: string, password: string) {
            this.loading = true
            this.error = null

            try {
                const response = await authService.login({ email, password })

                // Store authentication data using the correct property names from API
                this.setAuthData(response.token, response.refresh_token, response.user)

                // Show success notification
                toastService.success('Connexion réussie', `Bienvenue, ${response.user.username}!`)

                return true
            } catch (error: unknown) {
                const apiError = error as ApiError;
                
                // Handle specific 401 Unauthorized error
                if (apiError.response?.status === 401) {
                    this.error = 'Email ou mot de passe incorrect'
                    toastService.error('Connexion échouée', 'Email ou mot de passe incorrect')
                } else {
                    this.error = apiError.response?.data?.message || 'Une erreur est survenue lors de la connexion'
                    toastService.error('Connexion échouée', this.error)
                }

                return false
            } finally {
                this.loading = false
            }
        },

        /**
         * Parse JWT token to extract data
         * @param token JWT token string
         * @returns Parsed token payload or null if invalid
         */
        parseJwt(token: string) {
            try {
                const base64Url = token.split('.')[1]
                const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
                const jsonPayload = decodeURIComponent(atob(base64).split('').map(function(c) {
                    return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2)
                }).join(''))

                return JSON.parse(jsonPayload)
            } catch (_e) {
                return null
            }
        },

        /**
         * Set authentication data in the store and localStorage
         * @param token JWT token
         * @param refreshToken Refresh token
         * @param user User data
         */
        setAuthData(token: string, refreshToken: string, user: User) {
            this.token = token
            this.refreshToken = refreshToken
            this.user = user

            // Parse token to get expiration time
            const parsedToken = this.parseJwt(token)
            if (parsedToken && parsedToken.exp) {
                this.tokenExpiration = parsedToken.exp
                localStorage.setItem('token_expiration', String(parsedToken.exp))
            }

            localStorage.setItem('token', token)
            localStorage.setItem('refresh_token', refreshToken)
        },

        /**
         * Refresh the authentication token
         * @returns Promise<boolean> True if token refresh was successful
         */
        async refreshAuthToken() {
            if (!this.refreshToken) return false

            try {
                const response = await authService.refreshToken(this.refreshToken)

                // Update access token (Symfony does not rotate the refresh token)
                this.token = response.token

                // Update expiration
                const parsedToken = this.parseJwt(response.token)
                if (parsedToken && parsedToken.exp) {
                    this.tokenExpiration = parsedToken.exp
                    localStorage.setItem('token_expiration', String(parsedToken.exp))
                }

                localStorage.setItem('token', response.token)

                // Show notification
                toastService.info('Token Refreshed', 'Your session has been extended', {
                    duration: 3000,
                    autoClose: true
                })

                return true
            } catch (_error) {
                // If refresh fails, log the user out but don't show notification
                // (the interceptor will handle showing the notification)
                this.clearAuthData()
                return false
            }
        },

        /**
         * Clear authentication data from store and localStorage
         * Internal helper method that doesn't show notifications
         */
        clearAuthData() {
            this.token = null
            this.refreshToken = null
            this.user = null
            this.tokenExpiration = null
            localStorage.removeItem('token')
            localStorage.removeItem('refresh_token')
            localStorage.removeItem('token_expiration')
        },

        /**
         * Fetch current user data from API
         * @returns Promise<boolean> True if user data was successfully fetched
         */
        async fetchCurrentUser() {
            if (!this.token) return false

            try {
                const user = await authService.getCurrentUser()
                this.user = user
                return true
            } catch (error) {
                console.error('Failed to fetch user data:', error)
                return false
            }
        },

        /**
         * Logout the current user
         * Clears tokens and user data from store and localStorage
         */
        async logout(suppressNotification = false) {
            // Show notification unless suppressed
            if (!suppressNotification) {
                toastService.success('Déconnecté', 'Vous avez été déconnecté avec succès')
            }

            // Clear local data
            this.clearAuthData()
        },
    },
    persist: true
})
