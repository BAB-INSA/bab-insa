<template>
  <div class="container mx-auto py-6 space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold">Gestion des Matchs 2v2</h1>
        <p class="text-muted-foreground">Gérez tous les matchs d'équipes de la plateforme</p>
      </div>
    </div>

    <Card>
      <CardContent class="pt-6">
        <TeamMatchTable />
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { Card, CardContent } from '@/shared/components/ui/card'
import TeamMatchTable from '@/features/admin/components/team-match/TeamMatchTable.vue'
</script>