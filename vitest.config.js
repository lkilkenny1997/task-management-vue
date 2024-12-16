import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath } from 'node:url'

export default defineConfig({
  plugins: [vue()],
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./resources/tests/setup.ts']
  },
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources', import.meta.url))
    }
  }
})