import type { PlayerMin } from './player'

export interface TournamentMin {
    id: number
    name: string
    slug: string
}

/**
 * Match interface (MatchOutput)
 */
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

/** POST body */
export interface MatchCreateData {
    player1Id: number
    player2Id: number
    winnerId: number
    tournamentId?: number
}

/** PATCH body */
export interface MatchUpdateData {
    status: 'confirmed' | 'rejected' | 'cancelled'
}
