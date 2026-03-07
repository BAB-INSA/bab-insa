import type { TournamentMin } from '@/features/core/types/match'

/** TeamMinOutput embedded in TeamMatchOutput */
export interface TeamMin {
    id: number
    name: string
    slug: string
    eloRating: number
}

/** TeamMatchOutput */
export interface TeamMatch {
    id: number
    team1: TeamMin
    team2: TeamMin
    winnerTeam: TeamMin | null
    status: 'pending' | 'confirmed' | 'rejected' | 'cancelled'
    tournament: TournamentMin | null
    confirmedAt: string | null
    createdAt: string
    updatedAt: string
}

export interface TeamMatchFilters {
    team_id?: string
    player_id?: string
    tournament_id?: string
    status?: 'pending' | 'confirmed' | 'rejected' | 'cancelled'
    from?: string
    to?: string
}

export interface TeamMatchesResponse {
    data: TeamMatch[]
    total: number
    page: number
    pageSize: number
    totalPages: number
}
