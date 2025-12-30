<template>
  <div class="container py-6" style="max-width: 800px;">
    <!-- Header Section -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-1">
        <i class="fas fa-user-gear mr-2" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>
        Profile Settings
      </h1>
      <p class="text-gray-500 text-sm">Update your account information</p>
    </div>

    <!-- Form Section -->
    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
      <div class="p-6">
        <form @submit.prevent="handleSubmit" class="space-y-5">
          <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded flex items-start text-sm">
            <i class="fas fa-exclamation-circle mr-2 mt-0.5"></i>
            <span>{{ error }}</span>
          </div>

          <!-- Personal Information -->
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                  Name
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                  placeholder="Enter your name"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                  Email
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                  placeholder="Enter your email"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1.5">
                Phone
              </label>
              <input
                v-model="form.phone"
                type="text"
                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
                placeholder="Enter your phone number"
              />
            </div>
          </div>

          <!-- Password Section -->
          <div class="pt-4 border-t border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">
              New Password
            </label>
            <input
              v-model="form.password"
              type="password"
              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition text-sm"
              placeholder="Leave blank to keep current password"
            />
            <p class="text-xs text-gray-500 mt-1.5">
              Leave blank if you don't want to change your password
            </p>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="flex-1 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-medium py-2.5 px-4 rounded-lg transition duration-200 flex items-center justify-center text-sm"
            >
              <i v-if="!loading" class="fas fa-save mr-2"></i>
              <i v-else class="fas fa-spinner fa-spin mr-2"></i>
              {{ loading ? 'Saving...' : 'Save Changes' }}
            </button>
            <button
              type="button"
              @click="resetForm"
              :disabled="loading"
              class="px-4 py-2.5 border border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-lg transition duration-200 flex items-center justify-center hover:bg-gray-50 text-sm"
            >
              <i class="fas fa-undo mr-2"></i>
              Reset
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      :show="showSuccessModal"
      title="Profile Updated Successfully!"
      message="Your profile has been updated successfully."
      @update:show="showSuccessModal = false"
    />

    <!-- Error Modal -->
    <ErrorModal
      :show="showErrorModal"
      title="Update Failed"
      :message="errorMessage"
      @update:show="showErrorModal = false"
    />
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: ''
})

const loading = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const errorMessage = ref('')

// Computed properties for user info
const user = computed(() => {
  return auth.user?.value || auth.user
})

const isAdmin = computed(() => {
  return user.value?.type === 'admin'
})

// Store initial form values for reset
const initialForm = ref({
  name: '',
  email: '',
  phone: '',
  password: ''
})

onMounted(async () => {
  await auth.checkAuth()
  // Handle both ref and direct access
  const currentUser = auth.user?.value || auth.user
  if (currentUser) {
    form.value = {
      name: currentUser.name || '',
      email: currentUser.email || '',
      phone: currentUser.phone || '',
      password: ''
    }
    // Store initial values for reset
    initialForm.value = { ...form.value }
  }
})

const resetForm = () => {
  form.value = {
    name: initialForm.value.name,
    email: initialForm.value.email,
    phone: initialForm.value.phone,
    password: ''
  }
  error.value = ''
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const data = { ...form.value }
    if (!data.password) {
      delete data.password
    }
    
    const response = await api.put('/settings/profile', data)
    showSuccessModal.value = true
    
    // Refresh user data
    await auth.checkAuth()
    
    // Update form with latest user data (including phone)
    const currentUser = auth.user?.value || auth.user
    if (currentUser) {
      form.value = {
        name: currentUser.name || '',
        email: currentUser.email || '',
        phone: currentUser.phone || '',
        password: ''
      }
      // Update initial form values
      initialForm.value = {
        name: form.value.name,
        email: form.value.email,
        phone: form.value.phone,
        password: ''
      }
    }
    
    // Clear password field
    form.value.password = ''
  } catch (err) {
    console.error('Error updating profile:', err)
    const errorMsg = err?.message || 'Failed to update profile. Please try again.'
    
    // Handle validation errors
    if (err?.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      errorMessage.value = errors.join(', ')
    } else {
      errorMessage.value = err?.response?.data?.message || errorMsg
    }
    
    showErrorModal.value = true
    error.value = errorMessage.value
  } finally {
    loading.value = false
  }
}
</script>


