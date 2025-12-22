<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-5">
      <div>
        <h1 class="text-2xl font-bold mb-2">
          <i class="fas fa-file-contract mr-2" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>Contracts
        </h1>
        <p class="text-gray-600 mb-0">{{ isAdmin ? 'View and manage all rental contracts' : 'View your rental contracts' }}</p>
      </div>
      <div v-if="isAdmin" class="flex items-center gap-3">
        <NuxtLink
          to="/contracts/create"
          class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
        >
          <i class="fas fa-plus mr-2"></i>Add New Contract
        </NuxtLink>
        <button
          @click="showDeletedModal = true"
          class="bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
        >
          <i class="fas fa-trash-restore mr-2"></i>View Deleted
        </button>
      </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl mb-4" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>
        <p class="text-gray-600">Loading contracts...</p>
      </div>

      <div v-else-if="contracts.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <thead :class="isAdmin ? 'bg-gradient-to-r from-blue-600 to-teal-500' : 'bg-gradient-to-r from-green-600 to-teal-500'" class="text-white">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">ID</th>
              <th v-if="isAdmin" class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Boarder</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Boarding House</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Start Date</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">End Date</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Rent Amount</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="contract in contracts" :key="contract.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ contract.id }}</td>
              <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ contract.boarder?.name || 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ contract.boardingHouse?.name || contract.boarding_house?.name || 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(contract.start_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(contract.end_date) }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">₱{{ formatCurrency(contract.rent_amount) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusBadgeClass(contract.status)">
                  {{ contract.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center gap-2">
                  <NuxtLink
                    :to="`/contracts/${contract.id}`"
                    :class="isAdmin ? 'bg-blue-50 hover:bg-blue-100 text-blue-600' : 'bg-green-50 hover:bg-green-100 text-green-600'" class="p-2 rounded-lg transition inline-flex items-center justify-center"
                    style="min-width: 36px; min-height: 36px;"
                    title="View"
                  >
                    <i class="fas fa-eye" style="font-size: 14px;"></i>
                  </NuxtLink>
                  <NuxtLink
                    v-if="isAdmin"
                    :to="`/contracts/${contract.id}/edit`"
                    class="bg-yellow-50 hover:bg-yellow-100 text-yellow-600 p-2 rounded-lg transition inline-flex items-center justify-center"
                    style="min-width: 36px; min-height: 36px;"
                    title="Edit"
                  >
                    <i class="fas fa-edit" style="font-size: 14px;"></i>
                  </NuxtLink>
                  <button
                    v-if="isAdmin"
                    @click="handleDelete(contract)"
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
        <i class="fas fa-file-contract text-5xl text-gray-400 mb-4"></i>
        <h4 class="text-xl font-semibold text-gray-600 mb-2">No data yet</h4>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-model:show="showDeleteModal"
      title="Delete Contract"
      message="Are you sure you want to delete this contract?"
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

    <!-- Deleted Contracts Modal -->
    <DeletedContractsModal
      v-model:show="showDeletedModal"
      @restored="fetchContracts"
    />
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import ConfirmModal from '~/components/ConfirmModal.vue'
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'
import DeletedContractsModal from '~/components/DeletedContractsModal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()

const contracts = ref([])
const loading = ref(true)
const showDeleteModal = ref(false)
const showDeletedModal = ref(false)
const selectedContract = ref(null)
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successTitle = ref('Success')
const successMessage = ref('')
const errorTitle = ref('Error')
const errorMessage = ref('')

// Check if user is admin - handle both ref and direct access
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchContracts()
})

const fetchContracts = async () => {
  loading.value = true
  try {
    const response = await api.get('/contracts')
    
    // Handle paginated response from Laravel
    let allContracts = []
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, ... }
      allContracts = response.data
    } else if (Array.isArray(response)) {
      // Direct array response
      allContracts = response
    } else {
      allContracts = []
    }
    
    // Frontend filtering as safety measure - ensure boarders only see their own contracts
    const user = auth.user?.value || auth.user
    if (user && user.type === 'boarder' && !isAdmin.value) {
      contracts.value = allContracts.filter(contract => {
        const contractBoarderId = contract.boarder_id || contract.boarder?.id
        return contractBoarderId === user.id
      })
    } else {
      contracts.value = allContracts
    }
  } catch (error) {
    console.error('Error fetching contracts:', error)
    contracts.value = []
  } finally {
    loading.value = false
  }
}

const handleDelete = (contract) => {
  selectedContract.value = contract
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!selectedContract.value) return
  
  try {
    await api.delete(`/contracts/${selectedContract.value.id}`)
    await fetchContracts()
    showDeleteModal.value = false
    selectedContract.value = null
    successTitle.value = 'Contract Deleted'
    successMessage.value = 'Contract has been deleted successfully.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error deleting contract:', error)
    errorTitle.value = 'Delete Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to delete contract. Please try again.'
    showErrorModal.value = true
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

