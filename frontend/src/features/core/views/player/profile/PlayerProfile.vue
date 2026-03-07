<!-- src/features/core/views/player/profile/PlayerProfile.vue -->
<template>
  <div class="space-y-6">
    <div v-if="isLoading" class="text-center">
      <div class="text-lg">Chargement...</div>
    </div>
    
    <div v-else-if="!playerData" class="text-center">
      <div class="text-lg text-red-600">Joueur introuvable</div>
    </div>
    
    <template v-else>
      <!-- Header avec nom du joueur -->
      <div class="flex items-center gap-4 mb-6">
        <div class="flex items-center justify-center w-16 h-16 bg-primary/10 rounded-full">
          <User class="w-8 h-8 text-primary" />
        </div>
        <div>
          <h1 class="text-3xl font-bold">{{ playerData.username }}</h1>
          <p class="text-muted-foreground">Membre depuis le {{ formatDate(playerData.created_at) }}</p>
        </div>
      </div>

      <!-- Section Statistiques 1v1 -->
      <div v-if="playerData.total_matches > 0" class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
          <Trophy class="w-5 h-5 text-yellow-600" />
          <h3 class="text-xl font-semibold">Statistiques 1v1</h3>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- ELO Rating -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-yellow-100 rounded-full">
                <TrendingUp class="w-6 h-6 text-yellow-600" />
              </div>
              <div class="text-3xl font-bold text-yellow-600 mb-2">{{ Math.round(playerData.elo_rating) }}</div>
              <div class="text-sm font-medium text-muted-foreground">ELO 1v1</div>
            </CardContent>
          </Card>

          <!-- Parties jouées -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-blue-100 rounded-full">
                <Target class="w-6 h-6 text-blue-600" />
              </div>
              <div class="text-3xl font-bold text-blue-600 mb-2">{{ playerData.total_matches }}</div>
              <div class="text-sm font-medium text-muted-foreground">Parties jouées</div>
            </CardContent>
          </Card>

          <!-- Victoires -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-green-100 rounded-full">
                <Trophy class="w-6 h-6 text-green-600" />
              </div>
              <div class="text-3xl font-bold text-green-600 mb-2">{{ playerData.wins }}</div>
              <div class="text-sm font-medium text-muted-foreground">Victoires</div>
            </CardContent>
          </Card>

          <!-- Défaites -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-red-100 rounded-full">
                <Target class="w-6 h-6 text-red-600" />
              </div>
              <div class="text-3xl font-bold text-red-600 mb-2">{{ getLosses(playerData) }}</div>
              <div class="text-sm font-medium text-muted-foreground">Défaites</div>
            </CardContent>
          </Card>

          <!-- Taux de victoire -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-purple-100 rounded-full">
                <TrendingUp class="w-6 h-6 text-purple-600" />
              </div>
              <div class="text-3xl font-bold text-purple-600 mb-2">{{ winRate }}%</div>
              <div class="text-sm font-medium text-muted-foreground">Taux de victoire</div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Section Statistiques 2v2 -->
      <div v-if="playerData.team_total_matches > 0" class="space-y-4">
        <div class="flex items-center gap-2 mb-4">
          <Users class="w-5 h-5 text-blue-600" />
          <h3 class="text-xl font-semibold">Statistiques 2v2</h3>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
          <!-- ELO Team Rating -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-blue-100 rounded-full">
                <TrendingUp class="w-6 h-6 text-blue-600" />
              </div>
              <div class="text-3xl font-bold text-blue-600 mb-2">{{ Math.round(playerData.team_elo_rating) }}</div>
              <div class="text-sm font-medium text-muted-foreground">ELO 2v2</div>
            </CardContent>
          </Card>

          <!-- Parties jouées en équipe -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-cyan-100 rounded-full">
                <Target class="w-6 h-6 text-cyan-600" />
              </div>
              <div class="text-3xl font-bold text-cyan-600 mb-2">{{ playerData.team_total_matches }}</div>
              <div class="text-sm font-medium text-muted-foreground">Parties en équipe</div>
            </CardContent>
          </Card>

          <!-- Victoires en équipe -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-emerald-100 rounded-full">
                <Trophy class="w-6 h-6 text-emerald-600" />
              </div>
              <div class="text-3xl font-bold text-emerald-600 mb-2">{{ playerData.team_wins }}</div>
              <div class="text-sm font-medium text-muted-foreground">Victoires</div>
            </CardContent>
          </Card>

          <!-- Défaites en équipe -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-orange-100 rounded-full">
                <Target class="w-6 h-6 text-orange-600" />
              </div>
              <div class="text-3xl font-bold text-orange-600 mb-2">{{ playerData.team_losses }}</div>
              <div class="text-sm font-medium text-muted-foreground">Défaites</div>
            </CardContent>
          </Card>

          <!-- Taux de victoire en équipe -->
          <Card class="text-center">
            <CardContent class="pt-6">
              <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 bg-indigo-100 rounded-full">
                <TrendingUp class="w-6 h-6 text-indigo-600" />
              </div>
              <div class="text-3xl font-bold text-indigo-600 mb-2">{{ teamWinRate }}%</div>
              <div class="text-sm font-medium text-muted-foreground">Taux de victoire</div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Section Graphique ELO et Derniers Matches -->
      <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mt-6">
        <!-- Graphique ELO -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>Évolution ELO</CardTitle>
              <div v-if="showEloToggle" class="flex bg-muted rounded-lg p-1">
                <button
                  @click="selectedEloType = 'solo'"
                  :class="[
                    'px-3 py-1 rounded-md text-sm font-medium transition-colors',
                    selectedEloType === 'solo' 
                      ? 'bg-background text-foreground shadow-sm' 
                      : 'text-muted-foreground hover:text-foreground'
                  ]"
                >
                  1v1
                </button>
                <button
                  @click="selectedEloType = 'team'"
                  :class="[
                    'px-3 py-1 rounded-md text-sm font-medium transition-colors',
                    selectedEloType === 'team' 
                      ? 'bg-background text-foreground shadow-sm' 
                      : 'text-muted-foreground hover:text-foreground'
                  ]"
                >
                  2v2
                </button>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <EloChart 
              :player-id="parseInt(playerId)" 
              :elo-history="currentEloHistory"
              :is-loading="isLoadingElo"
              :match-type="selectedEloType"
            />
          </CardContent>
        </Card>

        <!-- Derniers Matches -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Clock class="w-5 h-5" />
              Derniers matches
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="recentMatches.length === 0" class="text-center text-muted-foreground py-8">
              Aucun match trouvé
            </div>
            <div v-else class="space-y-3">
              <div 
                v-for="match in recentMatches" 
                :key="match.id"
                class="flex items-center justify-between p-4 rounded-lg border bg-muted/20"
              >
                <div class="flex items-center gap-3">
                  <User class="w-5 h-5 text-muted-foreground" />
                  <div>
                    <div class="font-medium">
                      vs <PlayerLink v-if="getOpponent(match, playerData.id)" :player="getOpponent(match, playerData.id)!" />
                      <span v-else>Joueur inconnu</span>
                    </div>
                    <div class="text-sm text-muted-foreground">
                      {{ formatDateShort(match.created_at) }}
                    </div>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <Badge 
                    :variant="getMatchResult(match, playerData.id) === 'win' ? 'default' : getMatchResult(match, playerData.id) === 'loss' ? 'destructive' : 'secondary'"
                  >
                    {{ getMatchResult(match, playerData.id) === 'win' ? 'Victoire' : getMatchResult(match, playerData.id) === 'loss' ? 'Défaite' : 'En attente' }}
                  </Badge>
                  <Badge v-if="match.status !== 'confirmed'" variant="outline" class="text-xs">
                    {{ match.status === 'pending' ? 'En attente' : 'Rejeté' }}
                  </Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { Card, CardContent, CardHeader, CardTitle } from '@/shared/components/ui/card'
