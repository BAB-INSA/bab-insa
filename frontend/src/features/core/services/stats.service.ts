import apiClient from '@/core/lib/axios.ts'

/** StatsOutput */
export interface Stats {
    totalPlayers: number
    totalMatches: number
    totalTeams: number
    totalTeamMatches: number
    totalTournaments: number
}

class StatsService {
    async getStats(): Promise<Stats> {
        const response = await apiClient.get('/api/stats')
        return response.data
    }
}

export default new StatsService()
