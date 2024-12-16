export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
}

export interface Task {
  id: number;
  title: string;
  description: string | null;
  category: 'work' | 'personal' | 'urgent';
  deadline: string;
  completed: boolean;
}
export interface TaskFilters {
  search?: string
  category?: string
  completed?: string
  deadline?: string
  sort_by?: string
  sort_direction?: string
}