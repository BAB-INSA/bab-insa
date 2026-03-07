<template>
  <div class="space-y-4">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-4">
        <Input
          v-model="searchQuery"
          placeholder="Rechercher un utilisateur..."
          class="w-64"
        />
      </div>
      <Button @click="loadUsers">
        Actualiser
      </Button>
    </div>

    <div class="border rounded-lg">
      <div class="relative overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="text-xs uppercase bg-gray-50 dark:bg-gray-700">
            <tr>
              <th class="px-6 py-3">Utilisateur</th>
              <th class="px-6 py-3">Email</th>
              <th class="px-6 py-3">Rôles</th>
              <th class="px-6 py-3">Statut</th>
              <th class="px-6 py-3">Dernière connexion</th>
              <th class="px-6 py-3">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="loading" class="border-b">
              <td colspan="6" class="px-6 py-4 text-center">
                <Spinner />
              </td>
            </tr>
            <tr
              v-else-if="!users.length"
              class="border-b"
            >
              <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                Aucun utilisateur trouvé
              </td>
            </tr>
            <tr
              v-else
              v-for="user in users"
              :key="user.id"
              class="border-b hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-6 py-4">
                <div class="flex items-center space-x-3">
                  <UserAvatar :user="user" :size="'sm'" />
                  <div>
                    <div class="font-medium">{{ user.username }}</div>
                    <div class="text-gray-500 text-xs">ID: {{ user.id }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">{{ user.email }}</td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <Badge
                    v-for="role in user.roles"
                    :key="role"
                    :variant="getRoleVariant(role)"
                  >
                    {{ formatRole(role) }}
                  </Badge>
                </div>
              </td>
              <td class="px-6 py-4">
                <Badge 
                  :variant="user.enabled ? 'default' : 'destructive'"
                  :class="user.enabled ? 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900 dark:text-green-300 dark:border-green-700' : ''"
                >
                  {{ user.enabled ? 'Actif' : 'Inactif' }}
                </Badge>
              </td>
              <td class="px-6 py-4">
                {{ formatDate(user.last_login) }}
              </td>
              <td class="px-6 py-4">
                <div class="flex space-x-2">
                  <Button
                    variant="outline"
                    size="sm"
                    @click="editUser(user)"
                  >
                    Modifier
                  </Button>
                  <Button
                    v-if="permissions.canDeleteUsers && user.id !== currentUserId"
                    variant="destructive"
                    size="sm"
                    @click="confirmDeleteUser(user)"
                  >
                    Supprimer
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="totalPages > 1" class="flex justify-center">
      <Pagination
        :page="currentPage"
        :items-per-page="pageSize"
        :total="totalPages * pageSize"
        @update:page="goToPage"
      />
    </div>

    <UserEditModal
      v-if="selectedUser"
      :user="selectedUser"
      :show="showEditModal"
      @close="closeEditModal"
      @saved="onUserSaved"
    />

    <Dialog :open="showDeleteConfirm" @update:open="showDeleteConfirm = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Confirmer la suppression</DialogTitle>
          <DialogDescription>
            Êtes-vous sûr de vouloir supprimer l'utilisateur <strong>{{ userToDelete?.username }}</strong> ?
            Cette action est irréversible.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="showDeleteConfirm = false">
            Annuler
          </Button>
          <Button variant="destructive" @click="deleteUser" :disabled="deleting">
            {{ deleting ? 'Suppression...' : 'Supprimer' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useAdmin } from '@/features/admin/composables/useAdmin'
import { useAuthStore } from '@/features/auth/stores/auth'
import adminUsersService from '@/features/admin/services/admin-users.service'
import toastService from '@/shared/services/toast.service'
import type { User } from '@/features/auth/types/auth'
import type { UserFilters } from '@/features/admin/types/admin'

import { Button } from '@/shared/components/ui/button'
import { Input } from '@/shared/components/ui/input'
import { Badge } from '@/shared/components/ui/badge'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/shared/components/ui/dialog'
import { Pagination } from '@/shared/components/ui/pagination'
import Spinner from '@/shared/components/ui/Spinner.vue'
import UserAvatar from '@/features/auth/components/UserAvatar.vue'
import UserEditModal from './UserEditModal.vue'

const { permissions } = useAdmin()
const authStore = useAuthStore()

const users = ref<User[]>([])
const loading = ref(false)
const deleting = ref(false)
const searchQuery = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const pageSize = 20

const selectedUser = ref<User | null>(null)
const showEditModal = ref(false)
const userToDelete = ref<User | null>(null)
const showDeleteConfirm = ref(false)

const currentUserId = computed(() => authStore.user?.id)

const filters = computed<UserFilters>(() => ({
  search: searchQuery.value || undefined
}))

const loadUsers = async () => {
  loading.value = true
  try {
    const response = await adminUsersService.getUsers(filters.value, currentPage.value, pageSize)
    users.value = response.data
    totalPages.value = response.totalPages
  } catch (error) {
    toastService.error('Erreur', 'Impossible de charger les utilisateurs')
    console.error('Failed to load users:', error)
  } finally {
    loading.value = false
  }
}

const editUser = (user: User) => {
  selectedUser.value = user
  showEditModal.value = true
}

const closeEditModal = () => {
  selectedUser.value = null
  showEditModal.value = false
}

const onUserSaved = () => {
  closeEditModal()
  loadUsers()
}

const confirmDeleteUser = (user: User) => {
  userToDelete.value = user
  showDeleteConfirm.value = true
}

const deleteUser = async () => {
  if (!userToDelete.value) return
  
  deleting.value = true
  try {
    await adminUsersService.deleteUser(userToDelete.value.id)
    toastService.success('Succès', 'Utilisateur supprimé avec succès')
    showDeleteConfirm.value = false
    userToDelete.value = null
    loadUsers()
  } catch (error) {
    toastService.error('Erreur', 'Impossible de supprimer l\'utilisateur')
    console.error('Failed to delete user:', error)
  } finally {
    deleting.value = false
  }
}

const goToPage = (page: number) => {
  currentPage.value = page
  loadUsers()
}

const getRoleVariant = (role: string) => {
  switch (role) {
    case 'admin':
      return 'destructive'
    case 'user':
      return 'outline'
    default:
      return 'secondary'
  }
}

const formatRole = (role: string) => {
  return role.charAt(0).toUpperCase() + role.slice(1)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

watch(searchQuery, () => {
  currentPage.value = 1
  loadUsers()
})

onMounted(() => {
  loadUsers()
})
</script>