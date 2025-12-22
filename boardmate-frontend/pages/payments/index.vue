<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-5">
      <div>
        <h1 class="text-2xl font-bold mb-2">
          <i class="fas fa-credit-card mr-2" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>Payments
        </h1>
        <p class="text-gray-600 mb-0">{{ isAdmin ? 'Track and manage all payment records' : 'View your payment history' }}</p>
      </div>
      <div class="flex items-center gap-3">
        <NuxtLink
          to="/payments/create"
          :class="isAdmin ? 'bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600' : 'bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600'" class="text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
        >
          <i class="fas fa-plus mr-2"></i>
          {{ isAdmin ? 'Record New Payment' : 'Make Payment' }}
        </NuxtLink>
        <button
          v-if="isAdmin"
          @click="showDeletedModal = true"
          class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
        >
          <i class="fas fa-trash-restore mr-2"></i>View Deleted
        </button>
      </div>
    </div>

    <!-- Contract Summary for Boarders -->
    <div v-if="isBoarder && contracts.length > 0" class="bg-green-50 border-l-4 border-green-500 rounded-xl shadow-md p-6 mb-4">
      <h5 class="font-bold text-green-900 mb-4">
        <i class="fas fa-file-contract mr-2"></i>My Contract Summary
      </h5>
      <div class="space-y-4">
        <div
          v-for="contract in contracts"
          :key="contract.id"
          class="bg-white p-4 rounded-lg"
        >
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div>
              <strong>Contract #{{ contract.id }}</strong>
            </div>
            <div>
              <strong>Rent Amount:</strong> ₱{{ formatCurrency(contract.rent_amount) }}
            </div>
            <div>
              <strong>Duration:</strong> {{ formatDate(contract.start_date) }} - {{ formatDate(contract.end_date) }}
            </div>
            <div>
              <strong>Status:</strong>
              <span :class="getStatusBadgeClass(contract.status)" class="ml-2">
                {{ contract.status }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl mb-4" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>
        <p class="text-gray-600">Loading payments...</p>
      </div>

      <div v-else-if="payments.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <thead :class="isAdmin ? 'bg-gradient-to-r from-blue-600 to-teal-500' : 'bg-gradient-to-r from-green-600 to-teal-500'" class="text-white">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">ID</th>
              <th v-if="isAdmin" class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Boarder</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Contract</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Amount</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Payment Date</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Type</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="payment in payments" :key="payment.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ payment.id }}</td>
              <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ payment.boarder?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                Contract #{{ payment.contract?.id || 'N/A' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">₱{{ formatCurrency(payment.amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(payment.payment_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span v-if="payment.payment_type" class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                  {{ payment.payment_type }}
                </span>
                <span v-else class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                  N/A
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getPaymentStatusBadgeClass(payment.status)">
                  {{ payment.status === 'completed' ? 'Completed' : payment.status === 'cancelled' ? 'Cancelled' : payment.status === 'failed' ? 'Failed' : payment.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center gap-2">
                  <NuxtLink
                    :to="`/payments/${payment.id}`"
                    :class="isAdmin ? 'bg-blue-50 hover:bg-blue-100 text-blue-600' : 'bg-green-50 hover:bg-green-100 text-green-600'" class="p-2 rounded-lg transition inline-flex items-center justify-center"
                    style="min-width: 36px; min-height: 36px;"
                    title="View"
                  >
                    <i class="fas fa-eye" style="font-size: 14px;"></i>
                  </NuxtLink>
                  <button
                    v-if="isAdmin && payment.status === 'pending'"
                    @click="handleApprove(payment.id)"
                    class="bg-green-50 hover:bg-green-100 text-green-600 p-2 rounded-lg transition inline-flex items-center justify-center"
                    style="min-width: 36px; min-height: 36px; border: none; cursor: pointer;"
                    title="Approve"
                  >
                    <i class="fas fa-check" style="font-size: 14px;"></i>
                  </button>
                  <button
                    v-if="isAdmin && payment.status === 'pending'"
                    @click="openRejectModal(payment)"
                    class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-lg transition inline-flex items-center justify-center"
                    style="min-width: 36px; min-height: 36px; border: none; cursor: pointer;"
                    title="Reject"
                  >
                    <i class="fas fa-times" style="font-size: 14px;"></i>
                  </button>
                  <NuxtLink
                    v-if="isAdmin"
                    :to="`/payments/${payment.id}/edit`"
                    class="bg-yellow-50 hover:bg-yellow-100 text-yellow-600 p-2 rounded-lg transition inline-flex items-center justify-center"
                    style="min-width: 36px; min-height: 36px;"
                    title="Edit"
                  >
                    <i class="fas fa-edit" style="font-size: 14px;"></i>
                  </NuxtLink>
                  <button
                    v-if="isAdmin"
                    @click="handleDelete(payment)"
                    class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-lg transition inline-flex items-center justify-center"
                    style="min-width: 36px; min-height: 36px; border: none; cursor: pointer;"
                    title="Delete"
                  >
                    <i class="fas fa-trash" style="font-size: 14px;"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-12">
        <i class="fas fa-credit-card text-5xl text-gray-400 mb-4"></i>
        <h4 class="text-xl font-semibold text-gray-600 mb-2">No data yet</h4>
      </div>
    </div>
    
    <!-- Reject Modal -->
    <RejectModal
      v-model:show="showRejectModal"
      title="Reject Payment"
      message="Are you sure you want to reject this payment?"
      :details="selectedPayment ? `Amount: ₱${formatCurrency(selectedPayment.amount)}<br>Contract: #${selectedPayment.contract?.id || 'N/A'}<br>Boarder: ${selectedPayment.boarder?.name || 'N/A'}` : ''"
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

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-model:show="showDeleteModal"
      title="Delete Payment"
      message="Are you sure you want to delete this payment?"
      variant="danger"
      confirm-text="Delete"
      @confirm="confirmDelete"
    />

    <!-- Deleted Payments Modal -->
    <DeletedPaymentsModal
      v-model:show="showDeletedModal"
      @restored="fetchPayments"
    />
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import ConfirmModal from '~/components/ConfirmModal.vue'
import RejectModal from '~/components/RejectModal.vue'
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'
import DeletedPaymentsModal from '~/components/DeletedPaymentsModal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()

const payments = ref([])
const contracts = ref([])
const loading = ref(true)
const showRejectModal = ref(false)
const showDeleteModal = ref(false)
const showDeletedModal = ref(false)
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const selectedPayment = ref(null)
const selectedPaymentForDelete = ref(null)
const successTitle = ref('')
const successMessage = ref('')
const errorTitle = ref('')
const errorMessage = ref('')

// Check if user is admin - handle both ref and direct access
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

// Check if user is boarder
const isBoarder = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'boarder'
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchPayments()
  if (isBoarder.value) {
    await fetchContracts()
  }
})

const fetchPayments = async () => {
  loading.value = true
  try {
    const response = await api.get('/payments')
    
    // Handle paginated response from Laravel
    let allPayments = []
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, ... }
      allPayments = response.data
    } else if (Array.isArray(response)) {
      // Direct array response
      allPayments = response
    } else {
      allPayments = []
    }
    
    // Frontend filtering as safety measure - ensure boarders only see their own payments
    const user = auth.user?.value || auth.user
    if (user && user.type === 'boarder' && !isAdmin.value) {
      payments.value = allPayments.filter(payment => {
        // Check if payment belongs to boarder's contract
        const paymentBoarderId = payment.boarder_id || payment.boarder?.id
        const contractBoarderId = payment.contract?.boarder_id || payment.contract?.boarder?.id
        return paymentBoarderId === user.id || contractBoarderId === user.id
      })
    } else {
      payments.value = allPayments
    }
  } catch (error) {
    console.error('Error fetching payments:', error)
    payments.value = []
  } finally {
    loading.value = false
  }
}

