<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading...</p>
    </div>

    <div v-else>
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-0">
          <i class="fas fa-edit mr-2"></i>Edit Service
        </h1>
        <div class="flex items-center gap-2">
          <NuxtLink
            :to="`/services/${route.params.id}`"
            class="bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-eye mr-1"></i>View
          </NuxtLink>
          <NuxtLink
            to="/services"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-arrow-left mr-1"></i>Back to List
          </NuxtLink>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
        <div class="p-6">
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded">
              {{ error }}
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
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
                Service Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Description <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.description"
                rows="4"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              ></textarea>
            </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
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
                <span class="ml-2 text-sm text-gray-700">This is a recurring service</span>
              </label>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Additional Notes
              </label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
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
                :disabled="submitting"
                class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
              >
                <i v-if="!submitting" class="fas fa-save mr-1"></i>
                <i v-else class="fas fa-spinner fa-spin mr-1"></i>
                {{ submitting ? 'Updating...' : 'Update Service' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      v-model:show="showSuccessModal"
      title="Service Updated Successfully!"
      message="The service has been updated successfully."
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
const route = useRoute()

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

const boardingHouses = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const errorTitle = ref('Error')
const errorMessage = ref('')

onMounted(async () => {
  await auth.checkAuth()
  await Promise.all([fetchService(), fetchBoardingHouses()])
})

const fetchService = async () => {
  loading.value = true
  try {
    const response = await api.get(`/services/${route.params.id}`)
    // Handle both direct response and wrapped response
    const service = response.data || response
    
    if (service) {
      form.value = {
        boarding_house_id: service.boarding_house_id || service.boardingHouse?.id || '',
        name: service.name || '',
        description: service.description || '',
        price: service.price || '',
        category: service.category || '',
        availability: service.availability || 'available',
        is_recurring: service.is_recurring || false,
        notes: service.notes || ''
      }
    } else {
      error.value = 'Failed to load service data.'
    }
  } catch (err) {
    error.value = 'Failed to load service.'
    console.error('Error fetching service:', err)
  } finally {
    loading.value = false
  }
}

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
  submitting.value = true
  error.value = ''
  
  try {
    await api.put(`/services/${route.params.id}`, form.value)
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo(`/services/${route.params.id}`)
    }, 2000)
  } catch (err) {
    console.error('Error updating service:', err)
    const errorMsg = err?.message || 'Failed to update service. Please try again.'
    
    // Handle validation errors
    if (err?.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      errorMessage.value = errors.join(', ')
    } else {
      errorMessage.value = err?.response?.data?.message || errorMsg
    }
    
    errorTitle.value = 'Update Failed'
    showErrorModal.value = true
    error.value = errorMessage.value
  } finally {
    submitting.value = false
  }
}
</script>

