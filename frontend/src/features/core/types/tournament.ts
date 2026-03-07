export type {
    Tournament,
    TournamentFilters,
    TournamentsResponse,
} from '@/features/admin/types/tournament'

import type { Team } from '@/features/core/types/team'
import type { TeamMatch } from '@/features/admin/types/team-match'

export interface TournamentTeamsResponse {
    data: Team[]
    total: number
    page: number
    pageSize: number
    totalPages: number
}

export interface TournamentMatchesResponse {
    data: TeamMatch[]
    total: number
    page: number
    pageSize: number
    totalPages: number
}
