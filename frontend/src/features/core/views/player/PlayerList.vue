<!-- src/features/core/views/player/PlayerList.vue -->
<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col space-y-2">
      <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
        <Users class="h-8 w-8" />
        Liste des Joueurs
      </h1>
      <p class="text-muted-foreground">
        Tous les joueurs inscrits avec leurs statistiques
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-8">
      <div class="text-lg">Chargement des joueurs...</div>
    </div>

    <!-- Players Table -->
    <div v-else class="rounded-lg border">
      <table class="w-full">
        <thead>
          <tr class="border-b bg-muted/50">
            <th class="px-4 py-3 text-left font-medium">Pseudo</th>
            <th class="px-4 py-3 text-left font-medium">ELO Rating</th>
            <th class="px-4 py-3 text-left font-medium">Victoires</th>
            <th class="px-4 py-3 text-left font-medium">Défaites</th>
            <th class="px-4 py-3 text-left font-medium">Pourcentage</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="player in players" :key="player.id" class="border-b hover:bg-muted/30 transition-colors even:bg-muted/20">
            <td class="px-4 py-3">
              <PlayerLink :player="player" class="font-medium text-primary hover:text-primary/80" />
            </td>
            <td class="px-4 py-3 font-medium">{{ player.elo_rating }}</td>
            <td class="px-4 py-3 text-green-600 dark:text-green-400 font-medium">{{ player.wins }}</td>
            <td class="px-4 py-3 text-red-600 dark:text-red-400 font-medium">{{ getLosses(player) }}</td>
            <td class="px-4 py-3 font-medium">{{ getWinRate(player) }}%</td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="players.length === 0" class="py-12 text-center">
        <Users class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">Aucun joueur trouvé</h3>
        <p class="text-muted-foreground">Il n'y a pas encore de joueurs inscrits.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center">
      <Pagination
        v-slot="{ page }"
        :items-per-page="itemsPerPage"
        :total="totalPlayers"
        :default-page="currentPage"
        @update:page="onPageChange"
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
import { ref, computed, onMounted } from 'vue'
import { Users } from 'lucide-vue-next'
import {
  Pagination,
  PaginationContent,
  PaginationEllipsis,
  PaginationItem,
  PaginationNext,
  PaginationPrevious,
} from '@/shared/components/ui/pagination'
import PlayerService from '@/features/core/services/player.service'
import PlayerLink from '@/features/core/components/PlayerLink.vue'
import { usePlayer } from '@/features/core/composables/usePlayer'
import type { Player } from '@/features/core/types/player'
import type { PaginatedResponse } from '@/shared/types/pagination'

const players = ref<Player[]>([])
const paginationInfo = ref<PaginatedResponse<Player> | null>(null)
const isLoading = ref(true)
const currentPage = ref(1)
const itemsPerPage = 10

const totalPages = computed(() => paginationInfo.value?.totalPages || 1)
const totalPlayers = computed(() => paginationInfo.value?.total || 0)

const { getWinRate, getLosses } = usePlayer()

const loadPlayers = async (page: number = currentPage.value) => {
  try {
    isLoading.value = true
    const response = await PlayerService.getPlayers({
      page,
      pageSize: itemsPerPage
    })
    paginationInfo.value = response
    players.value = response.data
    currentPage.value = page
  } catch (error) {
    console.error('Error fetching players:', error)
  } finally {
    isLoading.value = false
  }
}

const onPageChange = (page: number) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    loadPlayers(page)
  }
}

onMounted(() => {
  loadPlayers(1)
})
</script>