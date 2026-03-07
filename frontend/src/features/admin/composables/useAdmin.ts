import { computed } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth'
import type { AdminPermissions } from '@/features/admin/types/admin'

export function useAdmin() {
    const authStore = useAuthStore()

    const isAdmin = computed(() => {
        return authStore.user?.roles.includes('ROLE_ADMIN') || authStore.user?.roles.includes('ROLE_SUPER_ADMIN') || false
    })

    const isSuperAdmin = computed(() => {
        return authStore.user?.roles.includes('ROLE_SUPER_ADMIN') || false
    })

    const hasRole = (role: string) => {
        return authStore.user?.roles.includes(role) || false
    }

    const permissions = computed<AdminPermissions>(() => {
        if (!authStore.user) {
            return {
                canManageUsers: false,
                canViewStats: false,
                canViewLogs: false,
                canManageRoles: false,
                canDeleteUsers: false,
                canViewSystemInfo: false
            }
        }

        const userRoles = authStore.user.roles

        return {
            canManageUsers: userRoles.includes('ROLE_ADMIN') || userRoles.includes('ROLE_SUPER_ADMIN'),
            canViewStats: userRoles.includes('ROLE_ADMIN') || userRoles.includes('ROLE_SUPER_ADMIN'),
            canViewLogs: userRoles.includes('ROLE_ADMIN') || userRoles.includes('ROLE_SUPER_ADMIN'),
            canManageRoles: userRoles.includes('ROLE_SUPER_ADMIN'),
            canDeleteUsers: userRoles.includes('ROLE_SUPER_ADMIN'),
            canViewSystemInfo: userRoles.includes('ROLE_ADMIN') || userRoles.includes('ROLE_SUPER_ADMIN')
        }
    })

    const checkPermission = (permission: keyof AdminPermissions) => {
        return permissions.value[permission]
    }

    return {
        isAdmin,
        isSuperAdmin,
        hasRole,
        permissions,
        checkPermission
    }
}