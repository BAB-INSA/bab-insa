/** EloHistoryOutput */
export interface EloHistory {
    id: number
    playerId: number
    matchId: number
    eloBefore: number
    eloAfter: number
    eloChange: number
    opponentId: number | null
    createdAt: string
}

/** TeamEloHistoryOutput */
export interface TeamEloHistory {
    id: number
    playerId: number
    teamMatchId: number
    eloBefore: number
    eloAfter: number
    eloChange: number
    opponentTeamId: number | null
    createdAt: string
}