const fetchContracts = async () => {
  try {
    const response = await api.get('/contracts')
    const allContracts = Array.isArray(response) ? response : (response.data || [])
    // Filter to only Active contracts for the contract summary
    contracts.value = allContracts.filter(c => c.status === 'Active')
  } catch (error) {
    console.error('Error fetching contracts:', error)
    contracts.value = []
  }
}

const handleDelete = (payment) => {
  selectedPaymentForDelete.value = payment
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!selectedPaymentForDelete.value) return
  
  try {
    await api.delete(`/payments/${selectedPaymentForDelete.value.id}`)
    await fetchPayments()
    showDeleteModal.value = false
    selectedPaymentForDelete.value = null
    successTitle.value = 'Payment Deleted'
    successMessage.value = 'Payment has been deleted successfully.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error deleting payment:', error)
    errorTitle.value = 'Delete Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to delete payment. Please try again.'
    showErrorModal.value = true
  }
}

const handleApprove = async (id) => {
  try {
    await api.post(`/payments/${id}/approve`)
    await fetchPayments()
    successTitle.value = 'Payment Approved'
    successMessage.value = 'Payment has been approved successfully.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error approving payment:', error)
    errorTitle.value = 'Approval Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to approve payment. Please try again.'
    showErrorModal.value = true
  }
}

const openRejectModal = (payment) => {
  selectedPayment.value = payment
  showRejectModal.value = true
}

const handleReject = async (reason) => {
  if (!selectedPayment.value) return
  
  try {
    await api.post(`/payments/${selectedPayment.value.id}/reject`, {
      rejection_reason: reason || null
    })
    await fetchPayments()
    selectedPayment.value = null
    successTitle.value = 'Payment Rejected'
    successMessage.value = 'Payment has been rejected successfully.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error rejecting payment:', error)
    errorTitle.value = 'Rejection Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to reject payment. Please try again.'
    showErrorModal.value = true
  }
}

const getPaymentStatusBadgeClass = (status) => {
  const statusLower = status?.toLowerCase() || ''
  if (statusLower === 'completed') {
    return 'bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  } else if (statusLower === 'cancelled' || statusLower === 'failed') {
    return 'bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  } else {
    return 'bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  }
}

const getStatusBadgeClass = (status) => {
  const statusLower = status?.toLowerCase() || ''
  if (statusLower === 'active') {
    return 'bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  } else if (statusLower === 'cancelled') {
    return 'bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  } else {
    return 'bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}
</script>

