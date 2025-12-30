<template>
  <div>
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl mb-4" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>
      <p class="text-gray-600">Loading application details...</p>
    </div>

    <div v-else-if="application" class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-md border-0 p-6">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Application Details</h1>
            <span
              :class="[
                'px-4 py-2 rounded-full text-sm font-semibold inline-block',
                getStatusClass(application.status)
              ]"
            >
              {{ application.status }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <!-- Transfer Approval Buttons (for previous boarding house admin) - Only show if transfer not yet approved/rejected -->
            <template v-if="isTransferRequestForCurrentAdmin(application) && application.status === 'pending' && !isTransferApproved(application) && !isTransferRejected(application)">
              <button
                @click="handleApproveTransfer"
                class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
              >
                <i class="fas fa-exchange-alt mr-2"></i>Approve Transfer
              </button>
              <button
                @click="showRejectTransferModal = true"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
              >
                <i class="fas fa-times mr-2"></i>Reject Transfer
              </button>
            </template>
            
            <!-- Regular Application Approval Buttons (for new boarding house admin) - Only show if transfer approved or not a transfer, AND status is still pending -->
            <template v-if="isAdmin && application.status === 'pending' && (!isTransferRequest(application) || (isTransferRequest(application) && isTransferApproved(application))) && !isTransferRequestForCurrentAdmin(application)">
            <button
              @click="showApproveModal = true"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-check mr-2"></i>Approve
            </button>
            <button
              @click="showRejectModal = true"
              class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-times mr-2"></i>Reject
            </button>
            </template>
            
            <!-- Waiting Message for New Admin -->
            <span
              v-if="isAdmin && application.status === 'pending' && isTransferRequest(application) && !isTransferApproved(application) && !isTransferRejected(application) && !isTransferRequestForCurrentAdmin(application)"
              class="text-sm text-orange-600 font-semibold"
            >
              Waiting for transfer approval...
            </span>
            
            <!-- Success Message for Previous Admin after Transfer Approval -->
            <span
              v-if="isTransferRequestForCurrentAdmin(application) && isTransferApproved(application) && application.status === 'pending'"
              class="text-sm text-green-600 font-semibold"
            >
              Transfer approved. Waiting for new boarding house admin to review.
            </span>
            
            <NuxtLink
              to="/applications"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-arrow-left mr-2"></i>Back to Applications
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Transfer Status Banner (if transfer request) -->
      <div v-if="isTransferRequest(application)" class="bg-white rounded-xl shadow-md border-0 p-6">
        <div class="flex items-center gap-4">
          <div class="flex-1">
            <h3 class="text-lg font-bold text-gray-900 mb-2">
              <i class="fas fa-exchange-alt mr-2 text-orange-600"></i>Transfer Request
            </h3>
            <p class="text-sm text-gray-600 mb-0">
              <span v-if="isTransferApproved(application)" class="text-green-600 font-semibold">
                <i class="fas fa-check-circle mr-1"></i>Transfer approved by previous boarding house admin
              </span>
              <span v-else-if="isTransferRejected(application)" class="text-red-600 font-semibold">
                <i class="fas fa-times-circle mr-1"></i>Transfer rejected by previous boarding house admin
              </span>
              <span v-else class="text-orange-600 font-semibold">
                <i class="fas fa-clock mr-1"></i>Waiting for transfer approval from previous boarding house admin
              </span>
            </p>
            <p v-if="isTransferRequest(application)" class="text-sm text-gray-500 mt-2 mb-0">
              From: <strong>{{ getPreviousBoardingHouseName(application) }}</strong> → To: <strong>{{ getBoardingHouseName(application) }}</strong>
            </p>
          </div>
        </div>
      </div>

      <!-- Application Info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Boarding House Info -->
        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Boarding House</h2>
          <div class="space-y-3">
            <div>
              <div class="text-sm text-gray-500">Name</div>
              <div class="font-semibold text-gray-900">{{ getBoardingHouseName(application) || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500">Address</div>
              <div class="text-gray-700">{{ getBoardingHouseAddress(application) || 'N/A' }}</div>
            </div>
            <NuxtLink
              v-if="getBoardingHouseId(application)"
              :to="`/boarding-houses/${getBoardingHouseId(application)}`"
              :class="isAdmin ? 'text-blue-600 hover:text-blue-700' : 'text-green-600 hover:text-green-700'" class="text-sm font-semibold inline-flex items-center"
            >
              View Details <i class="fas fa-arrow-right ml-1"></i>
            </NuxtLink>
          </div>
        </div>

        <!-- Applicant Info -->
        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Applicant</h2>
          <div class="space-y-3">
            <div>
              <div class="text-sm text-gray-500">Name</div>
              <div class="font-semibold text-gray-900">{{ application.boarder?.name || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500">Email</div>
              <div class="text-gray-700">{{ application.boarder?.email || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500">Phone</div>
              <div class="text-gray-700">{{ application.boarder?.phone || 'N/A' }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Advance Payment Info -->
      <div v-if="application.advance_payment || application.advancePayment" class="bg-white rounded-xl shadow-md border-0 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">
          <i class="fas fa-money-bill-wave mr-2" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>
          Advance Payment
        </h2>
        <div class="space-y-3">
          <div v-if="application.advance_payment || application.advancePayment">
            <div class="text-sm text-gray-500">Amount</div>
            <div class="font-semibold text-lg text-gray-900">
              ₱{{ formatCurrency((application.advance_payment || application.advancePayment)?.amount) }}
            </div>
          </div>
          <div v-if="application.advance_payment || application.advancePayment">
            <div class="text-sm text-gray-500">Status</div>
            <span 
              :class="{
                'bg-green-100 text-green-800': (application.advance_payment || application.advancePayment)?.status === 'completed',
                'bg-red-100 text-red-800': (application.advance_payment || application.advancePayment)?.status === 'refunded',
                'bg-yellow-100 text-yellow-800': (application.advance_payment || application.advancePayment)?.status === 'pending'
              }"
              class="px-3 py-1 rounded-full text-sm font-semibold"
            >
              {{ (application.advance_payment || application.advancePayment)?.status === 'completed' ? 'Paid' : 
                 (application.advance_payment || application.advancePayment)?.status === 'refunded' ? 'Refunded' : 'Pending' }}
            </span>
          </div>
          <div v-if="application.advance_payment || application.advancePayment">
            <div class="text-sm text-gray-500">Payment Method</div>
            <div class="text-gray-700">{{ (application.advance_payment || application.advancePayment)?.method || 'N/A' }}</div>
          </div>
          <div v-if="(application.advance_payment || application.advancePayment)?.reference_number">
            <div class="text-sm text-gray-500">Reference Number</div>
            <div class="text-gray-700 font-mono">{{ (application.advance_payment || application.advancePayment)?.reference_number }}</div>
          </div>
          <div v-if="application.status === 'rejected' && (application.advance_payment || application.advancePayment)?.status === 'refunded'" 
               class="mt-4 p-4 bg-green-50 border-l-4 border-green-500 rounded">
            <div class="flex items-start">
              <i class="fas fa-check-circle text-green-600 mr-2 mt-0.5"></i>
              <div>
                <strong class="text-green-800">Refund Processed</strong>
                <p class="text-green-700 text-sm mt-1">
                  Advance payment has been automatically refunded due to application rejection.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Policies Acceptance -->
      <div v-if="application.policies_text" class="bg-white rounded-xl shadow-md border-0 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">
          <i class="fas fa-file-contract mr-2" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>
          Policies & Terms
        </h2>
        <div class="mb-4">
          <div class="flex items-center mb-2">
            <span class="text-sm font-semibold text-gray-700 mr-2">Accepted:</span>
            <span 
              :class="application.policies_accepted ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              class="px-3 py-1 rounded-full text-sm font-semibold"
            >
              <i :class="application.policies_accepted ? 'fas fa-check-circle mr-1' : 'fas fa-times-circle mr-1'"></i>
              {{ application.policies_accepted ? 'Yes' : 'No' }}
            </span>
            <span v-if="application.policies_accepted_at" class="text-sm text-gray-500 ml-2">
              on {{ formatDate(application.policies_accepted_at) }}
            </span>
          </div>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded p-4 max-h-64 overflow-y-auto">
          <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ application.policies_text }}</pre>
        </div>
        <p class="text-xs text-gray-500 mt-2">
          <i class="fas fa-info-circle mr-1"></i>
          This is the policy text that was shown and accepted at the time of application.
        </p>
      </div>

      <!-- Message -->
      <div v-if="application.message" class="bg-white rounded-xl shadow-md border-0 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Message from Applicant</h2>
        <p class="text-gray-700 whitespace-pre-wrap">{{ application.message }}</p>
      </div>

      <!-- Application Timeline -->
      <div class="bg-white rounded-xl shadow-md border-0 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Timeline</h2>
        <div class="space-y-4">
          <div class="flex items-start">
            <div :class="isAdmin ? 'bg-blue-100' : 'bg-green-100'" class="rounded-full p-2 mr-4">
              <i class="fas fa-paper-plane" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>
            </div>
            <div>
              <div class="font-semibold text-gray-900">Application Submitted</div>
              <div class="text-sm text-gray-500">{{ formatDate(application.created_at) }}</div>
            </div>
          </div>
          <div v-if="application.reviewed_at" class="flex items-start">
            <div :class="[
              'rounded-full p-2 mr-4',
              application.status === 'approved' ? 'bg-green-100' : 'bg-red-100'
            ]">
              <i :class="[
                'fas',
                application.status === 'approved' ? 'fa-check text-green-600' : 'fa-times text-red-600'
              ]"></i>
            </div>
            <div>
              <div class="font-semibold text-gray-900">
                {{ application.status === 'approved' ? 'Application Approved' : 'Application Rejected' }}
              </div>
              <div class="text-sm text-gray-500">
                {{ formatDate(application.reviewed_at) }}
                <span v-if="application.reviewed_by"> by {{ application.reviewed_by?.name }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Approve Modal -->
      <ApproveModal
        v-model:show="showApproveModal"
        title="Approve Application"
        message="Are you sure you want to approve this application?"
        :details="getApproveModalDetails()"
        :note="getApproveModalNote()"
        approve-text="Approve Application"
        @approve="handleApprove"
      />
      
      <!-- Reject Modal -->
      <RejectModal
        v-model:show="showRejectModal"
        title="Reject Application"
        message="Are you sure you want to reject this application?"
        :details="`Application #${application.id}<br>Boarder: ${getBoarderName(application) || 'N/A'}<br>Boarding House: ${getBoardingHouseName(application) || 'N/A'}`"
        reason-label="Reason for rejection"
        reason-placeholder="Please provide a reason for rejection..."
        reject-text="Reject Application"
        @reject="handleReject"
      />
      
      <!-- Reject Transfer Modal -->
      <RejectModal
        v-model:show="showRejectTransferModal"
        title="Reject Transfer Request"
        message="Are you sure you want to reject this transfer request? This will cancel the application."
        :details="`Application #${application.id}<br>Boarder: ${getBoarderName(application) || 'N/A'}<br>Transferring to: ${getBoardingHouseName(application) || 'N/A'}`"
        reason-label="Reason for rejection"
        reason-placeholder="Please provide a reason for rejection..."
        reject-text="Reject Transfer"
        @reject="handleRejectTransfer"
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

    <div v-else class="text-center py-12">
      <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
      <h4 class="text-xl font-semibold text-gray-600 mb-2">Application Not Found</h4>
      <NuxtLink
        to="/applications"
        :class="isAdmin ? 'bg-blue-600 hover:bg-blue-700' : 'bg-green-600 hover:bg-green-700'" class="text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
      >
        <i class="fas fa-arrow-left mr-2"></i>Back to Applications
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import ApproveModal from '~/components/ApproveModal.vue'
import RejectModal from '~/components/RejectModal.vue'
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()
const route = useRoute()

const application = ref(null)
const loading = ref(true)
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const showRejectTransferModal = ref(false)
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successTitle = ref('Success')
const successMessage = ref('')
const errorTitle = ref('Error')
const errorMessage = ref('')

// Check if user is admin
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

onMounted(async () => {
  await fetchApplication()
})

const fetchApplication = async () => {
  loading.value = true
  try {
    const response = await api.get(`/applications/${route.params.id}`)
    
    // Handle various response formats
    if (response && response.data) {
      application.value = response.data
    } else if (response && response.id) {
      // Direct application object
      application.value = response
    } else {
      application.value = response
    }
    
    console.log('Application data received:', application.value)
  } catch (error) {
    console.error('Error fetching application:', error)
    application.value = null
  } finally {
    loading.value = false
  }
}

const handleApprove = async () => {
  try {
    const response = await api.post(`/applications/${route.params.id}/approve`)
    console.log('Application approved:', response)
    showApproveModal.value = false
    
    // Refresh application data to get updated status
    await fetchApplication()
    
    // Show appropriate success message based on transfer status
    successTitle.value = 'Application Approved'
    if (response.is_transfer && response.previous_boarding_house) {
      successMessage.value = `Application approved! The boarder has been transferred from ${response.previous_boarding_house.name} to ${getBoardingHouseName(application.value) || 'this boarding house'}.`
    } else {
      successMessage.value = 'Application approved successfully! The boarder has been assigned to the boarding house.'
    }
    
    showSuccessModal.value = true
    
    // Auto-close modal after 2 seconds
    setTimeout(() => {
      showSuccessModal.value = false
    }, 2000)
  } catch (error) {
    console.error('Error approving application:', error)
    showApproveModal.value = false
    errorTitle.value = 'Approval Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to approve application'
    showErrorModal.value = true
  }
}

const getApproveModalDetails = () => {
  if (!application.value) return ''
  
  let details = `Application #${application.value.id}<br>Boarder: ${getBoarderName(application.value) || 'N/A'}<br>Boarding House: ${getBoardingHouseName(application.value) || 'N/A'}`
  
  // Check if boarder has a current boarding house (for transfer)
  const boarder = application.value.boarder || application.value.boarder_id
  if (boarder && typeof boarder === 'object' && boarder.boarding_house_id) {
    const currentHouse = boarder.boarding_house || boarder.boarding_house_id
    if (currentHouse && typeof currentHouse === 'object' && currentHouse.name) {
      details += `<br><br><strong>Transfer Notice:</strong><br>Boarder is currently assigned to <strong>${currentHouse.name}</strong>. Approving will transfer them to this boarding house.`
    }
  }
  
  return details
}

const getApproveModalNote = () => {
  if (!application.value) return 'The boarder will be assigned to this boarding house upon approval.'
  
  const boarder = application.value.boarder || application.value.boarder_id
  if (boarder && typeof boarder === 'object' && boarder.boarding_house_id) {
    return 'The boarder will be transferred from their current boarding house and assigned to this one upon approval. The previous landlord will be notified.'
  }
  
  return 'The boarder will be assigned to this boarding house upon approval.'
}

const handleReject = async (reason) => {
  if (!reason || reason.trim() === '') {
    errorTitle.value = 'Validation Error'
    errorMessage.value = 'Please provide a reason for rejection.'
    showErrorModal.value = true
    return
  }
  
  try {
    const response = await api.post(`/applications/${route.params.id}/reject`, {
      admin_notes: reason
    })
    console.log('Application rejected:', response)
    showRejectModal.value = false
    
    // Refresh application data to get updated status
    await fetchApplication()
    
    successTitle.value = 'Application Rejected'
    successMessage.value = 'Application rejected successfully.'
    
    // Add refund message if advance payment was refunded
    if (response.refunded && response.refund_amount) {
      successMessage.value += ` Advance payment of ₱${formatCurrency(response.refund_amount)} has been automatically refunded.`
    }
    
    showSuccessModal.value = true
    
    // Auto-close modal after 2 seconds
    setTimeout(() => {
      showSuccessModal.value = false
    }, 2000)
  } catch (error) {
    console.error('Error rejecting application:', error)
    showRejectModal.value = false
    errorTitle.value = 'Rejection Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to reject application'
    showErrorModal.value = true
  }
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800'
  }
  return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

// Helper functions to handle both snake_case and camelCase relationship names
const getBoardingHouseName = (application) => {
  return application?.boarding_house?.name || application?.boardingHouse?.name || 'N/A'
}

const getBoardingHouseAddress = (application) => {
  return application?.boarding_house?.address || application?.boardingHouse?.address || 'N/A'
}

const getBoardingHouseId = (application) => {
  return application?.boarding_house?.id || application?.boardingHouse?.id || application?.boarding_house_id
}

const getBoarderName = (application) => {
  return application?.boarder?.name || 'N/A'
}

const getPreviousBoardingHouseName = (application) => {
  if (!application || !application.boarder) return 'Previous Boarding House'
  
  const boarder = application.boarder
  return boarder.boarding_house?.name || boarder.boardingHouse?.name || 'Previous Boarding House'
}

const isTransferRequest = (application) => {
  if (!application || !application.boarder) return false
  
  const boarder = application.boarder
  const currentBoardingHouseId = boarder.boarding_house_id || boarder.boardingHouse?.id || boarder.boarding_house?.id
  const applicationBoardingHouseId = application.boarding_house_id || application.boardingHouse?.id
  
  return currentBoardingHouseId && currentBoardingHouseId !== applicationBoardingHouseId
}

const isTransferApproved = (application) => {
  return !!application?.transfer_approved_at
}

const isTransferRejected = (application) => {
  return !!application?.transfer_rejected_at
}

const isTransferRequestForCurrentAdmin = (application) => {
  // Check if current admin owns the boarder's current boarding house
  if (!isAdmin.value) return false
  if (!isTransferRequest(application)) return false
  
  const user = auth.user?.value || auth.user
  if (!user || user.type !== 'admin') return false
  
  const boarder = application.boarder
  if (!boarder) return false
  
  const currentBoardingHouse = boarder.boarding_house || boarder.boardingHouse
  if (!currentBoardingHouse) return false
  
  // Check if current admin owns this boarding house
  return currentBoardingHouse.admin_id === user.id
}

const handleApproveTransfer = async () => {
  try {
    const response = await api.post(`/applications/${route.params.id}/approve-transfer`)
    console.log('Transfer approved:', response)
    
    // Refresh application data to get updated transfer status
    await fetchApplication()
    
    successTitle.value = 'Transfer Approved'
    successMessage.value = 'Transfer request has been approved. The new boarding house admin can now review the application.'
    showSuccessModal.value = true
    
    // Auto-close modal and refresh after 2 seconds
    setTimeout(() => {
      showSuccessModal.value = false
    }, 2000)
  } catch (error) {
    console.error('Error approving transfer:', error)
    errorTitle.value = 'Transfer Approval Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to approve transfer'
    showErrorModal.value = true
  }
}

const handleRejectTransfer = async (reason) => {
  if (!reason || reason.trim() === '') {
    errorTitle.value = 'Validation Error'
    errorMessage.value = 'Please provide a reason for rejection.'
    showErrorModal.value = true
    return
  }
  
  try {
    const response = await api.post(`/applications/${route.params.id}/reject-transfer`, {
      admin_notes: reason
    })
    console.log('Transfer rejected:', response)
    showRejectTransferModal.value = false
    await fetchApplication()
    successTitle.value = 'Transfer Rejected'
    successMessage.value = 'Transfer request has been rejected. The application has been cancelled.'
    showSuccessModal.value = true
    
    // Auto-close modal and refresh after 2 seconds
    setTimeout(() => {
      showSuccessModal.value = false
    }, 2000)
  } catch (error) {
    console.error('Error rejecting transfer:', error)
    showRejectTransferModal.value = false
    errorTitle.value = 'Transfer Rejection Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to reject transfer'
    showErrorModal.value = true
  }
}
</script>

