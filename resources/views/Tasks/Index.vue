<script setup lang="ts">
import { ref, onMounted, watch } from "vue";
import { useToast } from "@/components/ui/toast";
import { getDeadlineStatus } from "@/lib/utils";
import { useFilterQuery } from "@/composables/useFilterQuery";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import TaskFormModal from "./TaskFormModal.vue";
import TaskFilters from "./TaskFilters.vue";
import axios from "axios";
import {
  CalendarDays,
  CheckCircle2,
  Clock,
  Edit,
  Plus,
  Trash2,
  Briefcase,
  Home,
  AlertOctagon,
} from "lucide-vue-next";
import { formatDate } from "@/lib/utils";
import type { Task } from "@/types";

const { toast } = useToast();
const tasks = ref<Task[]>([]);
const loading = ref(true);
const showTaskForm = ref(false);
const editingTask = ref<Task | null>(null);
const totalTasks = ref(0);

const { filters } = useFilterQuery({
  search: "",
  category: "",
  completed: "",
  deadline: "",
  sort_by: "deadline",
  sort_direction: "asc",
});

watch(
  filters,
  () => {
    fetchTasks();
  },
  { deep: true }
);

const categoryIcons = {
  work: Briefcase,
  personal: Home,
  urgent: AlertOctagon,
};

const fetchTasks = async () => {
  try {
    loading.value = true;

    const params = new URLSearchParams();
    Object.entries(filters.value).forEach(([key, value]) => {
      if (value) params.append(key, value);
    });

    const response = await axios.get(`/api/tasks?${params.toString()}`);
    tasks.value = response.data.tasks;
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to load tasks",
      variant: "destructive",
    });
  } finally {
    loading.value = false;
  }
};

const toggleComplete = async (task: Task) => {
  try {
    await axios.put(`/api/tasks/${task.id}`, {
      completed: !task.completed,
    });
    task.completed = !task.completed;
    toast({
      title: task.completed ? "Task completed" : "Task uncompleted",
      description: task.title,
    });

    await fetchTasks();
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to update task status",
      variant: "destructive",
    });
  }
};

const deleteTask = async (taskId: number) => {
  if (!confirm("Are you sure you want to delete this task?")) return;

  try {
    await axios.delete(`/api/tasks/${taskId}`);
    tasks.value = tasks.value.filter((task) => task.id !== taskId);
    toast({
      title: "Task deleted",
      description: "The task has been deleted successfully",
    });
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to delete task",
      variant: "destructive",
    });
  }
};

const editTask = (task: Task) => {
  editingTask.value = task;
  showTaskForm.value = true;
};

const saveTask = async (taskData: Partial<Task>) => {
  try {
    if (editingTask.value) {
      const response = await axios.put(
        `/api/tasks/${editingTask.value.id}`,
        taskData
      );
      const index = tasks.value.findIndex(
        (t) => t.id === editingTask.value!.id
      );
      tasks.value[index] = response.data.data.task;
      toast({
        title: "Task updated",
        description: "The task has been updated successfully",
      });
    } else {
      const response = await axios.post("/api/tasks", taskData);
      tasks.value.push(response.data.data.task);
      toast({
        title: "Task created",
        description: "The task has been created successfully",
      });
    }
    closeTaskForm();
    await fetchTasks();
  } catch (error) {
    toast({
      title: "Error",
      description: "Failed to save task",
      variant: "destructive",
    });
  }
};

const closeTaskForm = () => {
  showTaskForm.value = false;
  editingTask.value = null;
};

const getCategoryColor = (category: string) => {
  const colors = {
    work: "bg-blue-100 text-blue-800",
    personal: "bg-green-100 text-green-800",
    urgent: "bg-red-100 text-red-800",
  };
  return colors[category as keyof typeof colors] || "bg-gray-100 text-gray-800";
};

onMounted(fetchTasks);
</script>

<template>
  <div class="space-y-6">
    <TaskFilters v-if="tasks.length > 0" v-model="filters" />

    <Card class="h-[calc(100vh-18rem)]">
      <CardHeader class="sticky top-0 z-10 bg-background border-b">
        <div class="flex items-center justify-between">
          <div class="space-y-2">
            <div class="flex items-center gap-3">
              <CardTitle>Tasks</CardTitle>
              <div class="rounded-full bg-primary px-2.5 py-0.5 text-sm text-primary-foreground font-bold">
                {{ tasks.length }}
              </div>
            </div>
            <CardDescription>Manage your tasks and track their progress</CardDescription>
          </div>
          <Button @click="showTaskForm = true">
            <Plus class="h-4 w-4" />
            <span class="hidden lg:inline ml-2">Add Task</span>
          </Button>
        </div>
      </CardHeader>

      <CardContent class="h-[calc(100%-105px)] overflow-y-auto">
        <div v-if="loading" class="py-8 text-center text-muted-foreground">
          Loading tasks...
        </div>

        <div v-else-if="tasks.length === 0" class="py-8 text-center text-muted-foreground">
          No tasks found. Create your first task to get started!
        </div>

        <div v-else class="divide-y mt-4">
          <div v-for="task in tasks" :key="task.id" class="py-4 first:pt-0">
            <div class="flex items-start justify-between space-x-4">
              <div class="flex items-start space-x-4">
                <Button variant="ghost" size="icon" @click="toggleComplete(task)"
                  :class="task.completed ? 'text-green-500' : 'text-muted-foreground'">
                  <CheckCircle2 class="h-5 w-5" />
                </Button>

                <div>
                  <h3 class="font-medium" :class="{ 'line-through text-muted-foreground': task.completed }">
                    {{ task.title }}
                  </h3>
                  <p class="mt-1 text-sm text-muted-foreground" :class="{ 'line-through': task.completed }">
                    {{ task.description }}
                  </p>
                  <div class="mt-2 flex items-center space-x-4 text-sm">
                    <span :class="[
                      'rounded-full px-2.5 py-1 text-xs font-medium flex items-center gap-1.5 capitalize',
                      getCategoryColor(task.category),
                    ]">
                      <component :is="categoryIcons[task.category]" class="h-3.5 w-3.5" />
                      {{ task.category }}
                    </span>

                    <span class="flex items-center text-sm gap-1.5"
                      :class="getDeadlineStatus(task.deadline, task.completed).class">
                      <Clock class="h-3.5 w-3.5" />
                      {{ getDeadlineStatus(task.deadline, task.completed).text }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="flex space-x-2">
                <Button variant="ghost" size="icon" @click="editTask(task)">
                  <Edit class="h-4 w-4" />
                </Button>
                <Button variant="ghost" size="icon" @click="deleteTask(task.id)">
                  <Trash2 class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <TaskFormModal v-if="showTaskForm" :task="editingTask" @close="closeTaskForm" @save="saveTask" />
  </div>
</template>