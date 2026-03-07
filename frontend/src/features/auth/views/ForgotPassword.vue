// src/views/ForgotPassword.vue
<script setup lang="ts">
import ForgotPasswordForm from '@/features/auth/components/ForgotPasswordForm.vue';
</script>

<template>
  <div class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold">BAB INSA</h1>
        <p class="mt-2 text-gray-600">Saisissez votre e-mail pour recevoir un lien de réinitialisation</p>
      </div>

      <ForgotPasswordForm />
    </div>
  </div>
</template>