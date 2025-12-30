<template>
  <div>
    <div class="bg-white rounded-xl shadow-md border-0 p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-900">
          <i class="fas fa-clipboard-list mr-2" :class="auth.user?.type === 'admin' ? 'text-blue-600' : 'text-green-600'"></i>
          {{ auth.user?.type === 'admin' ? 'Applications' : 'My Applications' }}
        </h1>
        <NuxtLink
          v-if="auth.user?.type === 'boarder'"
          to="/applications/create"
          class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-200 flex items-center"
        >
          <i class="fas fa-paper-plane mr-2"></i>New Application
        </NuxtLink>
      </div>

      <!-- Filter Tabs -->
      <div class="flex gap-2 border-b border-gray-200">
        <button
          v-for="status in statusFilters"
          :key="status.value"
          @click="activeFilter = status.value"
          :class="[
            'px-4 py-2 font-semibold transition duration-200 border-b-2',
            activeFilter === status.value
              ? (auth.user?.type === 'admin' ? 'border-blue-600 text-blue-600' : 'border-green-600 text-green-600')
              : 'border-transparent text-gray-600 hover:text-gray-900'
          ]"
        >
          {{ status.label }}
          <span v-if="status.count" class="ml-2 bg-gray-200 text-gray-700 text-xs px-2 py-0.5 rounded-full">
            {{ status.count }}
          </span>
        </button>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl mb-4" :class="auth.user?.type === 'admin' ? 'text-blue-600' : 'text-green-600'"></i>
      <p class="text-gray-600">Loading applications...</p>
    </div>

    <div v-else-if="applications.length > 0" class="space-y-4">
      <div
        v-for="application in applications"
        :key="application.id"
        class="bg-white rounded-xl shadow-md border-0 p-6 hover:shadow-xl transition-all duration-300"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-4 mb-3">
              <div>
              <h3 class="text-lg font-bold text-gray-900">
                  <span v-if="isTransferRequest(application)" class="text-orange-600">
                    <i class="fas fa-exchange-alt mr-1"></i>Transfer Request from {{ getPreviousBoardingHouseName(application) }}
                  </span>
                  <span v-else>
                {{ getBoardingHouseName(application) || 'Boarding House' }}
                  </span>
              </h3>
                <p v-if="isTransferRequest(application)" class="text-sm text-gray-600 mt-1">
                  To: {{ getBoardingHouseName(application) }}
                </p>
              </div>
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  getStatusClass(application.status)
                ]"
              >
                {{ application.status }}
              </span>
              <span
                v-if="isTransferRequest(application) && !isTransferApproved(application) && !isTransferRejected(application)"
                class="px-3 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-800"
              >
                Waiting for Transfer Approval
              </span>
              <span
                v-else-if="isTransferRequest(application) && isTransferApproved(application)"
                class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800"
              >
                Transfer Approved
              </span>
            </div>
            <div class="text-sm text-gray-600 mb-2">
              <i class="fas fa-map-marker-alt mr-2"></i>
              {{ getBoardingHouseAddress(application) || 'N/A' }}
            </div>
            <div class="text-sm text-gray-600 mb-2">
              <i class="fas fa-user mr-2"></i>
              Applicant: {{ getBoarderName(application) || 'N/A' }}
            </div>
            <div class="text-sm text-gray-500">
              <i class="fas fa-calendar mr-2"></i>
              Applied: {{ formatDate(application.created_at) }}
            </div>
          </div>
          <div class="flex gap-2">
            <NuxtLink
              :to="`/applications/${application.id}`"
              :class="auth.user?.type === 'admin' ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700'" class="text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm"
            >
              View Details
            </NuxtLink>
            <!-- Transfer Approval Buttons (for previous boarding house admin) -->
            <template v-if="isTransferRequestForCurrentAdmin(application) && application.status === 'pending' && !isTransferApproved(application) && !isTransferRejected(application)">
              <button
                @click="handleApproveTransfer(application.id)"
                class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm"
              >
                Approve Transfer
              </button>
              <button
                @click="openRejectTransferModal(application)"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm"
              >
                Reject Transfer
              </button>
            </template>
            
            <!-- Regular Application Approval Buttons (for new boarding house admin) -->
            <template v-else-if="auth.user?.type === 'admin' && application.status === 'pending' && (!isTransferRequest(application) || isTransferApproved(application))">
            <button
              @click="handleApprove(application.id)"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm"
                :disabled="isTransferRequest(application) && !isTransferApproved(application)"
            >
              Approve
            </button>
            <button
              @click="openRejectModal(application)"
              class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm"
            >
              Reject
            </button>
            </template>
            
            <!-- Waiting Message for New Admin -->
            <span
              v-if="auth.user?.type === 'admin' && application.status === 'pending' && isTransferRequest(application) && !isTransferApproved(application) && !isTransferRejected(application)"
              class="text-sm text-orange-600 font-semibold"
            >
              Waiting for transfer approval...
            </span>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="bg-white rounded-xl shadow-md border-0 p-12 text-center">
      <i class="fas fa-clipboard-list text-5xl text-gray-400 mb-4"></i>
      <h4 class="text-xl font-semibold text-gray-600 mb-2">No Applications Found</h4>
      <p class="text-gray-500 mb-4">
        {{ activeFilter === 'all' 
          ? 'You haven\'t submitted any applications yet.' 
          : `No ${activeFilter} applications found.` }}
      </p>
      <NuxtLink
        v-if="auth.user?.type === 'boarder'"
        to="/applications/create"
        class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
      >
        <i class="fas fa-paper-plane mr-2"></i>Create Application
      </NuxtLink>
    </div>
    
    <!-- Reject Modal -->
    <RejectModal
      v-model:show="showRejectModal"
      :title="selectedApplication?.isTransferRejection ? 'Reject Transfer Request' : 'Reject Application'"
      :message="selectedApplication?.isTransferRejection ? 'Are you sure you want to reject this transfer request? This will cancel the application.' : 'Are you sure you want to reject this application?'"
      :details="selectedApplication ? `Application #${selectedApplication.id}<br>Boarder: ${selectedApplication.boarder?.name || 'N/A'}<br>Boarding House: ${selectedApplication.boarding_house?.name || 'N/A'}` : ''"
      reason-label="Reason for rejection"
      reason-placeholder="Please provide a reason for rejection..."
      :reject-text="selectedApplication?.isTransferRejection ? 'Reject Transfer' : 'Reject Application'"
      @reject="handleReject"
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
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()
const route = useRoute()

