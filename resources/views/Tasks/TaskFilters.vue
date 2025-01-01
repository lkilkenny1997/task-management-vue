<script setup lang="ts">
import { ref, watch } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Search, Filter, ArrowUpDown, X, ChevronDown } from 'lucide-vue-next'
import { Card, CardContent } from '@/components/ui/card'

const props = defineProps<{
  modelValue: {
    search: string
    category: string
    completed: string
    deadline: string
    sort_by: string
    sort_direction: string
  }
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: typeof props.modelValue): void
}>()

const searchInput = ref(props.modelValue.search)

const debouncedSearch = useDebounceFn((value: string) => {
  emit('update:modelValue', {
    ...props.modelValue,
    search: value,
  })
}, 500)

watch(searchInput, (newValue) => {
  debouncedSearch(newValue)
})

watch(
  () => props.modelValue.search,
  (newValue) => {
    if (newValue !== searchInput.value) {
      searchInput.value = newValue
    }
  }
)

const deadlineOptions = [
  { value: '', label: 'All' },
  { value: 'overdue', label: 'Overdue' },
  { value: 'today', label: 'Due Today' },
  { value: 'week', label: 'Due This Week' },
  { value: 'month', label: 'Due This Month' },
]

const sortOptions = [
  { value: 'deadline', label: 'Deadline' },
  { value: 'title', label: 'Title' },
]

const clearFilters = () => {
  searchInput.value = ''

  emit('update:modelValue', {
    search: '',
    category: '',
    completed: '',
    deadline: '',
    sort_by: 'deadline',
    sort_direction: 'asc',
  })
}

const updateFilter = (key: string, value: string) => {
  emit('update:modelValue', {
    ...props.modelValue,
    [key]: value,
  })
}
</script>

<template>
  <Card class="mb-6">
    <CardContent class="space-y-4 p-4">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="relative">
          <select
            :value="modelValue.category"
            @change="(e) => updateFilter('category', (e.target as HTMLSelectElement).value)"
            class="flex h-10 w-full appearance-none rounded-md border border-input bg-background px-3 py-2 pr-8 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
          >
            <option value="">All Categories</option>
            <option value="work">Work</option>
            <option value="personal">Personal</option>
            <option value="urgent">Urgent</option>
          </select>
          <ChevronDown class="absolute right-3 top-3 h-4 w-4 opacity-50" />
        </div>

        <div class="relative">
          <select
            :value="modelValue.completed"
            @change="(e) => updateFilter('completed', (e.target as HTMLSelectElement).value)"
            class="flex h-10 w-full appearance-none rounded-md border border-input bg-background px-3 py-2 pr-8 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
          >
            <option value="">All Status</option>
            <option value="true">Completed</option>
            <option value="false">Pending</option>
          </select>
          <ChevronDown class="absolute right-3 top-3 h-4 w-4 opacity-50" />
        </div>

        <div class="relative">
          <select
            :value="modelValue.deadline"
            @change="(e) => updateFilter('deadline', (e.target as HTMLSelectElement).value)"
            class="flex h-10 w-full appearance-none rounded-md border border-input bg-background px-3 py-2 pr-8 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
          >
            <option v-for="option in deadlineOptions" :key="option.value" :value="option.value">
              {{ option.label }}
            </option>
          </select>
          <ChevronDown class="absolute right-3 top-3 h-4 w-4 opacity-50" />
        </div>
      </div>

      <div class="flex flex-col items-center justify-between gap-4 lg:flex-row">
        <div class="relative w-full flex-1 lg:w-auto">
          <Input v-model="searchInput" placeholder="Search tasks..." class="pr-8" />
          <Search class="absolute right-3 top-3 h-4 w-4 text-muted-foreground" />
        </div>

        <div class="flex items-center gap-2">
          <div class="relative">
            <select
              :value="modelValue.sort_by"
              @change="(e) => updateFilter('sort_by', (e.target as HTMLSelectElement).value)"
              class="flex h-10 appearance-none rounded-md border border-input bg-background px-3 py-2 pr-8 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
            >
              <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                Sort by {{ option.label }}
              </option>
            </select>
            <ChevronDown class="absolute right-3 top-3 h-4 w-4 opacity-50" />
          </div>

          <Button
            variant="ghost"
            size="icon"
            @click="
              updateFilter('sort_direction', modelValue.sort_direction === 'asc' ? 'desc' : 'asc')
            "
          >
            <ArrowUpDown class="h-4 w-4" />
          </Button>

          <Button variant="ghost" size="icon" @click="clearFilters">
            <X class="h-4 w-4" />
          </Button>
        </div>
      </div>
    </CardContent>
  </Card>
</template>
