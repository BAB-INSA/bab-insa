<template>
  <div class="space-y-4">
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

    <div class="border rounded-lg">
      <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3">ID</th>
              <th class="px-6 py-3">Joueurs</th>
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
              v-else-if="!matches.length"
              class="border-b"
            >
              <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                Aucun match trouvé
              </td>
            </tr>
            <tr
              v-else
              v-for="match in matches"
              :key="match.id"
              class="border-b hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-6 py-4 font-medium">#{{ match.id }}</td>
              <td class="px-6 py-4">
                <div class="space-y-1">
                  <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs">
                      {{ match.player1.username.charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-medium">{{ match.player1.username }}</span>
                    <Crown v-if="match.winner?.id === match.player1.id" class="h-4 w-4 text-yellow-500" />
                  </div>
                  <div class="text-gray-400 text-xs">vs</div>
                  <div class="flex items-center space-x-2">
                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center text-white text-xs">
                      {{ match.player2.username.charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-medium">{{ match.player2.username }}</span>
                    <Crown v-if="match.winner?.id === match.player2.id" class="h-4 w-4 text-yellow-500" />
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="font-mono text-lg">
                  {{ match.player1.eloRating }} - {{ match.player2.eloRating }}
                </div>
                <div class="text-xs text-gray-500">ELO Rating</div>
              </td>
              <td class="px-6 py-4">
                <Badge :variant="getStatusVariant(match.status)">
                  {{ formatStatus(match.status) }}
                </Badge>
              </td>
              <td class="px-6 py-4">
                {{ formatDate(match.createdAt) }}
              </td>
              <td class="px-6 py-4">
                {{ match.confirmedAt ? formatDate(match.confirmedAt) : '-' }}
              </td>
              <td class="px-6 py-4">
                <div class="flex space-x-2">
                  <Button
                    v-if="match.status === 'pending'"
                    variant="outline"
                    size="sm"
                    @click="editMatch(match)"
                  >
                    Modifier
                  </Button>
                  <Button
                    v-if="match.status === 'pending'"
                    variant="secondary"
                    size="sm"
                    @click="cancelMatch(match)"
                    :disabled="cancelling"
                  >
                    Annuler
                  </Button>
                  <Button
                    v-if="match.status === 'confirmed' && canDeleteMatch(match)"
                    variant="destructive"
                    size="sm"
                    @click="confirmDeleteMatch(match)"
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

    <MatchEditModal
      v-if="selectedMatch"
      :match="selectedMatch"
      :show="showEditModal"
      @close="closeEditModal"
      @saved="onMatchSaved"
    />

    <Dialog :open="showDeleteConfirm" @update:open="showDeleteConfirm = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Confirmer la suppression</DialogTitle>
          <DialogDescription>
            Êtes-vous sûr de vouloir supprimer le match #{{ matchToDelete?.id }} ?
            Cette action est irréversible.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="showDeleteConfirm = false">
            Annuler
          </Button>
          <Button variant="destructive" @click="deleteMatch" :disabled="deleting">
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
import adminMatchesService from '@/features/admin/services/admin-matches.service'
import PlayerService from '@/features/core/services/player.service'
import toastService from '@/shared/services/toast.service'
import type { Match, MatchFilters } from '@/features/admin/types/match'
import type { Player } from '@/features/core/types/player'

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
import MatchEditModal from './MatchEditModal.vue'

const { } = useAdmin()

const matches = ref<Match[]>([])
const players = ref<Player[]>([])
const loading = ref(false)
const deleting = ref(false)
const cancelling = ref(false)
const selectedPlayerId = ref('all')
const statusFilter = ref('all')
const dateFrom = ref('')
const dateTo = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const totalMatches = ref(0)
const pageSize = 10

const selectedMatch = ref<Match | null>(null)
const showEditModal = ref(false)
const matchToDelete = ref<Match | null>(null)
const showDeleteConfirm = ref(false)

const filters = computed<MatchFilters>(() => ({
  player_id: selectedPlayerId.value === 'all' ? undefined : selectedPlayerId.value,
  status: statusFilter.value === 'all' ? undefined : statusFilter.value as 'pending' | 'confirmed' | 'rejected' | 'cancelled',
  date_from: dateFrom.value || undefined,
  date_to: dateTo.value || undefined
}))

const loadMatches = async () => {
  loading.value = true
  try {
    const response = await adminMatchesService.getMatches(filters.value, currentPage.value, pageSize)
    matches.value = response.data
    totalPages.value = response.totalPages
    totalMatches.value = response.total
  } catch (error) {
    toastService.error('Erreur', 'Impossible de charger les matchs')
    console.error('Failed to load matches:', error)
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

const editMatch = (match: Match) => {
  selectedMatch.value = match
  showEditModal.value = true
}

const closeEditModal = () => {
  selectedMatch.value = null
  showEditModal.value = false
}

const onMatchSaved = () => {
  closeEditModal()
  loadMatches()
}

const cancelMatch = async (match: Match) => {
  cancelling.value = true
  try {
    await adminMatchesService.cancelMatch(match.id)
    toastService.success('Succès', 'Match annulé avec succès')
    loadMatches()
  } catch (error) {
    toastService.error('Erreur', 'Impossible d\'annuler le match')
    console.error('Failed to cancel match:', error)
  } finally {
    cancelling.value = false
  }
}

const confirmDeleteMatch = (match: Match) => {
  matchToDelete.value = match
  showDeleteConfirm.value = true
}

const deleteMatch = async () => {
  if (!matchToDelete.value) return
  
  deleting.value = true
  try {
    await adminMatchesService.deleteMatch(matchToDelete.value.id)
    toastService.success('Succès', 'Match supprimé avec succès')
    showDeleteConfirm.value = false
    matchToDelete.value = null
    loadMatches()
  } catch (error) {
    toastService.error('Erreur', 'Impossible de supprimer le match')
    console.error('Failed to delete match:', error)
  } finally {
    deleting.value = false
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

const getStatusVariant = (status: Match['status']) => {
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

const formatStatus = (status: Match['status']) => {
  const statusMap = {
    pending: 'En attente',
    confirmed: 'Confirmé',
    rejected: 'Rejeté',
    cancelled: 'Annulé'
  }
  return statusMap[status] || status
}

const canDeleteMatch = (match: Match) => {
  const oneWeekAgo = new Date()
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7)
  const matchCreatedAt = new Date(match.createdAt)
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

watch([selectedPlayerId, statusFilter, dateFrom, dateTo], () => {
  currentPage.value = 1
  loadMatches()
})

onMounted(() => {
  loadPlayers()
  loadMatches()
})
</script>