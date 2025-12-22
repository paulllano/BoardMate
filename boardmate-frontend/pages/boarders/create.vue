<template>
  <div class="container py-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold mb-0">
        <i class="fas fa-plus mr-2"></i>Create New Boarder
      </h1>
      <NuxtLink to="/boarders" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center">
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
                Full Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Enter full name"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Email Address <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Enter email address"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Phone Number <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.phone"
                type="text"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Enter phone number"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Age <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.age"
                type="number"
                min="18"
                max="100"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Enter age"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
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
              Address
            </label>
            <textarea
              v-model="form.address"
              rows="3"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              placeholder="Enter address"
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
              :disabled="loading"
              class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
            >
              <i v-if="!loading" class="fas fa-save mr-1"></i>
              <i v-else class="fas fa-spinner fa-spin mr-1"></i>
              {{ loading ? 'Creating...' : 'Create Boarder' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      :show="showSuccessModal"
      title="Boarder Created Successfully!"
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

const boardingHouses = ref([])
const loading = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successMessage = ref('Boarder has been created successfully!')
const errorTitle = ref('Creation Failed')
const errorMessage = ref('')

const form = ref({
  boarding_house_id: '',
  name: '',
  email: '',
  phone: '',
  age: '',
  date_of_birth: '',
  address: ''
})

onMounted(async () => {
  await auth.checkAuth()
  if (auth.user?.type !== 'admin') {
    navigateTo('/dashboard/admin')
    return
  }
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
    await api.post('/boarders', form.value)
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo('/boarders')
    }, 2000)
  } catch (err) {
    console.error('Error creating boarder:', err)
    const errorMsg = err?.message || 'Failed to create boarder. Please try again.'
    
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

