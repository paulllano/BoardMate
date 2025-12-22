<template>
  <div>
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading...</p>
    </div>

    <div v-else class="bg-white rounded-xl shadow-md border-0 p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Contract</h1>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
          {{ error }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-user mr-2 text-blue-600"></i>Boarder *
            </label>
            <select
              v-model="form.boarder_id"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Select a boarder</option>
              <option
                v-for="boarder in boarders"
                :key="boarder.id"
                :value="boarder.id"
              >
                {{ boarder.name }} ({{ boarder.email }})
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-building mr-2 text-blue-600"></i>Boarding House *
            </label>
            <select
              v-model="form.boarding_house_id"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Select a boarding house</option>
              <option
                v-for="house in boardingHouses"
                :key="house.id"
                :value="house.id"
              >
                {{ house.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-calendar mr-2 text-blue-600"></i>Start Date *
            </label>
            <input
              v-model="form.start_date"
              type="date"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-calendar mr-2 text-blue-600"></i>End Date *
            </label>
            <input
              v-model="form.end_date"
              type="date"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-money-bill mr-2 text-green-600"></i>Rent Amount *
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600">₱</span>
              <input
                v-model="form.rent_amount"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
              />
            </div>
          </div>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Status
          </label>
          <select
            v-model="form.status"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="Pending">Pending</option>
            <option value="Active">Active</option>
            <option value="Completed">Completed</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </div>

        <div class="flex gap-4">
          <NuxtLink
            :to="`/contracts/${route.params.id}`"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-times mr-2"></i>Cancel
          </NuxtLink>
          <button
            type="submit"
            :disabled="loading"
            class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center ml-auto"
          >
            <i v-if="!loading" class="fas fa-save mr-2"></i>
            <i v-else class="fas fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Updating...' : 'Update Contract' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      v-model:show="showSuccessModal"
      title="Contract Updated Successfully!"
      message="The contract has been updated successfully."
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

const boarders = ref([])
const boardingHouses = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const errorTitle = ref('Error')
const errorMessage = ref('')

const form = ref({
  boarder_id: '',
  boarding_house_id: '',
  start_date: '',
  end_date: '',
  rent_amount: '',
  status: 'Pending'
})

onMounted(async () => {
  await auth.checkAuth()
  
  // Handle both ref and direct access (user is a readonly ref from useState)
  const user = auth.user?.value || auth.user
  if (!user || user?.type !== 'admin') {
    navigateTo('/dashboard/admin')
    return
  }
  
  await Promise.all([fetchContract(), fetchBoarders(), fetchBoardingHouses()])
})

const fetchContract = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const contractId = route.params?.id || route.params.id
    
    if (!contractId || contractId === 'undefined' || contractId === 'null') {
      error.value = 'Invalid contract ID. Please check the URL.'
      loading.value = false
      return
    }
    
    console.log('Edit page - Fetching contract with ID:', contractId)
    const response = await api.get(`/contracts/${contractId}`)
    console.log('Contract API response:', response)
    
    // Handle different response formats
    let contract = null
    if (response && typeof response === 'object') {
      // If response has a data property (wrapped response)
      if (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
        contract = response.data
      } 
      // If response is the contract object directly (has id property)
      else if (response.id || response.boarder_id || response.boarding_house_id) {
        contract = response
      }
      // If response is wrapped in another structure, try to extract
      else {
        contract = response
      }
    }
    
    console.log('Extracted contract:', contract)
    
    if (contract && contract.id) {
      form.value = {
        boarder_id: contract.boarder_id || contract.boarder?.id || '',
        boarding_house_id: contract.boarding_house_id || contract.boardingHouse?.id || contract.boarding_house?.id || '',
        start_date: contract.start_date ? contract.start_date.split('T')[0] : '',
        end_date: contract.end_date ? contract.end_date.split('T')[0] : '',
        rent_amount: contract.rent_amount || '',
        status: contract.status || 'Pending'
      }
      console.log('✅ Form populated successfully:', form.value)
    } else {
      error.value = 'Failed to load contract data. Invalid response format.'
      console.error('❌ Invalid contract data structure:', contract)
    }
  } catch (err) {
    console.error('Error fetching contract:', err)
    error.value = err?.message || 'Failed to load contract. Please try again.'
  } finally {
    loading.value = false
  }
}

const fetchBoarders = async () => {
  try {
    const response = await api.get('/boarders')
    // Handle paginated response from Laravel
    if (response && response.data && Array.isArray(response.data)) {
      boarders.value = response.data
    } else if (Array.isArray(response)) {
      boarders.value = response
    } else {
      boarders.value = []
    }
  } catch (error) {
    console.error('Error fetching boarders:', error)
    boarders.value = []
  }
}

const fetchBoardingHouses = async () => {
  try {
    const response = await api.get('/boarding-houses')
    // Handle paginated response from Laravel
    if (response && response.data && Array.isArray(response.data)) {
      boardingHouses.value = response.data
    } else if (Array.isArray(response)) {
      boardingHouses.value = response
    } else {
      boardingHouses.value = []
    }
  } catch (error) {
    console.error('Error fetching boarding houses:', error)
    boardingHouses.value = []
  }
}

const handleSubmit = async () => {
  submitting.value = true
  error.value = ''
  
  try {
    await api.put(`/contracts/${route.params.id}`, form.value)
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo(`/contracts/${route.params.id}`)
    }, 2000)
  } catch (err) {
    console.error('Error updating contract:', err)
    const errorMsg = err?.message || 'Failed to update contract. Please try again.'
    
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

