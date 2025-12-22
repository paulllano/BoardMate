<template>
  <div class="container py-4" style="max-width: 720px;">
    <h1 class="text-xl font-semibold mb-4">Profile</h1>

    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div class="p-6">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded">
            {{ error }}
          </div>


          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Name</label>
              <input
                v-model="form.name"
                type="text"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
              <input
                v-model="form.phone"
                type="text"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">New Password</label>
              <input
                v-model="form.password"
                type="password"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Leave blank to keep current password"
              />
            </div>
          </div>

          <div class="pt-4">
            <button
              type="submit"
              :disabled="loading"
              class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
            >
              <i v-if="!loading" class="fas fa-save mr-2"></i>
              <i v-else class="fas fa-spinner fa-spin mr-2"></i>
              {{ loading ? 'Saving...' : 'Save' }}
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

onMounted(async () => {
  await auth.checkAuth()
  // Handle both ref and direct access
  const user = auth.user?.value || auth.user
  if (user) {
    form.value = {
      name: user.name || '',
      email: user.email || '',
      phone: user.phone || '',
      password: ''
    }
  }
})

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  success.value = false
  
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
    const user = auth.user?.value || auth.user
    if (user) {
      form.value = {
        name: user.name || '',
        email: user.email || '',
        phone: user.phone || '',
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


