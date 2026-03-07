import apiClient from '@/core/lib/axios'
import type { Tournament, TournamentFilters, TournamentsResponse, TournamentFormData } from '@/features/admin/types/tournament'

class AdminTournamentsService {
    async getTournaments(filters: TournamentFilters = {}, page: number = 1, limit: number = 20): Promise<TournamentsResponse> {
        const params = new URLSearchParams({
            page: page.toString(),
            limit: limit.toString(),
            ...Object.fromEntries(
                Object.entries(filters).filter(([, value]) => value !== undefined && value !== '')
            ),
        })

        const response = await apiClient.get(`/api/tournaments?${params}`)
        return response.data
    }

    async getTournamentById(id: number): Promise<Tournament> {
        const response = await apiClient.get(`/api/tournaments/${id}`)
        return response.data
    }

    async createTournament(data: TournamentFormData): Promise<Tournament> {
        const response = await apiClient.post('/api/tournaments', data)
        return response.data
    }

    async updateTournament(id: number, data: Partial<TournamentFormData>): Promise<Tournament> {
        const response = await apiClient.put(`/api/tournaments/${id}`, data)
        return response.data
    }

    async deleteTournament(id: number): Promise<void> {
        await apiClient.delete(`/api/tournaments/${id}`)
    }
}

export default new AdminTournamentsService()
