<template>
  <div class="auth-container">
    <div class="auth-card">
      <h1 class="auth-title">Create a New Account</h1>
      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label for="name">Full Name</label>
          <input type="text" id="name" v-model="name" required>
        </div>
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" id="email" v-model="email" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" v-model="password" required>
        </div>
        <div class="form-group">
          <label for="password_confirmation">Confirm Password</label>
          <input type="password" id="password_confirmation" v-model="password_confirmation" required>
        </div>
        <div v-if="error" class="error-message">{{ error }}</div>
        <button type="submit" class="auth-button">Register</button>
      </form>
      <div class="switch-auth">
        Already have an account? <router-link to="/login">Login here</router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { authService } from '../services/auth';

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const error = ref<string | null>(null);

const handleRegister = async () => {
  error.value = null;
  if (password.value !== password_confirmation.value) {
    error.value = 'Passwords do not match.';
    return;
  }
  
  try {
    await authService.register({
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    });
  } catch (err: any) {
    if (err.response && err.response.data && err.response.data.errors) {
        // Handle Laravel validation errors
        const errors = err.response.data.errors;
        const firstErrorKey = Object.keys(errors)[0];
        error.value = errors[firstErrorKey][0];
    } else {
        error.value = 'An unexpected error occurred during registration.';
    }
  }
};
</script>

<style scoped>
/* Scoped styles from Login.vue are sufficient and will be reused */
.auth-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f3f4f6;
}

.auth-card {
  background-color: white;
  padding: 2.5rem;
  border-radius: 0.5rem;
  box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
  width: 100%;
  max-width: 28rem;
}

.auth-title {
  text-align: center;
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-sizing: border-box;
}

.error-message {
  color: #ef4444;
  font-size: 0.875rem;
  margin-bottom: 1rem;
  text-align: center;
}

.auth-button {
  width: 100%;
  padding: 0.75rem;
  background-color: #4f46e5;
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-weight: 600;
  cursor: pointer;
}

.switch-auth {
  margin-top: 1.5rem;
  text-align: center;
  font-size: 0.875rem;
}

.switch-auth a {
  color: #4f46e5;
  font-weight: 600;
  text-decoration: none;
}
</style> 