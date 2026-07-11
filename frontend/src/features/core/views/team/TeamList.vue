<!-- src/features/core/views/team/TeamList.vue -->
<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col space-y-2">
      <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
        <Users class="h-8 w-8" />
        Liste des Équipes
      </h1>
      <p class="text-muted-foreground">
        Toutes les équipes avec leurs statistiques de matchs 2v2
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-8">
      <div class="text-lg">Chargement des équipes...</div>
    </div>

    <!-- Teams Table -->
    <div v-else class="rounded-lg border">
      <table class="w-full">
        <thead>
          <tr class="border-b bg-muted/50">
            <th class="px-4 py-3 text-left font-medium">Équipe</th>
            <th class="px-4 py-3 text-left font-medium">ELO Rating</th>
            <th class="px-4 py-3 text-left font-medium">Victoires</th>
            <th class="px-4 py-3 text-left font-medium">Défaites</th>
            <th class="px-4 py-3 text-left font-medium">Pourcentage</th>
            <th class="px-4 py-3 text-left font-medium">Matchs</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="team in teams" :key="team.id" class="border-b hover:bg-muted/30 transition-colors even:bg-muted/20">
            <td class="px-4 py-3">
              <TeamLink :team="team" />
            </td>
            <td class="px-4 py-3 font-medium">{{ Math.round(team.eloRating) }}</td>
            <td class="px-4 py-3 text-green-600 dark:text-green-400 font-medium">{{ team.wins }}</td>
            <td class="px-4 py-3 text-red-600 dark:text-red-400 font-medium">{{ team.losses }}</td>
            <td class="px-4 py-3 font-medium">{{ getWinRate(team) }}%</td>
            <td class="px-4 py-3 text-muted-foreground">{{ team.totalMatches }}</td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="teams.length === 0" class="py-12 text-center">
        <Users class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">Aucune équipe trouvée</h3>
        <p class="text-muted-foreground">Il n'y a pas encore d'équipes formées.</p>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center">
      <Pagination
        v-slot="{ page }"
        :items-per-page="itemsPerPage"
        :total="totalTeams"
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
import TeamService from '@/features/core/services/team.service'
import TeamLink from '@/features/core/components/TeamLink.vue'
import type { Team } from '@/features/core/types/team'
import type { PaginatedResponse } from '@/shared/types/pagination'

const teams = ref<Team[]>([])
const paginationInfo = ref<PaginatedResponse<Team> | null>(null)
const isLoading = ref(true)
const currentPage = ref(1)
const itemsPerPage = 10

const totalPages = computed(() => paginationInfo.value?.totalPages || 1)
const totalTeams = computed(() => paginationInfo.value?.total || 0)

const getWinRate = (team: Team): number => {
  if (team.totalMatches === 0) return 0
  return Math.round((team.wins / team.totalMatches) * 100)
}


const loadTeams = async (page: number = currentPage.value) => {
  try {
    isLoading.value = true
    const response = await TeamService.getTeams({
      page,
      pageSize: itemsPerPage
    })
    paginationInfo.value = response
    teams.value = response.data
    currentPage.value = page
  } catch (error) {
    console.error('Error fetching teams:', error)
  } finally {
    isLoading.value = false
  }
}

const onPageChange = (page: number) => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    loadTeams(page)
  }
}

onMounted(() => {
  loadTeams(1)
})
</script>