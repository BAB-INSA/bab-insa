<template>
  <div class="container mx-auto py-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold">Gestion des Utilisateurs</h1>
        <p class="text-muted-foreground">Gérez tous les comptes utilisateurs</p>
      </div>
    </div>

    <Card>
      <CardContent class="pt-6">
        <UserTable />
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { Card, CardContent } from '@/shared/components/ui/card'
import UserTable from '@/features/admin/components/user/UserTable.vue'
</script>