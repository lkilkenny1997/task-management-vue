import { vi } from 'vitest'
import { config } from '@vue/test-utils'

vi.mock('dayjs', () => {
  return {
    default: vi.fn(() => ({
      format: vi.fn(() => '2024-12-16'),
      fromNow: vi.fn(() => '2 days ago'),
      calendar: vi.fn(() => 'Today at 3:00 PM'),
      diff: vi.fn(() => 24),
      startOf: vi.fn().mockReturnThis(),
      endOf: vi.fn().mockReturnThis(),
      add: vi.fn().mockReturnThis(),
    })),
    extend: vi.fn(),
  }
})

config.global.mocks = {
  $router: {
    push: vi.fn(),
    replace: vi.fn(),
  },
}
