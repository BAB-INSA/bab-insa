<template>
  <Dialog :open="show" @update:open="$emit('close')">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>Modifier le match #{{ match.id }}</DialogTitle>
        <DialogDescription>
          Modifiez les informations du match
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="save" class="space-y-4">
        <div class="space-y-2">
          <Label>Gagnant</Label>
          <Select v-model="formData.winner_id">
            <SelectTrigger>
              <SelectValue placeholder="Sélectionner le gagnant" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem :value="match.player1.id.toString()">
                {{ match.player1.username }}
              </SelectItem>
              <SelectItem :value="match.player2.id.toString()">
                {{ match.player2.username }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <DialogFooter>
          <Button type="button" variant="outline" @click="$emit('close')">
            Annuler
          </Button>
          <Button type="submit" :disabled="saving">
            {{ saving ? 'Sauvegarde...' : 'Sauvegarder' }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue'
import adminMatchesService from '@/features/admin/services/admin-matches.service'
import toastService from '@/shared/services/toast.service'
import type { Match } from '@/features/admin/types/match'

import { Button } from '@/shared/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import { Label } from '@/shared/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'

interface Props {
  match: Match
  show: boolean
}

interface Emits {
  close: []
  saved: []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const saving = ref(false)

const formData = ref({
  winner_id: props.match.winner?.id?.toString() || ''
})

const save = async () => {
  saving.value = true
  try {
    const winnerId = parseInt(formData.value.winner_id)
    const updateData: Partial<Match> = {
      winner_id: winnerId
    }

    await adminMatchesService.updateMatch(props.match.id, updateData)
    
    toastService.success('Succès', 'Match mis à jour avec succès')
    emit('saved')
  } catch (error) {
    toastService.error('Erreur', 'Impossible de mettre à jour le match')
    console.error('Failed to update match:', error)
  } finally {
    saving.value = false
  }
}

watch(() => props.match, (newMatch) => {
  formData.value = {
    winner_id: newMatch.winner?.id?.toString() || ''
  }
}, { immediate: true })

</script>