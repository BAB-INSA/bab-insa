/**
 * Minimal player info embedded in matches and teams (PlayerMinOutput)
 */
export interface PlayerMin {
    id: number
    username: string
    slug: string
    eloRating: number
    rank: number
}

/**
 * Full player (PlayerOutput)
 */
export interface Player {
    id: number
    username: string
    slug: string
    eloRating: number
    rank: number
    totalMatches: number
    wins: number
    losses: number
    teamEloRating: number
    teamRank: number
    teamTotalMatches: number
    teamWins: number
    teamLosses: number
    createdAt: string
}

/**
 * ELO History entry for charts (simplified format)
 */
export interface EloChartEntry {
    date: string
    elo: number
    eloChange: number
    matchType?: string
}
