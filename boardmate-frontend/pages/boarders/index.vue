<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-5">
      <div>
        <h1 class="text-2xl font-bold mb-2">
          <i class="fas fa-users mr-2 text-blue-600"></i>Boarders
        </h1>
        <p class="text-gray-600 mb-0">Manage all boarders in your system</p>
      </div>
      <NuxtLink
        to="/boarders/create"
        class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
      >
        <i class="fas fa-plus mr-2"></i>Add New Boarder
      </NuxtLink>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
        <p class="text-gray-600">Loading boarders...</p>
      </div>

      <div v-else-if="boarders.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gradient-to-r from-blue-600 to-teal-500 text-white">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Name</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Email</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Phone</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Boarding House</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Contracts</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="boarder in boarders" :key="boarder.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ boarder.id }}</td>
              <td class="px-6 py-4">
                <div class="text-sm font-semibold text-gray-900">{{ boarder.name }}</div>
                <div v-if="boarder.date_of_birth" class="text-xs text-gray-500 mt-1">
                  Born: {{ formatDate(boarder.date_of_birth) }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ boarder.email }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ boarder.phone || 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ getBoardingHouseName(boarder) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                  {{ boarder.contracts?.length || 0 }} contracts
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center gap-2">
                  <NuxtLink
                    :to="`/boarders/${boarder.id}`"
                    class="bg-blue-50 hover:bg-blue-100 text-blue-600 p-2 rounded-lg transition"
                    title="View"
                  >
                    <i class="fas fa-eye"></i>
                  </NuxtLink>
                  <NuxtLink
                    :to="`/boarders/${boarder.id}/edit`"
                    class="bg-yellow-50 hover:bg-yellow-100 text-yellow-600 p-2 rounded-lg transition"
                    title="Edit"
                  >
                    <i class="fas fa-edit"></i>
                  </NuxtLink>
                  <button
                    @click="handleDelete(boarder)"
                    class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-lg transition"
                    title="Delete"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-12">
        <i class="fas fa-users text-5xl text-gray-400 mb-4"></i>
        <h4 class="text-xl font-semibold text-gray-600 mb-2">No boarders found</h4>
        <p class="text-gray-500 mb-4">Get started by adding your first boarder.</p>
        <NuxtLink
          to="/boarders/create"
          class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 inline-flex items-center"
        >
          <i class="fas fa-plus mr-2"></i>Add First Boarder
        </NuxtLink>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-model:show="showDeleteModal"
      title="Delete Boarder"
      message="Are you sure you want to delete this boarder? This action cannot be undone."
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
  middleware: ['auth', 'admin'],
  layout: 'app'
})

const api = useApi()
const auth = useAuth()

const boarders = ref([])
const loading = ref(true)
const showDeleteModal = ref(false)
const selectedBoarder = ref(null)

// Computed property to check if user is admin
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

onMounted(async () => {
  // Auth and admin checks are handled by middleware
  await fetchBoarders()
})

const fetchBoarders = async () => {
  loading.value = true
  try {
    const response = await api.get('/boarders')
    console.log('Boarders API response:', response)
    
    // Handle paginated response from Laravel
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, ... }
      boarders.value = response.data
    } else if (Array.isArray(response)) {
      // Direct array response
      boarders.value = response
    } else {
      boarders.value = []
    }
    
    console.log('Boarders loaded:', boarders.value.length)
  } catch (error) {
    console.error('Error fetching boarders:', error)
    boarders.value = []
  } finally {
    loading.value = false
  }
}

const handleDelete = (boarder) => {
  selectedBoarder.value = boarder
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!selectedBoarder.value) return
  
  try {
    await api.delete(`/boarders/${selectedBoarder.value.id}`)
    await fetchBoarders()
    showDeleteModal.value = false
    selectedBoarder.value = null
    successTitle.value = 'Boarder Deleted'
    successMessage.value = 'Boarder has been deleted successfully.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error deleting boarder:', error)
    errorTitle.value = 'Delete Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to delete boarder. Please try again.'
    showErrorModal.value = true
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

// Helper function to handle both snake_case and camelCase relationship names
const getBoardingHouseName = (boarder) => {
  // Check both formats
  if (boarder?.boarding_house?.name) {
    return boarder.boarding_house.name
  }
  if (boarder?.boardingHouse?.name) {
    return boarder.boardingHouse.name
  }
  // If no relationship loaded, check if we have boarding_house_id and need to fetch it
  if (boarder?.boarding_house_id && !boarder.boarding_house && !boarder.boardingHouse) {
    return 'Loading...'
  }
  return 'N/A'
}
</script>

