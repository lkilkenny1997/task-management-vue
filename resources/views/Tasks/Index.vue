<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { useFilterQuery } from '@/composables/useFilterQuery'
import { useTasks } from '@/composables/useTasks'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import TaskFormModal from './TaskFormModal.vue'
import TaskFilters from './TaskFilters.vue'
import { getDeadlineStatus } from '@/lib/utils'
import { Plus, Edit, Trash2, CheckCircle2, Clock } from 'lucide-vue-next'
import type { Task } from '@/types'

const showTaskForm = ref(false)
const editingTask = ref<Task | null>(null)

const { filters } = useFilterQuery({
  search: '',
  category: '',
  completed: '',
  deadline: '',
  sort_by: 'deadline',
  sort_direction: 'asc',
})

const {
  tasks,
  loading,
  fetchTasks,
  toggleComplete,
  deleteTask,
  saveTask,
  categoryIcons,
  getCategoryColor,
} = useTasks()

watch(
  filters,
  () => {
    fetchTasks(filters.value)
  },
  { deep: true }
)

const handleSaveTask = async (taskData: Partial<Task>) => {
  await saveTask(taskData, editingTask.value?.id)
  closeTaskForm()
  await fetchTasks(filters.value)
}

const closeTaskForm = () => {
  showTaskForm.value = false
  editingTask.value = null
}

const editTask = (task: Task) => {
  editingTask.value = task
  showTaskForm.value = true
}

onMounted(() => fetchTasks(filters.value))
</script>

<template>
  <div class="space-y-6">
    <TaskFilters v-model="filters" />

    <Card class="h-[calc(100vh-18rem)]">
      <CardHeader class="sticky top-0 z-10 border-b bg-background">
        <div class="flex items-center justify-between">
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <CardTitle>Tasks</CardTitle>
              <div
                class="rounded-full bg-primary px-2.5 py-0.5 text-sm font-bold text-primary-foreground"
              >
                {{ tasks.length }}
              </div>
            </div>
            <CardDescription>Manage your tasks and track their progress</CardDescription>
          </div>
          <Button @click="showTaskForm = true">
            <Plus class="h-4 w-4" />
            <span class="ml-2 hidden lg:inline">Add Task</span>
          </Button>
        </div>
      </CardHeader>

      <CardContent class="h-[calc(100%-105px)] overflow-y-auto">
        <div v-if="loading" class="py-8 text-center text-muted-foreground">Loading tasks...</div>

        <div v-else-if="tasks.length === 0" class="py-8 text-center text-muted-foreground">
          No tasks found. Create your first task to get started!
        </div>

        <div v-else class="mt-4 divide-y">
          <div v-for="task in tasks" :key="task.id" class="py-4 first:pt-0">
            <div class="flex items-start justify-between space-x-4">
              <div class="flex items-start space-x-4">
                <Button
                  variant="ghost"
                  size="icon"
                  @click="toggleComplete(task)"
                  :class="task.completed ? 'text-green-500' : 'text-muted-foreground'"
                >
                  <CheckCircle2 class="h-5 w-5" />
                </Button>

                <div>
                  <h3
                    class="font-medium"
                    :class="{ 'text-muted-foreground line-through': task.completed }"
                  >
                    {{ task.title }}
                  </h3>
                  <p
                    class="mt-1 text-sm text-muted-foreground"
                    :class="{ 'line-through': task.completed }"
                  >
                    {{ task.description }}
                  </p>
                  <div class="mt-2 flex items-center space-x-4 text-sm">
                    <span
                      :class="[
                        'flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium capitalize',
                        getCategoryColor(task.category),
                      ]"
                    >
                      <component :is="categoryIcons[task.category]" class="h-3.5 w-3.5" />
                      {{ task.category }}
                    </span>

                    <span
                      class="flex items-center gap-1.5 text-sm"
                      :class="getDeadlineStatus(task.deadline, task.completed).class"
                    >
                      <Clock class="h-3.5 w-3.5" />
                      {{ getDeadlineStatus(task.deadline, task.completed).text }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex space-x-2">
                <Button variant="ghost" size="icon" @click="editTask(task)">
                  <Edit class="h-4 w-4" />
                </Button>
                <Button variant="ghost" size="icon" @click="deleteTask(task.id)">
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <TaskFormModal
      v-if="showTaskForm"
      :task="editingTask"
      @close="closeTaskForm"
      @save="handleSaveTask"
    />
  </div>
</template>
