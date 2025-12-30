<template>
  <div class="container py-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold mb-0">
        <i class="fas fa-concierge-bell mr-2"></i>Create Service
      </h1>
      <NuxtLink to="/services" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center">
        <i class="fas fa-arrow-left mr-1"></i>Back to Services
      </NuxtLink>
    </div>
    
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div class="p-6">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded">
            {{ error }}
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-building mr-2 text-blue-600"></i>
              Boarding House <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.boarding_house_id"
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
            >
              <option value="">Select a boarding house</option>
              <option v-for="house in boardingHouses" :key="house.id" :value="house.id">
                {{ house.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-concierge-bell mr-2 text-blue-600"></i>
              Service Name <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              placeholder="Enter service name"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-align-left mr-2 text-blue-600"></i>
              Description <span class="text-red-500">*</span>
            </label>
            <textarea
              v-model="form.description"
              rows="4"
              required
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              placeholder="Enter service description"
            ></textarea>
          </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-money-bill-wave mr-2 text-blue-600"></i>
                Price <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600">₱</span>
                <input
                  v-model="form.price"
                  type="number"
                  step="0.01"
                  required
                  class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-tags mr-2 text-blue-600"></i>
                Category <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.category"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              >
                <option value="">Select Category</option>
                <option value="cleaning">Cleaning</option>
                <option value="maintenance">Maintenance</option>
                <option value="security">Security</option>
                <option value="utilities">Utilities</option>
                <option value="food">Food</option>
                <option value="transportation">Transportation</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-check-circle mr-2 text-blue-600"></i>
                Availability <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.availability"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              >
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
                <option value="limited">Limited</option>
              </select>
            </div>
          </div>

          <div>
            <label class="flex items-center">
              <input
                v-model="form.is_recurring"
                type="checkbox"
                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
              />
              <span class="ml-2 text-sm text-gray-700">
                <i class="fas fa-redo mr-1 text-blue-600"></i>
                This is a recurring service
              </span>
            </label>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-sticky-note mr-2 text-blue-600"></i>
              Additional Notes
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              placeholder="Enter additional notes..."
            ></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <NuxtLink
              to="/services"
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
              {{ loading ? 'Creating...' : 'Create Service' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      v-model:show="showSuccessModal"
      title="Service Created Successfully!"
      message="The service has been created successfully."
    />

    <!-- Error Modal -->
    <ErrorModal
      v-model:show="showErrorModal"
      :title="errorTitle"
      :message="errorMessage"
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

const boardingHouses = ref([])
const loading = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const errorTitle = ref('Error')
const errorMessage = ref('')

const form = ref({
  boarding_house_id: '',
  name: '',
  description: '',
  price: '',
  category: '',
  availability: 'available',
  is_recurring: false,
  notes: ''
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchBoardingHouses()
})

const fetchBoardingHouses = async () => {
  try {
    const response = await api.get('/boarding-houses')
    boardingHouses.value = Array.isArray(response) ? response : (response.data || [])
  } catch (error) {
    console.error('Error fetching boarding houses:', error)
    boardingHouses.value = []
  }
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  
  try {
    await api.post('/services', form.value)
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo('/services')
    }, 2000)
  } catch (err) {
    console.error('Error creating service:', err)
    const errorMsg = err?.message || 'Failed to create service. Please try again.'
    
    // Handle validation errors
    if (err?.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      errorMessage.value = errors.join(', ')
    } else {
      errorMessage.value = err?.response?.data?.message || errorMsg
    }
    
    errorTitle.value = 'Create Failed'
    showErrorModal.value = true
    error.value = errorMessage.value
  } finally {
    loading.value = false
  }
}
</script>

