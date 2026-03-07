<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useAuthStore } from '@/features/auth/stores/auth.ts'
import { Button } from '@/shared/components/ui/button'
import { RefreshCw } from 'lucide-vue-next'

const authStore = useAuthStore()
const refreshing = ref(false)
const timeRemaining = ref('')
const intervalId = ref<number | null>(null)

// Props
interface Props {
  showRefreshButton?: boolean
}
const props = withDefaults(defineProps<Props>(), {
  showRefreshButton: true
})

// Calculate token status (color, text)
const tokenStatus = computed(() => {
  if (!authStore.token) return { color: 'bg-gray-200', text: 'Non connecté' }
  if (authStore.isTokenExpired) return { color: 'bg-red-500', text: 'Expiré' }

  if (!authStore.tokenExpiration) return { color: 'bg-gray-200', text: 'Inconnu' }

  const now = Math.floor(Date.now() / 1000)
  const diff = authStore.tokenExpiration - now

  if (diff < 60) return { color: 'bg-red-500', text: 'Expire bientôt' }
  if (diff < 300) return { color: 'bg-yellow-500', text: 'Expire bientôt' }
  return { color: 'bg-green-500', text: 'Valide' }
})

// Update remaining time for token expiration
const updateTimeRemaining = () => {
  if (!authStore.tokenExpiration) {
    timeRemaining.value = 'Non disponible'
    return
  }

  const now = Math.floor(Date.now() / 1000)
  const diff = authStore.tokenExpiration - now

  if (diff <= 0) {
    timeRemaining.value = 'Expiré'
    return
  }

  const minutes = Math.floor(diff / 60)
  const seconds = diff % 60
  timeRemaining.value = `${minutes}:${seconds.toString().padStart(2, '0')}`
}

// Function to manually refresh the token
const handleRefreshToken = async () => {
  refreshing.value = true
  try {
    await authStore.refreshAuthToken()
  } finally {
    refreshing.value = false
  }
}

// Component initialization
onMounted(() => {
  updateTimeRemaining()
  intervalId.value = window.setInterval(updateTimeRemaining, 1000)
})

// Cleanup on component destruction
onUnmounted(() => {
  if (intervalId.value) {
    clearInterval(intervalId.value)
  }
})
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center gap-2">
    <div class="flex items-center gap-2">
      <span class="h-3 w-3 rounded-full" :class="tokenStatus.color"></span>
      <span class="text-sm whitespace-nowrap">{{ tokenStatus.text }}</span>
      <span class="text-xs text-muted-foreground whitespace-nowrap">{{ timeRemaining }}</span>
    </div>

    <Button
        v-if="props.showRefreshButton && authStore.refreshToken"
        size="sm"
        variant="outline"
        @click="handleRefreshToken"
        :disabled="refreshing"
        class="h-8 min-w-[140px]"
    >
      <RefreshCw :class="{'animate-spin': refreshing}" class="h-3 w-3 mr-2" />
      {{ refreshing ? 'Rafraîchissement...' : 'Rafraîchir le token' }}
    </Button>
  </div>
</template>