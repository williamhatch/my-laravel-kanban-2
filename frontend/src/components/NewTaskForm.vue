<script setup lang="ts">
import { ref, nextTick } from 'vue';
import apiClient from '../services/api';

interface Props {
  columnId: number;
}

const props = defineProps<Props>();
const emit = defineEmits(['task-added']);

const isCreating = ref(false);
const newTaskTitle = ref('');
const error = ref<string | null>(null);
const textareaRef = ref<HTMLTextAreaElement | null>(null);

const startCreating = async () => {
  isCreating.value = true;
  await nextTick(); // Wait for the DOM to update
  textareaRef.value?.focus(); // Focus the textarea
};

const cancelCreating = () => {
  isCreating.value = false;
  newTaskTitle.value = '';
  error.value = null;
};

const saveTask = async () => {
  if (!newTaskTitle.value.trim()) {
    error.value = 'Title is required.';
    return;
  }
  
  try {
    const response = await apiClient.post('/api/kanban/tasks', {
      title: newTaskTitle.value,
      column_id: props.columnId,
    });
    
    emit('task-added', response.data);
    cancelCreating();
  } catch (err) {
    error.value = 'Failed to create task.';
    console.error(err);
  }
};
</script>

<template>
  <div class="new-task-form">
    <div v-if="!isCreating" @click="startCreating" class="add-card-button">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 inline-block mr-1">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
      </svg>
      Add a card
    </div>
    <div v-else class="form-container">
      <textarea
        ref="textareaRef"
        v-model="newTaskTitle"
        class="task-textarea"
        placeholder="Enter a title for this card..."
        @keydown.enter.prevent="saveTask"
        @keydown.esc="cancelCreating"
      ></textarea>
      <div v-if="error" class="error-message">{{ error }}</div>
      <div class="form-actions">
        <button @click="saveTask" class="save-button">Save</button>
        <button @click="cancelCreating" class="cancel-button">Cancel</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.add-card-button {
  padding: 0.5rem;
  border-radius: 4px;
  cursor: pointer;
  color: #4a5568;
  font-weight: 500;
  display: flex;
  align-items: center;
  transition: background-color 0.2s, color 0.2s;
}

.add-card-button:hover {
  background-color: #d1d5db;
  color: #1f2937;
}

.add-card-button svg {
  width: 1rem;
  height: 1rem;
  margin-right: 0.25rem;
}

.form-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  border: 2px solid #3b82f6; /* Highlight when focused */
  border-radius: 4px;
  resize: vertical;
  box-sizing: border-box;
}

.task-textarea {
  width: 100%;
  padding: 0.5rem;
  border: none;
  outline: none;
}

.form-actions {
  display: flex;
  gap: 0.5rem;
}

.save-button {
  background-color: #48bb78;
  color: white;
  border: none;
  border-radius: 4px;
  padding: 0.5rem 1rem;
  cursor: pointer;
}

.cancel-button {
  background-color: transparent;
  border: none;
  color: #718096;
  cursor: pointer;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
}
</style> 