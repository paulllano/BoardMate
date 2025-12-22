<template>
  <div>
    <div class="bg-white rounded-xl shadow-md border-0 p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">
        <i class="fas fa-credit-card mr-2 text-green-600"></i>
        {{ auth.user?.type === 'admin' ? 'Record Payment' : 'Make Payment' }}
      </h1>

      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
        <p class="text-gray-600">Loading payment form...</p>
      </div>

      <!-- No Contracts Available Message -->
      <div v-else-if="availableContracts.length === 0" class="bg-yellow-50 border-l-4 border-yellow-400 text-yellow-700 px-4 py-6 rounded-lg mb-6">
        <div class="flex items-start">
          <i class="fas fa-exclamation-triangle text-2xl mr-3 mt-1"></i>
          <div>
            <h3 class="font-bold text-lg mb-2">No Active Contracts Available</h3>
            <p class="mb-3">
              <span v-if="isAdmin">You don't have any active contracts in your boarding houses available for payment recording.</span>
              <span v-else>You don't have any active contracts available for payment. All your contracts have been completed or you haven't been assigned to a boarding house yet.</span>
            </p>
            <div class="flex gap-3">
              <NuxtLink
                v-if="!isAdmin"
                to="/contracts"
                class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
              >
                <i class="fas fa-file-contract mr-2"></i>
                View My Contracts
              </NuxtLink>
              <NuxtLink
                to="/payments"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
              >
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Payments
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
          {{ error }}
        </div>

        <!-- ADMIN LAYOUT -->
        <template v-if="isAdmin">
          <!-- Row 1: What the payment is for -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Contract Selection -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-file-contract mr-2 text-blue-600"></i>Contract *
              </label>
              <select
                v-model="form.contract_id"
                required
                @change="onContractChange"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select a contract</option>
                <option
                  v-for="contract in filteredContracts"
                  :key="contract.id"
                  :value="contract.id"
                >
                  Contract #{{ contract.id }} - {{ getBoardingHouseName(contract) || 'N/A' }} ({{ getBoarderLastName(contract) }}) - {{ contract.status }}
                </option>
              </select>
              <p v-if="form.boarder_id && filteredContracts.length === 0" class="text-yellow-600 text-sm mt-1">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No active contracts found for the selected boarder.
              </p>
              <p v-if="selectedContract && selectedContract.status === 'Completed'" class="text-red-600 text-sm mt-1">
                <i class="fas fa-exclamation-circle mr-1"></i>
                This contract is already completed. You cannot make additional payments.
              </p>
            </div>

            <!-- Boarder Selection -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-user mr-2 text-blue-600"></i>Boarder *
              </label>
              <select
                v-model="form.boarder_id"
                required
                @change="onBoarderChange"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
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
          </div>

          <!-- Row 2: How much and what type -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Amount -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-money-bill mr-2 text-blue-600"></i>Amount *
              </label>
              <input
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="0.00"
              />
            </div>

            <!-- Payment Type -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-list mr-2 text-blue-600"></i>Payment Type
              </label>
              <select
                v-model="form.payment_type"
                @change="onPaymentTypeChange"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select payment type (optional)</option>
                <option value="full">Full Payment</option>
                <option value="partial">Partial Payment</option>
              </select>
              <p v-if="form.payment_type === 'full' && selectedContract" class="text-sm text-gray-600 mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                Amount will be set to monthly rent: ₱{{ formatCurrency(selectedContract.rent_amount || selectedContract.rentAmount) }}
              </p>
            </div>
          </div>

          <!-- Row 3: When and how -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Payment Date -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-calendar mr-2 text-blue-600"></i>Payment Date *
              </label>
              <input
                v-model="form.payment_date"
                type="date"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-credit-card mr-2 text-blue-600"></i>Payment Method *
              </label>
              <select
                v-model="form.payment_method"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select payment method</option>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>
          </div>

          <!-- Row 4: Status -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-info-circle mr-2 text-blue-600"></i>Status *
            </label>
            <select
              v-model="form.status"
              required
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="">Select status</option>
              <option value="pending">Pending</option>
              <option value="completed">Completed</option>
              <option value="failed">Failed</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
        </template>

        <!-- BOARDER LAYOUT (Option A: Simplified) -->
        <template v-else>
          <!-- Row 1: Contract (full width) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-file-contract mr-2 text-green-600"></i>Contract *
            </label>
            <select
              v-model="form.contract_id"
              required
              @change="onContractChange"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
            >
              <option value="">Select a contract</option>
              <option
                v-for="contract in filteredContracts"
                :key="contract.id"
                :value="contract.id"
              >
                Contract #{{ contract.id }} - {{ getBoardingHouseName(contract) || 'N/A' }} - {{ contract.status }}
              </option>
            </select>
            <p v-if="selectedContract && selectedContract.status === 'Completed'" class="text-red-600 text-sm mt-1">
              <i class="fas fa-exclamation-circle mr-1"></i>
              This contract is already completed. You cannot make additional payments.
            </p>
          </div>

          <!-- Row 2: Payment Type | Amount -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Payment Type -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-list mr-2 text-green-600"></i>Payment Type
              </label>
              <select
                v-model="form.payment_type"
                @change="onPaymentTypeChange"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              >
                <option value="">Select payment type (optional)</option>
                <option value="full">Full Payment</option>
                <option value="partial">Partial Payment</option>
              </select>
              <p v-if="form.payment_type === 'full' && selectedContract" class="text-sm text-gray-600 mt-1">
                <i class="fas fa-info-circle mr-1"></i>
                Amount will be set to monthly rent: ₱{{ formatCurrency(selectedContract.rent_amount || selectedContract.rentAmount) }}
              </p>
            </div>

            <!-- Amount -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-money-bill mr-2 text-green-600"></i>Amount *
              </label>
              <input
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="0.00"
              />
            </div>
          </div>

          <!-- Row 3: Payment Date | Payment Method -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Payment Date -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-calendar mr-2 text-green-600"></i>Payment Date *
              </label>
              <input
                v-model="form.payment_date"
                type="date"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              />
            </div>

            <!-- Payment Method -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-credit-card mr-2 text-green-600"></i>Payment Method *
              </label>
              <select
                v-model="form.payment_method"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              >
                <option value="">Select payment method</option>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
              </select>
            </div>
          </div>

          <!-- Row 4: Notes (full width) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-sticky-note mr-2 text-green-600"></i>Notes (Optional)
            </label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
              placeholder="Additional notes about this payment..."
            ></textarea>
          </div>
        </template>

        <!-- Notes (Admin only - separate section) -->
        <div v-if="isAdmin">
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-sticky-note mr-2 text-blue-600"></i>Notes (Optional)
          </label>
          <textarea
            v-model="form.notes"
            rows="3"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            placeholder="Additional notes about this payment..."
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
          <NuxtLink
            to="/payments"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-times mr-2"></i>Cancel
          </NuxtLink>
          <button
            type="submit"
            :disabled="loading"
            :class="isAdmin ? 'bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center ml-auto' : 'bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center ml-auto'"
          >
            <i v-if="!loading" class="fas fa-save mr-2"></i>
            <i v-else class="fas fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Processing...' : (isAdmin ? 'Record Payment' : 'Submit Payment') }}
          </button>
        </div>
      </form>
    </div>

    <!-- Already Paid Modal -->
    <Modal :show="showAlreadyPaidModal" title="Contract Already Completed" @update:show="showAlreadyPaidModal = false">
      <template #default>
        <div class="text-center py-4">
          <div class="mb-4">
            <i class="fas fa-check-circle text-6xl text-yellow-500"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Contract Already Completed</h3>
          <p class="text-gray-600 mb-4">
            This contract has already been fully paid and completed. You cannot make additional payments for completed contracts.
          </p>
          <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 px-4 py-3 rounded text-sm text-left">
            <strong>What does this mean?</strong>
            <ul class="list-disc list-inside mt-2 space-y-1">
              <li>All payments for this contract have been completed</li>
              <li>The contract status is now "Completed"</li>
              <li>If you need to make a payment, please select an active contract</li>
            </ul>
          </div>
        </div>
      </template>
      
      <template #footer>
        <button
          type="button"
          @click="showAlreadyPaidModal = false"
          class="bg-gradient-to-r from-blue-600 to-indigo-500 hover:from-blue-700 hover:to-indigo-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200 flex items-center mx-auto"
        >
          <i class="fas fa-check mr-2"></i>
          Got it!
        </button>
      </template>
    </Modal>

    <!-- Success Modal -->
    <Modal :show="showSuccessModal" :title="isAdmin ? 'Payment Recorded Successfully!' : 'Payment Submitted Successfully!'" @update:show="showSuccessModal = false">
      <template #default>
        <div class="text-center py-4">
          <div class="mb-4">
            <i :class="isAdmin ? 'fas fa-check-circle text-6xl text-blue-500' : 'fas fa-check-circle text-6xl text-green-500'"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">{{ isAdmin ? 'Payment Recorded Successfully!' : 'Payment Submitted Successfully!' }}</h3>
          <p class="text-gray-600 mb-4">
            <span v-if="isAdmin">The payment has been recorded and is now pending approval. The boarder will be notified once the payment is reviewed.</span>
            <span v-else>Your payment has been submitted and is pending review by the property owner. You will be notified once your payment is approved or if any issues arise.</span>
          </p>
          <div v-if="createdPayment?.reference_number || createdPayment?.referenceNumber" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-gray-700">Reference Number:</span>
              <span class="text-lg font-mono font-bold" :class="isAdmin ? 'text-blue-600' : 'text-green-600'">
                {{ createdPayment.reference_number || createdPayment.referenceNumber }}
              </span>
            </div>
            <p class="text-xs text-gray-500 mt-2">Please save this reference number for your records.</p>
          </div>
          <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 px-4 py-3 rounded text-sm text-left">
            <strong>What's next?</strong>
            <ul class="list-disc list-inside mt-2 space-y-1">
              <li v-if="isAdmin">Review the payment details in your payments list</li>
              <li v-if="isAdmin">Approve or reject the payment as needed</li>
              <li v-if="!isAdmin">Wait for the property owner to review your payment</li>
              <li v-if="!isAdmin">You will be notified once a decision is made</li>
              <li>Check your payment status in your dashboard</li>
            </ul>
          </div>
        </div>
      </template>
      
      <template #footer>
        <button
          type="button"
          @click="showSuccessModal = false; navigateTo('/payments')"
          :class="isAdmin ? 'bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200 flex items-center mx-auto' : 'bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200 flex items-center mx-auto'"
        >
          <i class="fas fa-check mr-2"></i>
          Got it!
        </button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import Modal from '~/components/Modal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()

