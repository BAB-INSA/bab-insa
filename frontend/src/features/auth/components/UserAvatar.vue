// src/components/user/UserAvatar.vue
<script setup lang="ts">
import { computed, ref } from 'vue';
import { useAuthStore } from '@/features/auth/stores/auth.ts';
import { User } from 'lucide-vue-next';
import { Avatar, AvatarImage, AvatarFallback } from '@/shared/components/ui/avatar';
import { cn } from '@/core/lib/utils.ts';
import gravatarUrl from 'gravatar-url';

// Props for customization
const props = withDefaults(defineProps<{
  size?: 'sm' | 'md' | 'lg' | 'xl';
  bordered?: boolean;
  email?: string; // Optional email (defaults to current user email)
  showFallback?: boolean; // Show initials fallback when no image
  class?: string;
}>(), {
  size: 'md',
  bordered: false,
  showFallback: true
});

// Get auth store for current user
const authStore = useAuthStore();

// State management
const loaded = ref(false);
const error = ref(false);

// Get user email (from props or current user)
const userEmail = computed(() => {
  return props.email || authStore.user?.email || '';
});

// User's initials for the fallback
const userInitials = computed(() => {
  return authStore.user?.username
      ? authStore.user.username.substring(0, 2).toUpperCase()
      : '?';
});

// Get Gravatar URL using gravatar-url library
const avatarUrl = computed(() => {
  if (!userEmail.value) return '';

  const size = getSizePixels(props.size);
  
  // Use gravatar-url library for proper MD5 hashing and URL generation
  return gravatarUrl(userEmail.value, {
    size,
    default: 'identicon'
  });
});

// Convert size prop to pixels for Gravatar
const getSizePixels = (size: string): number => {
  const sizeMap = {
    sm: 32,
    md: 40,
    lg: 48,
    xl: 64
  };
  return sizeMap[size as keyof typeof sizeMap] || 40;
};

// Size classes
const sizeClasses = {
  sm: 'h-8 w-8 text-xs',
  md: 'h-10 w-10 text-sm',
  lg: 'h-12 w-12 text-base',
  xl: 'h-16 w-16 text-lg'
};

// Container classes
const containerClass = computed(() => {
  return cn(
      sizeClasses[props.size],
      props.bordered ? 'ring-2 ring-background' : '',
      props.class
  );
});

// Handle image load
const handleLoad = () => {
  loaded.value = true;
  error.value = false;
};

// Handle image error
const handleError = () => {
  loaded.value = false;
  error.value = true;
};

</script>

<template>
  <Avatar
      :class="containerClass"
      data-testid="user-avatar"
  >
    <!-- Gravatar avatar image -->
    <AvatarImage
        v-if="userEmail && avatarUrl"
        :src="avatarUrl"
        alt="User Avatar"
        @load="handleLoad"
        @error="handleError"
    />

    <!-- Fallback with user initials or icon -->
    <AvatarFallback
        v-if="!loaded || error || !userEmail"
        class="bg-muted"
    >
      <template v-if="showFallback && userInitials && !error">
        {{ userInitials }}
      </template>
      <User v-else class="h-4 w-4" />
    </AvatarFallback>
  </Avatar>
</template>