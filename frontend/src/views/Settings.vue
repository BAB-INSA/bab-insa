<!-- src/views/Settings.vue -->
<script setup lang="ts">
// Simplified imports since tabs are removed
import ThemeGrid from '@/features/theme/components/ThemeGrid.vue'

// Route no longer needed

// Active tab management
// Removed language tab, only appearance remains

// Language tab removed, no tab switching needed

// Language tab removed
</script>

<template>
  <div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">Paramètres</h1>
        <p class="text-muted-foreground">Personnalisez votre expérience et vos préférences</p>
      </div>

      <!-- Single section, no tabs needed -->

      <!-- Contenu -->
      <div class="space-y-8">
        <!-- Appearance Settings -->
        <section class="bg-card rounded-lg border border-border p-6">
          <ThemeGrid />
        </section>
      </div>
    </div>
  </div>
</template>