import dayjs from 'dayjs'
import { type ClassValue, clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'
import relativeTime from 'dayjs/plugin/relativeTime'
import calendar from 'dayjs/plugin/calendar'

dayjs.extend(relativeTime)
dayjs.extend(calendar)

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export function formatDate(input: string | number | Date): string {
  const date = new Date(input)
  return date.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  })
}

export function formatDateTime(input: string | number | Date): string {
  const date = new Date(input)
  return date.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
  })
}

export const getDeadlineStatus = (deadline: string, completed: boolean) => {
  const now = dayjs()
  const taskDeadline = dayjs(deadline)
  const diff = taskDeadline.diff(now, 'hour', true)
  
  if (completed) {
    return {
      text: `Completed (Due: ${taskDeadline.format('MMM D, YYYY [at] h:mm A')})`,
      class: 'text-green-600'
    }
  }

  // overdue
  if (diff < 0) {
    if (Math.abs(diff) < 24) {
      return { 
        text: taskDeadline.fromNow(), 
        class: 'text-destructive font-bold'
      }
    }
    return { 
      text: taskDeadline.fromNow(),
      class: 'text-destructive font-bold' 
    }
  }

  // due within 24 hours
  if (diff <= 24) {
    if (diff <= 1) {
      return { 
        text: 'Due in less than an hour!', 
        class: 'text-destructive font-bold' 
      }
    }
    return { 
      text: taskDeadline.fromNow(),
      class: 'text-orange-500 font-bold' 
    }
  }

  // today or tomorrow
  if (diff <= 48) {
    return {
      text: taskDeadline.calendar(null, {
        sameDay: '[Today at] h:mm A',
        nextDay: '[Tomorrow at] h:mm A',
      }),
      class: 'text-orange-500 font-bold'
    }
  }

  // within a week
  if (diff <= 168) {
    return { 
      text: taskDeadline.calendar(null, {
        sameDay: '[Today at] h:mm A',
        nextDay: '[Tomorrow at] h:mm A',
        nextWeek: 'dddd [at] h:mm A',
      }),
      class: 'text-blue-500' 
    }
  }

  return { 
    text: taskDeadline.format('MMM D, YYYY [at] h:mm A'),
    class: 'text-muted-foreground' 
  }
}