const contracts = ref([])
const boarders = ref([])
const loading = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const createdPayment = ref(null)
const showAlreadyPaidModal = ref(false)

const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

const form = ref({
  contract_id: '',
  boarder_id: '', // Will be set automatically from contract (boarders) or selected (admins)
  amount: '',
  payment_date: new Date().toISOString().split('T')[0],
  payment_method: '',
  payment_type: '',
  notes: '',
  status: '' // Required for admins, optional for boarders (defaults to pending)
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchFormData()
})

const fetchFormData = async () => {
  loading.value = true
  try {
    const response = await api.get('/payments/create')
    
    // useApi returns the JSON directly, not wrapped in 'data'
    if (response) {
      boarders.value = response.boarders || []
      contracts.value = (response.contracts || []).map(contract => ({
        ...contract,
        boarder_id: contract.boarder_id || contract.boarder?.id || contract.boarderId
      }))
    } else {
      boarders.value = []
      contracts.value = []
    }
  } catch (error) {
    console.error('Error fetching form data:', error)
    boarders.value = []
    contracts.value = []
  } finally {
    loading.value = false
  }
}

// Helper functions to handle both snake_case and camelCase relationship names
const getBoardingHouseName = (contract) => {
  return contract?.boarding_house?.name || contract?.boardingHouse?.name || 'N/A'
}

