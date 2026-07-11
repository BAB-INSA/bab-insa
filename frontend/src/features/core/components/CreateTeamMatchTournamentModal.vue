<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Swords class="w-5 h-5" />
          Déclarer un match de tournoi
        </DialogTitle>
        <DialogDescription>
          Enregistrez le résultat d'un match joué dans le cadre de ce tournoi.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4 mt-6">
        <!-- Team 1 (User's team) -->
        <div>
          <Label for="team1">Votre équipe</Label>
          <Select v-model="selectedTeam1Id" required>
            <SelectTrigger>
              <SelectValue placeholder="Choisissez votre équipe" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="team in userTournamentTeams"
                :key="team.id"
                :value="team.id.toString()"
              >
                {{ team.name }} ({{ team.player1.username }} & {{ team.player2.username }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Team 2 (Opponent team) -->
        <div>
          <Label for="team2">Équipe adverse</Label>
          <Select v-model="selectedTeam2Id" required>
            <SelectTrigger>
              <SelectValue placeholder="Choisissez l'équipe adverse" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="team in availableOpponentTeams"
                :key="team.id"
                :value="team.id.toString()"
              >
                {{ team.name }} ({{ team.player1?.username || 'Joueur 1' }} & {{ team.player2?.username || 'Joueur 2' }})
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Winner Selection -->
        <div v-if="selectedTeam1Id && selectedTeam2Id">
          <Label>Équipe gagnante</Label>
          <div class="space-y-2 mt-2">
            <div class="flex items-center space-x-2">
              <input
                type="radio"
                id="tournament-team1-winner"
                :value="selectedTeam1Id"
                v-model="winnerTeamId"
                class="w-4 h-4"
              />
              <Label for="tournament-team1-winner" class="cursor-pointer">
                {{ getTeamName(selectedTeam1Id) }}
              </Label>
            </div>
            <div class="flex items-center space-x-2">
              <input
                type="radio"
                id="tournament-team2-winner"
                :value="selectedTeam2Id"
                v-model="winnerTeamId"
                class="w-4 h-4"
              />
              <Label for="tournament-team2-winner" class="cursor-pointer">
                {{ getTeamName(selectedTeam2Id) }}
              </Label>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4">
          <Button
            type="button"
            variant="outline"
            @click="isOpen = false"
            class="flex-1"
          >
            Annuler
          </Button>
          <Button
            type="submit"
            :disabled="isSubmitting || !selectedTeam1Id || !selectedTeam2Id || !winnerTeamId"
            class="flex-1"
          >
            <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
            Déclarer le match
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import { Button } from '@/shared/components/ui/button'
import { Label } from '@/shared/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'
import { Swords, Loader2 } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useAuthStore } from '@/features/auth/stores/auth'
import TeamMatchService from '@/features/core/services/team-match.service'
import PlayerService from '@/features/core/services/player.service'
import type { Team } from '@/features/core/types/team'
import type { TeamMatch } from '@/features/admin/types/team-match'

interface Props {
  open?: boolean
  tournamentId: number
  tournamentTeams: Team[]
}

const props = withDefaults(defineProps<Props>(), {
  open: false,
})

const emit = defineEmits<{
  'update:open': [value: boolean]
  'tournament-match-created': [teamMatch: TeamMatch]
}>()

const authStore = useAuthStore()
const isOpen = ref(props.open)
const isSubmitting = ref(false)
const selectedTeam1Id = ref<string>('')
const selectedTeam2Id = ref<string>('')
const winnerTeamId = ref<string>('')
const playerTeams = ref<Team[]>([])

// Player's teams that are also registered in the tournament
const userTournamentTeams = computed(() => {
  const tournamentTeamIds = new Set(props.tournamentTeams.map(t => t.id))
  return playerTeams.value.filter(t => tournamentTeamIds.has(t.id))
})

// Opponent teams: all tournament teams except the selected user team
const availableOpponentTeams = computed(() => {
  return props.tournamentTeams.filter(
    team => team.id.toString() !== selectedTeam1Id.value,
  )
})

// Build a lookup of all tournament teams for display
const allTeamsMap = computed(() => {
  const map = new Map<string, Team>()
  for (const team of props.tournamentTeams) {
    map.set(team.id.toString(), team)
  }
  for (const team of playerTeams.value) {
    map.set(team.id.toString(), team)
  }
  return map
})

const getTeamName = (teamId: string): string => {
  const team = allTeamsMap.value.get(teamId)
  if (!team) return 'Équipe inconnue'
  return `${team.name} (${team.player1?.username || 'Joueur 1'} & ${team.player2?.username || 'Joueur 2'})`
}

watch(() => props.open, (val) => {
  isOpen.value = val
})

watch(isOpen, (val) => {
  emit('update:open', val)
  if (!val) {
    selectedTeam1Id.value = ''
    selectedTeam2Id.value = ''
    winnerTeamId.value = ''
  }
})

// Reset winner when teams change
watch([selectedTeam1Id, selectedTeam2Id], () => {
  winnerTeamId.value = ''
})

const loadPlayerTeams = async () => {
  if (!authStore.user?.id) return
  try {
    playerTeams.value = await PlayerService.getPlayerTeams(authStore.user.id)
  } catch (error) {
    console.error('Error loading player teams:', error)
  }
}

const handleSubmit = async () => {
  if (!selectedTeam1Id.value || !selectedTeam2Id.value || !winnerTeamId.value) return

  try {
    isSubmitting.value = true

    const createdMatch = await TeamMatchService.createTeamMatch({
      team1Id: parseInt(selectedTeam1Id.value),
      team2Id: parseInt(selectedTeam2Id.value),
      winnerTeamId: parseInt(winnerTeamId.value),
      tournamentId: props.tournamentId,
    })

    const winnerTeam = allTeamsMap.value.get(winnerTeamId.value)
    toast.success('Match de tournoi créé !', {
      description: `Victoire de ${winnerTeam?.name || "l'équipe"}`,
    })

    emit('tournament-match-created', createdMatch)
    isOpen.value = false
  } catch (error) {
    console.error('Error creating tournament match:', error)
    toast.error('Erreur lors de la création du match', {
      description: 'Veuillez réessayer ou contacter un administrateur.',
    })
  } finally {
    isSubmitting.value = false
  }
}

onMounted(loadPlayerTeams)

watch(isOpen, (val) => {
  if (val) loadPlayerTeams()
})
</script>
