import { ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'

interface FilterState {
  search: string
  category: string
  completed: string
  deadline: string
  sort_by: string
  sort_direction: string
}

export function useFilterQuery(defaultFilters: FilterState) {
  const router = useRouter()
  const route = useRoute()

  const filters = ref<FilterState>({
    search: route.query.search as string || defaultFilters.search,
    category: route.query.category as string || defaultFilters.category,
    completed: route.query.completed as string || defaultFilters.completed,
    deadline: route.query.deadline as string || defaultFilters.deadline,
    sort_by: route.query.sort_by as string || defaultFilters.sort_by,
    sort_direction: route.query.sort_direction as string || defaultFilters.sort_direction,
  })

  watch(filters, (newFilters) => {
    const query: Record<string, string> = {}
    Object.entries(newFilters).forEach(([key, value]) => {
      if (value) {
        query[key] = value
      }
    })

    router.replace({ query })
  }, { deep: true })

  watch(
    () => route.query,
    (newQuery) => {
      filters.value = {
        search: newQuery.search as string || defaultFilters.search,
        category: newQuery.category as string || defaultFilters.category,
        completed: newQuery.completed as string || defaultFilters.completed,
        deadline: newQuery.deadline as string || defaultFilters.deadline,
        sort_by: newQuery.sort_by as string || defaultFilters.sort_by,
        sort_direction: newQuery.sort_direction as string || defaultFilters.sort_direction,
      }
    },
    { deep: true }
  )

  return {
    filters
  }
}