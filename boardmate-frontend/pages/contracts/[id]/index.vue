<template>
  <div>
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading contract details...</p>
    </div>

    <div v-else-if="contract" class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-md border-0 p-6">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Contract #{{ contract.id }}</h1>
            <span
              :class="[
                'px-4 py-2 rounded-full text-sm font-semibold inline-block',
                getStatusClass(contract.status)
              ]"
            >
              {{ contract.status }}
            </span>
          </div>
          <div class="flex gap-2">
            <NuxtLink
              v-if="auth.user?.type === 'admin'"
              :to="`/contracts/${contract.id}/edit`"
              class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-edit mr-2"></i>Edit
            </NuxtLink>
            <NuxtLink
              to="/contracts"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-arrow-left mr-2"></i>Back
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Contract Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Contract Information</h2>
          <div class="space-y-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Rent Amount</div>
              <div class="text-2xl font-bold text-green-600">₱{{ formatCurrency(contract.rent_amount) }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Start Date</div>
              <div class="font-semibold text-gray-900">{{ formatDate(contract.start_date) }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">End Date</div>
              <div class="font-semibold text-gray-900">{{ formatDate(contract.end_date) }}</div>
            </div>
            <div v-if="contract.terms">
              <div class="text-sm text-gray-500 mb-1">Terms</div>
              <div class="text-gray-700 whitespace-pre-wrap">{{ contract.terms }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Parties</h2>
          <div class="space-y-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Boarder</div>
              <div class="font-semibold text-gray-900">{{ contract.boarder?.name || 'N/A' }}</div>
              <div class="text-sm text-gray-600">{{ contract.boarder?.email || '' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Boarding House</div>
              <div class="font-semibold text-gray-900">{{ contract.boarding_house?.name || contract.boardingHouse?.name || 'N/A' }}</div>
              <div class="text-sm text-gray-600">{{ contract.boarding_house?.address || contract.boardingHouse?.address || '' }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Advance Payment Credit Breakdown -->
      <div v-if="contract.advance_payment_credit > 0" class="bg-white rounded-xl shadow-md border-0 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">
          <i class="fas fa-wallet mr-2 text-blue-600"></i>Advance Payment Credit
        </h2>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-700">Total Advance Payment:</span>
              <span class="font-semibold text-gray-900">₱{{ formatCurrency(creditBreakdown.total) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-700">Credit Used:</span>
              <span class="font-semibold text-red-600">-₱{{ formatCurrency(creditBreakdown.used) }}</span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-blue-200">
              <span class="font-semibold text-gray-900">Available Credit:</span>
              <span class="text-xl font-bold text-blue-600">₱{{ formatCurrency(contract.advance_payment_credit) }}</span>
            </div>
            <p class="text-xs text-blue-700 mt-2">
              <i class="fas fa-info-circle mr-1"></i>
              This credit can be applied to reduce your payment amounts when making payments for this contract.
            </p>
          </div>
        </div>
      </div>

      <!-- Payments Section -->
      <div class="bg-white rounded-xl shadow-md border-0 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Related Payments</h2>
        <div v-if="payments.length > 0" class="space-y-3">
          <div
            v-for="payment in payments"
            :key="payment.id"
            class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
          >
            <div>
              <div class="font-semibold text-gray-900">₱{{ formatCurrency(payment.amount) }}</div>
              <div class="text-sm text-gray-600">{{ formatDate(payment.payment_date) }}</div>
            </div>
            <div class="flex items-center gap-3">
              <span
                :class="[
                  'px-3 py-1 rounded-full text-xs font-semibold',
                  getPaymentStatusClass(payment.status)
                ]"
              >
                {{ payment.status }}
              </span>
              <NuxtLink
                :to="`/payments/${payment.id}`"
                class="text-blue-600 hover:text-blue-700 text-sm"
              >
                View
              </NuxtLink>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-8 text-gray-500">
          <i class="fas fa-credit-card text-3xl mb-2"></i>
          <p>No payments recorded for this contract.</p>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
      <h4 class="text-xl font-semibold text-gray-600 mb-2">Contract Not Found</h4>
      <NuxtLink
        to="/contracts"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
      >
        <i class="fas fa-arrow-left mr-2"></i>Back to Contracts
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()
const route = useRoute()

const contract = ref(null)
const payments = ref([])
const loading = ref(true)

// Calculate credit breakdown
const creditBreakdown = computed(() => {
  if (!contract.value || !contract.value.advance_payment_credit) {
    return { total: 0, used: 0, available: 0 }
  }
  
  const available = parseFloat(contract.value.advance_payment_credit) || 0
  // We need to get total from advance payment - for now estimate from available
  // In a real scenario, you'd fetch this from the API
  const total = available // This would come from advance payment record
  const used = total - available
  
  return {
    total: total,
    used: Math.max(0, used),
    available: available
  }
})

onMounted(async () => {
  await fetchContract()
  await fetchPayments()
})

const fetchContract = async () => {
  loading.value = true
  try {
    const contractId = route.params?.id || route.params.id
    
    if (!contractId || contractId === 'undefined' || contractId === 'null') {
      console.error('No contract ID provided in route')
      contract.value = null
      loading.value = false
      return
    }
    
    console.log('Fetching contract with ID:', contractId)
    const response = await api.get(`/contracts/${contractId}`)
    console.log('Contract API response:', response)
    
    // Handle different response formats
    if (response && typeof response === 'object') {
      // If response has a data property (wrapped response)
      if (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
        contract.value = response.data
      } 
      // If response is the contract object directly (has id property)
      else if (response.id || response.boarder || response.boardingHouse || response.boarding_house) {
        contract.value = response
      }
      // If response is wrapped in another structure, try to extract
      else {
        contract.value = response
      }
    } else {
      console.warn('Unexpected response format:', response)
      contract.value = null
    }
    
    console.log('Contract data set:', contract.value)
    
    // Validate that we have contract data
    if (!contract.value || (!contract.value.id && !contract.value.boarder && !contract.value.boardingHouse && !contract.value.boarding_house)) {
      console.warn('Contract data appears to be invalid or empty')
    }
  } catch (error) {
    console.error('Error fetching contract:', error)
    contract.value = null
  } finally {
    loading.value = false
  }
}

const fetchPayments = async () => {
  try {
    const response = await api.get(`/contracts/${route.params.id}/payments`)
    payments.value = Array.isArray(response) ? response : (response.data || [])
  } catch (error) {
    console.error('Error fetching payments:', error)
    payments.value = []
  }
}

const getStatusClass = (status) => {
  const statusLower = status?.toLowerCase() || ''
  const classes = {
    active: 'bg-green-100 text-green-800',
    pending: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[statusLower] || 'bg-gray-100 text-gray-800'
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

