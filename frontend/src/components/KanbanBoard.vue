<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Sortable } from 'sortablejs-vue3';
import { authState, authService } from '../services/auth';
import apiClient from '../services/api';
import NewTaskForm from './NewTaskForm.vue';
import TaskModal from './TaskModal.vue';

// Define the types for our data
interface Task {
  id: number;
  title: string;
  description: string | null;
  order: number;
  column_id: number;
}

interface Column {
  id: number;
  name: string;
  tasks: Task[];
}

const columns = ref<Column[]>([]);
const isLoading = ref(true);
const error = ref<string | null>(null);

// Modal state
const selectedTask = ref<Task | null>(null);
const isModalVisible = ref(false);

const openTaskModal = (task: Task) => {
  selectedTask.value = task;
  isModalVisible.value = true;
};

const closeModal = () => {
  isModalVisible.value = false;
  selectedTask.value = null;
};

// Fetch data from the API
const fetchData = async () => {
  try {
    const response = await apiClient.get('/api/kanban');
    columns.value = response.data;
  } catch (err) {
    error.value = 'Failed to fetch data from the server.';
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

// Handle drag-and-drop sync
const onDragEnd = async () => {
  try {
    await apiClient.put('/api/kanban/sync', {
      columns: columns.value
    });
  } catch (err) {
    error.value = 'Failed to sync board state.';
    console.error(err);
  }
};

// Handle the event when a new task is added
const handleTaskAdded = (newTask: Task) => {
  const column = columns.value.find(col => col.id === newTask.column_id);
  if (column) {
    column.tasks.push(newTask);
  }
};

const handleTaskUpdated = (updatedTask: Task) => {
  const column = columns.value.find(col => col.id === updatedTask.column_id);
  if (column) {
    const taskIndex = column.tasks.findIndex(t => t.id === updatedTask.id);
    if (taskIndex !== -1) {
      column.tasks[taskIndex] = updatedTask;
    }
  }
};

const handleTaskDeleted = (taskId: number) => {
  for (const column of columns.value) {
    const taskIndex = column.tasks.findIndex(t => t.id === taskId);
    if (taskIndex !== -1) {
      column.tasks.splice(taskIndex, 1);
      break; 
    }
  }
};

const handleLogout = () => {
  authService.logout();
};

onMounted(fetchData);
</script>

<template>
  <div class="kanban-board">
    <header class="board-header">
      <h1>My Kanban Board</h1>
      <div class="user-info" v-if="authState.isAuthenticated">
        <span>Welcome, {{ authState.user?.name }}</span>
        <button @click="handleLogout" class="logout-button">Logout</button>
      </div>
    </header>
    <div v-if="isLoading">Loading...</div>
    <div v-if="error" class="error">{{ error }}</div>

    <div class="board-layout" v-if="!isLoading && !error">
      <!-- Columns -->
      <div v-for="column in columns" :key="column.id" class="kanban-column">
        <h2>{{ column.name }}</h2>

        <Sortable
          :list="column.tasks"
          item-key="id"
          :options="{ group: 'tasks' }"
          class="drag-area"
          @end="onDragEnd"
        >
          <template #item="{ element: task }">
            <div class="task-card" @click="openTaskModal(task)">
              <p>{{ task.title }}</p>
            </div>
          </template>
        </Sortable>

        <!-- New Task Form -->
        <NewTaskForm :column-id="column.id" @task-added="handleTaskAdded" />
      </div>
    </div>

    <TaskModal 
      :task="selectedTask" 
      :show="isModalVisible" 
      @close="closeModal"
      @task-updated="handleTaskUpdated"
      @task-deleted="handleTaskDeleted"
    />
  </div>
</template>

<style scoped>
.board-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logout-button {
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 0.5rem 1rem;
  cursor: pointer;
}

.kanban-board {
  padding: 2rem;
  background-color: #f9fafb;
  min-height: 100vh;
}

.board-layout {
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
  overflow-x: auto;
  padding-bottom: 1rem;
}

.kanban-column {
  background-color: #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
  min-width: 280px;
  flex-shrink: 0;
}

.kanban-column h2 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 1rem;
}

.drag-area {
  min-height: 100px;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.task-card {
  background-color: #ffffff;
  border-radius: 4px;
  padding: 1rem;
  box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
  cursor: grab;
}

.task-card p {
  margin: 0;
}

.error {
    color: #ef4444;
}
</style> 