<template>
  <router-link
    :to="`/tournament/${props.tournament.id}/${props.tournament.slug}`"
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
import type { Tournament } from '@/features/core/types/tournament'

interface Props {
  tournament: Tournament
  showId?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  showId: false,
  class: ''
})

const displayText = computed(() => {
  if (props.showId) {
    return `${props.tournament.name} (#${props.tournament.id})`
  }
  return props.tournament.name
})
</script>
