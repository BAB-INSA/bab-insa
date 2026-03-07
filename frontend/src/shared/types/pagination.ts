/**
 * Generic paginated response interface
 */
export interface PaginatedResponse<T> {
    data: T[]
    total: number
    page: number
    pageSize: number
    totalPages: number
}

/**
 * Pagination parameters for API requests
 */
export interface PaginationParams {
    page?: number
    pageSize?: number
    limit?: number
    wins?: 1
    losses?: 1
}