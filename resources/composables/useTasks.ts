import { useToast } from '@/components/ui/toast'
import * as taskService from '@/services/taskService'
import type { Task } from '@/types'
import { AlertOctagon, Briefcase, Home } from 'lucide-vue-next'
import { ref } from 'vue'

export function useTasks() {
  const tasks = ref<Task[]>([])
  const loading = ref(true)
  const { toast } = useToast()

  const categoryIcons = {
    work: Briefcase,
    personal: Home,
    urgent: AlertOctagon,
  }

  const getCategoryColor = (category: string) => {
    const colors = {
      work: 'bg-blue-100 text-blue-800',
      personal: 'bg-green-100 text-green-800',
      urgent: 'bg-red-100 text-red-800',
    }
    return colors[category as keyof typeof colors] || 'bg-gray-100 text-gray-800'
  }

  const fetchTasks = async (filters: any) => {
    try {
      loading.value = true
      tasks.value = await taskService.fetchTasks(filters)
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to load tasks',
        variant: 'destructive',
      })
    } finally {
      loading.value = false
    }
  }

  const toggleComplete = async (task: Task) => {
    try {
      const updatedTask = await taskService.toggleTaskCompletion(task.id, !task.completed)
      const index = tasks.value.findIndex((t) => t.id === task.id)
      if (index !== -1) {
        tasks.value[index] = updatedTask
      }

      toast({
        title: updatedTask.completed ? 'Task completed' : 'Task uncompleted',
        description: task.title,
      })
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to update task status',
        variant: 'destructive',
      })
    }
  }

  const deleteTask = async (taskId: number) => {
    if (!confirm('Are you sure you want to delete this task?')) return

    try {
      await taskService.deleteTask(taskId)
      tasks.value = tasks.value.filter((task) => task.id !== taskId)
      toast({
        title: 'Task deleted',
        description: 'The task has been deleted successfully',
      })
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to delete task',
        variant: 'destructive',
      })
    }
  }

  const saveTask = async (taskData: Partial<Task>, editingTaskId?: number) => {
    try {
      let savedTask
      if (editingTaskId) {
        savedTask = await taskService.updateTask(editingTaskId, taskData)
        const index = tasks.value.findIndex((t) => t.id === editingTaskId)
        if (index !== -1) {
          tasks.value[index] = savedTask
        }
        toast({
          title: 'Task updated',
          description: 'The task has been updated successfully',
        })
      } else {
        savedTask = await taskService.createTask(taskData)
        tasks.value.push(savedTask)
        toast({
          title: 'Task created',
          description: 'The task has been created successfully',
        })
      }
      return savedTask
    } catch (error) {
      toast({
        title: 'Error',
        description: 'Failed to save task',
        variant: 'destructive',
      })
      throw error
    }
  }

  return {
    tasks,
    loading,
    fetchTasks,
    toggleComplete,
    deleteTask,
    saveTask,
    categoryIcons,
    getCategoryColor,
  }
}
