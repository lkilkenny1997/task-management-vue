import { describe, it, expect, beforeEach, vi } from 'vitest'
import { useTasks } from '@/composables/useTasks'
import * as taskService from '@/services/taskService'
import type { Task } from '@/types'

vi.mock('@/services/taskService')
vi.mock('@/components/ui/toast', () => ({
  useToast: vi.fn(() => ({
    toast: vi.fn()
  }))
}))

describe('useTasks', () => {
  const mockTasks = [
    {
      id: 1,
      title: 'Test Task',
      description: 'Description',
      category: 'work' as Task['category'],
      deadline: '2024-12-16T15:00:00',
      completed: false
    }
  ]

  beforeEach(() => {
    vi.clearAllMocks()
  })

  it('fetches tasks successfully', async () => {
    vi.spyOn(taskService, 'fetchTasks').mockResolvedValue(mockTasks)
    
    const { tasks, loading, fetchTasks } = useTasks()
    
    expect(loading.value).toBe(true)
    await fetchTasks({})
    
    expect(loading.value).toBe(false)
    expect(tasks.value).toEqual(mockTasks)
  })

  it('handles task completion toggle', async () => {
    const mockUpdatedTask = { ...mockTasks[0], completed: true }
    vi.spyOn(taskService, 'toggleTaskCompletion').mockResolvedValue(mockUpdatedTask)
    
    const { tasks, toggleComplete } = useTasks()
    tasks.value = mockTasks
    
    await toggleComplete(mockTasks[0])
    
    expect(taskService.toggleTaskCompletion).toHaveBeenCalledWith(1, true)
    expect(tasks.value[0].completed).toBe(true)
  })

  it('deletes tasks successfully', async () => {
    vi.spyOn(taskService, 'deleteTask').mockResolvedValue()
    window.confirm = vi.fn(() => true)
    
    const { tasks, deleteTask } = useTasks()
    tasks.value = mockTasks
    
    await deleteTask(1)
    
    expect(taskService.deleteTask).toHaveBeenCalledWith(1)
    expect(tasks.value).toHaveLength(0)
  })

  it('saves new task successfully', async () => {
    const newTask = {
      title: 'New Task',
      category: 'work' as Task['category'],
      deadline: '2024-12-16T15:00:00'
    }
    const savedTask: Task = { 
      id: 2, 
      ...newTask, 
      description: '',
      completed: false
    }
    
    vi.spyOn(taskService, 'createTask').mockResolvedValue(savedTask)
    
    const { tasks, saveTask } = useTasks()
    tasks.value = mockTasks
    
    await saveTask(newTask)
    
    expect(taskService.createTask).toHaveBeenCalledWith(newTask)
    expect(tasks.value).toHaveLength(2)
    expect(tasks.value[1]).toEqual(savedTask)
  })
})