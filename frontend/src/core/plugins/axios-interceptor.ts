import apiClient from '@/core/lib/axios.ts'
import { useAuthStore } from '@/features/auth/stores/auth.ts'
import router from '@/core/router'
import toastService from '@/shared/services/toast.service.ts'

// Enable debug mode in development
const DEBUG = import.meta.env.DEV

// Flag to prevent multiple logout notifications
let isHandlingLogout = false

// Normalize API Platform Hydra responses:
// - collections → { data, total, page, pageSize, totalPages }
// - single items → strip @context, @type, @id fields
//
// Handles both API Platform formats:
//   - old: hydra:member / hydra:totalItems / hydra:view
//   - new: member / totalItems / view  (AP v4 without hydra prefix)
function normalizeHydraResponse(data: unknown): unknown {
    if (data && typeof data === 'object') {
        const obj = data as Record<string, unknown>

        // Detect collection by presence of members key (either format)
        const membersKey = 'hydra:member' in obj ? 'hydra:member' : 'member' in obj ? 'member' : null

        if (membersKey) {
            const members = (obj[membersKey] as unknown[]) ?? []
            const totalItems = ((obj['hydra:totalItems'] ?? obj['totalItems']) as number) ?? members.length

            let page = 1
            const view = (obj['hydra:view'] ?? obj['view']) as Record<string, unknown> | undefined
            if (view?.['@id']) {
                const m = (view['@id'] as string).match(/[?&]page=(\d+)/)
                if (m) page = parseInt(m[1])
            }

            const pageSize = members.length || 20
            const totalPages = totalItems > 0 ? Math.ceil(totalItems / pageSize) : 1

            return { data: members, total: totalItems, page, pageSize, totalPages }
        }

        // Single item: strip JSON-LD metadata
        if ('@type' in obj || '@context' in obj) {
            const { '@context': _ctx, '@type': _type, '@id': _id, ...rest } = obj
            return rest
        }
    }
    return data
}

// Setup for request and response interceptors
export default {
    setup() {
        // Routes that must NOT carry the Authorization header (they are public and/or issue tokens)
        const NO_AUTH_ROUTES = ['/auth/login', '/auth/register', '/auth/refresh', '/auth/reset-password']

        // Request interceptor - runs before each API request
        apiClient.interceptors.request.use(
            config => {
                const authStore = useAuthStore()

                const isPublicAuthRoute = NO_AUTH_ROUTES.some(route => config.url?.startsWith(route))

                // If we have a token, add it to the request header (except for public auth routes)
                if (authStore.token && !isPublicAuthRoute) {
                    config.headers['Authorization'] = `Bearer ${authStore.token}`

                    // Check if the token is about to expire (less than 30 seconds)
                    if (authStore.tokenExpiration) {
                        const now = Math.floor(Date.now() / 1000)
                        const timeLeft = authStore.tokenExpiration - now

                        if (DEBUG) {
                            console.log(`[Debug] Time remaining for token: ${timeLeft} seconds`)
                        }
                    }
                }

                return config
            },
            error => {
                return Promise.reject(error)
            }
        )

        // Response interceptor - handles Hydra normalization + 401 Unauthorized + token refresh
        apiClient.interceptors.response.use(
            response => {
                // Normalize API Platform Hydra collection responses
                response.data = normalizeHydraResponse(response.data)
                return response
            },
            async error => {
                const originalRequest = error.config
                const isLoginRequest = originalRequest.url?.includes('/auth/login')

                // Avoid infinite refresh loops
                if (originalRequest._retry) {
                    return Promise.reject(error)
                }

                // If the error is 401 (Unauthorized) and not a login request
                if (error.response && error.response.status === 401 && !isLoginRequest) {
                    const authStore = useAuthStore()

                    // If we don't have a refresh token or the request was for a refresh token
                    if (!authStore.refreshToken || originalRequest.url === '/auth/refresh') {
                        if (!isHandlingLogout) {
                            isHandlingLogout = true
                            await authStore.logout()
                            toastService.error('Session Expired', 'Please log in again')
                            router.push({name: 'Login'});
                            setTimeout(() => { isHandlingLogout = false }, 1000)
                        }
                        return Promise.reject(error)
                    }

                    originalRequest._retry = true

                    try {
                        const success = await authStore.refreshAuthToken()

                        if (success) {
                            originalRequest.headers['Authorization'] = `Bearer ${authStore.token}`
                            return apiClient(originalRequest)
                        } else {
                            if (!isHandlingLogout) {
                                isHandlingLogout = true
                                await authStore.logout()
                                toastService.error('Session Expired', 'Please log in again')
                                router.push({name: 'Login'});
                                setTimeout(() => { isHandlingLogout = false }, 1000)
                            }
                            return Promise.reject(error)
                        }
                    } catch (refreshError) {
                        if (!isHandlingLogout) {
                            isHandlingLogout = true
                            await authStore.logout()
                            toastService.error('Session Expired', 'Please log in again')
                            router.push({name: 'Login'});
                            setTimeout(() => { isHandlingLogout = false }, 1000)
                        }
                        return Promise.reject(refreshError)
                    }
                }

                return Promise.reject(error)
            }
        )

        // Debug logging for development environments
        if (DEBUG) {
            apiClient.interceptors.request.use(request => {
                console.log('Outgoing request:', request.method?.toUpperCase(), request.url)
                return request
            })

            apiClient.interceptors.response.use(response => {
                console.log('Incoming response:', response.status, response.config.url)
                return response
            }, error => {
                console.error('Response error:', error.response?.status, error.config?.url)
                return Promise.reject(error)
            })
        }
    }
}