const getBoarderName = (contract) => {
  return contract?.boarder?.name || contract?.boarder_name || 'N/A'
}

// Extract last name from full name
const getBoarderLastName = (contract) => {
  const fullName = getBoarderName(contract)
  if (!fullName || fullName === 'N/A') return 'N/A'
  
  const nameParts = fullName.trim().split(/\s+/)
  // Return the last part (last name), or full name if only one part
  return nameParts.length > 1 ? nameParts[nameParts.length - 1] : fullName
}

// Filter contracts to only show Active or Pending contracts (not Completed or Cancelled)
const availableContracts = computed(() => {
  return contracts.value.filter(contract => {
    const status = contract.status || contract.contract_status
    return status === 'Active' || status === 'Pending'
  })
})

// For admins, filter contracts by selected boarder
const filteredContracts = computed(() => {
  if (!isAdmin.value) {
    return availableContracts.value
  }
  
  // If no boarder selected, show all available contracts
  if (!form.value.boarder_id) {
    return availableContracts.value
  }
  
  // Filter contracts by selected boarder
  const filtered = availableContracts.value.filter(contract => {
    const contractBoarderId = contract.boarder_id || contract.boarder?.id || contract.boarderId
    // Convert both to strings for comparison to handle number/string mismatches
    return String(contractBoarderId) === String(form.value.boarder_id)
  })
  
  return filtered
})

