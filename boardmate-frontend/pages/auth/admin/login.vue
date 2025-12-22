<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden" style="background: linear-gradient(135deg, #0d6efd 0%, #20c997 50%, #fd7e14 100%); opacity: 0.9;">
    <div class="max-w-md w-full mx-4 relative z-10">
      <div class="bg-white rounded-lg shadow-xl border-0 p-8">
        <div class="text-center mb-6">
          <div class="bg-blue-100 rounded-full inline-flex items-center justify-center mb-4" style="width: 80px; height: 80px;">
            <i class="fas fa-user-shield" style="font-size: 2.5rem; color: #0d6efd; display: block;"></i>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">Admin Login</h2>
          <p class="text-gray-600">Welcome back! Please login to your account.</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-envelope mr-2 text-blue-600"></i>Email Address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter your email"
            />
          </div>
          
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-lock mr-2 text-blue-600"></i>Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              placeholder="Enter your password"
            />
          </div>

          <div class="flex justify-between items-center mb-4">
            <div class="flex items-center">
              <input
                id="remember"
                v-model="form.remember"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <label for="remember" class="ml-2 text-sm text-gray-700">Remember me</label>
            </div>
            <NuxtLink to="/auth/admin/register" class="text-blue-600 hover:text-blue-700 font-semibold text-sm">
              Create Account
            </NuxtLink>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white font-semibold py-3 px-6 rounded-lg shadow-sm transition duration-200 flex items-center justify-center"
          >
            <i class="fas fa-sign-in-alt mr-2"></i>
            <span v-if="loading">Logging in...</span>
            <span v-else>Login</span>
          </button>
        </form>

        <div class="text-center mt-6">
          <NuxtLink to="/auth/role" class="text-gray-600 hover:text-gray-700 text-sm">
            <i class="fas fa-arrow-left mr-1"></i>Back to Home
          </NuxtLink>
        </div>
      </div>
    </div>
    
    <!-- Success Modal -->
    <SuccessModal
      v-model:show="showSuccessModal"
      title="Login Successful!"
      message="Welcome back! Redirecting to your dashboard..."
      :loading="true"
      loading-text="Redirecting..."
    />
    
    <!-- Error Modal -->
    <ErrorModal
      v-model:show="showErrorModal"
      title="Login Failed"
      :message="error || 'Invalid credentials. Please check your email and password and try again.'"
    />
  </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  layout: false
})

useHead({
  link: [
    {
      rel: 'stylesheet',
      href: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
      crossorigin: 'anonymous'
    }
  ]
})

const auth = useAuth()

const form = ref({
  email: '',
  password: '',
  remember: false
})

const loading = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  showErrorModal.value = false
  
  try {
    const result = await auth.adminLogin(form.value.email, form.value.password)
    
    if (result.success) {
      // Wait a moment for auth state to update
      await auth.checkAuth()
      showSuccessModal.value = true
      // Redirect after 2 seconds
      setTimeout(() => {
        navigateTo('/dashboard/admin')
      }, 2000)
    } else {
      error.value = result.message || 'Login failed. Please check your credentials.'
      showErrorModal.value = true
    }
  } catch (err) {
    console.error('Login error:', err)
    error.value = err instanceof Error ? err.message : 'An error occurred. Please try again.'
    showErrorModal.value = true
  } finally {
    loading.value = false
  }
}
</script>
