import apiClient from '@/core/lib/axios.ts'
import type { EloHistory } from '@/features/core/types/elo-history.ts'

class EloHistoryService {
    async getRecentEloHistory(): Promise<EloHistory[]> {
        const response = await apiClient.get('/api/elo-history/recent?limit=10')
        return response.data.data
    }
}

export default new EloHistoryService()
