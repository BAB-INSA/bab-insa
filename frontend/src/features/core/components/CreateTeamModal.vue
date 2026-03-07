<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Users class="w-5 h-5" />
          Créer une Équipe
        </DialogTitle>
        <DialogDescription>
          Formez une équipe avec un autre joueur pour participer aux matchs 2v2.
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4 mt-6">
        <!-- Player 1 (current user) -->
        <div>
          <Label for="player1">Joueur 1 (Vous)</Label>
          <Input
            id="player1"
            :value="currentUserName"
            disabled
            class="bg-muted"
          />
        </div>

        <!-- Player 2 Selection -->
        <div>
          <Label for="player2">Partenaire</Label>
          <Select v-model="selectedPlayer2Id" required>
            <SelectTrigger>
              <SelectValue placeholder="Choisissez votre partenaire" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem 
                v-for="player in availablePlayers" 
                :key="player.id" 
                :value="player.id.toString()"
              >
                {{ player.username }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Team Name -->
        <div>
          <Label for="teamName">Nom de l'équipe</Label>
          <Input
            id="teamName"
            v-model="teamName"
            placeholder="ex: Les Pros"
            required
          />
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
            :disabled="isSubmitting || !selectedPlayer2Id || !teamName.trim()"
            class="flex-1"
          >
            <Loader2 v-if="isSubmitting" class="w-4 h-4 mr-2 animate-spin" />
            Créer l'équipe
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
import { Input } from '@/shared/components/ui/input'
import { Label } from '@/shared/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'
import { Users, Loader2 } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useAuthStore } from '@/features/auth/stores/auth'
import TeamService, { type TeamCreateData } from '@/features/core/services/team.service'
import PlayerService from '@/features/core/services/player.service'
import type { Team } from '@/features/core/types/team'
import type { Player } from '@/features/core/types/player'

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
  'team-created': [team: Team]
}>()

// State
const authStore = useAuthStore()
const isOpen = ref(props.open)
const isSubmitting = ref(false)
const selectedPlayer2Id = ref<string>('')
const teamName = ref('')
const availablePlayers = ref<Player[]>([])

// Computed
const currentUserName = computed(() => authStore.user?.username || 'Utilisateur')

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

// Methods
const resetForm = () => {
  selectedPlayer2Id.value = ''
  teamName.value = ''
}

const loadPlayers = async () => {
  try {
    const response = await PlayerService.getPlayers({ page: 1, pageSize: 100 })
    // Filter out current user
    availablePlayers.value = response.data.filter(player => player.id !== authStore.user?.id)
  } catch (error) {
    console.error('Error loading players:', error)
    toast.error('Erreur lors du chargement des joueurs')
  }
}

const handleSubmit = async () => {
  if (!authStore.user?.id || !selectedPlayer2Id.value || !teamName.value.trim()) {
    return
  }

  try {
    isSubmitting.value = true
    
    const teamData: TeamCreateData = {
      player1_id: authStore.user.id,
      player2_id: parseInt(selectedPlayer2Id.value),
      name: teamName.value.trim()
    }
    
    // Créer l'équipe
    const createdTeam = await TeamService.createTeam(teamData)
    
    // Notification de succès
    toast.success('Équipe créée avec succès !', {
      description: `L'équipe "${createdTeam.name}" a été formée.`
    })
    
    // Émettre l'événement
    emit('team-created', createdTeam)
    
    // Fermer la modal
    isOpen.value = false
    
  } catch (error: unknown) {
    console.error('Error creating team:', error)
    
    // Gestion spécifique de l'erreur 409 (équipe déjà existante)
    if (error && typeof error === 'object' && 'response' in error && 
        error.response && typeof error.response === 'object' && 'status' in error.response &&
        (error.response as { status: number }).status === 409) {
      toast.error('Cette équipe existe déjà', {
        description: 'Vous avez déjà formé une équipe avec ce joueur.'
      })
    } else {
      toast.error('Erreur lors de la création de l\'équipe', {
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

// Load players when component mounts or modal opens
onMounted(() => {
  loadPlayers()
})

watch(isOpen, (newValue) => {
  if (newValue) {
    loadPlayers()
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