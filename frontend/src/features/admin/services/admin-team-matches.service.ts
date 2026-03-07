import apiClient from '@/core/lib/axios'
import type { TeamMatch, TeamMatchFilters, TeamMatchesResponse } from '@/features/admin/types/team-match'

class AdminTeamMatchesService {
    async getTeamMatches(filters: TeamMatchFilters = {}, page: number = 1, limit: number = 20): Promise<TeamMatchesResponse> {
        const params = new URLSearchParams({
            page: page.toString(),
            limit: limit.toString(),
            ...Object.fromEntries(
                Object.entries(filters).filter(([, value]) => value !== undefined && value !== '' && value !== 'all')
            ),
        })

        const response = await apiClient.get(`/api/team-matches?${params}`)
        return response.data
    }

    async getTeamMatchById(id: number): Promise<TeamMatch> {
        const response = await apiClient.get(`/api/team-matches/${id}`)
        return response.data
    }

    async updateTeamMatch(matchId: number, data: { status: string }): Promise<TeamMatch> {
        const response = await apiClient.patch(`/api/team-matches/${matchId}`, data)
        return response.data
    }

    async deleteTeamMatch(matchId: number): Promise<void> {
        await apiClient.delete(`/api/team-matches/${matchId}`)
    }

    async cancelTeamMatch(matchId: number): Promise<TeamMatch> {
        const response = await apiClient.patch(`/api/team-matches/${matchId}/cancel`)
        return response.data
    }
}

export default new AdminTeamMatchesService()
