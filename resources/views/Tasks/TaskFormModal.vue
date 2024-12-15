<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import dayjs from 'dayjs'
import type { Task } from '@/types'

const props = defineProps<{
  task: Task | null
}>()

const emit = defineEmits<{
  (e: 'close'): void
  (e: 'save', data: Partial<Task>): void
}>()

const form = ref({
  title: '',
  description: '',
  category: 'work',
  deadline: dayjs().add(1, 'day').startOf('hour').add(1, 'hour').format('YYYY-MM-DDTHH:mm')
})

const errors = ref({
  title: '',
  category: '',
  deadline: ''
})

onMounted(() => {
  if (props.task) {
    form.value = {
      ...props.task,
      deadline: dayjs(props.task.deadline).format('YYYY-MM-DDTHH:mm')
    }
  }
})

const validateForm = () => {
  let isValid = true
  errors.value = {
    title: '',
    category: '',
    deadline: ''
  }

  if (!form.value.title.trim()) {
    errors.value.title = 'Title is required'
    isValid = false
  }

  if (!form.value.category) {
    errors.value.category = 'Category is required'
    isValid = false
  }

  if (!form.value.deadline) {
    errors.value.deadline = 'Deadline is required'
    isValid = false
  } else if (dayjs(form.value.deadline).isBefore(dayjs())) {
    errors.value.deadline = 'Deadline must be in the future'
    isValid = false
  }

  return isValid
}

const handleSubmit = () => {
  if (validateForm()) {
    emit('save', { ...form.value })
  }
}
</script>

<template>
  <Dialog :open="true" @update:open="emit('close')">
    <DialogContent class="sm:max-w-[425px]">
      <DialogHeader>
        <DialogTitle>{{ task ? 'Edit Task' : 'Create Task' }}</DialogTitle>
      </DialogHeader>

      <form @submit.prevent="handleSubmit" class="space-y-4 py-4">
        <div class="space-y-2">
          <Label for="title">Title</Label>
          <Input
            id="title"
            v-model="form.title"
            placeholder="Task title"
          />
          <p v-if="errors.title" class="text-sm text-destructive">{{ errors.title }}</p>
        </div>

        <div class="space-y-2">
          <Label for="description">Description</Label>
          <Textarea
            id="description"
            v-model="form.description"
            placeholder="Add task details..."
            rows="3"
          />
        </div>

        <div class="space-y-2">
          <Label for="category">Category</Label>
          <select
            id="category"
            v-model="form.category"
            class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
          >
            <option value="work">Work</option>
            <option value="personal">Personal</option>
            <option value="urgent">Urgent</option>
          </select>
          <p v-if="errors.category" class="text-sm text-destructive">{{ errors.category }}</p>
        </div>

        <div class="space-y-2">
          <Label for="deadline">Deadline</Label>
          <Input
            id="deadline"
            v-model="form.deadline"
            type="datetime-local"
          />
          <p v-if="errors.deadline" class="text-sm text-destructive">{{ errors.deadline }}</p>
        </div>
      </form>

      <DialogFooter>
        <Button variant="outline" @click="emit('close')" class="mt-4 lg:mt-0">Cancel</Button>
        <Button type="submit" @click="handleSubmit">
          {{ task ? 'Update' : 'Create' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>