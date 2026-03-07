import type { RouteRecordRaw } from 'vue-router'
import AdminUsers from '@/features/admin/views/AdminUsers.vue'
import AdminMatches from '@/features/admin/views/AdminMatches.vue'
import AdminTeamMatches from '@/features/admin/views/AdminTeamMatches.vue'
import AdminTournaments from '@/features/admin/views/AdminTournaments.vue'

export const adminRoutes: RouteRecordRaw[] = [
    {
        path: 'admin/users',
        name: 'AdminUsers',
        component: AdminUsers,
        meta: { 
            requiresAuth: true,
            requiresAdmin: true,
            title: 'Administration - Gestion des utilisateurs'
        }
    },
    {
        path: 'admin/matches',
        name: 'AdminMatches',
        component: AdminMatches,
        meta: { 
            requiresAuth: true,
            requiresAdmin: true,
            title: 'Administration - Gestion des matchs'
        }
    },
    {
        path: 'admin/team-matches',
        name: 'AdminTeamMatches',
        component: AdminTeamMatches,
        meta: { 
            requiresAuth: true,
            requiresAdmin: true,
            title: 'Administration - Gestion des matchs 2v2'
        }
    },
    {
        path: 'admin/tournaments',
        name: 'AdminTournaments',
        component: AdminTournaments,
        meta: {
            requiresAuth: true,
            requiresAdmin: true,
            title: 'Administration - Gestion des tournois'
        }
    }
    // Ici on pourra ajouter d'autres routes admin plus tard :
    // {
    //     path: 'admin/dashboard',
    //     name: 'AdminDashboard',
    //     component: () => import('@/features/admin/views/AdminDashboard.vue'),
    //     meta: { 
    //         requiresAuth: true,
    //         requiresAdmin: true,
    //         title: 'Administration - Dashboard'
    //     }
    // },
    // {
    //     path: 'admin/logs',
    //     name: 'AdminLogs', 
    //     component: () => import('@/features/admin/views/AdminLogs.vue'),
    //     meta: { 
    //         requiresAuth: true,
    //         requiresAdmin: true,
    //         title: 'Administration - Logs'
    //     }
    // }
]