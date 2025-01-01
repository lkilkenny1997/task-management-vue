import axios from 'axios'
import type { Task, TaskFilters } from '@/types'

const BASE_URL = '/api/tasks'

const createQueryString = (filters: TaskFilters): string => {
  const params = new URLSearchParams()
  Object.entries(filters).forEach(([key, value]) => {
    if (value) params.append(key, value)
  })
  return params.toString()
}

export const fetchTasks = async (filters: TaskFilters): Promise<Task[]> => {
  const queryString = createQueryString(filters)
  const response = await axios.get(`${BASE_URL}?${queryString}`)
  return response.data.tasks
}

export const createTask = async (taskData: Partial<Task>): Promise<Task> => {
  const response = await axios.post(BASE_URL, taskData)
  return response.data.data.task
}

export const updateTask = async (taskId: number, taskData: Partial<Task>): Promise<Task> => {
  const response = await axios.put(`${BASE_URL}/${taskId}`, taskData)
  return response.data.data.task
}

export const deleteTask = async (taskId: number): Promise<void> => {
  await axios.delete(`${BASE_URL}/${taskId}`)
}

export const toggleTaskCompletion = async (taskId: number, completed: boolean): Promise<Task> => {
  return updateTask(taskId, { completed })
}
