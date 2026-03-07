<template>
  <router-link
    :to="`/team/${props.team.id}/${props.team.slug}`"
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

interface Props {
  team: { id: number; name: string; slug: string; player1?: { username: string }; player2?: { username: string } }
  showId?: boolean
  class?: string
}

const props = withDefaults(defineProps<Props>(), {
  showId: false,
  class: ''
})

const displayText = computed(() => {
  const teamName = props.team.name || `${props.team.player1?.username || 'Joueur 1'} & ${props.team.player2?.username || 'Joueur 2'}`
  
  if (props.showId) {
    return `${teamName} (#${props.team.id})`
  }
  return teamName
})
</script>