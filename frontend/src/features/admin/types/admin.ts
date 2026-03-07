export interface AdminStats {
    totalUsers: number
    activeUsers: number
    totalMatches: number
    todayMatches: number
    totalPlayers: number
    activePlayers: number
}

export interface UserFilters {
    search?: string
    role?: string
    status?: 'active' | 'inactive' | 'all'
    sortBy?: 'username' | 'email' | 'created_at' | 'last_login'
    sortOrder?: 'asc' | 'desc'
}

export interface AdminPermissions {
    canManageUsers: boolean
    canViewStats: boolean
    canViewLogs: boolean
    canManageRoles: boolean
    canDeleteUsers: boolean
    canViewSystemInfo: boolean
}
