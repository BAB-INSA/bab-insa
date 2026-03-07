<template>
  <Dialog :open="show" @update:open="$emit('close')">
    <DialogContent class="max-w-md">
      <DialogHeader>
        <DialogTitle>Modifier l'utilisateur</DialogTitle>
        <DialogDescription>
          Modifiez les informations de {{ user.username }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="save" class="space-y-4">
        <div class="space-y-2">
          <Label>Email</Label>
          <Input
            v-model="formData.email"
            type="email"
            placeholder="email@exemple.com"
          />
        </div>

        <div class="space-y-2">
          <Label>Statut</Label>
          <Select v-model="formData.enabled">
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="true">Actif</SelectItem>
              <SelectItem value="false">Inactif</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <div class="space-y-2">
          <Label>Rôles</Label>
          <div class="space-y-2">
            <div v-for="role in availableRoles" :key="role" class="flex items-center space-x-2">
              <input
                :id="role"
                v-model="formData.roles"
                :value="role"
                type="checkbox"
                class="rounded border-gray-300"
                :disabled="role === 'user' && formData.roles.length === 1 && formData.roles.includes('user')"
              />
              <Label :for="role">{{ formatRole(role) }}</Label>
            </div>
          </div>
          <p class="text-xs text-muted-foreground">
            Un utilisateur doit avoir au moins le rôle "User"
          </p>
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
import adminUsersService from '@/features/admin/services/admin-users.service'
import toastService from '@/shared/services/toast.service'
import type { User } from '@/features/auth/types/auth'

import { Button } from '@/shared/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import { Input } from '@/shared/components/ui/input'
import { Label } from '@/shared/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/shared/components/ui/select'

interface Props {
  user: User
  show: boolean
}

interface Emits {
  close: []
  saved: []
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const saving = ref(false)

const availableRoles = [
  'user',
  'admin'
]

const formData = ref({
  email: props.user.email,
  enabled: props.user.enabled.toString(),
  roles: props.user.roles.length > 0 ? [...props.user.roles] : ['user']
})

const save = async () => {
  saving.value = true
  try {
    // S'assurer qu'il y a au moins le rôle "user"
    const roles = formData.value.roles.length > 0 ? formData.value.roles : ['user']
    if (!roles.includes('user')) {
      roles.push('user')
    }
    
    // Faire un seul PATCH avec tous les changements
    await adminUsersService.updateUser(props.user.id, {
      email: formData.value.email,
      enabled: formData.value.enabled === 'true',
      roles: roles
    })
    
    toastService.success('Succès', 'Utilisateur mis à jour avec succès')
    emit('saved')
  } catch (error) {
    toastService.error('Erreur', 'Impossible de mettre à jour l\'utilisateur')
    console.error('Failed to update user:', error)
  } finally {
    saving.value = false
  }
}

const formatRole = (role: string) => {
  return role.charAt(0).toUpperCase() + role.slice(1)
}

watch(() => props.user, (newUser) => {
  formData.value = {
    email: newUser.email,
    enabled: newUser.enabled.toString(),
    roles: newUser.roles.length > 0 ? [...newUser.roles] : ['user']
  }
}, { immediate: true })
</script>