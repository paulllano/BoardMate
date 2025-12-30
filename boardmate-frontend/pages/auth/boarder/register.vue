<template>
  <div class="min-h-screen flex items-center justify-center relative overflow-hidden" 
       style="background: linear-gradient(135deg, #0d6efd 0%, #20c997 50%, #fd7e14 100%); opacity: 0.9;">
    <div class="container relative z-10 px-4">
      <div class="flex justify-center">
        <div class="w-full max-w-2xl">
          <div class="bg-white rounded-lg shadow-xl border-0 p-8 md:p-10">
            <div class="text-center mb-8">
              <div class="bg-green-100 rounded-full inline-flex items-center justify-center mb-4" 
                   style="width: 80px; height: 80px;">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
              </div>
              <h2 class="text-3xl font-bold mb-2">Create Boarder Account</h2>
              <p class="text-gray-600">Join BoardMate and find your perfect home near Caraga State University (CSU).</p>
            </div>

            <form @submit.prevent="handleRegister" class="space-y-6">
              <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                {{ error }}
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user mr-2 text-green-600"></i>
                    Full Name
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Enter your name"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-envelope mr-2 text-green-600"></i>
                    Email Address
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Enter your email"
                  />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-phone mr-2 text-green-600"></i>
                    Phone Number
                  </label>
                  <input
                    v-model="form.phone"
                    type="text"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Enter your phone"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar mr-2 text-green-600"></i>
                    Date of Birth
                  </label>
                  <input
                    v-model="form.date_of_birth"
                    type="date"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-map-marker-alt mr-2 text-green-600"></i>
                  Address
                </label>
                <textarea
                  v-model="form.address"
                  rows="2"
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  placeholder="Enter your address"
                ></textarea>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-venus-mars mr-2 text-green-600"></i>
                  Gender *
                </label>
                <select
                  v-model="form.gender"
                  required
                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                >
                  <option value="">Select your gender</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-green-600"></i>
                    Password
                  </label>
                  <input
                    v-model="form.password"
                    type="password"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Create a password"
                  />
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-lock mr-2 text-green-600"></i>
                    Confirm Password
                  </label>
                  <input
                    v-model="form.password_confirmation"
                    type="password"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Confirm your password"
                  />
                </div>
              </div>

              <button
                type="submit"
                :disabled="loading"
                class="w-full bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-4 px-6 rounded-lg shadow-lg transition duration-200 flex items-center justify-center"
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
                <NuxtLink to="/auth/boarder/login" class="text-green-600 hover:text-green-700 font-semibold no-underline">
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
      message="Your boarder account has been created successfully."
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
  date_of_birth: '',
  address: '',
  gender: '',
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
    const result = await auth.boarderRegister(form.value)
    console.log('Registration result:', result)
    
    if (result.success) {
      showSuccessModal.value = true
      // Wait a bit for state to settle, then redirect
      await new Promise(resolve => setTimeout(resolve, 2000))
      await navigateTo('/dashboard/boarder')
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

