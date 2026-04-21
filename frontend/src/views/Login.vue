<template>
  <div class="login-container">
    <div class="login-visual">
      <div class="visual-content">
        <h1 class="visual-title">Le Moqufe</h1>
        <p class="visual-subtitle"> Permettre aux utilisateurs de trouver et réserver facilement des artisans fiables (plombiers, électriciens, entretien de piscine ou jardin, etc.) au Maroc.
</p>
      </div>
    </div>

    <div class="login-form-wrapper">
      <div class="form-header">
        <h1>Welcome Back</h1>
        <p>Please sign in to your artisan account.</p>
      </div>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input v-model="email" type="email" class="form-input" placeholder="name@example.com" required />
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-wrapper">
            <input v-model="password" :type="showPassword ? 'text' : 'password'" class="form-input" placeholder="••••••••" required />
            <button type="button" class="toggle-password" @click="showPassword = !showPassword">
              <span>{{ showPassword ? 'Hide' : 'Show' }}</span>
            </button>
          </div>
        </div>

        <button type="submit" class="btn-submit" :disabled="isLoading">
          <span v-if="isLoading" class="spinner"></span>
          <span v-else>Sign In</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const email = ref("");
const password = ref("");
const isLoading = ref(false);
const showPassword = ref(false);
const router = useRouter();

const submit = async () => {
  isLoading.value = true;
  
  try {
    console.log("Login Attempt:", { email: email.value, password: password.value });

    await new Promise(r => setTimeout(r, 1000))

    router.push("/");

  } catch (error) {
    alert("Login failed!");
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
.login-container {
    display: flex;
    width: 100%;
    max-width: 1000px;
    height: 600px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    overflow: hidden;
    margin: 2rem auto;
}

.login-visual { flex: 1; background-color: #5D4037; display: flex; align-items: center; justify-content: center; color: white; padding: 2rem; }
.login-form-wrapper { flex: 1; padding: 3rem; display: flex; flex-direction: column; justify-content: center; }

.form-group { margin-bottom: 1.5rem; }
.form-input { width: 100%; padding: 0.8rem; border: 1px solid #BCAAA4; border-radius: 8px; }
.btn-submit { width: 100%; padding: 1rem; background-color: #5D4037; color: white; border: none; border-radius: 8px; cursor: pointer; }
.spinner { border: 2px solid #fff; border-top: 2px solid transparent; border-radius: 50%; width: 16px; height: 16px; animation: spin 0.8s linear infinite; }

@keyframes spin { to { transform: rotate(360deg); } }
</style>