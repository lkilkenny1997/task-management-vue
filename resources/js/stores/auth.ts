import { defineStore } from 'pinia';
import axios from 'axios';
import type { User } from '../../types';
import { useAuth } from '@/composables/useAuth';

interface AuthState {
  user: User | null;
  isAuthenticated: boolean;
  initialising: boolean;
}

interface LoginCredentials {
  email: string;
  password: string;
}

interface RegisterData extends LoginCredentials {
  name: string;
  password_confirmation: string;
}

export const useAuthStore = defineStore('auth', {
  state: (): AuthState => ({
    user: null,
    isAuthenticated: false,
    initialising: true,
  }),

  actions: {
    async register(userData: RegisterData) {
      try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post<{ user: User; message: string }>('/api/register', userData);
        this.user = response.data.user;
        this.isAuthenticated = true;
        return response;
      } catch (error) {
        throw error;
      }
    },

    async login(credentials: LoginCredentials) {
      try {
        await axios.get('/sanctum/csrf-cookie');
        const response = await axios.post<{ user: User; message: string }>('/api/login', credentials);
        this.user = response.data.user;
        this.isAuthenticated = true;
        return response;
      } catch (error) {
        this.user = null;
        this.isAuthenticated = false;
        throw error;
      }
    },

    async logout() {
      try {
        await axios.post('/api/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.isAuthenticated = false;
      }
    },

    async getUser() {
      try {
        const response = await axios.get<User>('/api/user');
        this.user = response.data;
        this.isAuthenticated = true;
        return response.data;
      } catch (error) {
        this.user = null;
        this.isAuthenticated = false;
        throw error;
      }
    },

    async init() {
      this.initialising = true;
      try {
        await axios.get('/sanctum/csrf-cookie');
      } finally {
        this.initialising = false;
      }
    },

    async checkAuth() {
      try {
        await this.getUser();
        return true;
      } catch (error) {
        this.user = null;
        this.isAuthenticated = false;
        return false;
      }
    }
  }
});