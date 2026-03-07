<!-- src/features/core/components/CreateMatchForm.vue -->
<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Sélection de l'adversaire -->
    <div class="space-y-2">
      <label for="opponent" class="text-sm font-medium">
        Adversaire
      </label>
      <Select v-model="selectedOpponentId" required>
        <SelectTrigger>
          <SelectValue placeholder="Choisir un adversaire..." />
        </SelectTrigger>
        <SelectContent>
          <SelectItem 
            v-for="player in availablePlayers" 
            :key="player.id" 
            :value="player.id.toString()"
          >
            {{ player.username }} (ELO: {{ (player as any).elo_rating || 1000 }})
          </SelectItem>
        </SelectContent>
      </Select>
    </div>

    <!-- Sélection du gagnant -->
    <div class="space-y-2">
      <label class="text-sm font-medium">
        Gagnant du match
      </label>
      <div class="grid grid-cols-2 gap-3">
        <label class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer transition-colors hover:bg-muted/50" :class="winnerId === currentUserId ? 'border-primary bg-primary/5' : ''">
          <input 
            type="radio" 
            :value="currentUserId" 
            v-model="winnerId" 
            class="sr-only"
            required
          />
          <div class="flex items-center space-x-2">
            <Trophy class="w-4 h-4" :class="winnerId === currentUserId ? 'text-primary' : 'text-muted-foreground'" />
            <span :class="winnerId === currentUserId ? 'font-medium' : ''">
              Moi ({{ currentUser?.username }})
            </span>
          </div>
        </label>
        
        <label 
          v-if="selectedOpponent"
          class="flex items-center space-x-2 p-3 border rounded-lg cursor-pointer transition-colors hover:bg-muted/50" 
          :class="winnerId === selectedOpponent.id ? 'border-primary bg-primary/5' : ''"
        >
          <input 
            type="radio" 
            :value="selectedOpponent.id" 
            v-model="winnerId" 
            class="sr-only"
            required
          />
          <div class="flex items-center space-x-2">
            <Trophy class="w-4 h-4" :class="winnerId === selectedOpponent.id ? 'text-primary' : 'text-muted-foreground'" />
            <span :class="winnerId === selectedOpponent.id ? 'font-medium' : ''">
              {{ selectedOpponent.username }}
            </span>
          </div>
        </label>
        
        <div v-else class="flex items-center justify-center p-3 border border-dashed rounded-lg text-muted-foreground text-sm">
          Sélectionnez d'abord un adversaire
        </div>
      </div>
    </div>

    <!-- Résumé du match -->
    <div v-if="selectedOpponent" class="p-4 bg-muted/30 rounded-lg">
      <h4 class="font-medium mb-2">Résumé du match</h4>
      <div class="text-sm space-y-1">
        <div>
          <strong>{{ currentUser?.username }}</strong> (ELO: {{ (currentUser as any)?.elo_rating || 1000 }}) 
          vs 
          <strong>{{ selectedOpponent.username }}</strong> (ELO: {{ (selectedOpponent as any).elo_rating || 1000 }})
        </div>
        <div v-if="winnerId" class="text-primary">
          🏆 Gagnant : {{ winnerId === currentUserId ? currentUser?.username : selectedOpponent.username }}
        </div>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end space-x-3 pt-4">
      <Button 
        type="button" 
        variant="outline" 
        @click="$emit('cancel')"
        :disabled="isSubmitting"
      >
        Annuler
      </Button>
      <Button 
        type="submit" 
        :disabled="!selectedOpponentId || !winnerId || isSubmitting"
        class="min-w-[120px]"
      >
        <div v-if="isSubmitting" class="flex items-center space-x-2">
          <div class="w-4 h-4 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
          <span>Création...</span>
        </div>
        <span v-else>Créer le match</span>
      </Button>
    </div>
  </form>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Button } from '@/shared/components/ui/button'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'
import { Trophy } from 'lucide-vue-next'
import { useAuthStore } from '@/features/auth/stores/auth'
import PlayerService from '@/features/core/services/player.service'
import type { Player } from '@/features/core/types/player'
import type { MatchCreateData } from '@/features/core/services/match.service'

// Props
interface Props {
  isSubmitting?: boolean
}

withDefaults(defineProps<Props>(), {
  isSubmitting: false
})

// Emits
const emit = defineEmits<{
  submit: [matchData: MatchCreateData]
  cancel: []
}>()

// State
const authStore = useAuthStore()
const availablePlayers = ref<Player[]>([])
const selectedOpponentId = ref<string>('')
const winnerId = ref<number | null>(null)
const isLoading = ref(true)

// Computed
const currentUser = computed(() => authStore.user)
const currentUserId = computed(() => authStore.user?.id || 0)

const selectedOpponent = computed(() => {
  if (!selectedOpponentId.value) return null
  return availablePlayers.value.find(p => p.id.toString() === selectedOpponentId.value) || null
})

// Methods
const loadPlayers = async () => {
  try {
    isLoading.value = true
    const response = await PlayerService.getPlayers({ page: 1, pageSize: 100 })
    // Exclure l'utilisateur connecté de la liste
    availablePlayers.value = response.data.filter(player => player.id !== currentUserId.value)
  } catch (error) {
    console.error('Error loading players:', error)
  } finally {
    isLoading.value = false
  }
}

const handleSubmit = () => {
  if (!selectedOpponentId.value || !winnerId.value) return
  
  const matchData = {
    player1_id: currentUserId.value,
    player2_id: parseInt(selectedOpponentId.value),
    winner_id: winnerId.value
  }
  
  emit('submit', matchData)
}

// Reset form
const resetForm = () => {
  selectedOpponentId.value = ''
  winnerId.value = null
}

// Expose reset method
defineExpose({
  resetForm
})

onMounted(() => {
  loadPlayers()
})
</script>