import { Badge } from '@/shared/components/ui/badge'
import { Trophy, Target, TrendingUp, Clock, User, Users } from 'lucide-vue-next'
import PlayerService from '@/features/core/services/player.service'
import PlayerLink from '@/features/core/components/PlayerLink.vue'
import EloChart from '@/features/core/components/EloChart.vue'
import { usePlayer } from '@/features/core/composables/usePlayer'
import { useDateFormatter } from '@/shared/composables/useDateFormatter'
import type { Player, EloChartEntry } from '@/features/core/types/player'
import type { Match } from '@/features/core/types/match'

const route = useRoute()
const playerId = computed(() => route.params.id as string)

const playerData = ref<Player | null>(null)
const recentMatches = ref<Match[]>([])
const eloHistory = ref<EloChartEntry[]>([])
const teamEloHistory = ref<EloChartEntry[]>([])
const isLoading = ref(true)
const isLoadingElo = ref(true)
const selectedEloType = ref<'solo' | 'team'>('team')

const { getWinRate, getLosses } = usePlayer()
const { formatDate, formatDateShort } = useDateFormatter()

const winRate = computed(() => {
  if (!playerData.value) return 0
  return getWinRate(playerData.value)
})

const teamWinRate = computed(() => {
  if (!playerData.value || playerData.value.team_total_matches === 0) return 0
  return Math.round((playerData.value.team_wins / playerData.value.team_total_matches) * 100)
})

