/** TournamentOutput */
export interface Tournament {
    id: number
    name: string
    slug: string
    type: 'solo' | 'team'
    status: 'opened' | 'ongoing' | 'finished'
    description: string | null
    nbParticipants: number
    nbMatches: number
    createdAt: string
    updatedAt: string
}

export interface TournamentFilters {
    type?: 'solo' | 'team'
    status?: 'opened' | 'ongoing' | 'finished'
}

export interface TournamentsResponse {
    data: Tournament[]
    total: number
    page: number
    pageSize: number
    totalPages: number
}

export interface TournamentFormData {
    name: string
    type: 'solo' | 'team'
    status: 'opened' | 'ongoing' | 'finished'
    description?: string
}
