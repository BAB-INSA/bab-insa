<template>
  <Dialog :open="show" @update:open="$emit('close')">
    <DialogContent class="max-w-lg">
      <DialogHeader>
        <DialogTitle>{{ tournament ? `Modifier le tournoi #${tournament.id}` : 'Créer un tournoi' }}</DialogTitle>
        <DialogDescription>
          {{ tournament ? 'Modifiez les informations du tournoi' : 'Remplissez les informations pour créer un tournoi' }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="save" class="space-y-4">
        <div class="space-y-2">
          <Label>Nom</Label>
          <Input v-model="formData.name" placeholder="Nom du tournoi" />
        </div>

        <div class="space-y-2">
          <Label>Type</Label>
          <Select v-model="formData.type" :disabled="!!tournament">
            <SelectTrigger>
              <SelectValue placeholder="Sélectionner le type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="solo">Solo</SelectItem>
              <SelectItem value="team">Équipe</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label>Statut</Label>
          <Select v-model="formData.status">
            <SelectTrigger>
              <SelectValue placeholder="Sélectionner le statut" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="opened">Ouvert</SelectItem>
              <SelectItem value="ongoing">En cours</SelectItem>
              <SelectItem value="finished">Terminé</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label>Description</Label>
          <textarea
            v-model="formData.description"
            placeholder="Description du tournoi"
            rows="3"
            class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
          />
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
import adminTournamentsService from '@/features/admin/services/admin-tournaments.service'
import toastService from '@/shared/services/toast.service'
import type { Tournament, TournamentFormData } from '@/features/admin/types/tournament'

import { Button } from '@/shared/components/ui/button'
import { Input } from '@/shared/components/ui/input'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import { Label } from '@/shared/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'

interface Props {
  tournament: Tournament | null
  show: boolean
}

interface Emits {
  close: []
  saved: []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const saving = ref(false)

const getDefaultFormData = (): TournamentFormData => ({
  name: '',
  type: 'solo',
  status: 'opened',
  description: ''
})

const formData = ref<TournamentFormData>(getDefaultFormData())

const save = async () => {
  if (!formData.value.name.trim()) {
    toastService.error('Erreur', 'Le nom du tournoi est requis')
    return
  }

  saving.value = true
  try {
    if (props.tournament) {
      const { type: _, ...updateData } = formData.value
      await adminTournamentsService.updateTournament(props.tournament.id, updateData)
      toastService.success('Succès', 'Tournoi mis à jour avec succès')
    } else {
      await adminTournamentsService.createTournament(formData.value)
      toastService.success('Succès', 'Tournoi créé avec succès')
    }
    emit('saved')
  } catch (error) {
    toastService.error('Erreur', props.tournament ? 'Impossible de mettre à jour le tournoi' : 'Impossible de créer le tournoi')
    console.error('Failed to save tournament:', error)
  } finally {
    saving.value = false
  }
}

watch(() => props.tournament, (newTournament) => {
  if (newTournament) {
    formData.value = {
      name: newTournament.name,
      type: newTournament.type,
      status: newTournament.status,
      description: newTournament.description ?? ''
    }
  } else {
    formData.value = getDefaultFormData()
  }
}, { immediate: true })

watch(() => props.show, (newShow) => {
  if (newShow && !props.tournament) {
    formData.value = getDefaultFormData()
  }
})
</script>
