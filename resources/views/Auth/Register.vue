<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/js/stores/auth'
import { useToast } from '@/components/ui/toast'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Card,
  CardContent,
  CardDescription,
  CardFooter,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import { Toaster } from '@/components/ui/toast'
import { Loader2Icon } from 'lucide-vue-next'

const router = useRouter()
const auth = useAuthStore()
const { toast } = useToast()
const loading = ref(false)
const errors = reactive({
  name: '',
  email: '',
  password: '',
})

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const handleSubmit = async () => {
  try {
    loading.value = true
    errors.name = ''
    errors.email = ''
    errors.password = ''

    await auth.register(form)

    toast({
      title: 'Success!',
      description: 'Your account has been created successfully.',
    })

    router.push({ name: 'tasks' })
  } catch (e: any) {
    if (e.response?.data?.errors) {
      const serverErrors = e.response.data.errors
      errors.name = serverErrors.name?.[0] || ''
      errors.email = serverErrors.email?.[0] || ''
      errors.password = serverErrors.password?.[0] || ''
    } else {
      toast({
        title: 'Error',
        description: e.response?.data?.message || 'An error occurred during registration.',
        variant: 'destructive',
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
        <CardTitle>Create an account</CardTitle>
        <CardDescription>Enter your details to get started</CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input
              id="name"
              v-model="form.name"
              type="text"
              placeholder="John Doe"
              :disabled="loading"
              required
            />
            <p v-if="errors.name" class="text-sm text-destructive">{{ errors.name }}</p>
          </div>

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

          <div class="space-y-2">
            <Label for="password_confirmation">Confirm Password</Label>
            <Input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              :disabled="loading"
              required
            />
          </div>

          <Button type="submit" class="w-full" :disabled="loading">
            <Loader2Icon v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
            Create account
          </Button>
        </form>
      </CardContent>
      <CardFooter class="flex flex-col space-y-4">
        <div class="text-center text-sm text-muted-foreground">
          Already have an account?
          <router-link :to="{ name: 'login' }" class="ml-1 text-primary hover:underline">
            Sign in
          </router-link>
        </div>
      </CardFooter>
    </Card>
    <Toaster />
  </div>
</template>
