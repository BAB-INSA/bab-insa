import apiClient from '@/core/lib/axios.ts'
import type { Player, EloChartEntry } from '@/features/core/types/player.ts'
import type { EloHistory, TeamEloHistory } from '@/features/core/types/elo-history.ts'
import type { Match } from '@/features/core/types/match.ts'
import type { Team } from '@/features/core/types/team.ts'
import type { PaginatedResponse, PaginationParams } from '@/shared/types/pagination.ts'

class PlayerService {
    async getPlayers(params?: PaginationParams): Promise<PaginatedResponse<Player>> {
        const queryParams = new URLSearchParams()

        if (params?.page) queryParams.append('page', params.page.toString())
        if (params?.pageSize) queryParams.append('limit', params.pageSize.toString())

        const queryString = queryParams.toString()
        const url = `/api/players${queryString ? `?${queryString}` : ''}`

        const response = await apiClient.get(url)
        return response.data
    }

    async getTopPlayers(limit: number = 100): Promise<Player[]> {
        const response = await apiClient.get(`/api/players/top?limit=${limit}`)
        return response.data.data
    }

    async getPlayer(id: number): Promise<Player> {
        const response = await apiClient.get(`/api/players/${id}`)
        return response.data
    }

    async getPlayerMatches(id: number, params?: PaginationParams): Promise<PaginatedResponse<Match>> {
        const queryParams = new URLSearchParams()

        if (params?.page) queryParams.append('page', params.page.toString())
        if (params?.pageSize) queryParams.append('limit', params.pageSize.toString())

        const queryString = queryParams.toString()
        const url = `/api/players/${id}/matches${queryString ? `?${queryString}` : ''}`

        const response = await apiClient.get(url)
        return response.data
    }

    async getPlayerEloHistory(id: number, limit?: number, matchType?: 'solo' | 'team'): Promise<EloChartEntry[]> {
        const queryParams = new URLSearchParams()
        if (limit) queryParams.append('limit', limit.toString())
        const queryString = queryParams.toString()

        if (matchType === 'team') {
            const url = `/api/players/${id}/team-elo-history${queryString ? `?${queryString}` : ''}`
            const response = await apiClient.get<{ data: TeamEloHistory[] }>(url)
            return response.data.data.map((entry: TeamEloHistory): EloChartEntry => ({
                date: entry.createdAt,
                elo: entry.eloAfter,
                eloChange: entry.eloChange,
                matchType: 'team',
            }))
        }

        const url = `/api/players/${id}/elo-history${queryString ? `?${queryString}` : ''}`
        const response = await apiClient.get<{ data: EloHistory[] }>(url)
        return response.data.data.map((entry: EloHistory): EloChartEntry => ({
            date: entry.createdAt,
            elo: entry.eloAfter,
            eloChange: entry.eloChange,
            matchType: matchType || 'ranked',
        }))
    }

    async getTopTeams(limit: number = 10): Promise<Player[]> {
        const response = await apiClient.get(`/api/players/top-teams?limit=${limit}`)
        return response.data.data
    }

    async getPlayerTeams(id: number): Promise<Team[]> {
        const response = await apiClient.get(`/api/players/${id}/teams`)
        return response.data.data
    }
}

export default new PlayerService()
