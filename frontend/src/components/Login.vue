<template>
  <div class="login-container">
    <div class="card shadow">
      <div class="card-body p-5">
        <h2 class="text-center mb-4">Traffic Tracker</h2>
        <p class="text-center text-muted mb-4">Sign in to your dashboard</p>

        <form @submit.prevent="handleLogin">
          <!-- Error Alert -->
          <div v-if="error" class="alert alert-danger" role="alert">
            {{ error }}
          </div>

          <!-- Username Field -->
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input
              type="text"
              class="form-control"
              id="username"
              v-model="username"
              required
              :disabled="loading"
            />
          </div>

          <!-- Password Field -->
          <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input
              type="password"
              class="form-control"
              id="password"
              v-model="password"
              required
              :disabled="loading"
            />
          </div>

          <!-- Submit Button -->
          <button type="submit" class="btn btn-primary w-100" :disabled="loading">
            <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import authService from '@/services/auth';

export default {
  name: 'Login',
  setup() {
    const router = useRouter();
    const username = ref('');
    const password = ref('');
    const loading = ref(false);
    const error = ref('');

    const handleLogin = async () => {
      error.value = '';
      loading.value = true;

      try {
        const result = await authService.login(username.value, password.value);

        if (result.success) {
          router.push('/');
        } else {
          error.value = result.error;
        }
      } catch (err) {
        error.value = 'An unexpected error occurred. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    return {
      username,
      password,
      loading,
      error,
      handleLogin,
    };
  },
};
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 20px;
}

.card {
  max-width: 400px;
  width: 100%;
  border: none;
  border-radius: 10px;
}
</style>