// When boarder is selected (admin only), filter contracts
const onBoarderChange = () => {
  // If a contract is already selected, check if it belongs to the selected boarder
  if (form.value.contract_id && form.value.boarder_id) {
    const selectedContract = contracts.value.find(c => c.id == form.value.contract_id)
    if (selectedContract) {
      const contractBoarderId = selectedContract.boarder_id || selectedContract.boarder?.id || selectedContract.boarderId
      // If the contract doesn't match the selected boarder, clear it
      if (contractBoarderId != form.value.boarder_id) {
        form.value.contract_id = ''
      }
      // If it matches, keep it selected
    }
  } else {
    // If no contract selected, just ensure boarder_id is set
    form.value.boarder_id = form.value.boarder_id || ''
  }
}

// Get selected contract details
const selectedContract = computed(() => {
  if (!form.value.contract_id) return null
  return contracts.value.find(c => c.id == form.value.contract_id)
})

// When contract is selected, automatically set boarder_id (for boarders/admins) and amount (if full payment)
const onContractChange = () => {
  if (form.value.contract_id) {
    const selectedContract = contracts.value.find(c => c.id == form.value.contract_id)
    if (selectedContract) {
      // Extract boarder_id from contract (handle both formats)
      const contractBoarderId = selectedContract.boarder_id || selectedContract.boarder?.id || selectedContract.boarderId
      
      // For admins, auto-fill boarder if not already selected or if it doesn't match
      if (isAdmin.value) {
        if (!form.value.boarder_id || form.value.boarder_id != contractBoarderId) {
          form.value.boarder_id = contractBoarderId
        }
      } else {
        // For boarders, always set boarder_id from contract
        form.value.boarder_id = contractBoarderId
      }
      
      // Auto-fill amount if payment type is "full"
      if (form.value.payment_type === 'full') {
        const rentAmount = selectedContract.rent_amount || selectedContract.rentAmount
        if (rentAmount) {
          form.value.amount = rentAmount
        }
      }
    }
  } else {
    // If no contract selected, clear boarder_id for boarders only
    if (!isAdmin.value) {
      form.value.boarder_id = ''
    }
  }
}

// When payment type changes, auto-fill or clear amount
const onPaymentTypeChange = () => {
  if (form.value.payment_type === 'full' && form.value.contract_id) {
    // Auto-fill with rent amount
    const selectedContract = contracts.value.find(c => c.id == form.value.contract_id)
    if (selectedContract) {
      const rentAmount = selectedContract.rent_amount || selectedContract.rentAmount
      if (rentAmount) {
        form.value.amount = rentAmount
      }
    }
  } else if (form.value.payment_type === 'partial') {
    // Clear amount for partial payment (user will enter manually)
    form.value.amount = ''
  }
}

