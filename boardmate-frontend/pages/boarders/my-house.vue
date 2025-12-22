<template>
  <div class="container py-4">
    <div class="mb-4">
      <h1 class="text-xl font-semibold mb-0">
        <i class="fas fa-home mr-2"></i>My Boarding House
      </h1>
    </div>

    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
      <p class="text-gray-600">Loading...</p>
    </div>

    <div v-else-if="boardingHouse">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Main Content -->
        <div class="md:col-span-2 space-y-4">
          <!-- Boarding House Details -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-info-circle mr-2"></i>Boarding House Details
              </h5>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <h4 class="text-xl font-bold text-green-600 mb-2">{{ boardingHouse.name }}</h4>
                  <p class="text-gray-600 mb-3">{{ boardingHouse.address }}</p>
                  <div v-if="boardingHouse.description">
                    <p><strong>Description:</strong></p>
                    <p class="text-gray-600">{{ boardingHouse.description }}</p>
                  </div>
                </div>
                <div>
                  <!-- Empty space for layout balance -->
                </div>
              </div>
              
              <!-- Admin/Manager Information Section -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <h6 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                  <i class="fas fa-user-tie mr-2 text-green-600"></i>Property Manager
                </h6>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="space-y-2">
                    <div>
                      <span class="text-sm text-gray-500">Name:</span>
                      <span class="ml-2 font-semibold text-gray-900">{{ boardingHouse.admin?.name || 'N/A' }}</span>
                    </div>
                    <div v-if="boardingHouse.admin?.email">
                      <span class="text-sm text-gray-500">Email:</span>
                      <span class="ml-2 text-gray-700">{{ boardingHouse.admin.email }}</span>
                    </div>
                    <div v-if="boardingHouse.admin?.phone">
                      <span class="text-sm text-gray-500">Phone:</span>
                      <span class="ml-2 text-gray-700">
                        <i class="fas fa-phone mr-1 text-gray-400"></i>{{ boardingHouse.admin.phone }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Housemates -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-users mr-2"></i>Housemates ({{ boarders.length }})
              </h5>
            </div>
            <div class="p-6">
              <div v-if="boarders.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                  v-for="boarder in boarders"
                  :key="boarder.id"
                  class="bg-gray-50 border border-gray-200 rounded-lg p-4"
                >
                  <h6 class="font-semibold text-gray-900 mb-1">{{ boarder.name }}</h6>
                  <p class="text-sm text-gray-600 mb-1">{{ boarder.email }}</p>
                  <p v-if="boarder.phone" class="text-sm text-gray-600">{{ boarder.phone }}</p>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <p>No other boarders found.</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- House Statistics -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-chart-bar mr-2"></i>House Statistics
              </h5>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-4 text-center">
                <div class="border-r border-gray-200 pr-4">
                  <h4 class="text-2xl font-bold text-green-600">{{ boarders.length }}</h4>
                  <small class="text-gray-600">Residents</small>
                </div>
                <div>
                  <h4 class="text-2xl font-bold text-green-600">{{ contractsCount }}</h4>
                  <small class="text-gray-600">Contracts</small>
                </div>
                <div class="border-r border-gray-200 pr-4 pt-4 border-t border-gray-200">
                  <h4 class="text-2xl font-bold text-yellow-600">{{ reviewsCount }}</h4>
                  <small class="text-gray-600">Reviews</small>
                </div>
                <div class="pt-4 border-t border-gray-200">
                  <h4 class="text-2xl font-bold text-cyan-600">{{ averageRating }}</h4>
                  <small class="text-gray-600">Avg Rating</small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Available Services -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-concierge-bell mr-2"></i>Available Services
              </h5>
            </div>
            <div class="p-6">
              <div v-if="services.length > 0" class="space-y-4">
                <div v-for="service in services.slice(0, 3)" :key="service.id" class="pb-4 border-b border-gray-200 last:border-0 last:pb-0">
                  <h6 class="font-semibold text-gray-900 mb-1">{{ service.name }}</h6>
                  <p class="text-gray-600 text-sm mb-2">{{ service.description && service.description.length > 80 ? service.description.substring(0, 80) + '...' : service.description }}</p>
                  <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                    ₱{{ formatCurrency(service.price) }}
                  </span>
                </div>
                <NuxtLink
                  v-if="services.length > 3"
                  to="/services"
                  class="bg-green-50 hover:bg-green-100 text-green-600 text-sm font-semibold py-2 px-4 rounded-lg transition inline-block"
                >
                  View All Services
                </NuxtLink>
              </div>
              <div v-else class="text-center py-4 text-gray-500">
                <p class="text-sm">No services available.</p>
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
                <div v-for="review in reviews.slice(0, 2)" :key="review.id" class="pb-4 border-b border-gray-200 last:border-0 last:pb-0">
                  <div class="flex justify-between items-start mb-2">
                    <strong class="text-sm">{{ review.is_anonymous ? 'Anonymous' : (review.boarder?.name || 'Anonymous') }}</strong>
                    <span class="text-yellow-500 text-xs">
                      <i v-for="i in 5" :key="i" :class="i <= review.rating ? 'fas fa-star' : 'far fa-star'"></i>
                    </span>
                  </div>
                  <p class="text-gray-600 text-xs mb-0">
                    {{ review.comment && review.comment.length > 80 ? review.comment.substring(0, 80) + '...' : review.comment }}
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
      <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden max-w-2xl mx-auto p-8">
        <i class="fas fa-home text-6xl text-gray-300 mb-6"></i>
        <h4 class="text-2xl font-bold text-gray-700 mb-3">No Boarding House Yet?</h4>
        <p class="text-gray-600 mb-6 text-lg">Start your journey by finding the perfect boarding house that suits your needs.</p>
        <NuxtLink
          to="/boarding-houses/browse"
          class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition-all duration-200 inline-flex items-center hover:-translate-y-0.5 hover:shadow-lg text-lg"
        >
          <i class="fas fa-search-location mr-2"></i>Browse Houses
        </NuxtLink>
      </div>
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

const boardingHouse = ref(null)
const boarders = ref([])
const services = ref([])
const reviews = ref([])
const contractsCount = ref(0)
const reviewsCount = ref(0)
const averageRating = ref('0.0')
const loading = ref(true)

onMounted(async () => {
  await auth.checkAuth()
  
  // Wait a tick to ensure auth state is fully loaded
  await nextTick()
  
  // Handle both ref and direct access (user is a readonly ref from useState)
  const user = auth.user?.value || auth.user
  
  // Only allow boarders to access this page
  if (!user || user?.type !== 'boarder') {
    // Redirect to appropriate dashboard based on user type
    if (user?.type === 'admin') {
      navigateTo('/dashboard/admin')
    } else {
      navigateTo('/dashboard/boarder')
    }
    return
  }
  
  await fetchMyHouse()
})

const fetchMyHouse = async () => {
  loading.value = true
  try {
    // Get user ID - handle both ref and direct access
    const user = auth.user?.value || auth.user
    const boarderId = user?.id
    
    if (!boarderId) {
      console.error('No boarder ID found')
      boardingHouse.value = null
      loading.value = false
      return
    }
    
    // Fetch boarder's boarding house
    const boarderResponse = await api.get(`/boarders/${boarderId}`)
    const boarder = boarderResponse?.data || boarderResponse
    
    if (boarder && (boarder.boarding_house_id || boarder.boardingHouse?.id || boarder.boarding_house?.id)) {
      const houseId = boarder.boarding_house_id || boarder.boardingHouse?.id || boarder.boarding_house?.id
      const houseResponse = await api.get(`/boarding-houses/${houseId}`)
      const house = houseResponse?.data || houseResponse
      
      if (house && house.id) {
        boardingHouse.value = house
        
        // Fetch related data
        await Promise.all([
          fetchBoarders(houseId),
          fetchServices(houseId),
          fetchReviews(houseId)
        ])
        
        // Calculate statistics
        contractsCount.value = house.contracts?.length || house.contracts_count || 0
        reviewsCount.value = reviews.value.length
        if (reviews.value.length > 0) {
          const sum = reviews.value.reduce((acc, r) => acc + (r.rating || 0), 0)
          averageRating.value = (sum / reviews.value.length).toFixed(1)
        }
      } else {
        boardingHouse.value = null
      }
    } else {
      // No boarding house assigned
      boardingHouse.value = null
    }
  } catch (error) {
    console.error('Error fetching my house:', error)
    boardingHouse.value = null
  } finally {
    loading.value = false
  }
}

const fetchBoarders = async (houseId) => {
  try {
    const response = await api.get(`/boarding-houses/${houseId}/boarders`)
    boarders.value = Array.isArray(response) ? response : (response.data || [])
    // Filter out current user
    boarders.value = boarders.value.filter(b => b.id !== auth.user.value?.id)
  } catch (error) {
    console.error('Error fetching boarders:', error)
    boarders.value = []
  }
}

const fetchServices = async (houseId) => {
  try {
    const response = await api.get(`/boarding-houses/${houseId}/services`)
    services.value = Array.isArray(response) ? response : (response.data || [])
  } catch (error) {
    console.error('Error fetching services:', error)
    services.value = []
  }
}

const fetchReviews = async (houseId) => {
  try {
    const response = await api.get(`/boarding-houses/${houseId}/reviews`)
    reviews.value = Array.isArray(response) ? response : (response.data || [])
  } catch (error) {
    console.error('Error fetching reviews:', error)
    reviews.value = []
  }
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}
</script>

