// src/components/profile/ProfileInfoTab.vue
<template>
  <div>
    <!-- Error display -->
    <div v-if="error" class="p-3 bg-red-50 text-red-700 rounded-md flex items-center gap-2 mb-6">
      <AlertCircle class="h-5 w-5 flex-shrink-0" />
      <p>{{ error }}</p>
    </div>

    <!-- Loading state -->
    <div v-if="loading && !formValues.username" class="flex justify-center py-8">
      <Spinner size="lg" color="text-primary" />
    </div>

    <!-- Form -->
    <form v-else @submit="onSubmit" class="space-y-6">
      <!-- Username field -->
      <div class="space-y-2">
        <label for="username" class="text-sm font-medium">Nom d'utilisateur</label>
        <Input
            id="username"
            name="username"
            v-model="formValues.username"
            placeholder="Choisissez votre nom d'utilisateur"
        />
        <p class="text-sm text-muted-foreground">
          Votre nom d'utilisateur public
        </p>
      </div>

      <!-- Email field -->
      <div class="space-y-2">
        <label for="email" class="text-sm font-medium">Adresse e-mail</label>
        <Input
            id="email"
            name="email"
            type="email"
            v-model="formValues.email"
            placeholder="votre@email.com"
        />
        <p class="text-sm text-muted-foreground">
          Votre adresse e-mail pour les notifications
        </p>
      </div>


      <!-- Submit button -->
      <Button
          type="submit"
          :disabled="loading"
          class="w-full sm:w-auto"
      >
        <template v-if="loading">
          <Spinner color="text-white" size="sm" :mr="true" />
          Enregistrement...
        </template>
        <template v-else>
          <Save class="mr-2 h-4 w-4" />
          Enregistrer
        </template>
      </Button>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/features/auth/stores/auth.ts';
import { Button } from '@/shared/components/ui/button';
import { Input } from '@/shared/components/ui/input';
import { z } from 'zod';
import { AlertCircle, Save } from 'lucide-vue-next';
import authService from '@/features/auth/services/auth.service.ts';
import toastService from '@/shared/services/toast.service.ts';
import Spinner from '@/shared/components/ui/Spinner.vue';
import type { ApiError } from '@/core/types';

// Initialize stores and i18n
const authStore = useAuthStore();
// Language is now fixed to French

// Emit events with safeguard
const emit = defineEmits<{
  'profile-updated': []
}>();

// Form state
const loading = ref(false);
const error = ref<string | null>(null);

// Local reactive state for form values
const formValues = ref({
  username: '',
  email: ''
});

// Create form validation schema
const validationSchema = z.object({
  username: z.string()
      .min(3, 'Le nom d\'utilisateur doit contenir au moins 3 caractères')
      .max(20, 'Le nom d\'utilisateur doit contenir au maximum 20 caractères')
      .regex(/^[a-zA-Z0-9_]+$/, 'Le nom d\'utilisateur ne peut contenir que des lettres, chiffres et tirets bas'),
  email: z.string()
      .email('Veuillez saisir une adresse e-mail valide')
});

// Language feature removed

/**
 * Load user data from /users/me API
 */
const loadUserData = async () => {
  loading.value = true;
  try {
    const userData = await authService.getCurrentUser();

    // Update our local form values
    formValues.value = {
      username: userData.username || '',
      email: userData.email || ''
    };

    // Update auth store with fresh user data
    authStore.user = userData;

    console.log('User data loaded from /users/me:', userData);
  } catch (err) {
    console.error('Failed to load user data:', err);
    error.value = 'Échec du chargement des données utilisateur';
  } finally {
    loading.value = false;
  }
};

/**
 * Handle form submission
 */
const onSubmit = async (event: Event) => {
  event.preventDefault();

  // Validate the form manually with Zod
  try {
    // Parse will throw an error if validation fails
    validationSchema.parse(formValues.value);

    loading.value = true;
    error.value = null;

    try {
      if (!authStore.user?.id) {
        error.value = 'Utilisateur non trouvé';
        return;
      }

      const updatedUser = await authService.updateUser(authStore.user.id, {
        username: formValues.value.username,
        email: formValues.value.email
      });

      // Update auth store with fresh data from API
      authStore.user = updatedUser;

      // Show success notification
      toastService.success('Profil mis à jour', 'Vos informations ont été mises à jour avec succès');

      // Emit event to parent
      emit('profile-updated');
    } catch (err: unknown) {
      const apiError = err as ApiError;
      error.value = apiError.response?.data?.message || 'Échec de la mise à jour du profil';
      toastService.error('Erreur', error.value);
    } finally {
      loading.value = false;
    }
  } catch (validationError) {
    // Handle Zod validation errors
    if (validationError instanceof z.ZodError) {
      error.value = validationError.errors[0]?.message || 'Erreur de validation';
    } else {
      error.value = 'Erreur de validation';
    }
  }
};

// Load user data on component mount
onMounted(() => {
  loadUserData();
});
</script>


