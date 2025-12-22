<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden" 
       style="background: linear-gradient(135deg, #0d6efd 0%, #20c997 50%, #fd7e14 100%); opacity: 0.9;">
    <div class="container relative z-10 px-4">
      <div class="flex justify-center">
        <div class="w-full max-w-2xl">
          <div class="bg-white rounded-lg shadow-xl border-0 p-8 md:p-10">
            <div class="text-center mb-8">
              <div class="bg-blue-100 rounded-full inline-flex items-center justify-center mb-4" 
                   style="width: 80px; height: 80px;">
                <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <h2 class="text-3xl font-bold mb-2">Create Admin Account</h2>
              <p class="text-gray-600">Join BoardMate as a property owner or staff member.</p>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-6">
              <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ error }}
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <svg class="w-4 h-4 inline mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Full Name
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter your name"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <svg class="w-4 h-4 inline mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Email Address
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter your email"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <svg class="w-4 h-4 inline mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    Phone Number
                  </label>
                  <input
                    v-model="form.phone"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Enter your phone"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <svg class="w-4 h-4 inline mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Role
                  </label>
                  <select
                    v-model="form.role"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  >
                    <option value="">Select role</option>
                    <option value="owner">Owner</option>
                    <option value="staff">Staff</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <svg class="w-4 h-4 inline mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Password
                  </label>
                  <input
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Create a password"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <svg class="w-4 h-4 inline mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Confirm Password
                  </label>
                  <input
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Confirm your password"
                  />
                </div>
              </div>

              <button
                type="submit"
                :disabled="loading"
                class="w-full bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-4 px-6 rounded-lg shadow-lg transition duration-200 flex items-center justify-center"
              >
                <svg v-if="!loading" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                </svg>
                <span v-if="loading">Creating Account...</span>
                <span v-else>Create Account</span>
              </button>
            </form>

            <div class="text-center mt-6">
              <p class="mb-2">
                <NuxtLink to="/auth/admin/login" class="text-blue-600 hover:text-blue-700 font-semibold no-underline">
                  Already have an account? Login
                </NuxtLink>
              </p>
              <NuxtLink to="/auth/role" class="text-gray-500 hover:text-gray-700 text-sm no-underline">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Home
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Success Modal -->
    <SuccessModal
      v-model:show="showSuccessModal"
      title="Registration Successful!"
      message="Your admin account has been created successfully."
      :loading="true"
      loading-text="Redirecting to dashboard..."
    />
  </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth'
import SuccessModal from '~/components/SuccessModal.vue'

definePageMeta({
  layout: false
})

const auth = useAuth()

const form = ref({
  name: '',
  email: '',
  phone: '',
  role: '',
  password: '',
  password_confirmation: ''
})

const loading = ref(false)
const error = ref('')
const showSuccessModal = ref(false)

const handleRegister = async () => {
  loading.value = true
  error.value = ''
  
  try {
    console.log('Submitting registration with data:', { ...form.value, password: '***', password_confirmation: '***' })
    const result = await auth.adminRegister(form.value)
    console.log('Registration result:', result)
    
    if (result.success) {
      showSuccessModal.value = true
      // Wait a bit for state to settle, then redirect
      await new Promise(resolve => setTimeout(resolve, 2000))
      await navigateTo('/dashboard/admin')
    } else {
      error.value = result.message || 'Registration failed. Please check your information.'
      console.error('Registration failed:', result.message)
    }
  } catch (err) {
    console.error('Registration error:', err)
    // Handle error message extraction
    let errorMessage = 'An error occurred. Please try again.'
    if (err && typeof err === 'object' && 'message' in err) {
      errorMessage = String(err.message) || errorMessage
    }
    error.value = errorMessage
  } finally {
    loading.value = false
  }
}
</script>

