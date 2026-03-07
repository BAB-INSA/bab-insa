<template>
    <div class="space-y-6">
        <div v-if="isLoading" class="flex justify-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
        </div>

        <div v-else-if="!tournament" class="text-center">
            <div class="text-lg text-red-600">Tournoi introuvable</div>
        </div>

        <template v-else>
            <!-- Header -->
            <div class="space-y-2">
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-bold">{{ tournament.name }}</h1>
                    <Badge :variant="getStatusVariant(tournament.status)">
                        {{ getStatusLabel(tournament.status) }}
                    </Badge>
                    <div class="flex items-center gap-2 ml-auto">
                        <Button
                            v-if="canLeaveTournament"
                            size="sm"
                            variant="destructive"
                            :disabled="isLeavingTournament"
                            @click="handleLeaveTournament"
                        >
                            <Loader2 v-if="isLeavingTournament" class="w-4 h-4 mr-2 animate-spin" />
                            <LogOut v-else class="w-4 h-4 mr-2" />
                            Quitter le tournoi
                        </Button>
                        <Button
                            v-if="canDeclareMatch"
                            size="sm"
                            @click="showCreateMatch = true"
                        >
                            <Swords class="w-4 h-4 mr-2" />
                            Déclarer un match
                        </Button>
                    </div>
                </div>
                <p v-if="tournament.description" class="text-muted-foreground">{{ tournament.description }}</p>
            </div>

            <!-- Create Tournament Match Modal -->
            <CreateTeamMatchTournamentModal
                v-model:open="showCreateMatch"
                :tournament-id="tournament.id"
                :tournament-teams="teams"
                @tournament-match-created="onMatchCreated"
            />

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-blue-100 rounded-full">
                            <Users class="w-6 h-6 text-blue-600" />
                        </div>
                        <div class="text-3xl font-bold text-blue-600 mb-2">{{ tournament.nb_participants }}</div>
                        <div class="text-sm font-medium text-muted-foreground">Participants</div>
                    </CardContent>
                </Card>

                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-cyan-100 rounded-full">
                            <Swords class="w-6 h-6 text-cyan-600" />
                        </div>
                        <div class="text-3xl font-bold text-cyan-600 mb-2">{{ tournament.nb_matches }}</div>
                        <div class="text-sm font-medium text-muted-foreground">Matchs</div>
                    </CardContent>
                </Card>

                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-green-100 rounded-full">
                            <Trophy class="w-6 h-6 text-green-600" />
                        </div>
                        <div class="text-3xl font-bold text-green-600 mb-2">
                            {{ tournament.type === 'solo' ? 'Solo' : 'Équipe' }}
                        </div>
                        <div class="text-sm font-medium text-muted-foreground">Type</div>
                    </CardContent>
                </Card>

                <Card class="text-center">
                    <CardContent class="pt-6">
                        <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-purple-100 rounded-full">
                            <Target class="w-6 h-6 text-purple-600" />
                        </div>
                        <div class="text-3xl font-bold text-purple-600 mb-2">
                            {{ getStatusLabel(tournament.status) }}
                        </div>
                        <div class="text-sm font-medium text-muted-foreground">Statut</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Tabs -->
            <Card>
                <CardContent class="pt-6">
                    <Tabs default-value="teams" :value="activeTab" @update:model-value="handleTabChange" class="w-full">
                        <TabsList class="grid w-full grid-cols-2 mb-6">
                            <TabsTrigger value="teams">
                                <Users class="mr-2 h-4 w-4" />
                                Équipes
                            </TabsTrigger>
                            <TabsTrigger value="matches">
                                <Swords class="mr-2 h-4 w-4" />
                                Matchs
                            </TabsTrigger>
                        </TabsList>

                        <!-- Teams Tab -->
                        <TabsContent value="teams" class="mt-0">
                            <div v-if="teamsLoading" class="flex justify-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            </div>

                            <div v-else-if="teams.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-left py-3 px-4 font-medium text-muted-foreground">Équipe</th>
                                                <th class="text-center py-3 px-4 font-medium text-muted-foreground">Victoires</th>
                                                <th class="text-center py-3 px-4 font-medium text-muted-foreground">Défaites</th>
                                                <th class="text-center py-3 px-4 font-medium text-muted-foreground">ELO</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="entry in teams"
                                                :key="entry.id"
                                                class="border-b last:border-0 hover:bg-muted/50 transition-colors"
                                            >
                                                <td class="py-3 px-4">
                                                    <TeamLink :team="entry.team" />
                                                </td>
                                                <td class="py-3 px-4 text-center font-medium text-green-600">{{ entry.wins }}</td>
                                                <td class="py-3 px-4 text-center font-medium text-red-600">{{ entry.losses }}</td>
                                                <td class="py-3 px-4 text-center font-medium">{{ Math.round(entry.team.elo_rating) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Teams Pagination -->
                                <div v-if="teamsTotalPages > 1" class="flex justify-center mt-4">
                                    <Pagination
                                        v-slot="{ page }"
                                        :items-per-page="pageSize"
                                        :total="teamsTotal"
                                        :default-page="teamsPage"
                                        @update:page="goToTeamsPage"
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

                            <div v-else class="text-center py-8">
                                <p class="text-muted-foreground">Aucune équipe inscrite pour le moment.</p>
                            </div>
                        </TabsContent>

                        <!-- Matches Tab -->
                        <TabsContent value="matches" class="mt-0">
                            <div v-if="matchesLoading" class="flex justify-center py-8">
                                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                            </div>

                            <div v-else-if="matches.length > 0">
                                <div class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead>
                                            <tr class="border-b">
                                                <th class="text-left py-3 px-4 font-medium text-muted-foreground">Équipe 1</th>
                                                <th class="text-center py-3 px-4 font-medium text-muted-foreground"></th>
                                                <th class="text-right py-3 px-4 font-medium text-muted-foreground">Équipe 2</th>
                                                <th class="text-center py-3 px-4 font-medium text-muted-foreground">Statut</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="match in matches"
                                                :key="match.id"
                                                class="border-b last:border-0 hover:bg-muted/50 transition-colors"
                                            >
                                                <td class="py-3 px-4">
                                                    <span :class="{'font-bold text-green-600': match.winner_team?.id === match.team1.id}">
                                                        <TeamLink :team="match.team1" />
                                                    </span>
                                                    <span v-if="match.winner_team?.id === match.team1.id" class="ml-1">👑</span>
                                                </td>
                                                <td class="py-3 px-4 text-center text-muted-foreground text-sm">VS</td>
                                                <td class="py-3 px-4 text-right">
                                                    <span v-if="match.winner_team?.id === match.team2.id" class="mr-1">👑</span>
                                                    <span :class="{'font-bold text-green-600': match.winner_team?.id === match.team2.id}">
                                                        <TeamLink :team="match.team2" />
                                                    </span>
                                                </td>
                                                <td class="py-3 px-4 text-center">
                                                    <Badge
                                                        :variant="getMatchStatusVariant(match.status)"
                                                        class="capitalize"
                                                    >
                                                        {{ getMatchStatusLabel(match.status) }}
                                                    </Badge>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Matches Pagination -->
                                <div v-if="matchesTotalPages > 1" class="flex justify-center mt-4">
                                    <Pagination
                                        v-slot="{ page }"
                                        :items-per-page="pageSize"
                                        :total="matchesTotal"
                                        :default-page="matchesPage"
                                        @update:page="goToMatchesPage"
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

                            <div v-else class="text-center py-8">
                                <p class="text-muted-foreground">Aucun match pour ce tournoi.</p>
                            </div>
                        </TabsContent>
                    </Tabs>
                </CardContent>
            </Card>
        </template>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { Card, CardContent } from '@/shared/components/ui/card'
import { Badge } from '@/shared/components/ui/badge'
import { Button } from '@/shared/components/ui/button'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/shared/components/ui/tabs'
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/shared/components/ui/pagination'
import { Users, Swords, Trophy, Target, LogOut, Loader2 } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import tournamentService from '@/features/core/services/tournament.service'
import TeamLink from '@/features/core/components/TeamLink.vue'
import CreateTeamMatchTournamentModal from '@/features/core/components/CreateTeamMatchTournamentModal.vue'
import { useAuthStore } from '@/features/auth/stores/auth'
import type { Tournament } from '@/features/core/types/tournament'
import type { TournamentTeamEntry } from '@/features/core/types/tournament'
import type { TeamMatch } from '@/features/admin/types/team-match'

const route = useRoute()
const authStore = useAuthStore()
const tournamentId = computed(() => route.params.id as string)

const tournament = ref<Tournament | null>(null)
const isLoading = ref(true)
const activeTab = ref('teams')
const pageSize = 10
const showCreateMatch = ref(false)
const isLeavingTournament = ref(false)

// Show "Déclarer un match" button only if tournament is ongoing and user is part of an enrolled team
const canDeclareMatch = computed(() => {
    if (!authStore.isAuthenticated || !authStore.user?.id) return false
    if (tournament.value?.status !== 'ongoing') return false
    const userId = authStore.user.id
    return teams.value.some(
        entry => entry.team.player1_id === userId || entry.team.player2_id === userId,
    )
})

// Find the user's enrolled team entry in this tournament
const userTeamEntry = computed(() => {
    if (!authStore.isAuthenticated || !authStore.user?.id) return null
    const userId = authStore.user.id
    return teams.value.find(
        entry => entry.team.player1_id === userId || entry.team.player2_id === userId,
    ) ?? null
})

// Show "Quitter le tournoi" button if tournament is team type, status is opened, and user is enrolled
const canLeaveTournament = computed(() => {
    if (!tournament.value) return false
    if (tournament.value.type !== 'team') return false
    if (tournament.value.status !== 'opened') return false
    return !!userTeamEntry.value
})

const handleLeaveTournament = async () => {
    if (!userTeamEntry.value || !tournament.value) return

    try {
        isLeavingTournament.value = true
        await tournamentService.leaveTournament(tournament.value.id, userTeamEntry.value.team.id)
        toast.success('Vous avez quitté le tournoi', {
            description: `Votre équipe "${userTeamEntry.value.team.name}" a été retirée du tournoi.`,
        })
        await loadTournament()
    } catch (error) {
        console.error('Error leaving tournament:', error)
        toast.error('Erreur lors de la désinscription du tournoi')
    } finally {
        isLeavingTournament.value = false
    }
}

// Teams state
const teams = ref<TournamentTeamEntry[]>([])
const teamsLoading = ref(false)
const teamsPage = ref(1)
const teamsTotalPages = ref(1)
const teamsTotal = ref(0)

// Matches state (lazy loaded)
const matches = ref<TeamMatch[]>([])
const matchesLoading = ref(false)
const matchesPage = ref(1)
const matchesTotalPages = ref(1)
const matchesTotal = ref(0)
const matchesLoaded = ref(false)

const loadTournament = async () => {
    const id = parseInt(tournamentId.value)
    if (isNaN(id)) return

    try {
        isLoading.value = true
        tournament.value = await tournamentService.getTournamentById(id)
        await loadTeams()
    } catch (error) {
        console.error('Error fetching tournament:', error)
        tournament.value = null
    } finally {
        isLoading.value = false
    }
}

const loadTeams = async () => {
    const id = parseInt(tournamentId.value)
    if (isNaN(id)) return

    teamsLoading.value = true
    try {
        const response = await tournamentService.getTournamentTeams(id, teamsPage.value, pageSize)
        teams.value = response.data
        teamsTotalPages.value = response.totalPages
        teamsTotal.value = response.total
    } catch (error) {
        console.error('Error loading tournament teams:', error)
    } finally {
        teamsLoading.value = false
    }
}

const loadMatches = async () => {
    const id = parseInt(tournamentId.value)
    if (isNaN(id)) return

    matchesLoading.value = true
    try {
        const response = await tournamentService.getTournamentMatches(id, matchesPage.value, pageSize)
        matches.value = response.data
        matchesTotalPages.value = response.totalPages
        matchesTotal.value = response.total
        matchesLoaded.value = true
    } catch (error) {
        console.error('Error loading tournament matches:', error)
    } finally {
        matchesLoading.value = false
    }
}

const handleTabChange = (value: string | number | boolean) => {
    activeTab.value = value as string
    if (value === 'matches' && !matchesLoaded.value) {
        loadMatches()
    }
}

const goToTeamsPage = (page: number) => {
    if (page >= 1 && page <= teamsTotalPages.value && page !== teamsPage.value) {
        teamsPage.value = page
        loadTeams()
    }
}

const goToMatchesPage = (page: number) => {
    if (page >= 1 && page <= matchesTotalPages.value && page !== matchesPage.value) {
        matchesPage.value = page
        loadMatches()
    }
}

const onMatchCreated = async () => {
    await loadTournament()
    if (activeTab.value === 'matches') {
        await loadMatches()
    } else {
        matchesLoaded.value = false
    }
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

const getMatchStatusLabel = (status: string) => {
    switch (status) {
        case 'pending':
            return 'En attente'
        case 'confirmed':
            return 'Confirmé'
        case 'rejected':
            return 'Rejeté'
        case 'cancelled':
            return 'Annulé'
        default:
            return status
    }
}

const getMatchStatusVariant = (status: string) => {
    switch (status) {
        case 'confirmed':
            return 'default'
        case 'pending':
            return 'secondary'
        case 'rejected':
            return 'destructive'
        case 'cancelled':
            return 'outline'
        default:
            return 'outline'
    }
}

watch(tournamentId, () => {
    matchesLoaded.value = false
    matchesPage.value = 1
    teamsPage.value = 1
    activeTab.value = 'teams'
    loadTournament()
}, { immediate: true })
</script>
