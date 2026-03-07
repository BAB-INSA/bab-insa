<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Trophy class="w-5 h-5" />
          Rejoindre un tournoi équipe
        </DialogTitle>
        <DialogDescription>
          Sélectionnez un tournoi ouvert et l'équipe avec laquelle vous souhaitez participer.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4 mt-6">
        <!-- Tournament Selection -->
        <div>
          <Label for="tournament">Tournoi</Label>
          <Select v-model="selectedTournamentId" required>
            <SelectTrigger>
              <SelectValue placeholder="Choisissez un tournoi" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="tournament in tournaments"
                :key="tournament.id"
                :value="tournament.id.toString()"
              >
                {{ tournament.name }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Team Selection -->
        <div>
          <Label for="team">Votre équipe</Label>
          <Select v-model="selectedTeamId" required>
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
            :disabled="isSubmitting || !selectedTournamentId || !selectedTeamId"
            class="flex-1"
          >
            <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
            Rejoindre
          </Button>
        </div>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import { Button } from '@/shared/components/ui/button'
import { Label } from '@/shared/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'
import { Trophy, Loader2 } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useAuthStore } from '@/features/auth/stores/auth'
import TournamentService from '@/features/core/services/tournament.service'
import PlayerService from '@/features/core/services/player.service'
import type { Tournament } from '@/features/admin/types/tournament'
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
  'joined': []
}>()

// State
const authStore = useAuthStore()
const isOpen = ref(props.open)
const isSubmitting = ref(false)
const selectedTournamentId = ref<string>('')
const selectedTeamId = ref<string>('')
const tournaments = ref<Tournament[]>([])
const playerTeams = ref<Team[]>([])

// Watchers
watch(() => props.open, (newValue) => {
  isOpen.value = newValue
})

watch(isOpen, (newValue) => {
  emit('update:open', newValue)

  if (!newValue) {
    resetForm()
  }
})

// Methods
const resetForm = () => {
  selectedTournamentId.value = ''
  selectedTeamId.value = ''
}

const loadTournaments = async () => {
  try {
    tournaments.value = await TournamentService.getOpenedTeamTournaments()
  } catch (error) {
    console.error('Error loading tournaments:', error)
    toast.error('Erreur lors du chargement des tournois')
  }
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

const handleSubmit = async () => {
  if (!selectedTournamentId.value || !selectedTeamId.value) return

  try {
    isSubmitting.value = true

    await TournamentService.joinTournament(
      parseInt(selectedTournamentId.value),
      parseInt(selectedTeamId.value)
    )

    const tournament = tournaments.value.find(t => t.id.toString() === selectedTournamentId.value)
    toast.success('Inscription réussie !', {
      description: `Vous avez rejoint le tournoi "${tournament?.name || ''}"`
    })

    emit('joined')
    isOpen.value = false
  } catch (error: unknown) {
    console.error('Error joining tournament:', error)
    const apiError = error as { response?: { status?: number; data?: { message?: string } } }
    if (apiError.response?.status === 409) {
      toast.error(apiError.response.data?.message || 'Inscription impossible')
    } else {
      toast.error('Erreur lors de l\'inscription au tournoi', {
        description: 'Veuillez réessayer ou contacter un administrateur.'
      })
    }
  } finally {
    isSubmitting.value = false
  }
}

const handleCancel = () => {
  isOpen.value = false
}

// Load data when component mounts or modal opens
onMounted(() => {
  loadTournaments()
  loadPlayerTeams()
})

watch(isOpen, (newValue) => {
  if (newValue) {
    loadTournaments()
    loadPlayerTeams()
  }
})
</script>
