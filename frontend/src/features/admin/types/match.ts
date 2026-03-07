import type { PlayerMin } from '@/features/core/types/player'

export type { PlayerMin as Player }

export interface TournamentMin {
    id: number
    name: string
    slug: string
}

/** MatchOutput */
export interface Match {
    id: number
    player1: PlayerMin
    player2: PlayerMin
    winner: PlayerMin | null
    status: 'pending' | 'confirmed' | 'rejected' | 'cancelled'
    tournament: TournamentMin | null
    confirmedAt: string | null
    createdAt: string
    updatedAt: string
}

export interface MatchFilters {
    player_id?: string
    status?: 'pending' | 'confirmed' | 'rejected' | 'cancelled'
    from?: string
    to?: string
}

export interface MatchesResponse {
    data: Match[]
    total: number
    page: number
    pageSize: number
    totalPages: number
}
