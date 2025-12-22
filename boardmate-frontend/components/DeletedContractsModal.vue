<template>
  <Modal :show="show" title="Deleted Contracts" @update:show="handleClose" size="large">
    <div v-if="loading" class="text-center py-8">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading deleted contracts...</p>
    </div>

    <div v-else-if="deletedContracts.length > 0" class="max-h-96 overflow-y-auto">
      <table class="w-full text-sm">
        <thead class="bg-gradient-to-r from-blue-600 to-teal-500 text-white sticky top-0">
          <tr>
            <th class="px-4 py-3 text-left font-semibold">ID</th>
            <th class="px-4 py-3 text-left font-semibold">Boarder</th>
            <th class="px-4 py-3 text-left font-semibold">Boarding House</th>
            <th class="px-4 py-3 text-left font-semibold">Rent Amount</th>
            <th class="px-4 py-3 text-left font-semibold">Status</th>
            <th class="px-4 py-3 text-left font-semibold">Deleted At</th>
            <th class="px-4 py-3 text-left font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="contract in deletedContracts" :key="contract.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 text-gray-900">{{ contract.original_contract_id }}</td>
            <td class="px-4 py-3 text-gray-900">{{ contract.boarder?.name || 'N/A' }}</td>
            <td class="px-4 py-3 text-gray-900">{{ contract.boardingHouse?.name || contract.boarding_house?.name || (contract.boarding_house_id ? `ID: ${contract.boarding_house_id}` : 'N/A') }}</td>
            <td class="px-4 py-3 font-semibold text-green-600">₱{{ formatCurrency(contract.rent_amount) }}</td>
            <td class="px-4 py-3">
              <span :class="getStatusBadgeClass(contract.status)" class="text-xs font-semibold px-2 py-1 rounded-full">
                {{ contract.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-gray-600 text-xs">{{ formatDate(contract.deleted_at) }}</td>
            <td class="px-4 py-3">
              <button
                @click="handleRestore(contract.id)"
                :disabled="restoring === contract.id"
                class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition duration-200 flex items-center gap-1"
                :class="restoring === contract.id ? 'opacity-50 cursor-not-allowed' : ''"
              >
                <i v-if="restoring === contract.id" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-undo"></i>
                {{ restoring === contract.id ? 'Restoring...' : 'Restore' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="text-center py-8">
      <i class="fas fa-file-contract text-5xl text-gray-400 mb-4"></i>
      <p class="text-gray-600">No deleted contracts found.</p>
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

const deletedContracts = ref([])
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

const getStatusBadgeClass = (status) => {
  const statusMap = {
    'Active': 'bg-green-100 text-green-800',
    'Pending': 'bg-yellow-100 text-yellow-800',
    'Completed': 'bg-blue-100 text-blue-800',
    'Terminated': 'bg-red-100 text-red-800',
    'Cancelled': 'bg-gray-100 text-gray-800'
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const fetchDeletedContracts = async () => {
  loading.value = true
  try {
    const response = await api.get('/contracts/deleted')
    console.log('Deleted contracts response:', response)
    deletedContracts.value = Array.isArray(response) ? response : (response.data || [])
    console.log('Deleted contracts after processing:', deletedContracts.value)
  } catch (error) {
    console.error('Error fetching deleted contracts:', error)
    console.error('Error details:', error.response || error)
    deletedContracts.value = []
    alert('Failed to load deleted contracts: ' + (error.message || 'Unknown error'))
  } finally {
    loading.value = false
  }
}

const handleRestore = async (id) => {
  restoring.value = id
  try {
    const response = await api.post(`/contracts/deleted/${id}/restore`)
    if (response.success) {
      // Remove from list
      deletedContracts.value = deletedContracts.value.filter(c => c.id !== id)
      // Emit event to refresh main contracts list
      emit('restored')
      // Show success modal
      successMessage.value = 'Contract restored successfully!'
      showSuccessModal.value = true
      // Auto-close after 2 seconds
      setTimeout(() => {
        showSuccessModal.value = false
      }, 2000)
    }
  } catch (error) {
    console.error('Error restoring contract:', error)
    errorMessage.value = error.response?.data?.message || error.message || 'Failed to restore contract. Please try again.'
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
    fetchDeletedContracts()
  }
})
</script>
