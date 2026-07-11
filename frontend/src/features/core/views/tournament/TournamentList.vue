<template>
    <div class="container mx-auto py-6 space-y-6">
        <div class="flex flex-col gap-4">
            <h1 class="text-3xl font-bold">Tournois</h1>
            <p class="text-muted-foreground">Consultez la liste des tournois en cours et terminés</p>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="pt-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Select v-model="typeFilter">
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="Type" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Tous les types</SelectItem>
                                <SelectItem value="solo">Solo</SelectItem>
                                <SelectItem value="team">Équipe</SelectItem>
                            </SelectContent>
                        </Select>

                        <Select v-model="statusFilter">
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="Statut" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Tous</SelectItem>
                                <SelectItem value="opened">Ouvert</SelectItem>
                                <SelectItem value="ongoing">En cours</SelectItem>
                                <SelectItem value="finished">Terminé</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex space-x-2">
                        <Button variant="outline" @click="resetFilters">
                            Réinitialiser
                        </Button>
                        <Button @click="loadTournaments">
                            Actualiser
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Loading State -->
        <div v-if="loading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <!-- Tournaments Table -->
        <Card v-else-if="tournaments.length > 0">
            <CardHeader>
                <CardTitle>Liste des Tournois</CardTitle>
                <p class="text-sm text-muted-foreground">
                    {{ totalTournaments }} tournoi(s) trouvé(s)
                </p>
            </CardHeader>
            <CardContent>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-3 px-4 font-medium text-muted-foreground">Nom</th>
                                <th class="text-left py-3 px-4 font-medium text-muted-foreground">Type</th>
                                <th class="text-left py-3 px-4 font-medium text-muted-foreground">Statut</th>
                                <th class="text-center py-3 px-4 font-medium text-muted-foreground">Participants</th>
                                <th class="text-center py-3 px-4 font-medium text-muted-foreground">Matchs</th>
                                <th class="text-right py-3 px-4 font-medium text-muted-foreground">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="tournament in tournaments"
                                :key="tournament.id"
                                class="border-b last:border-0 hover:bg-muted/50 transition-colors"
                            >
                                <td class="py-3 px-4">
                                    <TournamentLink :tournament="tournament" />
                                </td>
                                <td class="py-3 px-4">
                                    <Badge :variant="tournament.type === 'solo' ? 'secondary' : 'default'">
                                        {{ tournament.type === 'solo' ? 'Solo' : 'Équipe' }}
                                    </Badge>
                                </td>
                                <td class="py-3 px-4">
                                    <Badge :variant="getStatusVariant(tournament.status)">
                                        {{ getStatusLabel(tournament.status) }}
                                    </Badge>
                                </td>
                                <td class="py-3 px-4 text-center">{{ tournament.nbParticipants }}</td>
                                <td class="py-3 px-4 text-center">{{ tournament.nbMatches }}</td>
                                <td class="py-3 px-4 text-right text-muted-foreground">
                                    {{ formatDate(tournament.createdAt) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>

        <!-- Empty State -->
        <Card v-else>
            <CardContent class="py-12">
                <div class="text-center">
                    <h3 class="text-lg font-medium mb-2">Aucun tournoi trouvé</h3>
                    <p class="text-muted-foreground">Aucun tournoi ne correspond à vos critères de recherche.</p>
                </div>
            </CardContent>
        </Card>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center">
            <Pagination
                v-slot="{ page }"
                :items-per-page="pageSize"
                :total="totalTournaments"
                :default-page="currentPage"
                @update:page="goToPage"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationPrevious />
                    <template v-for="(item, index) in items" :key="index">
                        <PaginationItem
                            v-if="item.type === 'page'"
                            :value="item.value"
                            :is-active="item.value === page"
                        >
                            {{ item.value }}
                        </PaginationItem>
                        <PaginationEllipsis v-else :index="index" />
                    </template>
                    <PaginationNext />
                </PaginationContent>
            </Pagination>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/shared/components/ui/card'
import { Button } from '@/shared/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'
import { Badge } from '@/shared/components/ui/badge'
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/shared/components/ui/pagination'
import tournamentService from '@/features/core/services/tournament.service'
import TournamentLink from '@/features/core/components/TournamentLink.vue'
import type { Tournament, TournamentFilters } from '@/features/core/types/tournament'

const tournaments = ref<Tournament[]>([])
const loading = ref(false)
const typeFilter = ref('all')
const statusFilter = ref('all')
const currentPage = ref(1)
const totalPages = ref(1)
const totalTournaments = ref(0)
const pageSize = 10

const filters = computed<TournamentFilters>(() => ({
    type: typeFilter.value === 'all' ? undefined : typeFilter.value as 'solo' | 'team',
    status: statusFilter.value === 'all' ? undefined : statusFilter.value as 'opened' | 'ongoing' | 'finished'
}))

const loadTournaments = async () => {
    loading.value = true
    try {
        const response = await tournamentService.getTournaments(filters.value, currentPage.value, pageSize)
        tournaments.value = response.data
        totalPages.value = response.totalPages
        totalTournaments.value = response.total
    } catch (error) {
        console.error('Error loading tournaments:', error)
    } finally {
        loading.value = false
    }
}

const resetFilters = () => {
    typeFilter.value = 'all'
    statusFilter.value = 'all'
    currentPage.value = 1
    loadTournaments()
}

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
        currentPage.value = page
        loadTournaments()
    }
}

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'opened':
            return 'Ouvert'
        case 'ongoing':
            return 'En cours'
        case 'finished':
            return 'Terminé'
        default:
            return status
    }
}

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'opened':
            return 'default'
        case 'ongoing':
            return 'secondary'
        case 'finished':
            return 'outline'
        default:
            return 'outline'
    }
}

watch(filters, () => {
    currentPage.value = 1
    loadTournaments()
}, { deep: true })

onMounted(() => {
    loadTournaments()
})
</script>
