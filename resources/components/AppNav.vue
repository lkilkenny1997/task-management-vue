<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { Button } from '@/components/ui/button'
import { useToast } from '@/components/ui/toast'
import { ListTodo, LogOut } from 'lucide-vue-next'

const router = useRouter()
const auth = useAuthStore()
const { toast } = useToast()

const logout = async () => {
  try {
    await auth.logout()
    toast({
      title: 'Success',
      description: 'You have been logged out successfully',
    })
    router.push('/login')
  } catch (error) {
    toast({
      title: 'Error',
      description: 'There was a problem logging out',
      variant: 'destructive',
    })
  }
}
</script>

<template>
  <nav class="border-b bg-background">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-16 items-center justify-between">
        <div class="flex items-center">
          <div class="flex items-center">
            <ListTodo class="h-6 w-6 text-primary" />
            <span class="ml-2 text-lg font-semibold">Task Manager</span>
          </div>

          <div class="ml-10 hidden items-center space-x-4 lg:flex">
            <router-link
              to="/tasks"
              class="rounded-md px-3 py-2 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
              :class="
                $route.path === '/tasks'
                  ? 'bg-accent text-accent-foreground'
                  : 'text-muted-foreground'
              "
            >
              Tasks
            </router-link>
          </div>
        </div>

        <div>
          <Button variant="ghost" @click="logout" class="flex items-center">
            <LogOut class="mr-2 h-4 w-4" />
            Logout
          </Button>
        </div>
      </div>
    </div>
  </nav>
</template>
