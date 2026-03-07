<template>
    <div class="container mx-auto py-6 space-y-6">
        <div class="flex flex-col gap-4">
            <h1 class="text-3xl font-bold">Historique des Matchs</h1>
            <p class="text-muted-foreground">Consultez l'historique complet des matchs avec filtres et pagination</p>
        </div>

        <!-- Filters -->
        <Card>
            <CardContent class="pt-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <Select v-model="selectedPlayerId">
                            <SelectTrigger class="w-64">
                                <SelectValue placeholder="Filtrer par joueur..." />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Tous les joueurs</SelectItem>
                                <SelectItem 
                                    v-for="player in players" 
                                    :key="player.id" 
                                    :value="player.id.toString()"
                                >
                                    {{ player.username }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select v-model="statusFilter">
                            <SelectTrigger class="w-40">
                                <SelectValue placeholder="Statut" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Tous</SelectItem>
                                <SelectItem value="pending">En attente</SelectItem>
                                <SelectItem value="confirmed">Confirmé</SelectItem>
                                <SelectItem value="rejected">Rejeté</SelectItem>
                            </SelectContent>
                        </Select>
                        <Input
                            v-model="dateFrom"
                            type="date"
                            placeholder="Date de début"
                            class="w-40"
                        />
                        <Input
                            v-model="dateTo"
                            type="date"
                            placeholder="Date de fin"
                            class="w-40"
                        />
                    </div>
                    <div class="flex space-x-2">
                        <Button variant="outline" @click="resetFilters">
                            Réinitialiser
                        </Button>
                        <Button @click="loadMatches">
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

        <!-- Matches List -->
        <Card v-else-if="matches.length > 0">
            <CardHeader>
                <CardTitle>Matchs</CardTitle>
                <p class="text-sm text-muted-foreground">
                    {{ totalMatches }} match(s) trouvé(s)
                </p>
            </CardHeader>
            <CardContent>
                <div class="space-y-4">
                    <div
                        v-for="match in matches"
                        :key="match.id"
                        class="flex items-center justify-between p-4 border rounded-lg hover:bg-muted/50 transition-colors"
                    >
                        <div class="flex items-center gap-4">
                            <div class="flex flex-col">
                                <div class="font-medium">
                                    {{ match.player1?.username }} vs {{ match.player2?.username }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    {{ formatDate(match.createdAt) }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-4">
                            <div class="text-sm">
                                <span class="font-medium">Vainqueur:</span>
                                {{ match.winner?.username || 'Aucun vainqueur' }}
                            </div>
                            <Badge
                                :variant="getStatusVariant(match.status)"
                                class="capitalize"
                            >
                                {{ getStatusLabel(match.status) }}
                            </Badge>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Empty State -->
        <Card v-else>
            <CardContent class="py-12">
                <div class="text-center">
                    <h3 class="text-lg font-medium mb-2">Aucun match trouvé</h3>
                    <p class="text-muted-foreground">Aucun match ne correspond à vos critères de recherche.</p>
                </div>
            </CardContent>
        </Card>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex justify-center">
            <Pagination
                v-slot="{ page }"
                :items-per-page="pageSize"
                :total="totalMatches"
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
import { Input } from '@/shared/components/ui/input'
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
import matchService from '@/features/core/services/match.service'
import playerService from '@/features/core/services/player.service'
import type { Match } from '@/features/core/types/match'
import type { Player } from '@/features/core/types/player'

const getStatusLabel = (status: string) => {
    switch (status) {
        case 'pending':
            return 'En attente'
        case 'confirmed':
            return 'Confirmé'
        case 'rejected':
            return 'Rejeté'
        default:
            return status
    }
}

const matches = ref<Match[]>([])
const players = ref<Player[]>([])
const loading = ref(false)
const selectedPlayerId = ref('all')
const statusFilter = ref('all')
const dateFrom = ref('')
const dateTo = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const totalMatches = ref(0)
const pageSize = 10

const filters = computed(() => ({
  player_id: selectedPlayerId.value === 'all' ? undefined : parseInt(selectedPlayerId.value),
  status: statusFilter.value === 'all' ? undefined : statusFilter.value as 'pending' | 'confirmed' | 'rejected',
  from: dateFrom.value || undefined,
  to: dateTo.value || undefined,
}))


const loadMatches = async () => {
  loading.value = true
  try {
    const response = await matchService.getMatchesPaginated({ ...filters.value, page: currentPage.value, limit: pageSize })
    matches.value = response.data
    totalPages.value = response.totalPages
    totalMatches.value = response.total
  } catch (error) {
    console.error('Error loading matches:', error)
  } finally {
    loading.value = false
  }
}

const loadPlayers = async () => {
  try {
    const response = await playerService.getPlayers()
    players.value = Array.isArray(response) ? response : response.data
  } catch (error) {
    console.error('Error loading players:', error)
  }
}

const resetFilters = () => {
  selectedPlayerId.value = 'all'
  statusFilter.value = 'all'
  dateFrom.value = ''
  dateTo.value = ''
  currentPage.value = 1
  loadMatches()
}


const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    currentPage.value = page
    loadMatches()
  }
}

watch(filters, () => {
  currentPage.value = 1
  loadMatches()
}, { deep: true })

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString()
}

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'confirmed':
            return 'default'
        case 'pending':
            return 'secondary'
        case 'rejected':
            return 'destructive'
        default:
            return 'outline'
    }
}

onMounted(() => {
    loadPlayers()
    loadMatches()
})
</script>