const showEloToggle = computed(() => {
  return playerData.value && playerData.value.total_matches > 0 && playerData.value.team_total_matches > 0
})

const currentEloHistory = computed(() => {
  return selectedEloType.value === 'solo' ? eloHistory.value : teamEloHistory.value
})


const getMatchResult = (match: Match, currentPlayerId: number) => {
  if (match.winner_id === currentPlayerId) return 'win'
  if (match.winner_id && match.winner_id !== currentPlayerId) return 'loss'
  return 'pending'
}

const getOpponent = (match: Match, currentPlayerId: number) => {
  return match.player1_id === currentPlayerId ? match.player2 : match.player1
}

const loadPlayerData = async () => {
  const id = parseInt(playerId.value)
  if (isNaN(id)) return
  
  try {
    isLoading.value = true
    const [player, matchesResponse] = await Promise.all([
      PlayerService.getPlayer(id),
      PlayerService.getPlayerMatches(id, { page: 1, pageSize: 5 })
    ])
    playerData.value = player
    recentMatches.value = matchesResponse.data
    
    // Charger l'historique ELO en parallèle
    loadEloHistory(id)
  } catch (error) {
    console.error('Error fetching player data:', error)
  } finally {
    isLoading.value = false
  }
}

// Watcher pour surveiller les changements d'ID dans l'URL
watch(playerId, () => {
  loadPlayerData()
}, { immediate: true })

// Watcher pour recharger les données ELO quand on change de type
watch(selectedEloType, async (newType) => {
  if (!playerData.value) return
  
  const id = parseInt(playerId.value)
  if (isNaN(id)) return
  
  // Si on n'a pas encore chargé les données pour ce type, les charger
  if (newType === 'solo' && eloHistory.value.length === 0) {
    try {
      isLoadingElo.value = true
      eloHistory.value = await PlayerService.getPlayerEloHistory(id, 30, 'solo')
    } catch (error) {
      console.error('Error loading solo ELO history:', error)
    } finally {
      isLoadingElo.value = false
    }
  } else if (newType === 'team' && teamEloHistory.value.length === 0) {
    try {
      isLoadingElo.value = true
      teamEloHistory.value = await PlayerService.getPlayerEloHistory(id, 30, 'team')
    } catch (error) {
      console.error('Error loading team ELO history:', error)
    } finally {
      isLoadingElo.value = false
    }
  }
})

onMounted(() => {
  // Le watcher avec immediate: true gère déjà le chargement initial
})

const loadEloHistory = async (id: number) => {
  try {
    isLoadingElo.value = true
    
    // Charger les deux historiques en parallèle
    const [soloHistory, teamHistory] = await Promise.all([
      PlayerService.getPlayerEloHistory(id, 30, 'solo'),
      PlayerService.getPlayerEloHistory(id, 30, 'team')
    ])
    
    eloHistory.value = soloHistory
    teamEloHistory.value = teamHistory
    
    // Définir le type par défaut en fonction des données disponibles (priorité 2v2)
    if (playerData.value?.team_total_matches && playerData.value.team_total_matches > 0) {
      selectedEloType.value = 'team'
    } else if (playerData.value?.total_matches && playerData.value.total_matches > 0) {
      selectedEloType.value = 'solo'
    }
  } catch (error) {
    console.error('Error fetching ELO history:', error)
    eloHistory.value = []
    teamEloHistory.value = []
  } finally {
    isLoadingElo.value = false
  }
}
</script>