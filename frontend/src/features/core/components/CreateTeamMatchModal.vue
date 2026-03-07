<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Trophy class="w-5 h-5" />
          Nouveau Match 2v2
        </DialogTitle>
        <DialogDescription>
          Enregistrez le résultat d'un match d'équipe que vous venez de jouer.
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
                v-for="team in playerTeams" 
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
                id="team1-winner"
                :value="selectedTeam1Id"
                v-model="winnerTeamId"
                class="w-4 h-4"
              />
              <Label for="team1-winner" class="cursor-pointer">
                {{ getTeamDisplayName(selectedTeam1Id, playerTeams) }}
              </Label>
            </div>
            <div class="flex items-center space-x-2">
              <input
                type="radio"
                id="team2-winner"
                :value="selectedTeam2Id"
                v-model="winnerTeamId"
                class="w-4 h-4"
              />
              <Label for="team2-winner" class="cursor-pointer">
                {{ getTeamDisplayName(selectedTeam2Id, allTeams) }}
              </Label>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-3 pt-4">
          <Button
            type="button"
            variant="outline"
            @click="handleCancel"
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
            Créer le match
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, computed } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import { Button } from '@/shared/components/ui/button'
import { Label } from '@/shared/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'
import { Trophy, Loader2 } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useAuthStore } from '@/features/auth/stores/auth'
import TeamMatchService, { type TeamMatchCreateData } from '@/features/core/services/team-match.service'
import type { TeamMatch } from '@/features/admin/types/team-match'
import PlayerService from '@/features/core/services/player.service'
import TeamService from '@/features/core/services/team.service'
import type { Team } from '@/features/core/types/team'

// Props
interface Props {
  open?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  open: false
})

// Emits
const emit = defineEmits<{
  'update:open': [value: boolean]
  'team-match-created': [teamMatch: TeamMatch]
}>()

// State
const authStore = useAuthStore()
const isOpen = ref(props.open)
const isSubmitting = ref(false)
const selectedTeam1Id = ref<string>('')
const selectedTeam2Id = ref<string>('')
const winnerTeamId = ref<string>('')
const playerTeams = ref<Team[]>([])
const allTeams = ref<Team[]>([])

// Computed
const availableOpponentTeams = computed(() => {
  return allTeams.value.filter(team => 
    team.id.toString() !== selectedTeam1Id.value
  )
})

// Watchers
watch(() => props.open, (newValue) => {
  isOpen.value = newValue
})

watch(isOpen, (newValue) => {
  emit('update:open', newValue)
  
  // Reset form when closing
  if (!newValue) {
    resetForm()
  }
})

// Reset winner when teams change
watch([selectedTeam1Id, selectedTeam2Id], () => {
  winnerTeamId.value = ''
})

// Methods
const resetForm = () => {
  selectedTeam1Id.value = ''
  selectedTeam2Id.value = ''
  winnerTeamId.value = ''
}

const getTeamDisplayName = (teamId: string, teams: Team[]): string => {
  const team = teams.find(t => t.id.toString() === teamId)
  if (!team) return 'Équipe inconnue'
  return `${team.name} (${team.player1?.username || 'Joueur 1'} & ${team.player2?.username || 'Joueur 2'})`
}

const loadPlayerTeams = async () => {
  if (!authStore.user?.id) return
  
  try {
    playerTeams.value = await PlayerService.getPlayerTeams(authStore.user.id)
  } catch (error) {
    console.error('Error loading player teams:', error)
    toast.error('Erreur lors du chargement de vos équipes')
  }
}

const loadAllTeams = async () => {
  try {
    const response = await TeamService.getTeams({ page: 1, pageSize: 100 })
    allTeams.value = response.data
  } catch (error) {
    console.error('Error loading teams:', error)
    toast.error('Erreur lors du chargement des équipes')
  }
}

const handleSubmit = async () => {
  if (!selectedTeam1Id.value || !selectedTeam2Id.value || !winnerTeamId.value) {
    return
  }

  try {
    isSubmitting.value = true
    
    const teamMatchData: TeamMatchCreateData = {
      team1_id: parseInt(selectedTeam1Id.value),
      team2_id: parseInt(selectedTeam2Id.value),
      winner_team_id: parseInt(winnerTeamId.value)
    }
    
    // Créer le match d'équipe
    const createdTeamMatch = await TeamMatchService.createTeamMatch(teamMatchData)
    
    // Notification de succès
    const winnerTeam = [...playerTeams.value, ...allTeams.value].find(t => t.id.toString() === winnerTeamId.value)
    toast.success('Match 2v2 créé avec succès !', {
      description: `Victoire de ${winnerTeam?.name || 'l\'équipe'}`
    })
    
    // Émettre l'événement
    emit('team-match-created', createdTeamMatch)
    
    // Fermer la modal
    isOpen.value = false
    
  } catch (error: unknown) {
    console.error('Error creating team match:', error)
    
    // Gestion spécifique de certaines erreurs si nécessaire
    toast.error('Erreur lors de la création du match', {
      description: 'Veuillez réessayer ou contacter un administrateur.'
    })
  } finally {
    isSubmitting.value = false
  }
}

const handleCancel = () => {
  isOpen.value = false
}

// Load data when component mounts or modal opens
onMounted(() => {
  loadPlayerTeams()
  loadAllTeams()
})

watch(isOpen, (newValue) => {
  if (newValue) {
    loadPlayerTeams()
    loadAllTeams()
  }
})

// Expose methods
const openModal = () => {
  isOpen.value = true
}

const closeModal = () => {
  isOpen.value = false
}

defineExpose({
  openModal,
  closeModal
})
</script>