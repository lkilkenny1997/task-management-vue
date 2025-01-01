import { createRouter, createWebHistory } from 'vue-router'
import Login from '../../views/Auth/Login.vue'
import Register from '../../views/Auth/Register.vue'
import Tasks from '../../views/Tasks/Index.vue'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    redirect: () => {
      const auth = useAuthStore()
      return auth.isAuthenticated ? { name: 'tasks' } : { name: 'login' }
    },
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { guestOnly: true },
  },
  {
    path: '/tasks',
    name: 'tasks',
    component: Tasks,
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()

  if (auth.initialising) {
    await new Promise((resolve) => {
      const unwatch = auth.$subscribe(() => {
        if (!auth.initialising) {
          unwatch()
          resolve()
        }
      })
    })
  }

  // For guest-only routes, redirect to tasks if already authenticated
  if (to.meta.guestOnly && auth.isAuthenticated) {
    return next({ name: 'tasks' })
  }

  // For protected routes, check auth status and redirect to login if not authenticated
  if (to.meta.requiresAuth) {
    const isAuthenticated = await auth.checkAuth()
    if (!isAuthenticated) {
      return next({ name: 'login' })
    }
  }

  next()
})

export default router
