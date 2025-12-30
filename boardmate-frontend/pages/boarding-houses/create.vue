<template>
  <div class="container py-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold mb-0">
        <i class="fas fa-plus mr-2"></i>Create New Boarding House
      </h1>
      <NuxtLink to="/boarding-houses" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i>Back to List
      </NuxtLink>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div class="p-6">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded">
            {{ error }}
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-user-tie mr-2 text-blue-600"></i>
                Admin <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.admin_id"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              >
                <option value="">Select an admin</option>
                <option v-for="admin in admins" :key="admin.id" :value="admin.id">
                  {{ admin.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-building mr-2 text-blue-600"></i>
                Boarding House Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Enter boarding house name"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
              Address <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.address"
              rows="3"
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              placeholder="Enter address"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-align-left mr-2 text-blue-600"></i>
              Description
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              placeholder="Enter description (optional)"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-venus-mars mr-2 text-blue-600"></i>
              Gender Preference <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.gender_preference"
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            >
              <option value="">Select gender preference</option>
              <option value="male">Male Only</option>
              <option value="female">Female Only</option>
              <option value="everyone">Everyone (Mixed)</option>
            </select>
            <p class="text-sm text-gray-500 mt-1">
              <i class="fas fa-info-circle mr-1"></i>
              Select who can apply to this boarding house. "Everyone" allows both male and female boarders.
            </p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-money-bill-wave mr-2 text-blue-600"></i>
              Advance Payment Amount <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.advance_payment_amount"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              placeholder="0.00"
            />
            <p class="text-sm text-gray-500 mt-1">
              <i class="fas fa-info-circle mr-1"></i>
              Required advance payment amount for reservation (e.g., 2000.00). This will be used as credit toward rent when contract is created.
            </p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-file-contract mr-2 text-blue-600"></i>
              Policies & Terms <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.policies"
              rows="6"
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-y"
              placeholder="Enter house rules, policies, and terms & conditions that boarders must accept when applying..."
            ></textarea>
            <p class="text-sm text-gray-500 mt-1">
              <i class="fas fa-info-circle mr-1"></i>
              Minimum 50 characters. These policies will be shown to boarders when they apply.
            </p>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <NuxtLink
              to="/boarding-houses"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-times mr-1"></i>Cancel
            </NuxtLink>
            <button
              type="submit"
              :disabled="loading"
              class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
            >
              <i v-if="!loading" class="fas fa-save mr-1"></i>
              <i v-else class="fas fa-spinner fa-spin mr-1"></i>
              {{ loading ? 'Creating...' : 'Create Boarding House' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      :show="showSuccessModal"
      title="Boarding House Created Successfully!"
      :message="successMessage"
      @update:show="showSuccessModal = false"
    />

    <!-- Error Modal -->
    <ErrorModal
      :show="showErrorModal"
      :title="errorTitle"
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
  admin_id: '',
  name: '',
  address: '',
  description: '',
  gender_preference: 'everyone',
  advance_payment_amount: 0,
  policies: ''
})

const admins = ref([])
const loading = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successMessage = ref('Boarding house has been created successfully!')
const errorTitle = ref('Creation Failed')
const errorMessage = ref('')

// Check if user is admin - handle both ref and direct access
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

onMounted(async () => {
  // Check auth first
  await auth.checkAuth()
  
  // Wait for user to be available
  let attempts = 0
  while ((!auth.user?.value) && attempts < 10) {
    await new Promise(resolve => setTimeout(resolve, 100))
    attempts++
  }
  
  const user = auth.user?.value
  if (!user || user.type !== 'admin') {
    navigateTo('/dashboard/admin')
    return
  }
  
  // Fetch admins for the dropdown
  try {
    const response = await api.get('/admins')
    // Handle paginated response from Laravel
    let adminsList = []
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, ... }
      adminsList = response.data
    } else if (Array.isArray(response)) {
      // Direct array response
      adminsList = response
    } else if (response && response.data && Array.isArray(response.data)) {
      // Another format with data property
      adminsList = response.data
    }
    admins.value = adminsList
    
    // Auto-select current admin if available
    if (user && (user.id || user.admin_id)) {
      const adminId = user.id || user.admin_id
      // Check if this admin exists in the list
      const adminExists = adminsList.find(a => a.id === adminId)
      if (adminExists) {
        form.value.admin_id = adminId.toString()
      }
    }
  } catch (err) {
    console.error('Error fetching admins:', err)
  }
})

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  
  try {
    await api.post('/boarding-houses', form.value)
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo('/boarding-houses')
    }, 2000)
  } catch (err) {
    console.error('Error creating boarding house:', err)
    const errorMsg = err?.message || 'Failed to create boarding house. Please try again.'
    
    // Handle validation errors
    if (err?.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      errorMessage.value = errors.join(', ')
    } else {
      errorMessage.value = err?.response?.data?.message || errorMsg
    }
    
    errorTitle.value = 'Creation Failed'
    showErrorModal.value = true
    error.value = errorMessage.value
  } finally {
    loading.value = false
  }
}
</script>