const applications = ref([])
const loading = ref(true)
const activeFilter = ref(route.query.status || 'all')
const showRejectModal = ref(false)
const selectedApplication = ref(null)
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successTitle = ref('Success')
const successMessage = ref('')
const errorTitle = ref('Error')
const errorMessage = ref('')

const statusFilters = computed(() => {
  const filters = [
    { value: 'all', label: 'All', count: null },
    { value: 'pending', label: 'Pending', count: null },
    { value: 'approved', label: 'Approved', count: null },
    { value: 'rejected', label: 'Rejected', count: null }
  ]
  // TODO: Add counts when API supports it
  return filters
})

onMounted(async () => {
  await fetchApplications()
})

const fetchApplications = async () => {
  loading.value = true
  try {
    const query = activeFilter.value !== 'all' ? `?status=${activeFilter.value}` : ''
    const response = await api.get(`/applications${query}`)
    
    // Handle paginated response from Laravel
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, ... }
      applications.value = response.data
    } else if (Array.isArray(response)) {
      // Direct array response
      applications.value = response
    } else {
      // Fallback
      applications.value = []
    }
    
    console.log('Applications fetched:', applications.value.length, 'applications')
  } catch (error) {
    console.error('Error fetching applications:', error)
    applications.value = []
  } finally {
    loading.value = false
  }
}

