import type { PlayerMin } from './player'

/** TeamOutput */
export interface Team {
    id: number
    name: string
    slug: string
    eloRating: number
    totalMatches: number
    wins: number
    losses: number
    createdAt: string
    player1: PlayerMin
    player2: PlayerMin
}