// Format currency helper
const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const handleSubmit = async () => {
  loading.value = true
  error.value = ''
  
  try {
    // Check if contract is selected
    if (!form.value.contract_id) {
      error.value = 'Please select a contract.'
      loading.value = false
      return
    }
    
    // Check if amount is provided
    if (!form.value.amount || parseFloat(form.value.amount) <= 0) {
      error.value = 'Please enter a valid payment amount.'
      loading.value = false
      return
    }
    
    // Check if payment method is selected
    if (!form.value.payment_method) {
      error.value = 'Please select a payment method.'
      loading.value = false
      return
    }
    
    // Check if payment date is provided
    if (!form.value.payment_date) {
      error.value = 'Please select a payment date.'
      loading.value = false
      return
    }
    
    // For boarders, payment_type is required
    if (!isAdmin.value && !form.value.payment_type) {
      error.value = 'Please select a payment type (Full or Partial).'
      loading.value = false
      return
    }
    
    // For admins, check if boarder is selected
    if (isAdmin.value && !form.value.boarder_id) {
      error.value = 'Please select a boarder.'
      loading.value = false
      return
    }
    
    // For admins, check if status is selected
    if (isAdmin.value && !form.value.status) {
      error.value = 'Please select a payment status.'
      loading.value = false
      return
    }
    
    // Check if selected contract is completed or cancelled
    const contract = selectedContract.value
    if (contract) {
      const status = contract.status || contract.contract_status
      if (status === 'Completed' || status === 'Cancelled') {
        showAlreadyPaidModal.value = true
        loading.value = false
        return
      }
    }
    
    // Prepare payment data (reference_number is auto-generated by backend)
    const paymentData = { ...form.value }
    
    // Convert amount to number
    if (paymentData.amount) {
      paymentData.amount = parseFloat(paymentData.amount)
    }
    
    // Ensure boarder_id is set
    const user = auth.user?.value || auth.user
    if (user?.type === 'boarder') {
      // For boarders, set boarder_id from authenticated user
      paymentData.boarder_id = user.id
      // Boarders must have payment_type
      if (!paymentData.payment_type) {
        error.value = 'Please select a payment type (Full or Partial).'
        loading.value = false
        return
      }
    } else if (user?.type === 'admin') {
      // For admins, boarder_id should be set from boarder selection
      // But also verify it matches the contract's boarder
      if (paymentData.contract_id) {
        const selectedContract = contracts.value.find(c => c.id == paymentData.contract_id)
        if (selectedContract) {
          const contractBoarderId = selectedContract.boarder_id || selectedContract.boarder?.id || selectedContract.boarderId
          // Use the boarder_id from form (admin selection) but verify it matches contract
          if (contractBoarderId != paymentData.boarder_id) {
            error.value = 'The selected boarder does not match the contract. Please verify your selection.'
            loading.value = false
            return
          }
        }
      }
    }
    
    // Final check: boarder_id must be set
    if (!paymentData.boarder_id) {
      error.value = isAdmin.value ? 'Please select a boarder and contract.' : 'Please select a contract to determine the boarder.'
      loading.value = false
      return
    }
    
    // Remove empty fields
    if (!paymentData.payment_type) delete paymentData.payment_type
    if (!paymentData.notes) delete paymentData.notes
    
    console.log('Submitting payment data:', paymentData)
    
    const response = await api.post('/payments', paymentData)
    
    console.log('Payment response:', response)
    
    // Store the created payment data (includes generated reference number)
    // Handle different response formats
    if (response?.payment) {
      createdPayment.value = response.payment
    } else if (response?.data) {
      createdPayment.value = response.data
    } else {
      createdPayment.value = response
    }
    
    // Show success modal
    showSuccessModal.value = true
  } catch (err) {
    console.error('Error submitting payment:', err)
    // Handle validation errors
    if (err?.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    } else {
      error.value = err?.response?.data?.message || err?.message || 'Failed to submit payment. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

