<template>
  <div class="space-y-6">
    <div v-if="isLoading" class="text-center">
      <div class="text-lg">Chargement...</div>
    </div>
    
    <div v-else-if="!teamData" class="text-center">
      <div class="text-lg text-red-600">Équipe introuvable</div>
    </div>
    
    <template v-else>
      <!-- Section Statistiques Équipe -->
      <div class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
          <Users class="w-5 h-5 text-blue-600" />
          <h3 class="text-xl font-semibold">{{ teamData.name }}</h3>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <!-- ELO Rating -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-blue-100 rounded-full">
                <TrendingUp class="w-6 h-6 text-blue-600" />
              </div>
              <div class="text-3xl font-bold text-blue-600 mb-2">{{ Math.round(teamData.elo_rating) }}</div>
              <div class="text-sm font-medium text-muted-foreground">ELO Équipe</div>
            </CardContent>
          </Card>

          <!-- Parties jouées -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-cyan-100 rounded-full">
                <Target class="w-6 h-6 text-cyan-600" />
              </div>
              <div class="text-3xl font-bold text-cyan-600 mb-2">{{ teamData.total_matches }}</div>
              <div class="text-sm font-medium text-muted-foreground">Parties jouées</div>
            </CardContent>
          </Card>

          <!-- Victoires -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-green-100 rounded-full">
                <Trophy class="w-6 h-6 text-green-600" />
              </div>
              <div class="text-3xl font-bold text-green-600 mb-2">{{ teamData.wins }}</div>
              <div class="text-sm font-medium text-muted-foreground">Victoires</div>
            </CardContent>
          </Card>

          <!-- Taux de victoire -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-purple-100 rounded-full">
                <TrendingUp class="w-6 h-6 text-purple-600" />
              </div>
              <div class="text-3xl font-bold text-purple-600 mb-2">{{ teamWinRate }}%</div>
              <div class="text-sm font-medium text-muted-foreground">Taux de victoire</div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Section Joueurs -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Joueur 1 -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <User class="w-5 h-5" />
              <PlayerLink :player="teamData.player1" />
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Stats individuelles du joueur 1 -->
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ Math.round(teamData.player1.elo_rating) }}</div>
                <div class="text-sm text-muted-foreground">ELO 1v1</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ Math.round(teamData.player1.team_elo_rating) }}</div>
                <div class="text-sm text-muted-foreground">ELO 2v2</div>
              </div>
            </div>
            
            <!-- Stats matches du joueur 1 -->
            <div class="grid grid-cols-2 gap-4 pt-2 border-t">
              <div class="text-center">
                <div class="text-lg font-semibold">{{ teamData.player1.total_matches }}</div>
                <div class="text-sm text-muted-foreground">Matches 1v1</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-semibold">{{ teamData.player1.team_total_matches }}</div>
                <div class="text-sm text-muted-foreground">Matches 2v2</div>
              </div>
            </div>

            <!-- Win rates du joueur 1 -->
            <div class="grid grid-cols-2 gap-4 pt-2 border-t">
              <div class="text-center">
                <div class="text-lg font-semibold text-green-600">{{ getPlayerWinRate(teamData.player1, 'solo') }}%</div>
                <div class="text-sm text-muted-foreground">Victoires 1v1</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-semibold text-green-600">{{ getPlayerWinRate(teamData.player1, 'team') }}%</div>
                <div class="text-sm text-muted-foreground">Victoires 2v2</div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Joueur 2 -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <User class="w-5 h-5" />
              <PlayerLink :player="teamData.player2" />
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Stats individuelles du joueur 2 -->
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ Math.round(teamData.player2.elo_rating) }}</div>
                <div class="text-sm text-muted-foreground">ELO 1v1</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ Math.round(teamData.player2.team_elo_rating) }}</div>
                <div class="text-sm text-muted-foreground">ELO 2v2</div>
              </div>
            </div>
            
            <!-- Stats matches du joueur 2 -->
            <div class="grid grid-cols-2 gap-4 pt-2 border-t">
              <div class="text-center">
                <div class="text-lg font-semibold">{{ teamData.player2.total_matches }}</div>
                <div class="text-sm text-muted-foreground">Matches 1v1</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-semibold">{{ teamData.player2.team_total_matches }}</div>
                <div class="text-sm text-muted-foreground">Matches 2v2</div>
              </div>
            </div>

            <!-- Win rates du joueur 2 -->
            <div class="grid grid-cols-2 gap-4 pt-2 border-t">
              <div class="text-center">
                <div class="text-lg font-semibold text-green-600">{{ getPlayerWinRate(teamData.player2, 'solo') }}%</div>
                <div class="text-sm text-muted-foreground">Victoires 1v1</div>
              </div>
              <div class="text-center">
                <div class="text-lg font-semibold text-green-600">{{ getPlayerWinRate(teamData.player2, 'team') }}%</div>
                <div class="text-sm text-muted-foreground">Victoires 2v2</div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useRoute } from 'vue-router'
import { Card, CardContent, CardHeader, CardTitle } from '@/shared/components/ui/card'
import { Trophy, Target, TrendingUp, User, Users } from 'lucide-vue-next'
import TeamService from '@/features/core/services/team.service'
import PlayerLink from '@/features/core/components/PlayerLink.vue'
import type { Team } from '@/features/core/types/team'
import type { Player } from '@/features/core/types/player'

const route = useRoute()
const teamId = computed(() => route.params.id as string)

const teamData = ref<Team | null>(null)
const isLoading = ref(true)

const teamWinRate = computed(() => {
  if (!teamData.value || teamData.value.total_matches === 0) return 0
  return Math.round((teamData.value.wins / teamData.value.total_matches) * 100)
})

const getPlayerWinRate = (player: Player, type: 'solo' | 'team') => {
  if (type === 'solo') {
    return player.total_matches > 0 ? Math.round((player.wins / player.total_matches) * 100) : 0
  } else {
    return player.team_total_matches > 0 ? Math.round((player.team_wins / player.team_total_matches) * 100) : 0
  }
}

const loadTeamData = async () => {
  const id = parseInt(teamId.value)
  if (isNaN(id)) return
  
  try {
    isLoading.value = true
    teamData.value = await TeamService.getTeam(id)
  } catch (error) {
    console.error('Error fetching team data:', error)
    teamData.value = null
  } finally {
    isLoading.value = false
  }
}

watch(teamId, () => {
  loadTeamData()
}, { immediate: true })
</script>