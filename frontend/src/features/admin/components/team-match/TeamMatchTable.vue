<template>
  <div class="space-y-4">
    <div class="space-y-4">
      <div class="flex items-center gap-4 flex-wrap">
        <Select v-model="selectedTeamId">
          <SelectTrigger class="w-64">
            <SelectValue placeholder="Filtrer par équipe..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">Toutes les équipes</SelectItem>
            <SelectItem
              v-for="team in teams"
              :key="team.id"
              :value="team.id.toString()"
            >
              {{ team.name }}
            </SelectItem>
          </SelectContent>
        </Select>

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

        <Select v-model="selectedTournamentId">
          <SelectTrigger class="w-64">
            <SelectValue placeholder="Filtrer par tournoi..." />
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="all">Tous les tournois</SelectItem>
            <SelectItem
              v-for="tournament in tournaments"
              :key="tournament.id"
              :value="tournament.id.toString()"
            >
              {{ tournament.name }}
            </SelectItem>
          </SelectContent>
        </Select>
      </div>

      <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-4">
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
        <div class="flex gap-2">
          <Button variant="outline" @click="resetFilters">
            Réinitialiser
          </Button>
          <Button @click="loadTeamMatches">
            Actualiser
          </Button>
        </div>
      </div>
    </div>

    <div class="border rounded-lg">
      <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3">ID</th>
              <th class="px-6 py-3">Équipes</th>
              <th class="px-6 py-3">ELO Rating</th>
              <th class="px-6 py-3">Statut</th>
              <th class="px-6 py-3">Créé le</th>
              <th class="px-6 py-3">Confirmé le</th>
              <th class="px-6 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" class="border-b">
              <td colspan="7" class="px-6 py-4 text-center">
                <Spinner />
              </td>
            </tr>
            <tr
              v-else-if="!teamMatches.length"
              class="border-b"
            >
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                Aucun match d'équipe trouvé
              </td>
            </tr>
            <tr
              v-else
              v-for="teamMatch in teamMatches"
              :key="teamMatch.id"
              class="border-b hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-6 py-4 font-medium">#{{ teamMatch.id }}</td>
              <td class="px-6 py-4">
                <div class="space-y-2">
                  <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs">
                      T1
                    </div>
                    <div class="font-medium">{{ teamMatch.team1.name }}</div>
                    <Crown v-if="teamMatch.winnerTeam?.id === teamMatch.team1.id" class="h-4 w-4 text-yellow-500" />
                  </div>
                  <div class="text-gray-400 text-xs text-center">vs</div>
                  <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white text-xs">
                      T2
                    </div>
                    <div class="font-medium">{{ teamMatch.team2.name }}</div>
                    <Crown v-if="teamMatch.winnerTeam?.id === teamMatch.team2.id" class="h-4 w-4 text-yellow-500" />
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="font-mono text-lg">
                  {{ Math.round(teamMatch.team1.eloRating) }} - {{ Math.round(teamMatch.team2.eloRating) }}
                </div>
                <div class="text-xs text-gray-500">ELO Rating</div>
              </td>
              <td class="px-6 py-4">
                <Badge :variant="getStatusVariant(teamMatch.status)">
                  {{ formatStatus(teamMatch.status) }}
                </Badge>
              </td>
              <td class="px-6 py-4">
                {{ formatDate(teamMatch.createdAt) }}
              </td>
              <td class="px-6 py-4">
                {{ teamMatch.confirmedAt ? formatDate(teamMatch.confirmedAt) : '-' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex space-x-2">
                  <Button
                    v-if="teamMatch.status === 'pending'"
                    variant="secondary"
                    size="sm"
                    @click="cancelTeamMatch(teamMatch)"
                    :disabled="cancelling"
                  >
                    Annuler
                  </Button>
                  <Button
                    v-if="teamMatch.status === 'confirmed' && canDeleteTeamMatch(teamMatch)"
                    variant="destructive"
                    size="sm"
                    @click="confirmDeleteTeamMatch(teamMatch)"
                  >
                    Supprimer
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="totalPages > 1" class="flex justify-center">
      <Pagination
        v-slot="{ page }"
        :items-per-page="pageSize"
        :total="totalTeamMatches"
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

    <Dialog :open="showDeleteConfirm" @update:open="showDeleteConfirm = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Confirmer la suppression</DialogTitle>
          <DialogDescription>
            Êtes-vous sûr de vouloir supprimer le match d'équipe #{{ teamMatchToDelete?.id }} ?
            Cette action est irréversible.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="showDeleteConfirm = false">
            Annuler
          </Button>
          <Button variant="destructive" @click="deleteTeamMatch" :disabled="deleting">
            {{ deleting ? 'Suppression...' : 'Supprimer' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Crown } from 'lucide-vue-next'
import { useAdmin } from '@/features/admin/composables/useAdmin'
import adminTeamMatchesService from '@/features/admin/services/admin-team-matches.service'
import PlayerService from '@/features/core/services/player.service'
import TeamService from '@/features/core/services/team.service'
import TournamentService from '@/features/core/services/tournament.service'
import type { Tournament } from '@/features/admin/types/tournament'
import toastService from '@/shared/services/toast.service'
import type { TeamMatch, TeamMatchFilters } from '@/features/admin/types/team-match'
import type { Player } from '@/features/core/types/player'
import type { Team } from '@/features/core/types/team'

import { Button } from '@/shared/components/ui/button'
import { Input } from '@/shared/components/ui/input'
import { Badge } from '@/shared/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/shared/components/ui/pagination'
import Spinner from '@/shared/components/ui/Spinner.vue'

const { } = useAdmin()

const teamMatches = ref<TeamMatch[]>([])
const players = ref<Player[]>([])
const teams = ref<Team[]>([])
const tournaments = ref<Tournament[]>([])
const loading = ref(false)
const deleting = ref(false)
const cancelling = ref(false)
const selectedTeamId = ref('all')
const selectedPlayerId = ref('all')
const selectedTournamentId = ref('all')
const statusFilter = ref('all')
const dateFrom = ref('')
const dateTo = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const totalTeamMatches = ref(0)
const pageSize = 10

const teamMatchToDelete = ref<TeamMatch | null>(null)
const showDeleteConfirm = ref(false)

const filters = computed<TeamMatchFilters>(() => ({
  team_id: selectedTeamId.value === 'all' ? undefined : selectedTeamId.value,
  player_id: selectedPlayerId.value === 'all' ? undefined : selectedPlayerId.value,
  tournament_id: selectedTournamentId.value === 'all' ? undefined : selectedTournamentId.value,
  status: statusFilter.value === 'all' ? undefined : statusFilter.value as 'pending' | 'confirmed' | 'rejected' | 'cancelled',
  from: dateFrom.value || undefined,
  to: dateTo.value || undefined
}))

const loadTeamMatches = async () => {
  loading.value = true
  try {
    const response = await adminTeamMatchesService.getTeamMatches(filters.value, currentPage.value, pageSize)
    teamMatches.value = response.data
    totalPages.value = response.totalPages
    totalTeamMatches.value = response.total
  } catch (error) {
    toastService.error('Erreur', 'Impossible de charger les matchs d\'équipes')
    console.error('Failed to load team matches:', error)
  } finally {
    loading.value = false
  }
}

const loadPlayers = async () => {
  try {
    const response = await PlayerService.getPlayers({ page: 1, pageSize: 100 })
    players.value = response.data
  } catch (error) {
    toastService.error('Erreur', 'Impossible de charger les joueurs')
    console.error('Failed to load players:', error)
  }
}

const loadTeams = async () => {
  try {
    const response = await TeamService.getTeams({ page: 1, pageSize: 100 })
    teams.value = response.data
  } catch (error) {
    toastService.error('Erreur', 'Impossible de charger les équipes')
    console.error('Failed to load teams:', error)
  }
}

const loadTournaments = async () => {
  try {
    const response = await TournamentService.getTournaments({ type: 'team' }, 1, 100)
    tournaments.value = response.data
  } catch (error) {
    toastService.error('Erreur', 'Impossible de charger les tournois')
    console.error('Failed to load tournaments:', error)
  }
}

const cancelTeamMatch = async (teamMatch: TeamMatch) => {
  cancelling.value = true
  try {
    await adminTeamMatchesService.cancelTeamMatch(teamMatch.id)
    toastService.success('Succès', 'Match d\'équipe annulé avec succès')
    loadTeamMatches()
  } catch (error) {
    toastService.error('Erreur', 'Impossible d\'annuler le match d\'équipe')
    console.error('Failed to cancel team match:', error)
  } finally {
    cancelling.value = false
  }
}

const confirmDeleteTeamMatch = (teamMatch: TeamMatch) => {
  teamMatchToDelete.value = teamMatch
  showDeleteConfirm.value = true
}

const deleteTeamMatch = async () => {
  if (!teamMatchToDelete.value) return
  
  deleting.value = true
  try {
    await adminTeamMatchesService.deleteTeamMatch(teamMatchToDelete.value.id)
    toastService.success('Succès', 'Match d\'équipe supprimé avec succès')
    showDeleteConfirm.value = false
    teamMatchToDelete.value = null
    loadTeamMatches()
  } catch (error) {
    toastService.error('Erreur', 'Impossible de supprimer le match d\'équipe')
    console.error('Failed to delete team match:', error)
  } finally {
    deleting.value = false
  }
}

const resetFilters = () => {
  selectedTeamId.value = 'all'
  selectedPlayerId.value = 'all'
  selectedTournamentId.value = 'all'
  statusFilter.value = 'all'
  dateFrom.value = ''
  dateTo.value = ''
  currentPage.value = 1
  loadTeamMatches()
}

const goToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    currentPage.value = page
    loadTeamMatches()
  }
}

const getStatusVariant = (status: TeamMatch['status']) => {
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

const formatStatus = (status: TeamMatch['status']) => {
  const statusMap = {
    pending: 'En attente',
    confirmed: 'Confirmé',
    rejected: 'Rejeté',
    cancelled: 'Annulé'
  }
  return statusMap[status] || status
}

const canDeleteTeamMatch = (teamMatch: TeamMatch) => {
  const oneWeekAgo = new Date()
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7)
  const matchCreatedAt = new Date(teamMatch.createdAt)
  return matchCreatedAt >= oneWeekAgo
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

watch([selectedTeamId, selectedPlayerId, selectedTournamentId, statusFilter, dateFrom, dateTo], () => {
  currentPage.value = 1
  loadTeamMatches()
})

onMounted(() => {
  loadPlayers()
  loadTeams()
  loadTournaments()
  loadTeamMatches()
})
</script>