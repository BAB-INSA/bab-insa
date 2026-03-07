<!-- src/features/core/views/PlayerLeaderboard.vue -->
<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col space-y-4">
      <h1 class="text-3xl font-bold tracking-tight flex items-center gap-2">
        <Trophy class="h-8 w-8 text-yellow-500" />
        Classement
      </h1>
      
      <!-- Tabs -->
      <div class="flex bg-muted rounded-lg p-1 w-fit">
        <button
          @click="selectedLeaderboardType = 'solo'"
          :class="[
            'px-4 py-2 rounded-md text-sm font-medium transition-colors',
            selectedLeaderboardType === 'solo' 
              ? 'bg-background text-foreground shadow-sm' 
              : 'text-muted-foreground hover:text-foreground'
          ]"
        >
          <Trophy class="w-4 h-4 mr-2 inline" />
          Classement 1v1
        </button>
        <button
          @click="selectedLeaderboardType = 'team'"
          :class="[
            'px-4 py-2 rounded-md text-sm font-medium transition-colors',
            selectedLeaderboardType === 'team' 
              ? 'bg-background text-foreground shadow-sm' 
              : 'text-muted-foreground hover:text-foreground'
          ]"
        >
          <Users class="w-4 h-4 mr-2 inline" />
          Classement 2v2
        </button>
      </div>
      
      <p class="text-muted-foreground">
        {{ selectedLeaderboardType === 'solo' 
          ? 'Les 100 meilleurs joueurs en 1v1 classés par rating ELO' 
          : 'Les 100 meilleurs joueurs en 2v2 classés par rating ELO'
        }}
      </p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-8">
      <div class="text-lg">Chargement du classement...</div>
    </div>

    <!-- Leaderboard -->
    <div v-else class="rounded-lg border">
      <table class="w-full">
        <thead>
          <tr class="border-b bg-muted/50">
            <th class="px-4 py-3 text-left font-medium">Rang</th>
            <th class="px-4 py-3 text-left font-medium">Joueur</th>
            <th class="px-4 py-3 text-left font-medium">ELO Rating</th>
            <th class="px-4 py-3 text-left font-medium">Victoires</th>
            <th class="px-4 py-3 text-left font-medium">Défaites</th>
            <th class="px-4 py-3 text-left font-medium">Pourcentage</th>
            <th class="px-4 py-3 text-left font-medium">Matches</th>
          </tr>
        </thead>
        <tbody>
          <tr 
            v-for="(player, index) in players" 
            :key="player.id" 
            class="border-b hover:bg-muted/30 transition-colors"
            :class="{
              'bg-yellow-50 dark:bg-yellow-950/20': index === 0,
              'bg-gray-50 dark:bg-gray-950/20': index === 1,
              'bg-amber-50 dark:bg-amber-950/20': index === 2,
              'even:bg-muted/20': index > 2
            }"
          >
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <component 
                  :is="getRankIcon(index)" 
                  v-if="getRankIcon(index)"
                  class="h-5 w-5"
                  :class="getRankColor(index)"
                />
                <span class="font-bold text-lg" :class="getRankColor(index)">
                  #{{ selectedLeaderboardType === 'solo' ? (player.rank ?? (index + 1)) : (player.teamRank ?? (index + 1)) }}
                </span>
              </div>
            </td>
            <td class="px-4 py-3">
              <PlayerLink :player="player" class="font-medium text-primary hover:text-primary/80" />
            </td>
            <td class="px-4 py-3">
              <span class="font-bold text-lg">
                {{ selectedLeaderboardType === 'solo' ? Math.round(player.eloRating) : Math.round(player.teamEloRating) }}
              </span>
            </td>
            <td class="px-4 py-3 text-green-600 dark:text-green-400 font-medium">
              {{ selectedLeaderboardType === 'solo' ? player.wins : player.teamWins }}
            </td>
            <td class="px-4 py-3 text-red-600 dark:text-red-400 font-medium">
              {{ selectedLeaderboardType === 'solo' ? getLosses(player) : player.teamLosses }}
            </td>
            <td class="px-4 py-3 font-medium">
              {{ selectedLeaderboardType === 'solo' ? getWinRate(player) : getTeamWinRate(player) }}%
            </td>
            <td class="px-4 py-3 text-muted-foreground">
              {{ selectedLeaderboardType === 'solo' ? player.totalMatches : player.teamTotalMatches }}
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Empty State -->
      <div v-if="players.length === 0" class="py-12 text-center">
        <Users class="h-12 w-12 mx-auto text-muted-foreground mb-4" />
        <h3 class="text-lg font-semibold mb-2">Aucun joueur dans le classement</h3>
        <p class="text-muted-foreground">Il n'y a pas encore de joueurs avec un rating ELO.</p>
      </div>
    </div>

    <!-- Stats Footer -->
    <div v-if="players.length > 0" class="text-center text-sm text-muted-foreground">
      Affichage de {{ players.length }} joueur{{ players.length > 1 ? 's' : '' }} dans le classement
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { Trophy, Medal, Award, Users } from 'lucide-vue-next'
import PlayerService from '@/features/core/services/player.service'
import PlayerLink from '@/features/core/components/PlayerLink.vue'
import { usePlayer } from '@/features/core/composables/usePlayer'
import type { Player } from '@/features/core/types/player'

const players = ref<Player[]>([])
const isLoading = ref(true)
const selectedLeaderboardType = ref<'solo' | 'team'>('team')

const { getWinRate, getLosses } = usePlayer()

const getTeamWinRate = (player: Player): number => {
  if (player.teamTotalMatches === 0) return 0
  return Math.round((player.teamWins / player.teamTotalMatches) * 100)
}

const getRankIcon = (index: number) => {
  switch (index) {
    case 0: return Trophy
    case 1: return Medal
    case 2: return Award
    default: return null
  }
}

const getRankColor = (index: number) => {
  switch (index) {
    case 0: return 'text-yellow-500'
    case 1: return 'text-gray-400'
    case 2: return 'text-amber-600'
    default: return 'text-muted-foreground'
  }
}

const loadLeaderboard = async () => {
  try {
    isLoading.value = true
    if (selectedLeaderboardType.value === 'solo') {
      players.value = await PlayerService.getTopPlayers(100)
    } else {
      players.value = await PlayerService.getTopTeams(100)
    }
  } catch (error) {
    console.error('Error fetching leaderboard:', error)
  } finally {
    isLoading.value = false
  }
}

watch(selectedLeaderboardType, () => {
  loadLeaderboard()
})

onMounted(() => {
  loadLeaderboard()
})
</script>