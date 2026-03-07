/**
 * User interface representing authenticated user data.
 * Minimal fields come from /auth/login, full fields from GET /api/users/me.
 */
export type User = {
    id: number
    email: string
    username: string
    slug: string
    enabled: boolean
    roles: string[]
    lastLogin?: string | null
    nbConnexion?: number
    createdAt?: string
}
