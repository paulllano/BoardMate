<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading...</p>
    </div>

    <div v-else>
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-0">
          <i class="fas fa-edit mr-2"></i>Edit Payment
        </h1>
        <div class="flex items-center gap-2">
          <NuxtLink
            :to="`/payments/${route.params.id}`"
            class="bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-eye mr-1"></i>View
          </NuxtLink>
          <NuxtLink
            to="/payments"
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
                  <i class="fas fa-user mr-2 text-blue-600"></i>
                  Boarder <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.boarder_id"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
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
                  <i class="fas fa-file-contract mr-2 text-blue-600"></i>
                  Contract
                </label>
                <select
                  v-model="form.contract_id"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                  <option value="">Select a contract (optional)</option>
                  <option
                    v-for="contract in contracts"
                    :key="contract.id"
                    :value="contract.id"
                  >
                    Contract #{{ contract.id }} - {{ contract.boarder?.name || 'N/A' }}
                  </option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-money-bill-wave mr-2 text-blue-600"></i>
                  Amount <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-600">₱</span>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="w-full pl-8 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  />
                </div>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-calendar mr-2 text-blue-600"></i>
                  Payment Date
                </label>
                <input
                  v-model="form.payment_date"
                  type="date"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-credit-card mr-2 text-blue-600"></i>
                  Payment Method <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.payment_method"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                  <option value="">Select payment method</option>
                  <option value="Cash">Cash</option>
                  <option value="GCash">GCash</option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  <i class="fas fa-tags mr-2 text-blue-600"></i>
                  Payment Type
                </label>
                <select
                  v-model="form.payment_type"
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                  <option value="">Select payment type</option>
                  <option value="full">Full Payment</option>
                  <option value="partial">Partial Payment</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-check-circle mr-2 text-blue-600"></i>
                Status <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.status"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
              >
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
                <option value="cancelled">Cancelled</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-hashtag mr-2 text-blue-600"></i>
                Reference Number
              </label>
              <input
                v-model="form.reference_number"
                type="text"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Enter reference number"
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-sticky-note mr-2 text-blue-600"></i>
                Notes
              </label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Additional notes..."
              ></textarea>
            </div>

            <div class="flex justify-end gap-2 pt-4">
              <NuxtLink
                to="/payments"
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
                {{ submitting ? 'Updating...' : 'Update Payment' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      :show="showSuccessModal"
      title="Payment Updated Successfully!"
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
const route = useRoute()

const boarders = ref([])
const contracts = ref([])

const form = ref({
  boarder_id: '',
  contract_id: '',
  amount: '',
  payment_date: '',
  payment_method: '',
  payment_type: '',
  status: '',
  reference_number: '',
  notes: ''
})

const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successMessage = ref('Payment has been updated successfully!')
const errorTitle = ref('Update Failed')
const errorMessage = ref('')

onMounted(async () => {
  await auth.checkAuth()
  
  // Handle both ref and direct access (user is a readonly ref from useState)
  const user = auth.user?.value || auth.user
  if (!user || user?.type !== 'admin') {
    navigateTo('/dashboard/admin')
    return
  }
  
  await Promise.all([fetchPayment(), fetchBoarders(), fetchContracts()])
})

const fetchPayment = async () => {
  loading.value = true
  error.value = ''
  
  try {
    const paymentId = route.params?.id || route.params.id
    
    if (!paymentId || paymentId === 'undefined' || paymentId === 'null') {
      error.value = 'Invalid payment ID. Please check the URL.'
      loading.value = false
      return
    }
    
    console.log('Edit page - Fetching payment with ID:', paymentId)
    const response = await api.get(`/payments/${paymentId}`)
    console.log('Payment API response:', response)
    
    // Handle different response formats
    let payment = null
    if (response && typeof response === 'object') {
      // If response has a data property (wrapped response)
      if (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
        payment = response.data
      } 
      // If response is the payment object directly (has id property)
      else if (response.id || response.amount || response.contract || response.boarder) {
        payment = response
      }
      // If response is wrapped in another structure, try to extract
      else {
        payment = response
      }
    }
    
    console.log('Extracted payment:', payment)
    
    if (payment && payment.id) {
      form.value = {
        boarder_id: payment.boarder_id || payment.boarder?.id || '',
        contract_id: payment.contract_id || payment.contract?.id || '',
        amount: payment.amount || '',
        payment_date: payment.payment_date ? payment.payment_date.split('T')[0] : '',
        payment_method: payment.payment_method || payment.method || '',
        payment_type: payment.payment_type || '',
        status: payment.status || 'pending',
        reference_number: payment.reference_number || '',
        notes: payment.notes || ''
      }
      console.log('✅ Form populated successfully:', form.value)
    } else {
      error.value = 'Failed to load payment data. Invalid response format.'
      console.error('❌ Invalid payment data structure:', payment)
    }
  } catch (err) {
    console.error('Error fetching payment:', err)
    error.value = err?.message || 'Failed to load payment. Please try again.'
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

const fetchContracts = async () => {
  try {
    const response = await api.get('/contracts')
    // Handle paginated response from Laravel
    if (response && response.data && Array.isArray(response.data)) {
      contracts.value = response.data
    } else if (Array.isArray(response)) {
      contracts.value = response
    } else {
      contracts.value = []
    }
  } catch (error) {
    console.error('Error fetching contracts:', error)
    contracts.value = []
  }
}

const handleSubmit = async () => {
  submitting.value = true
  error.value = ''
  
  try {
    const paymentId = route.params?.id || route.params.id
    await api.put(`/payments/${paymentId}`, form.value)
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo(`/payments/${paymentId}`)
    }, 2000)
  } catch (err) {
    console.error('Error updating payment:', err)
    const errorMsg = err?.message || 'Failed to update payment. Please try again.'
    
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

