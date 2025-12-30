<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-5">
      <div>
        <h1 class="text-2xl font-bold mb-2">
          <i class="fas fa-building mr-2 text-blue-600"></i>Boarding Houses
        </h1>
        <p class="text-gray-600 mb-0">View and manage all boarding houses</p>
      </div>
      <NuxtLink
        v-if="isAdmin"
        to="/boarding-houses/create"
        class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
      >
        <i class="fas fa-building mr-2"></i>Add New Boarding House
      </NuxtLink>
    </div>

    <!-- Grid Card Layout -->
      <div v-if="loading" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
        <p class="text-gray-600">Loading boarding houses...</p>
      </div>

    <div v-else-if="houses.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div
        v-for="house in houses"
        :key="house.id"
        class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
      >
        <!-- Card Header with Gradient -->
        <div class="bg-gradient-to-r from-blue-600 to-teal-500 p-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-white font-bold text-lg truncate max-w-[200px]" :title="house.name">
                {{ house.name }}
              </h3>
              <div class="flex items-center gap-2 mt-1">
                <p class="text-white text-opacity-80 text-xs">ID: {{ house.id }}</p>
                <span
                  v-if="house.gender_preference"
                  :class="{
                    'bg-blue-100 text-blue-800': house.gender_preference === 'male',
                    'bg-pink-100 text-pink-800': house.gender_preference === 'female',
                    'bg-purple-100 text-purple-800': house.gender_preference === 'everyone'
                  }"
                  class="inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full"
                >
                  <i
                    v-if="house.gender_preference === 'male'"
                    class="fas fa-mars mr-1"
                  ></i>
                  <i
                    v-else-if="house.gender_preference === 'female'"
                    class="fas fa-venus mr-1"
                  ></i>
                  <i
                    v-else
                    class="fas fa-venus-mars mr-1"
                  ></i>
                  {{ house.gender_preference === 'male' ? 'Male Only' : house.gender_preference === 'female' ? 'Female Only' : 'Everyone' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Card Body -->
        <div class="p-5">
          <!-- Description -->
          <div v-if="house.description" class="mb-4">
            <p class="text-sm text-gray-600" style="display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              {{ house.description }}
            </p>
          </div>

          <!-- Address -->
          <div class="mb-4">
            <div class="flex items-start">
              <i class="fas fa-map-marker-alt text-gray-400 mr-2 mt-1"></i>
              <p class="text-sm text-gray-700 flex-1">{{ house.address }}</p>
            </div>
          </div>

          <!-- Admin -->
          <div class="mb-4">
            <div class="flex items-center">
              <i class="fas fa-user-shield text-gray-400 mr-2"></i>
              <p class="text-sm text-gray-700">
                <span class="font-semibold">Admin:</span> {{ house.admin?.name || 'N/A' }}
              </p>
            </div>
                </div>

          <!-- Boarders Count Badge -->
          <div class="mb-4">
            <span class="inline-flex items-center bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1.5 rounded-full">
              <i class="fas fa-users mr-2"></i>
                  {{ house.boarders_count || house.boarders?.length || 0 }} boarders
                </span>
          </div>

          <!-- Action Buttons -->
          <div v-if="isAdmin" class="flex items-center gap-2 pt-4 border-t border-gray-200">
                  <NuxtLink
                    :to="`/boarding-houses/${house.id}`"
              class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-600 p-2.5 rounded-lg transition text-center font-semibold text-sm"
              title="View Details"
                  >
              <i class="fas fa-eye mr-1"></i>View
                  </NuxtLink>
                  <NuxtLink
                    :to="`/boarding-houses/${house.id}/edit`"
              class="flex-1 bg-yellow-50 hover:bg-yellow-100 text-yellow-600 p-2.5 rounded-lg transition text-center font-semibold text-sm"
                    title="Edit"
                  >
              <i class="fas fa-edit mr-1"></i>Edit
                  </NuxtLink>
                  <button
                    @click="handleDelete(house)"
              class="bg-red-50 hover:bg-red-100 text-red-600 p-2.5 rounded-lg transition font-semibold text-sm"
              style="min-width: 48px; border: none; cursor: pointer;"
                    title="Delete"
                  >
              <i class="fas fa-trash"></i>
                  </button>
                </div>
        </div>
      </div>
      </div>

    <div v-else class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div class="text-center py-12">
        <i class="fas fa-building text-5xl text-gray-400 mb-4"></i>
        <h4 class="text-xl font-semibold text-gray-600 mb-2">No boarding houses found</h4>
        <p class="text-gray-500 mb-4">
          <span v-if="isAdmin">Get started by creating your first boarding house.</span>
          <span v-else>Please check back later or contact an owner.</span>
        </p>
        <NuxtLink
          v-if="isAdmin"
          to="/boarding-houses/create"
          class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 inline-flex items-center"
        >
          <i class="fas fa-building mr-2"></i>Create First Boarding House
        </NuxtLink>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-model:show="showDeleteModal"
      title="Delete Boarding House"
      message="Are you sure you want to delete this boarding house? This action cannot be undone."
      variant="danger"
      confirm-text="Delete"
      @confirm="confirmDelete"
    />

    <!-- Success Modal -->
    <SuccessModal
      v-model:show="showSuccessModal"
      :title="successTitle"
      :message="successMessage"
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
import ConfirmModal from '~/components/ConfirmModal.vue'
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()
const route = useRoute()

const houses = ref([])
const loading = ref(true)
const showDeleteModal = ref(false)
const selectedHouse = ref(null)
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successTitle = ref('Success')
const successMessage = ref('')
const errorTitle = ref('Error')
const errorMessage = ref('')

// Check if user is admin - handle both ref and direct access
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchHouses()
})

const fetchHouses = async () => {
  loading.value = true
  try {
    const response = await api.get('/boarding-houses')
    console.log('Boarding houses API response:', response) // Debug log
    
    // Handle paginated response (Laravel pagination)
    if (response && response.data && Array.isArray(response.data)) {
      houses.value = response.data
      console.log('Using paginated data:', houses.value) // Debug log
    } else if (Array.isArray(response)) {
      houses.value = response
      console.log('Using direct array:', houses.value) // Debug log
    } else {
      houses.value = []
      console.warn('Unexpected response format:', response)
    }
    
    // Log boarders_count for debugging
    houses.value.forEach(house => {
      console.log(`House ${house.id}: boarders_count=${house.boarders_count}, boarders.length=${house.boarders?.length}`)
    })
  } catch (error) {
    console.error('Error fetching boarding houses:', error)
    houses.value = []
  } finally {
    loading.value = false
  }
}

const handleDelete = (house) => {
  selectedHouse.value = house
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!selectedHouse.value) return
  
  try {
    await api.delete(`/boarding-houses/${selectedHouse.value.id}`)
    await fetchHouses()
    showDeleteModal.value = false
    selectedHouse.value = null
    successTitle.value = 'Boarding House Deleted'
    successMessage.value = 'Boarding house has been deleted successfully.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error deleting boarding house:', error)
    errorTitle.value = 'Delete Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to delete boarding house. Please try again.'
    showErrorModal.value = true
  }
}
</script>

