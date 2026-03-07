import apiClient from '@/core/lib/axios.ts'
import type { Match, MatchCreateData } from '@/features/core/types/match.ts'

export interface MatchFilters {
    page?: number
    limit?: number
    player_id?: number
    status?: 'pending' | 'confirmed' | 'rejected' | 'cancelled'
    from?: string
    to?: string
}

export interface MatchPaginatedResponse {
    data: Match[]
    total: number
    page: number
    pageSize: number
    totalPages: number
}

class MatchService {
    async getMatches(): Promise<Match[]> {
        const response = await apiClient.get('/api/matches')
        return response.data.data
    }

    async getMatchesPaginated(filters: MatchFilters = {}): Promise<MatchPaginatedResponse> {
        const params = new URLSearchParams()

        if (filters.page) params.append('page', filters.page.toString())
        if (filters.limit) params.append('limit', filters.limit.toString())
        if (filters.player_id) params.append('player_id', filters.player_id.toString())
        if (filters.status) params.append('status', filters.status)
        if (filters.from) params.append('from', filters.from)
        if (filters.to) params.append('to', filters.to)

        const response = await apiClient.get(`/api/matches?${params.toString()}`)
        return response.data
    }

    async getRecentMatches(): Promise<Match[]> {
        const response = await apiClient.get('/api/matches/recent?limit=5')
        return response.data.data
    }

    async getMatch(id: number): Promise<Match> {
        const response = await apiClient.get(`/api/matches/${id}`)
        return response.data
    }

    async createMatch(matchData: MatchCreateData): Promise<Match> {
        const response = await apiClient.post('/api/matches', matchData)
        return response.data
    }
}

export default new MatchService()
