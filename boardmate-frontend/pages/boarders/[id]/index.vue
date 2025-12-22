<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading boarder details...</p>
    </div>

    <div v-else-if="boarder">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-0">
          <i class="fas fa-user mr-2"></i>{{ boarder.name }}
        </h1>
        <div class="flex items-center gap-2">
          <NuxtLink
            :to="`/boarders/${boarder.id}/edit`"
            class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-edit mr-1"></i>Edit
          </NuxtLink>
          <NuxtLink
            to="/boarders"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-arrow-left mr-1"></i>Back to List
          </NuxtLink>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Main Content -->
        <div class="md:col-span-2 space-y-4">
          <!-- Boarder Details -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-info-circle mr-2"></i>Boarder Details
              </h5>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p><strong>ID:</strong> {{ boarder.id }}</p>
                  <p><strong>Name:</strong> {{ boarder.name }}</p>
                  <p><strong>Email:</strong> {{ boarder.email }}</p>
                  <p><strong>Phone:</strong> {{ boarder.phone || 'N/A' }}</p>
                </div>
                <div>
                  <p><strong>Boarding House:</strong> {{ boarder.boarding_house?.name || boarder.boardingHouse?.name || 'N/A' }}</p>
                  <p><strong>Date of Birth:</strong> {{ formatDate(boarder.date_of_birth) }}</p>
                  <p v-if="boarder.address"><strong>Address:</strong></p>
                  <p v-if="boarder.address" class="text-gray-600">{{ boarder.address }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Contracts -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-file-contract mr-2"></i>Contracts ({{ contracts.length }})
              </h5>
            </div>
            <div class="p-6">
              <div v-if="contracts.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Contract ID</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Start Date</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">End Date</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Rent Amount</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="contract in contracts" :key="contract.id" class="hover:bg-gray-50">
                      <td class="px-4 py-3">{{ contract.id }}</td>
                      <td class="px-4 py-3">{{ formatDate(contract.start_date) }}</td>
                      <td class="px-4 py-3">{{ formatDate(contract.end_date) }}</td>
                      <td class="px-4 py-3 font-semibold text-green-600">₱{{ formatCurrency(contract.rent_amount) }}</td>
                      <td class="px-4 py-3">
                        <span :class="getStatusBadgeClass(contract.status)">
                          {{ contract.status }}
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <NuxtLink
                          :to="`/contracts/${contract.id}`"
                          class="bg-blue-50 hover:bg-blue-100 text-blue-600 p-2 rounded-lg transition"
                          title="View"
                        >
                          <i class="fas fa-eye"></i>
                        </NuxtLink>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <p>No contracts found for this boarder.</p>
              </div>
            </div>
          </div>
          
          <!-- Recent Payments -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-credit-card mr-2"></i>Recent Payments ({{ payments.length }})
              </h5>
            </div>
            <div class="p-6">
              <div v-if="payments.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Payment ID</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Amount</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Payment Date</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Status</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="payment in payments.slice(0, 5)" :key="payment.id" class="hover:bg-gray-50">
                      <td class="px-4 py-3">{{ payment.id }}</td>
                      <td class="px-4 py-3 font-semibold text-green-600">₱{{ formatCurrency(payment.amount) }}</td>
                      <td class="px-4 py-3">{{ formatDate(payment.payment_date) }}</td>
                      <td class="px-4 py-3">
                        <span :class="getPaymentStatusBadgeClass(payment.status)">
                          {{ payment.status === 'completed' ? 'Completed' : payment.status === 'cancelled' ? 'Cancelled' : payment.status === 'failed' ? 'Failed' : payment.status }}
                        </span>
                      </td>
                      <td class="px-4 py-3">
                        <NuxtLink
                          :to="`/payments/${payment.id}`"
                          class="bg-blue-50 hover:bg-blue-100 text-blue-600 p-2 rounded-lg transition"
                          title="View"
                        >
                          <i class="fas fa-eye"></i>
                        </NuxtLink>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <p>No payments found for this boarder.</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Statistics -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-chart-bar mr-2"></i>Statistics
              </h5>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-4 text-center">
                <div class="border-r border-gray-200 pr-4">
                  <h4 class="text-2xl font-bold text-blue-600">{{ contracts.length }}</h4>
                  <small class="text-gray-600">Contracts</small>
                </div>
                <div>
                  <h4 class="text-2xl font-bold text-green-600">{{ payments.length }}</h4>
                  <small class="text-gray-600">Payments</small>
                </div>
                <div class="border-r border-gray-200 pr-4 pt-4 border-t border-gray-200">
                  <h4 class="text-2xl font-bold text-yellow-600">{{ reviews.length }}</h4>
                  <small class="text-gray-600">Reviews</small>
                </div>
                <div class="pt-4 border-t border-gray-200">
                  <h4 class="text-2xl font-bold text-cyan-600">{{ averageRating }}</h4>
                  <small class="text-gray-600">Avg Rating</small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Recent Reviews -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-star mr-2"></i>Recent Reviews
              </h5>
            </div>
            <div class="p-6">
              <div v-if="reviews.length > 0" class="space-y-4">
                <div v-for="review in reviews.slice(0, 3)" :key="review.id" class="pb-4 border-b border-gray-200 last:border-0 last:pb-0">
                  <div class="flex justify-between items-start mb-2">
                    <strong class="text-sm">{{ review.boarding_house?.name || review.boardingHouse?.name || 'Anonymous' }}</strong>
                    <span class="text-yellow-500 text-xs">
                      <i v-for="i in 5" :key="i" :class="i <= review.rating ? 'fas fa-star' : 'far fa-star'"></i>
                    </span>
                  </div>
                  <p class="text-gray-600 text-xs mb-0">
                    {{ review.comment && review.comment.length > 100 ? review.comment.substring(0, 100) + '...' : review.comment }}
                  </p>
                </div>
              </div>
              <div v-else class="text-center py-4 text-gray-500">
                <p class="text-sm">No reviews yet.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
      <h4 class="text-xl font-semibold text-gray-600 mb-2">Boarder Not Found</h4>
      <NuxtLink
        to="/boarders"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
      >
        <i class="fas fa-arrow-left mr-2"></i>Back to Boarders
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

const boarder = ref(null)
const contracts = ref([])
const payments = ref([])
const reviews = ref([])
const averageRating = ref('0.0')
const loading = ref(true)

onMounted(async () => {
  await auth.checkAuth()
  await fetchBoarder()
  if (boarder.value) {
    calculateAverageRating()
  }
})

const fetchBoarder = async () => {
  loading.value = true
  try {
    const response = await api.get(`/boarders/${route.params.id}`)
    const boarderData = response.data || response
    
    if (boarderData) {
      boarder.value = boarderData
      contracts.value = Array.isArray(boarderData.contracts) ? boarderData.contracts : []
      payments.value = Array.isArray(boarderData.payments) ? boarderData.payments : []
      reviews.value = Array.isArray(boarderData.reviews) ? boarderData.reviews : []
    } else {
      boarder.value = null
    }
  } catch (error) {
    console.error('Error fetching boarder:', error)
    boarder.value = null
    contracts.value = []
    payments.value = []
    reviews.value = []
  } finally {
    loading.value = false
  }
}

const calculateAverageRating = () => {
  if (reviews.value.length > 0) {
    const sum = reviews.value.reduce((acc, r) => acc + (r.rating || 0), 0)
    averageRating.value = (sum / reviews.value.length).toFixed(1)
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

