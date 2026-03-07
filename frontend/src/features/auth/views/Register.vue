<script setup lang="ts">
import RegisterForm from '@/features/auth/components/RegisterForm.vue';
</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold">BAB INSA</h1>
        <p class="mt-2 text-gray-600">Rejoignez notre communauté dès maintenant</p>
      </div>

      <RegisterForm />
    </div>
  </div>
</template>