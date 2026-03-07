<script setup lang="ts">
import LoginForm from '@/features/auth/components/LoginForm.vue';
</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold">BAB INSA</h1>
        <p class="mt-2 text-gray-600">Connectez-vous pour accéder à votre espace</p>
      </div>

      <LoginForm />
    </div>
  </div>
</template>