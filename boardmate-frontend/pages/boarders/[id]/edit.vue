<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading...</p>
    </div>

    <div v-else>
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-0">
          <i class="fas fa-edit mr-2"></i>Edit Boarder
        </h1>
        <div class="flex items-center gap-2">
          <NuxtLink
            :to="`/boarders/${route.params.id}`"
            class="bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-eye mr-1"></i>View
          </NuxtLink>
          <NuxtLink
            to="/boarders"
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                  <i class="fas fa-user mr-2 text-blue-600"></i>
                  Full Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-envelope mr-2 text-blue-600"></i>
                  Email Address <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-phone mr-2 text-blue-600"></i>
                  Phone Number <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.phone"
                  type="text"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-birthday-cake mr-2 text-blue-600"></i>
                  Age <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.age"
                  type="number"
                  min="18"
                  max="100"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-calendar mr-2 text-blue-600"></i>
                  Date of Birth
                </label>
                <input
                  v-model="form.date_of_birth"
                  type="date"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-map-marker-alt mr-2 text-blue-600"></i>
                Address
              </label>
              <textarea
                v-model="form.address"
                rows="3"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4">
              <NuxtLink
                to="/boarders"
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
                {{ submitting ? 'Updating...' : 'Update Boarder' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      :show="showSuccessModal"
      title="Boarder Updated Successfully!"
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
  middleware: ['auth', 'admin'],
  layout: 'app'
})

const api = useApi()
const auth = useAuth()
const route = useRoute()

const form = ref({
  boarding_house_id: '',
  name: '',
  email: '',
  phone: '',
  age: '',
  date_of_birth: '',
  address: ''
})

const boardingHouses = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successMessage = ref('Boarder has been updated successfully!')
const errorTitle = ref('Update Failed')
const errorMessage = ref('')

onMounted(async () => {
  // Auth and admin checks are handled by middleware
  await Promise.all([fetchBoarder(), fetchBoardingHouses()])
})

const fetchBoarder = async () => {
  loading.value = true
  try {
    const boarderId = route.params.id
    if (!boarderId) {
      error.value = 'Invalid boarder ID'
      loading.value = false
      return
    }
    
    console.log('Fetching boarder with ID:', boarderId)
    const response = await api.get(`/boarders/${boarderId}`)
    console.log('Boarder API response:', response)
    
    // Handle different response formats
    const boarder = response?.data || response
    
    if (!boarder || !boarder.id) {
      error.value = 'Boarder not found'
      loading.value = false
      return
    }
    
    form.value = {
      boarding_house_id: boarder.boarding_house_id || boarder.boardingHouse?.id || boarder.boarding_house?.id || '',
      name: boarder.name || '',
      email: boarder.email || '',
      phone: boarder.phone || '',
      age: boarder.age || '',
      date_of_birth: boarder.date_of_birth ? boarder.date_of_birth.split('T')[0] : '',
      address: boarder.address || ''
    }
    
    console.log('Form populated:', form.value)
  } catch (err) {
    console.error('Error fetching boarder:', err)
    error.value = 'Failed to load boarder. Please try again.'
  } finally {
    loading.value = false
  }
}

const fetchBoardingHouses = async () => {
  try {
    const response = await api.get('/boarding-houses')
    console.log('Boarding houses API response:', response)
    
    // Handle paginated response from Laravel
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, ... }
      boardingHouses.value = response.data
    } else if (Array.isArray(response)) {
      // Direct array response
      boardingHouses.value = response
    } else {
      boardingHouses.value = []
    }
    
    console.log('Boarding houses loaded:', boardingHouses.value.length)
  } catch (error) {
    console.error('Error fetching boarding houses:', error)
    boardingHouses.value = []
  }
}

const handleSubmit = async () => {
  submitting.value = true
  error.value = ''
  
  try {
    await api.put(`/boarders/${route.params.id}`, form.value)
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo(`/boarders/${route.params.id}`)
    }, 2000)
  } catch (err) {
    console.error('Error updating boarder:', err)
    const errorMsg = err?.message || 'Failed to update boarder. Please try again.'
    
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

