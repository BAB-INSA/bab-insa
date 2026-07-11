import apiClient from '@/core/lib/axios'
import type { Match, MatchFilters, MatchesResponse } from '@/features/admin/types/match'

class AdminMatchesService {
    async getMatches(filters: MatchFilters = {}, page: number = 1, limit: number = 20): Promise<MatchesResponse> {
        const params = new URLSearchParams({
            page: page.toString(),
            limit: limit.toString(),
            ...Object.fromEntries(
                Object.entries(filters).filter(([, value]) => value !== undefined && value !== '')
            ),
        })

        const response = await apiClient.get(`/api/matches?${params}`)
        return response.data
    }

    async getMatchById(id: number): Promise<Match> {
        const response = await apiClient.get(`/api/matches/${id}`)
        return response.data
    }

    async updateMatch(matchId: number, data: { status?: string; winnerId?: number }): Promise<Match> {
        const response = await apiClient.patch(`/api/matches/${matchId}`, data)
        return response.data
    }

    async deleteMatch(matchId: number): Promise<void> {
        await apiClient.delete(`/api/matches/${matchId}`)
    }

    async cancelMatch(matchId: number): Promise<Match> {
        const response = await apiClient.patch(`/api/matches/${matchId}/cancel`)
        return response.data
    }
}

export default new AdminMatchesService()
