// src/components/auth/ForgotPasswordForm.vue
<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { Input } from '@/shared/components/ui/input'
import { Button } from '@/shared/components/ui/button'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle
} from '@/shared/components/ui/card'
import { Mail, AlertCircle, ArrowLeft } from 'lucide-vue-next'
import Spinner from '@/shared/components/ui/Spinner.vue'
import forgotPasswordService from '@/features/auth/services/forgot-password.service.ts'
import toastService from '@/shared/services/toast.service.ts'
import type {ApiError} from "@/core/types";

/**
 * ForgotPasswordForm component using Shadcn Card UI
 * Handles password reset link request
 */

const email = ref('')
const loading = ref(false)
const error = ref<string | null>(null)
const success = ref(false)
const router = useRouter()

/**
 * Validate email format
 */
const isEmailValid = computed(() => {
  if (!email.value) return true
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email.value)
})

/**
 * Check if form is valid
 */
const isFormValid = computed(() => {
  return email.value && isEmailValid.value
})

/**
 * Handle forgot password form submission
 */
const handleSubmit = async () => {
  if (!isFormValid.value) {
    error.value = 'Veuillez saisir votre adresse e-mail'
    return
  }

  loading.value = true
  error.value = null

  try {
    await forgotPasswordService.sendPasswordResetLink({ email: email.value, callBackUrl: '/reset-password?token=[token]' })

    success.value = true

    // Show success notification
    toastService.success(
        'Lien envoyé',
        'Vérifiez votre e-mail pour les instructions de réinitialisation de mot de passe'
    )

    // Redirect to login after 3 seconds
    setTimeout(() => {
      router.push('/login')
    }, 3000)

  } catch (err: unknown) {
    const apiError = err as ApiError;
    error.value = apiError.response?.data?.message || 'Impossible d\'envoyer le lien de réinitialisation. Veuillez réessayer.'

    // Show error notification
    toastService.error(
        'Erreur',
        error.value
    )
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <Card class="w-full max-w-md mx-auto">
    <CardHeader>
      <CardTitle class="text-2xl font-bold text-center">Mot de passe oublié</CardTitle>
      <CardDescription class="text-center">
        Saisissez votre e-mail pour recevoir un lien de réinitialisation
      </CardDescription>
    </CardHeader>

    <CardContent>
      <form v-if="!success" @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Error message -->
        <div v-if="error" class="p-3 bg-red-50 text-red-700 rounded-md flex items-center gap-2">
          <AlertCircle class="h-5 w-5" />
          <span>{{ error }}</span>
        </div>

        <!-- Email field -->
        <div class="space-y-2">
          <label for="email" class="text-sm font-medium">Adresse e-mail</label>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
              <Mail class="h-5 w-5" />
            </div>
            <Input
                id="email"
                type="email"
                v-model="email"
                placeholder="Saisissez votre adresse e-mail"
                class="pl-10"
                :disabled="loading"
                :class="{ 'border-red-500': email && !isEmailValid }"
                required
            />
          </div>
          <p v-if="email && !isEmailValid" class="text-sm text-red-500">
            Veuillez saisir une adresse e-mail valide
          </p>
        </div>

        <!-- Submit button -->
        <Button
            type="submit"
            class="w-full"
            :disabled="loading || !isFormValid"
        >
          <template v-if="loading">
            <Spinner color="text-white" size="md" :mr="true" />
            Envoi en cours...
          </template>
          <template v-else>
            Envoyer le lien
          </template>
        </Button>
      </form>

      <!-- Success message -->
      <div v-else class="text-center py-4">
        <div class="mb-4">
          <div class="h-12 w-12 rounded-full bg-green-100 mx-auto flex items-center justify-center">
            <Mail class="h-6 w-6 text-green-600" />
          </div>
        </div>
        <h3 class="text-lg font-medium mb-2">Lien envoyé</h3>
        <p class="text-sm text-muted-foreground">Vérifiez votre e-mail pour les instructions de réinitialisation de mot de passe</p>
      </div>
    </CardContent>

    <CardFooter class="flex justify-center">
      <RouterLink :to="{name: 'Login'}" class="text-primary hover:underline text-sm flex items-center gap-2">
        <ArrowLeft class="h-4 w-4" />
        Retour à la connexion
      </RouterLink>
    </CardFooter>
  </Card>
</template>