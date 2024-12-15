<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useToast } from '@/components/ui/toast'
import { 
  Card, 
  CardContent, 
  CardDescription, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import axios from 'axios'
import type { Task } from '@/types'

const { toast } = useToast()
const tasks = ref<Task[]>([])
const loading = ref(true)
const showTaskForm = ref(false)
const editingTask = ref<Task | null>(null)

const fetchTasks = async () => {
  try {
    loading.value = true
    const response = await axios.get('/api/tasks')
    tasks.value = response.data.tasks
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to load tasks",
      variant: "destructive",
    })
  } finally {
    loading.value = false
  }
}

onMounted(fetchTasks)
</script>

<template>
  <Card>
    <CardHeader>
      <div class="flex items-center justify-between">
        <div class="space-y-2">
          <CardTitle>Tasks</CardTitle>
          <CardDescription>Manage your tasks and track their progress</CardDescription>
        </div>
      </div>
    </CardHeader>
    <CardContent>
      <div v-if="loading" class="py-8 text-center text-muted-foreground">
        Loading tasks...
      </div>

      <div v-else-if="tasks.length === 0" class="py-8 text-center text-muted-foreground">
        No tasks found. Create your first task to get started!
      </div>

      <div v-else class="divide-y">
        <div
          v-for="task in tasks"
          :key="task.id"
          class="py-4 first:pt-0 last:pb-0"
        >
          <div class="flex items-start justify-between space-x-4">
            <div class="flex items-start space-x-4">
              <div>
                <h3 
                  class="font-medium"
                  :class="{ 'line-through text-muted-foreground': task.completed }"
                >
                  {{ task.title }}
                </h3>
                <p 
                  class="mt-1 text-sm text-muted-foreground"
                  :class="{ 'line-through': task.completed }"
                >
                  {{ task.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>