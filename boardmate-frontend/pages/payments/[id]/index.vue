<template>
  <div>
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading payment details...</p>
    </div>

    <div v-else-if="payment" class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-md border-0 p-6">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Payment #{{ payment.id }}</h1>
            <span
              :class="[
                'px-4 py-2 rounded-full text-sm font-semibold inline-block',
                getPaymentStatusClass(payment.status)
              ]"
            >
              {{ payment.status }}
            </span>
          </div>
          <div class="flex items-center gap-3">
            <button
              v-if="isAdmin && payment.status === 'pending'"
              @click="showApproveModal = true"
              class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-check mr-2"></i>Approve
            </button>
            <button
              v-if="isAdmin && payment.status === 'pending'"
              @click="showRejectModal = true"
              class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-times mr-2"></i>Reject
            </button>
            <NuxtLink
              to="/payments"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-arrow-left mr-2"></i>Back
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Payment Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Payment Information</h2>
          <div class="space-y-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Amount</div>
              <div class="text-3xl font-bold text-green-600">₱{{ formatCurrency(payment.amount) }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Payment Date</div>
              <div class="font-semibold text-gray-900">{{ formatDate(payment.payment_date) }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Payment Method</div>
              <div class="font-semibold text-gray-900">{{ payment.payment_method || payment.method || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Payment Type</div>
              <div class="font-semibold text-gray-900">{{ payment.payment_type || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Reference Number</div>
              <div class="font-semibold text-gray-900 font-mono">
                {{ payment.reference_number || payment.referenceNumber || 'Not provided' }}
              </div>
            </div>
            <div v-if="payment.notes">
              <div class="text-sm text-gray-500 mb-1">Notes</div>
              <div class="text-gray-700">{{ payment.notes }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Related Information</h2>
          <div class="space-y-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Contract</div>
              <div class="font-semibold text-gray-900">Contract #{{ payment.contract?.id || 'N/A' }}</div>
              <div v-if="payment.contract" class="text-sm text-gray-600 mt-1">
                Rent: ₱{{ formatCurrency(payment.contract.rent_amount) }}
              </div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Boarder</div>
              <div class="font-semibold text-gray-900">{{ payment.boarder?.name || payment.contract?.boarder?.name || 'N/A' }}</div>
              <div class="text-sm text-gray-600">{{ payment.boarder?.email || payment.contract?.boarder?.email || '' }}</div>
              <div v-if="payment.boarder?.phone || payment.contract?.boarder?.phone" class="text-sm text-gray-600">
                <i class="fas fa-phone mr-1"></i>{{ payment.boarder?.phone || payment.contract?.boarder?.phone }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Approve Modal -->
      <ApproveModal
        v-model:show="showApproveModal"
        title="Approve Payment"
        message="Are you sure you want to approve this payment?"
        :details="`Amount: ₱${formatCurrency(payment.amount)}<br>Contract: #${payment.contract?.id || 'N/A'}<br>Boarder: ${payment.boarder?.name || 'N/A'}`"
        note="The payment will be marked as completed and the boarder will be notified."
        approve-text="Approve Payment"
        @approve="handleApprove"
      />
      
      <!-- Reject Modal -->
      <RejectModal
        v-model:show="showRejectModal"
        title="Reject Payment"
        message="Are you sure you want to reject this payment?"
        :details="`Amount: ₱${formatCurrency(payment.amount)}<br>Contract: #${payment.contract?.id || 'N/A'}<br>Boarder: ${payment.boarder?.name || 'N/A'}`"
        reason-label="Rejection reason (optional)"
        reason-placeholder="Optional: Provide a reason for rejection..."
        reject-text="Reject Payment"
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

    <div v-else class="text-center py-12">
      <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
      <h4 class="text-xl font-semibold text-gray-600 mb-2">Payment Not Found</h4>
      <NuxtLink
        to="/payments"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
      >
        <i class="fas fa-arrow-left mr-2"></i>Back to Payments
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

const payment = ref(null)
const loading = ref(true)
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successTitle = ref('')
const successMessage = ref('')
const errorTitle = ref('')
const errorMessage = ref('')

// Check if user is admin
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchPayment()
})

const fetchPayment = async () => {
  loading.value = true
  try {
    const paymentId = route.params?.id || route.params.id
    
    if (!paymentId || paymentId === 'undefined' || paymentId === 'null') {
      console.error('No payment ID provided in route')
      payment.value = null
      loading.value = false
      return
    }
    
    console.log('Fetching payment with ID:', paymentId)
    const response = await api.get(`/payments/${paymentId}`)
    console.log('Payment API response:', response)
    
    // Handle different response formats
    if (response && typeof response === 'object') {
      // If response has a data property (wrapped response)
      if (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
        payment.value = response.data
      } 
      // If response is the payment object directly (has id property)
      else if (response.id || response.amount || response.contract || response.boarder) {
        payment.value = response
      }
      // If response is wrapped in another structure, try to extract
      else {
        payment.value = response
      }
    } else {
      console.warn('Unexpected response format:', response)
      payment.value = null
    }
    
    console.log('Payment data set:', payment.value)
    
    // Validate that we have payment data
    if (!payment.value || (!payment.value.id && !payment.value.amount && !payment.value.contract && !payment.value.boarder)) {
      console.warn('Payment data appears to be invalid or empty')
    }
  } catch (error) {
    console.error('Error fetching payment:', error)
    payment.value = null
  } finally {
    loading.value = false
  }
}

const handleApprove = async () => {
  try {
    const response = await api.post(`/payments/${route.params.id}/approve`)
    console.log('Payment approved:', response)
    showApproveModal.value = false
    await fetchPayment()
    
    successTitle.value = 'Payment Approved'
    successMessage.value = 'Payment approved successfully! The boarder has been notified.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error approving payment:', error)
    showApproveModal.value = false
    errorTitle.value = 'Approval Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to approve payment'
    showErrorModal.value = true
  }
}

const handleReject = async (reason) => {
  try {
    const response = await api.post(`/payments/${route.params.id}/reject`, {
      rejection_reason: reason || null
    })
    console.log('Payment rejected:', response)
    showRejectModal.value = false
    await fetchPayment()
    
    successTitle.value = 'Payment Rejected'
    successMessage.value = 'Payment rejected successfully. The boarder has been notified.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error rejecting payment:', error)
    showRejectModal.value = false
    errorTitle.value = 'Rejection Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to reject payment'
    showErrorModal.value = true
  }
}

const getPaymentStatusClass = (status) => {
  const classes = {
    paid: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800'
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}
</script>

