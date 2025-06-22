<script setup lang="ts">
import { ref, watch } from "vue";
import apiClient from "../services/api";

interface Task {
  id: number;
  title: string;
  description: string | null;
  order: number;
  column_id: number;
}

interface Props {
  task: Task | null;
  show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(["close", "task-updated", "task-deleted"]);

const editableTask = ref<Partial<Task>>({});

// When the modal is shown, copy the task prop to a local editable object
watch(
  () => props.task,
  (newTask) => {
    if (newTask) {
      editableTask.value = { ...newTask };
    }
  },
);

const closeModal = () => {
  emit("close");
};

const updateTask = async () => {
  if (!editableTask.value || !editableTask.value.id) return;
  try {
    const response = await apiClient.put(
      `/api/kanban/tasks/${editableTask.value.id}`,
      {
        title: editableTask.value.title,
        description: editableTask.value.description,
      },
    );
    emit("task-updated", response.data);
    closeModal();
  } catch (error) {
    console.error("Failed to update task:", error);
  }
};

const deleteTask = async () => {
  if (confirm("Are you sure you want to delete this task?")) {
    try {
      await apiClient.delete(`/api/kanban/tasks/${editableTask.value.id}`);
      emit("task-deleted", editableTask.value.id);
      emit("close");
    } catch (error) {
      console.error("Failed to delete task:", error);
    }
  }
};
</script>

<template>
  <div v-if="show" class="modal-overlay" @click.self="closeModal">
    <div class="modal-content">
      <div v-if="editableTask">
        <input
          type="text"
          v-model="editableTask.title"
          class="task-title-input"
        />
        <textarea
          v-model="editableTask.description"
          class="task-description-textarea"
          placeholder="Add a description..."
        ></textarea>
        <div class="modal-actions">
          <button @click="updateTask" class="save-button">Save</button>
          <button @click="deleteTask" class="delete-button">Delete</button>
          <button @click="closeModal" class="cancel-button">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background-color: white;
  padding: 2rem;
  border-radius: 8px;
  width: 90%;
  max-width: 500px;
}

.task-title-input {
  width: 100%;
  font-size: 1.5rem;
  font-weight: bold;
  border: none;
  margin-bottom: 1rem;
  padding: 0.5rem 0;
  box-sizing: border-box;
}

.task-title-input:focus {
  outline: none;
  border-bottom: 2px solid #4299e1;
}

.task-description-textarea {
  width: 100%;
  height: 150px;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  padding: 0.5rem;
  resize: vertical;
  margin-bottom: 1rem;
  box-sizing: border-box;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.save-button,
.delete-button,
.cancel-button {
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 4px;
  cursor: pointer;
  font-weight: 600;
}

.save-button {
  background-color: #48bb78;
  color: white;
}

.delete-button {
  background-color: #ef4444;
  color: white;
}

.cancel-button {
  background-color: #f7fafc;
  color: #718096;
}
</style>
