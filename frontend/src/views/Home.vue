<template>
  <div class="space-y-8">
    <!-- Header avec logo BAB'INSA -->
    <div class="flex flex-col items-center space-y-6">
      <img 
        src="/header-logo.svg" 
        alt="BAB'INSA - Association Baby-foot INSA Rouen" 
        class="h-24 md:h-28 w-auto"
      />
    </div>

    <!-- Stats Overview -->
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Joueurs</CardTitle>
          <UserIcon class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.totalPlayers }}</div>
          <p class="text-xs text-muted-foreground">Joueurs enregistrés</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Matchs 1v1</CardTitle>
          <TrophyIcon class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.totalMatches }}</div>
          <p class="text-xs text-muted-foreground">Matchs joués</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Tournois</CardTitle>
          <TrophyIcon class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.totalTournaments }}</div>
          <p class="text-xs text-muted-foreground">Tournois organisés</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Total Équipes</CardTitle>
          <UsersIcon class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.totalTeams }}</div>
          <p class="text-xs text-muted-foreground">Équipes actives</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Matchs 2v2</CardTitle>
          <UsersIcon class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.totalTeamMatches }}</div>
          <p class="text-xs text-muted-foreground">Matchs équipes</p>
        </CardContent>
      </Card>

      <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
          <CardTitle class="text-sm font-medium">Équipes</CardTitle>
          <UsersIcon class="h-4 w-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">{{ stats.totalTeams }}</div>
          <p class="text-xs text-muted-foreground">Équipes actives</p>
        </CardContent>
      </Card>
    </div>

    <!-- Main Content Grid -->
    <div class="grid gap-6 lg:grid-cols-3">
      <!-- Left Column: Leaderboards -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Classements ELO -->
        <div class="grid gap-6 md:grid-cols-2">
          <!-- Top 10 ELO Players 1v1 -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <TrophyIcon class="h-5 w-5" />
                Top 10 Classement 1v1
              </CardTitle>
              <CardDescription>Les meilleurs joueurs par points ELO 1v1</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div v-for="(player, index) in topPlayers" :key="player.id" class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full" 
                     :class="getRankBadgeClass(index)">
                  <span class="text-sm font-bold">{{ index + 1 }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <PlayerLink :player="player" class="text-left" />
                  <p class="text-sm text-muted-foreground">
                    {{ getWinLossRecord(player) }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="font-bold">{{ Math.round(player.eloRating) }}</p>
                  <p class="text-xs text-muted-foreground">ELO</p>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Top 10 Players 2v2 -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <UsersIcon class="h-5 w-5" />
                Top 10 Classement 2v2
              </CardTitle>
              <CardDescription>Les meilleurs joueurs en 2v2 par points ELO</CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div v-for="(player, index) in topTeams" :key="`team-${player.id}`" class="flex items-center gap-3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full" 
                     :class="getRankBadgeClass(index)">
                  <span class="text-sm font-bold">{{ index + 1 }}</span>
                </div>
                <div class="flex-1 min-w-0">
                  <PlayerLink :player="player" class="text-left" />
                  <p class="text-sm text-muted-foreground">
                    {{ getTeamWinLossRecord(player) }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="font-bold">{{ Math.round(player.teamEloRating) }}</p>
                  <p class="text-xs text-muted-foreground">ELO 2v2</p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Right Column: Actions and Recent Activity -->
      <div class="lg:col-span-1 space-y-6">
        <!-- Quick Actions -->
        <Card>
          <CardHeader>
            <CardTitle>Actions Rapides</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Button v-if="authStore.isAuthenticated" @click="openNewMatchModal" class="w-full justify-start" variant="outline">
              <PlusIcon class="mr-2 h-4 w-4" />
              Nouveau Match 1v1
            </Button>
            <Button v-if="authStore.isAuthenticated" @click="openNewTeamMatchModal" class="w-full justify-start" variant="outline">
              <TrophyIcon class="mr-2 h-4 w-4" />
              Nouveau Match 2v2
            </Button>
            <Button v-if="authStore.isAuthenticated" @click="openNewTeamModal" class="w-full justify-start" variant="outline">
              <UsersIcon class="mr-2 h-4 w-4" />
              Créer une Équipe
            </Button>
            <Button v-if="authStore.isAuthenticated" @click="openJoinTournamentModal" class="w-full justify-start" variant="outline">
              <TrophyIcon class="mr-2 h-4 w-4" />
              Rejoindre un Tournoi Équipe
            </Button>
            <Button @click="viewAllPlayers" class="w-full justify-start" variant="outline">
              <UserIcon class="mr-2 h-4 w-4" />
              Voir Tous les Joueurs
            </Button>
          </CardContent>
        </Card>
        <!-- Recent Matches -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <ClockIcon class="h-5 w-5" />
              Derniers Matchs
            </CardTitle>
            <CardDescription>Les {{ recentMatches.length }} derniers matchs joués</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div v-for="match in recentMatches" :key="match.id" 
                   class="flex items-center justify-between p-4 rounded-lg border">
                <div class="flex items-center gap-4">
                  <div class="text-center">
                    <PlayerLink :player="match.player1" />
                    <p class="text-sm text-muted-foreground">vs</p>
                    <PlayerLink :player="match.player2" />
                  </div>
                  <div class="text-center">
                    <Badge :variant="match.status === 'confirmed' ? 'default' : 'secondary'">
                      {{ getStatusLabel(match.status) }}
                    </Badge>
                  </div>
                </div>
                <div class="text-right">
                  <PlayerLink :player="match.winner" class="font-bold text-green-600" />
                  <p class="text-sm text-muted-foreground">{{ formatMatchDate(match.createdAt) }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>


      </div>
    </div>

    <!-- Create Match Modal -->
    <CreateMatchModal 
      v-model:open="showCreateMatchModal"
      @match-created="handleMatchCreated"
    />
    
    <!-- Create Team Modal -->
    <CreateTeamModal 
      v-model:open="showCreateTeamModal"
      @team-created="handleTeamCreated"
    />
    
    <!-- Create Team Match Modal -->
    <CreateTeamMatchModal
      v-model:open="showCreateTeamMatchModal"
      @team-match-created="handleTeamMatchCreated"
    />

    <!-- Join Tournament Modal -->
    <JoinTeamTournamentModal
      v-model:open="showJoinTournamentModal"
      @joined="handleTournamentJoined"
    />
  </div>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useSEO } from '@/shared/composables/useSEO'
import { useDateFormatter } from '@/shared/composables/useDateFormatter'
import { useAuthStore } from '@/features/auth/stores/auth'
import type { Player, Match } from '@/features/core/types'
import type { Team } from '@/features/core/types/team'
import type { TeamMatch } from '@/features/admin/types/team-match'
import playerService from '@/features/core/services/player.service'
import matchService from '@/features/core/services/match.service'
import statsService, { type Stats } from '@/features/core/services/stats.service'
import { usePlayer } from '@/features/core/composables/usePlayer'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/shared/components/ui/card'
import { Button } from '@/shared/components/ui/button'
import { Badge } from '@/shared/components/ui/badge'
import PlayerLink from '@/features/core/components/PlayerLink.vue'
import CreateMatchModal from '@/features/core/components/CreateMatchModal.vue'
import CreateTeamModal from '@/features/core/components/CreateTeamModal.vue'
import CreateTeamMatchModal from '@/features/core/components/CreateTeamMatchModal.vue'
import JoinTeamTournamentModal from '@/features/core/components/JoinTeamTournamentModal.vue'
import {
  UserIcon,
  TrophyIcon,
  ClockIcon,
  PlusIcon,
  UsersIcon,
} from 'lucide-vue-next'

// Router and auth store
const router = useRouter()
const authStore = useAuthStore()

// Reactive data
const topPlayersData = ref<Player[]>([])
const topTeamsData = ref<Player[]>([])
const recentMatchesData = ref<Match[]>([])
const statsData = ref<Stats>({
  totalPlayers: 0,
  totalMatches: 0,
  totalTeams: 0,
  totalTeamMatches: 0,
  totalTournaments: 0,
})
const isLoading = ref(false)
const showCreateMatchModal = ref(false)
const showCreateTeamModal = ref(false)
const showCreateTeamMatchModal = ref(false)
const showJoinTournamentModal = ref(false)

// Computed values
const topPlayers = computed(() => {
  return topPlayersData.value.slice(0, 10)
})

const topTeams = computed(() => {
  return topTeamsData.value.slice(0, 10)
})

const recentMatches = computed(() => {
  return recentMatchesData.value.slice(0, 10)
})


const stats = computed(() => ({
  totalPlayers: statsData.value.totalPlayers,
  totalMatches: statsData.value.totalMatches,
  totalTeams: statsData.value.totalTeams,
  totalTeamMatches: statsData.value.totalTeamMatches,
  totalTournaments: statsData.value.totalTournaments,
}))

// Utility functions
const { formatRelativeTime } = useDateFormatter()
const { getWinLossRecord } = usePlayer()

const getTeamWinLossRecord = (player: Player): string => {
  return `${player.teamWins}V - ${player.teamLosses}D (${player.teamTotalMatches} matchs)`
}


// Load top players from API
const loadTopPlayers = async () => {
  try {
    isLoading.value = true
    // Use getTopPlayers with limit 10 for dashboard
    topPlayersData.value = await playerService.getTopPlayers(10)
  } catch (error) {
    console.error('Error loading top players:', error)
  } finally {
    isLoading.value = false
  }
}

// Load top teams from API
const loadTopTeams = async () => {
  try {
    topTeamsData.value = await playerService.getTopTeams(10)
  } catch (error) {
    console.error('Error loading top teams:', error)
  }
}

// Load recent matches from API
const loadRecentMatches = async () => {
  try {
    recentMatchesData.value = await matchService.getRecentMatches()
  } catch (error) {
    console.error('Error loading recent matches:', error)
  }
}


// Load stats from API
const loadStats = async () => {
  try {
    statsData.value = await statsService.getStats()
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

// Load data on mount
onMounted(() => {
  loadTopPlayers()
  loadTopTeams()
  loadRecentMatches()
  loadStats()
})

const getRankBadgeClass = (index: number): string => {
  if (index === 0) return 'bg-yellow-500 text-yellow-900'
  if (index === 1) return 'bg-gray-400 text-gray-900'
  if (index === 2) return 'bg-amber-600 text-amber-900'
  return 'bg-muted text-muted-foreground'
}

const getStatusLabel = (status: string): string => {
  const labels: Record<string, string> = {
    'pending': 'En attente',
    'confirmed': 'Confirmé',
    'rejected': 'Rejeté'
  }
  return labels[status] || status
}

const formatMatchDate = (dateStr: string): string => {
  return formatRelativeTime(dateStr)
}

// Action handlers
const openNewMatchModal = () => {
  showCreateMatchModal.value = true
}

const openNewTeamModal = () => {
  showCreateTeamModal.value = true
}

const openNewTeamMatchModal = () => {
  showCreateTeamMatchModal.value = true
}

const handleMatchCreated = (_match: Match) => {
  // Recharger les données après création d'un match
  loadRecentMatches()
  loadTopPlayers()
  loadTopTeams()
  loadStats()
}

const handleTeamCreated = (_team: Team) => {
  // Recharger les données après création d'une équipe
  loadTopTeams()
  loadStats()
}

const handleTeamMatchCreated = (_teamMatch: TeamMatch) => {
  // Recharger les données après création d'un match d'équipe
  loadRecentMatches()
  loadTopTeams()
  loadStats()
}

const openJoinTournamentModal = () => {
  showJoinTournamentModal.value = true
}

const handleTournamentJoined = () => {
  loadStats()
}

const viewAllPlayers = () => {
  router.push({ name: 'PlayerList' })
}

// SEO Configuration
const seoData = computed(() => ({
  title: 'Dashboard Baby-Foot',
  description: 'Suivez les statistiques, classements et derniers matchs de baby-foot en temps réel.',
  ogType: 'website'
}))

useSEO(seoData)
</script>