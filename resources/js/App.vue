<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from '@/js/stores/auth'
import AppNav from '@/components/AppNav.vue'
import { Toaster } from '@/components/ui/toast'

const auth = useAuthStore()

onMounted(async () => {
  await auth.init()
})
</script>

<template>
  <div v-if="!auth.initializing" class="min-h-screen bg-background">
    <AppNav v-if="auth.isAuthenticated" />
    
    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
      <RouterView />
    </main>
    
    <Toaster />
  </div>
  <div v-else class="min-h-screen flex items-center justify-center">
    <div class="text-muted-foreground">Loading...</div>
  </div>
</template>