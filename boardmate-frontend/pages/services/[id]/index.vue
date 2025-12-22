<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading service details...</p>
    </div>

    <div v-else-if="service">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-0">
          <i class="fas fa-concierge-bell mr-2"></i>{{ serviceName }}
        </h1>
        <div class="flex items-center gap-2">
          <NuxtLink
            :to="`/services/${service.id}/edit`"
            class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-edit mr-1"></i>Edit
          </NuxtLink>
          <NuxtLink
            to="/services"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-arrow-left mr-1"></i>Back to List
          </NuxtLink>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Main Content -->
        <div class="md:col-span-2 space-y-4">
          <!-- Service Details -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-info-circle mr-2"></i>Service Information
              </h5>
            </div>
            <div class="p-6">
              <div class="mb-4">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">{{ serviceName }}</h3>
                <p class="text-gray-600">{{ service.description || 'No description available' }}</p>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p><strong>Category:</strong> {{ service.category || 'N/A' }}</p>
                  <p><strong>Price:</strong> <span class="text-green-600 font-semibold">₱{{ formatCurrency(service.price) }}</span></p>
                </div>
                <div>
                  <p><strong>Availability:</strong> 
                    <span :class="getAvailabilityBadgeClass(service.availability)">
                      {{ service.availability === 'available' ? 'Available' : service.availability === 'unavailable' ? 'Unavailable' : 'Limited' }}
                    </span>
                  </p>
                </div>
              </div>

              <div v-if="service.notes" class="mt-4">
                <p><strong>Additional Notes:</strong></p>
                <p class="text-gray-600">{{ service.notes }}</p>
              </div>

              <div v-if="service.is_recurring" class="mt-4">
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                  <i class="fas fa-sync mr-1"></i>Recurring Service
                </span>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- Boarding House Info -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-building mr-2"></i>Boarding House
              </h5>
            </div>
            <div class="p-6">
              <p class="font-semibold text-gray-900 mb-2">{{ service.boarding_house?.name || service.boardingHouse?.name || 'N/A' }}</p>
              <p class="text-sm text-gray-600">{{ service.boarding_house?.address || service.boardingHouse?.address || '' }}</p>
              <NuxtLink
                v-if="service.boarding_house || service.boardingHouse"
                :to="`/boarding-houses/${service.boarding_house?.id || service.boardingHouse?.id}`"
                class="text-blue-600 hover:text-blue-700 text-sm font-semibold inline-flex items-center mt-2"
              >
                View Details <i class="fas fa-arrow-right ml-1"></i>
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
      <h4 class="text-xl font-semibold text-gray-600 mb-2">Service Not Found</h4>
      <NuxtLink
        to="/services"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
      >
        <i class="fas fa-arrow-left mr-2"></i>Back to Services
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

const service = ref(null)
const loading = ref(true)

// Computed property to handle both name and service_name fields
const serviceName = computed(() => {
  if (!service.value) return 'Service Details'
  return service.value.name || service.value.service_name || 'Service'
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchService()
})

const fetchService = async () => {
  loading.value = true
  try {
    const serviceId = route.params?.id || route.params.id
    
    if (!serviceId || serviceId === 'undefined' || serviceId === 'null') {
      console.error('No service ID provided in route')
      service.value = null
      loading.value = false
      return
    }
    
    console.log('Fetching service with ID:', serviceId)
    
    const response = await api.get(`/services/${serviceId}`)
    console.log('Service API response:', response)
    
    // Handle different response formats
    if (response && typeof response === 'object') {
      // If response has a data property (wrapped response)
      if (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
        service.value = response.data
      } 
      // If response is the service object directly (has id, name, or service_name)
      else if (response.id || response.name || response.service_name) {
        service.value = response
      }
      // If response is wrapped in another structure, try to extract
      else {
        service.value = response
      }
    } else {
      console.warn('Unexpected response format:', response)
      service.value = null
    }
    
    console.log('Service data set:', service.value)
    
    // Validate that we have service data
    if (!service.value || (!service.value.id && !service.value.name && !service.value.service_name)) {
      console.warn('Service data appears to be invalid or empty')
    }
  } catch (error) {
    console.error('Error fetching service:', error)
    service.value = null
  } finally {
    loading.value = false
  }
}

const getAvailabilityBadgeClass = (availability) => {
  const availLower = availability?.toLowerCase() || ''
  if (availLower === 'available') {
    return 'bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  } else if (availLower === 'unavailable') {
    return 'bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  } else {
    return 'bg-yellow-100 text-yellow-800 text-xs font-semibold px-2.5 py-0.5 rounded-full'
  }
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}
</script>

