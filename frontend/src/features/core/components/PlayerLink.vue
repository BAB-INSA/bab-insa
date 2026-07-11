<!-- src/shared/components/PlayerLink.vue -->
<template>
  <router-link
    v-if="props.player"
    :to="`/player/${props.player.id}/${props.player.slug || props.player.username}`"
    :class="[
      'text-primary hover:text-primary/80 hover:underline transition-colors cursor-pointer font-medium',
      props.class
    ]"
  >
    {{ displayText }}
  </router-link>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { PlayerMin } from '@/features/core/types/player'

interface Props {
  player?: PlayerMin | null
  showId?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  showId: false,
  class: ''
})

const displayText = computed(() => {
  if (!props.player) {
    return ''
  }
  if (props.showId) {
    return `${props.player.username} (#${props.player.id})`
  }
  return props.player.username
})
</script>