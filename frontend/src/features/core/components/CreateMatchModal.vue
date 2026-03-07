<!-- src/features/core/components/CreateMatchModal.vue -->
<template>
  <Dialog v-model:open="isOpen">
    <DialogContent class="sm:max-w-md">
      <DialogHeader>
        <DialogTitle class="flex items-center gap-2">
          <Trophy class="w-5 h-5" />
          Nouveau Match
        </DialogTitle>
        <DialogDescription>
          Enregistrez le résultat d'un match que vous venez de jouer.
        </DialogDescription>
      </DialogHeader>

      <div class="mt-6">
        <CreateMatchForm
          ref="formRef"
          :is-submitting="isSubmitting"
          @submit="handleSubmit"
          @cancel="handleCancel"
        />
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import { Trophy } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import CreateMatchForm from './CreateMatchForm.vue'
import MatchService from '@/features/core/services/match.service'
import type { MatchCreateData } from '@/features/core/services/match.service'
import type { Match } from '@/features/core/types/match'

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
  'match-created': [match: Match]
}>()

// State
const isOpen = ref(props.open)
const isSubmitting = ref(false)
const formRef = ref<InstanceType<typeof CreateMatchForm> | null>(null)

// Watchers
watch(() => props.open, (newValue) => {
  isOpen.value = newValue
})

watch(isOpen, (newValue) => {
  emit('update:open', newValue)
  
  // Reset form when closing
  if (!newValue && formRef.value) {
    formRef.value.resetForm()
  }
})

// Methods
const handleSubmit = async (matchData: MatchCreateData) => {
  try {
    isSubmitting.value = true
    
    // Créer le match
    const createdMatch = await MatchService.createMatch(matchData)
    
    // Notification de succès
    toast.success('Match créé avec succès !', {
      description: 'En attente de validation par votre adversaire.'
    })
    
    // Émettre l'événement
    emit('match-created', createdMatch)
    
    // Fermer la modal
    isOpen.value = false
    
  } catch (error) {
    console.error('Error creating match:', error)
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