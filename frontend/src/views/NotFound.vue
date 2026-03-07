// src/views/NotFound.vue
<script setup lang="ts">
import { Button } from '@/shared/components/ui/button'
import { useRouter } from 'vue-router'

const router = useRouter()

const goBack = () => {
  router.back()
}
</script>

<template>
  <div class="flex items-center justify-center min-h-[calc(100vh-16rem)]">
    <div class="text-center max-w-2xl px-6">
      <div class="mb-8">
        <div class="flex justify-center mb-6">
          <div class="relative">
            <div class="text-[10rem] font-extrabold text-primary opacity-10 leading-none select-none">
              404
            </div>
            <div class="absolute inset-0 flex items-center justify-center">
              <span class="text-4xl md:text-5xl font-bold text-primary">
                Erreur
              </span>
            </div>
          </div>
        </div>
        <h2 class="text-2xl font-semibold mb-6">
          Oups ! Cette page n'existe pas.
        </h2>
        <p class="text-muted-foreground mb-8">
          La page que vous recherchez a peut-être été supprimée, renommée ou est temporairement indisponible.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
          <Button @click="goBack" class="cursor-pointer">
            Retour
          </Button>
          <Button asChild variant="outline">
            <RouterLink :to="{name: 'Home'}">
              Retour à l'accueil
            </RouterLink>
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>