const handleApprove = async (id) => {
  try {
    await api.post(`/applications/${id}/approve`)
    await fetchApplications()
    successTitle.value = 'Application Approved'
    successMessage.value = 'Application has been approved successfully. The boarder has been assigned to the boarding house.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error approving application:', error)
    errorTitle.value = 'Approval Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to approve application. Please try again.'
    showErrorModal.value = true
  }
}

const openRejectModal = (application) => {
  selectedApplication.value = application
  showRejectModal.value = true
}


const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

// Helper functions to handle both snake_case and camelCase relationship names
const getBoardingHouseName = (application) => {
  return application.boarding_house?.name || application.boardingHouse?.name || 'N/A'
}

const getBoardingHouseAddress = (application) => {
  return application.boarding_house?.address || application.boardingHouse?.address || 'N/A'
}

const getBoarderName = (application) => {
  return application.boarder?.name || 'N/A'
}

const isTransferRequest = (application) => {
  // Check if boarder has a current boarding house different from application boarding house
  const boarder = application.boarder
  if (!boarder) return false
  
  const currentBoardingHouseId = boarder.boarding_house_id || boarder.boardingHouse?.id || boarder.boarding_house?.id
  const applicationBoardingHouseId = application.boarding_house_id || application.boardingHouse?.id
  
  return currentBoardingHouseId && currentBoardingHouseId !== applicationBoardingHouseId
}

const getPreviousBoardingHouseName = (application) => {
  const boarder = application.boarder
  if (!boarder) return 'Previous Boarding House'
  
  return boarder.boarding_house?.name || boarder.boardingHouse?.name || 'Previous Boarding House'
}

const isTransferApproved = (application) => {
  return !!application.transfer_approved_at
}

const isTransferRejected = (application) => {
  return !!application.transfer_rejected_at
}

const isTransferRequestForCurrentAdmin = (application) => {
  // Check if current admin owns the boarder's current boarding house
  if (!auth.user?.value || auth.user?.value?.type !== 'admin') return false
  if (!isTransferRequest(application)) return false
  
  const boarder = application.boarder
  if (!boarder) return false
  
  const currentBoardingHouseId = boarder.boarding_house_id || boarder.boardingHouse?.id || boarder.boarding_house?.id
  const currentBoardingHouse = boarder.boarding_house || boarder.boardingHouse
  
  // Check if current admin owns this boarding house
  return currentBoardingHouse?.admin_id === auth.user.value.id
}

const handleApproveTransfer = async (id) => {
  try {
    await api.post(`/applications/${id}/approve-transfer`)
    await fetchApplications()
    successTitle.value = 'Transfer Approved'
    successMessage.value = 'Transfer request has been approved. The new boarding house admin can now review the application.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error approving transfer:', error)
    errorTitle.value = 'Transfer Approval Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to approve transfer. Please try again.'
    showErrorModal.value = true
  }
}

const openRejectTransferModal = (application) => {
  selectedApplication.value = { ...application, isTransferRejection: true }
  showRejectModal.value = true
}

const handleReject = async (reason) => {
  if (!selectedApplication.value) return
  
  const isTransferRejection = selectedApplication.value.isTransferRejection
  
  try {
    if (isTransferRejection) {
      // Reject transfer
      await api.post(`/applications/${selectedApplication.value.id}/reject-transfer`, {
        admin_notes: reason
      })
      successTitle.value = 'Transfer Rejected'
      successMessage.value = 'Transfer request has been rejected. The application has been cancelled.'
    } else {
      // Regular rejection
      await api.post(`/applications/${selectedApplication.value.id}/reject`, {
        admin_notes: reason
      })
      successTitle.value = 'Application Rejected'
      successMessage.value = 'Application has been rejected successfully.'
    }
    
    await fetchApplications()
    selectedApplication.value = null
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error rejecting:', error)
    errorTitle.value = isTransferRejection ? 'Transfer Rejection Failed' : 'Rejection Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || `Failed to ${isTransferRejection ? 'reject transfer' : 'reject application'}. Please try again.`
    showErrorModal.value = true
  }
}

watch(activeFilter, () => {
  fetchApplications()
})
</script>

