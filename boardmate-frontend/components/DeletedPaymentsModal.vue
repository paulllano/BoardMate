<template>
  <Modal :show="show" title="Deleted Payments" @update:show="handleClose" size="large">
    <div v-if="loading" class="text-center py-8">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading deleted payments...</p>
    </div>

    <div v-else-if="deletedPayments.length > 0" class="max-h-96 overflow-y-auto">
      <table class="w-full text-sm">
        <thead class="bg-gradient-to-r from-blue-600 to-teal-500 text-white sticky top-0">
          <tr>
            <th class="px-4 py-3 text-left font-semibold">ID</th>
            <th class="px-4 py-3 text-left font-semibold">Boarder</th>
            <th class="px-4 py-3 text-left font-semibold">Contract</th>
            <th class="px-4 py-3 text-left font-semibold">Amount</th>
            <th class="px-4 py-3 text-left font-semibold">Payment Date</th>
            <th class="px-4 py-3 text-left font-semibold">Status</th>
            <th class="px-4 py-3 text-left font-semibold">Deleted At</th>
            <th class="px-4 py-3 text-left font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="payment in deletedPayments" :key="payment.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-900">{{ payment.original_payment_id || payment.id }}</td>
            <td class="px-4 py-3 text-gray-900">{{ payment.boarder?.name || 'N/A' }}</td>
            <td class="px-4 py-3 text-gray-900">
              <span v-if="payment.contract">Contract #{{ payment.contract.id }}</span>
              <span v-else-if="payment.contract_id">Contract #{{ payment.contract_id }} (Deleted)</span>
              <span v-else>N/A</span>
            </td>
            <td class="px-4 py-3 font-semibold text-green-600">₱{{ formatCurrency(payment.amount) }}</td>
            <td class="px-4 py-3 text-gray-900 text-xs">{{ formatDate(payment.payment_date) }}</td>
            <td class="px-4 py-3">
              <span :class="getPaymentStatusBadgeClass(payment.status)" class="text-xs font-semibold px-2 py-1 rounded-full">
                {{ payment.status === 'completed' ? 'Completed' : payment.status === 'cancelled' ? 'Cancelled' : payment.status === 'failed' ? 'Failed' : payment.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">{{ formatDate(payment.deleted_at) }}</td>
            <td class="px-4 py-3">
              <button
                @click="handleRestore(payment)"
                :disabled="restoring === payment.id"
                class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition duration-200 flex items-center gap-1"
                :class="restoring === payment.id ? 'opacity-50 cursor-not-allowed' : ''"
              >
                <i v-if="restoring === payment.id" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-undo"></i>
                {{ restoring === payment.id ? 'Restoring...' : 'Restore' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="text-center py-8">
      <i class="fas fa-credit-card text-5xl text-gray-400 mb-4"></i>
      <p class="text-gray-600">No deleted payments found.</p>
    </div>

    <template #footer>
      <button
        @click="handleClose"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition duration-200"
      >
        Close
      </button>
    </template>
  </Modal>

  <!-- Success Modal -->
  <SuccessModal
    v-model:show="showSuccessModal"
    title="Success!"
    :message="successMessage"
  />

  <!-- Error Modal -->
  <ErrorModal
    v-model:show="showErrorModal"
    title="Error"
    :message="errorMessage"
  />
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:show', 'restored'])

const api = useApi()
const auth = useAuth()

const deletedPayments = ref([])
const loading = ref(false)
const restoring = ref(null)
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount || 0)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const getPaymentStatusBadgeClass = (status) => {
  const statusMap = {
    'completed': 'bg-green-100 text-green-800',
    'pending': 'bg-yellow-100 text-yellow-800',
    'failed': 'bg-red-100 text-red-800',
    'cancelled': 'bg-gray-100 text-gray-800'
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const fetchDeletedPayments = async () => {
  loading.value = true
  try {
    const response = await api.get('/payments/deleted')
    console.log('Deleted payments response:', response)
    deletedPayments.value = Array.isArray(response) ? response : (response.data || [])
    console.log('Deleted payments after processing:', deletedPayments.value)
  } catch (error) {
    console.error('Error fetching deleted payments:', error)
    console.error('Error details:', error.response || error)
    deletedPayments.value = []
    alert('Failed to load deleted payments: ' + (error.message || 'Unknown error'))
  } finally {
    loading.value = false
  }
}

const handleRestore = async (payment) => {
  // Always use the payment.id which is:
  // - For archived payments: the deleted_payments table id
  // - For admin-deleted payments: the actual payment id
  const paymentId = payment.id
  restoring.value = payment.id
  try {
    const response = await api.post(`/payments/deleted/${paymentId}/restore`)
    if (response.success) {
      // Remove from list
      deletedPayments.value = deletedPayments.value.filter(p => p.id !== payment.id)
      // Emit event to refresh main payments list
      emit('restored')
      // Show success modal with the message from the response
      successMessage.value = response.message || 'Payment restored successfully!'
      showSuccessModal.value = true
      // Auto-close after 2 seconds
      setTimeout(() => {
        showSuccessModal.value = false
      }, 2000)
    }
  } catch (error) {
    console.error('Error restoring payment:', error)
    errorMessage.value = error.response?.data?.message || error.message || 'Failed to restore payment. Please try again.'
    showErrorModal.value = true
  } finally {
    restoring.value = null
  }
}

const handleClose = () => {
  emit('update:show', false)
}

watch(() => props.show, (newVal) => {
  if (newVal) {
    fetchDeletedPayments()
  }
})
</script>
