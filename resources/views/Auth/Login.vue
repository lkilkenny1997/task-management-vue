<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useToast } from '@/components/ui/toast'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Toaster } from '@/components/ui/toast'
import { Loader2Icon } from 'lucide-vue-next'

const router = useRouter()
const auth = useAuthStore()
const { toast } = useToast()
const loading = ref(false)
const errors = reactive({
  email: '',
  password: ''
})

const form = reactive({
  email: '',
  password: ''
})

const handleSubmit = async () => {
  try {
    loading.value = true
    errors.email = ''
    errors.password = ''
    
    await auth.login(form)
    
    toast({
      title: "Success!",
      description: "You have successfully logged in.",
    })
    
    router.push({ name: 'tasks' })
  } catch (e: any) {
    if (e.response?.data?.errors) {
      const serverErrors = e.response.data.errors
      errors.email = serverErrors.email?.[0] || ''
      errors.password = serverErrors.password?.[0] || ''
    } else {
      toast({
        title: "Error",
        description: e.response?.data?.message || "An error occurred while logging in.",
        variant: "destructive",
      })
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="container flex min-h-screen items-center justify-center">
    <Card class="w-full max-w-sm">
      <CardHeader>
        <CardTitle>Welcome back!</CardTitle>
        <CardDescription>Enter your credentials to access your account</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              placeholder="name@example.com"
              :disabled="loading"
              required
            />
            <p v-if="errors.email" class="text-sm text-destructive">{{ errors.email }}</p>
          </div>
          
          <div class="space-y-2">
            <Label for="password">Password</Label>
            <Input
              id="password"
              v-model="form.password"
              type="password"
              :disabled="loading"
              required
            />
            <p v-if="errors.password" class="text-sm text-destructive">{{ errors.password }}</p>
          </div>

          <Button type="submit" class="w-full" :disabled="loading">
            <Loader2Icon v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
            Sign in
          </Button>
        </form>
      </CardContent>
      <CardFooter class="flex flex-col space-y-4">
        <div class="text-sm text-center text-muted-foreground">
          Don't have an account?
          <router-link
            :to="{ name: 'register' }"
            class="ml-1 text-primary hover:underline"
          >
            Sign up
          </router-link>
        </div>
      </CardFooter>
    </Card>
    <Toaster />
  </div>
</template>