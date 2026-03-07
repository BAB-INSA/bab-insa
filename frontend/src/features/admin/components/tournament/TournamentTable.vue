<template>
  <div class="space-y-4">
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
        <Button variant="outline" @click="openCreateModal">
          Ajouter un tournoi
        </Button>
        <Button @click="loadTournaments">
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
              <th class="px-6 py-3">Nom</th>
              <th class="px-6 py-3">Type</th>
              <th class="px-6 py-3">Statut</th>
              <th class="px-6 py-3">Participants</th>
              <th class="px-6 py-3">Matchs</th>
              <th class="px-6 py-3">Créé le</th>
              <th class="px-6 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" class="border-b">
              <td colspan="8" class="px-6 py-4 text-center">
                <Spinner />
              </td>
            </tr>
            <tr
              v-else-if="!tournaments.length"
              class="border-b"
            >
              <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                Aucun tournoi trouvé
              </td>
            </tr>
            <tr
              v-else
              v-for="tournament in tournaments"
              :key="tournament.id"
              class="border-b hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-6 py-4 font-medium">#{{ tournament.id }}</td>
              <td class="px-6 py-4 font-medium">{{ tournament.name }}</td>
              <td class="px-6 py-4">
                <Badge :variant="tournament.type === 'solo' ? 'secondary' : 'default'">
                  {{ tournament.type === 'solo' ? 'Solo' : 'Équipe' }}
                </Badge>
              </td>
              <td class="px-6 py-4">
                <Badge :variant="getStatusVariant(tournament.status)">
                  {{ formatStatus(tournament.status) }}
                </Badge>
              </td>
              <td class="px-6 py-4">{{ tournament.nb_participants }}</td>
              <td class="px-6 py-4">{{ tournament.nb_matches }}</td>
              <td class="px-6 py-4">{{ formatDate(tournament.created_at) }}</td>
              <td class="px-6 py-4">
                <div class="flex space-x-2">
                  <Button
                    variant="outline"
                    size="sm"
                    @click="editTournament(tournament)"
                  >
                    Modifier
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmDeleteTournament(tournament)"
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

    <TournamentEditModal
      :tournament="selectedTournament"
      :show="showEditModal"
      @close="closeEditModal"
      @saved="onTournamentSaved"
    />

    <Dialog :open="showDeleteConfirm" @update:open="showDeleteConfirm = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Confirmer la suppression</DialogTitle>
          <DialogDescription>
            Êtes-vous sûr de vouloir supprimer le tournoi « {{ tournamentToDelete?.name }} » ?
            Cette action est irréversible.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="showDeleteConfirm = false">
            Annuler
          </Button>
          <Button variant="destructive" @click="deleteTournament" :disabled="deleting">
            {{ deleting ? 'Suppression...' : 'Supprimer' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import adminTournamentsService from '@/features/admin/services/admin-tournaments.service'
import toastService from '@/shared/services/toast.service'
import type { Tournament, TournamentFilters } from '@/features/admin/types/tournament'

import { Button } from '@/shared/components/ui/button'
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
import TournamentEditModal from './TournamentEditModal.vue'

const tournaments = ref<Tournament[]>([])
const loading = ref(false)
const deleting = ref(false)
const typeFilter = ref('all')
const statusFilter = ref('all')
const currentPage = ref(1)
const totalPages = ref(1)
const totalTournaments = ref(0)
const pageSize = 10

const selectedTournament = ref<Tournament | null>(null)
const showEditModal = ref(false)
const tournamentToDelete = ref<Tournament | null>(null)
const showDeleteConfirm = ref(false)

const filters = computed<TournamentFilters>(() => ({
  type: typeFilter.value === 'all' ? undefined : typeFilter.value as 'solo' | 'team',
  status: statusFilter.value === 'all' ? undefined : statusFilter.value as 'opened' | 'ongoing' | 'finished'
}))

const loadTournaments = async () => {
  loading.value = true
  try {
    const response = await adminTournamentsService.getTournaments(filters.value, currentPage.value, pageSize)
    tournaments.value = response.data
    totalPages.value = response.totalPages
    totalTournaments.value = response.total
  } catch (error) {
    toastService.error('Erreur', 'Impossible de charger les tournois')
    console.error('Failed to load tournaments:', error)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  selectedTournament.value = null
  showEditModal.value = true
}

const editTournament = (tournament: Tournament) => {
  selectedTournament.value = tournament
  showEditModal.value = true
}

const closeEditModal = () => {
  selectedTournament.value = null
  showEditModal.value = false
}

const onTournamentSaved = () => {
  closeEditModal()
  loadTournaments()
}

const confirmDeleteTournament = (tournament: Tournament) => {
  tournamentToDelete.value = tournament
  showDeleteConfirm.value = true
}

const deleteTournament = async () => {
  if (!tournamentToDelete.value) return

  deleting.value = true
  try {
    await adminTournamentsService.deleteTournament(tournamentToDelete.value.id)
    toastService.success('Succès', 'Tournoi supprimé avec succès')
    showDeleteConfirm.value = false
    tournamentToDelete.value = null
    loadTournaments()
  } catch (error) {
    toastService.error('Erreur', 'Impossible de supprimer le tournoi')
    console.error('Failed to delete tournament:', error)
  } finally {
    deleting.value = false
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

const getStatusVariant = (status: Tournament['status']) => {
  switch (status) {
    case 'opened':
      return 'secondary'
    case 'ongoing':
      return 'default'
    case 'finished':
      return 'outline'
    default:
      return 'outline'
  }
}

const formatStatus = (status: Tournament['status']) => {
  const statusMap = {
    opened: 'Ouvert',
    ongoing: 'En cours',
    finished: 'Terminé'
  }
  return statusMap[status] || status
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

watch([typeFilter, statusFilter], () => {
  currentPage.value = 1
  loadTournaments()
})

onMounted(() => {
  loadTournaments()
})
</script>
