<script setup lang="ts">
import { onMounted } from 'vue'
import { useAuthStore } from './stores/auth'
import { useRouter } from 'vue-router'

const auth = useAuthStore()
const router = useRouter()

onMounted(async () => {
  await auth.init()
})

const logout = async () => {
  await auth.logout()
  router.push('/login')
}
</script>

<template>
  <div v-if="!auth.initializing" class="min-h-screen bg-gray-100">
    <nav v-if="auth.isAuthenticated" class="bg-white shadow">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <RouterLink :to="{ name: 'tasks' }" class="px-3 py-2 rounded-md text-sm font-medium">
              Tasks
            </RouterLink>
          </div>
          <div class="flex items-center">
            <button @click="logout" class="px-3 py-2 rounded-md text-sm font-medium text-red-600">
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>

    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <RouterView></RouterView>
    </main>
  </div>
  <div v-else class="min-h-screen flex items-center justify-center">
    <div class="text-gray-500">Loading...</div>
  </div>
</template>

