import apiClient from '@/core/lib/axios'
import type { TeamMatch } from '@/features/admin/types/team-match'

export interface TeamMatchCreateData {
    team1Id: number
    team2Id: number
    winnerTeamId: number
    tournamentId?: number
}

export interface TeamMatchFilters {
    page?: number
    limit?: number
    team_id?: number
    player_id?: number
    tournament_id?: number
    status?: 'pending' | 'confirmed' | 'rejected' | 'cancelled'
    from?: string
    to?: string
}

export interface TeamMatchPaginatedResponse {
    data: TeamMatch[]
    total: number
    page: number
    pageSize: number
    totalPages: number
}

class TeamMatchService {
    async createTeamMatch(teamMatchData: TeamMatchCreateData): Promise<TeamMatch> {
        const response = await apiClient.post('/api/team-matches', teamMatchData)
        return response.data
    }

    async getTeamMatchesPaginated(filters: TeamMatchFilters = {}): Promise<TeamMatchPaginatedResponse> {
        const params = new URLSearchParams()

        if (filters.page) params.append('page', filters.page.toString())
        if (filters.limit) params.append('limit', filters.limit.toString())
        if (filters.team_id) params.append('team_id', filters.team_id.toString())
        if (filters.player_id) params.append('player_id', filters.player_id.toString())
        if (filters.tournament_id) params.append('tournament_id', filters.tournament_id.toString())
        if (filters.status) params.append('status', filters.status)
        if (filters.from) params.append('from', filters.from)
        if (filters.to) params.append('to', filters.to)

        const response = await apiClient.get(`/api/team-matches?${params.toString()}`)
        return response.data
    }
}

export default new TeamMatchService()
