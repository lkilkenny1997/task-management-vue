export interface User {
  id: number;
  name: string;
  email: string;
  created_at: string;
  updated_at: string;
}

export interface Task {
  id: number;
  user_id: number;
  title: string;
  description: string | null;
  category: 'work' | 'personal' | 'urgent';
  deadline: string;
  completed: boolean;
  created_at: string;
  updated_at: string;
}