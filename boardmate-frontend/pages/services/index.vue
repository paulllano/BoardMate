<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-5">
      <div>
        <h1 class="text-2xl font-bold mb-2">
          <i class="fas fa-concierge-bell mr-2 text-blue-600"></i>Services
        </h1>
        <p class="text-gray-600 mb-0">Manage available services for boarding houses</p>
      </div>
      <NuxtLink
        to="/services/create"
        class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
      >
        <i class="fas fa-concierge-bell mr-2"></i>Add New Service
      </NuxtLink>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
        <p class="text-gray-600">Loading services...</p>
      </div>

      <div v-else-if="services.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gradient-to-r from-blue-600 to-teal-500 text-white">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">ID</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Name</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Description</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Price</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="service in services" :key="service.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ service.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-semibold text-gray-900">{{ service.name }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900">
                  {{ service.description && service.description.length > 50 ? service.description.substring(0, 50) + '...' : (service.description || 'No description') }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">₱{{ formatCurrency(service.price) }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getAvailabilityBadgeClass(service.availability)">
                  {{ service.availability === 'available' ? 'Available' : service.availability === 'unavailable' ? 'Unavailable' : 'Limited' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center gap-2">
                  <NuxtLink
                    :to="`/services/${service.id}`"
                    class="bg-blue-50 hover:bg-blue-100 text-blue-600 p-2 rounded-lg transition"
                    title="View"
                  >
                    <i class="fas fa-eye"></i>
                  </NuxtLink>
                  <NuxtLink
                    :to="`/services/${service.id}/edit`"
                    class="bg-yellow-50 hover:bg-yellow-100 text-yellow-600 p-2 rounded-lg transition"
                    title="Edit"
                  >
                    <i class="fas fa-edit"></i>
                  </NuxtLink>
                  <button
                    @click="handleDelete(service)"
                    class="bg-red-50 hover:bg-red-100 text-red-600 p-2 rounded-lg transition"
                    title="Delete"
                  >
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-12">
        <i class="fas fa-concierge-bell text-5xl text-gray-400 mb-4"></i>
        <h4 class="text-xl font-semibold text-gray-600 mb-2">No services found</h4>
        <p class="text-gray-500 mb-4">Get started by adding your first service.</p>
        <NuxtLink
          to="/services/create"
          class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 inline-flex items-center"
        >
          <i class="fas fa-concierge-bell mr-2"></i>Add First Service
        </NuxtLink>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-model:show="showDeleteModal"
      title="Delete Service"
      message="Are you sure you want to delete this service? This action cannot be undone."
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
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import ConfirmModal from '~/components/ConfirmModal.vue'
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()

const services = ref([])
const loading = ref(true)
const showDeleteModal = ref(false)
const selectedService = ref(null)

onMounted(async () => {
  await auth.checkAuth()
  await fetchServices()
})

const fetchServices = async () => {
  loading.value = true
  try {
    const response = await api.get('/services')
    services.value = Array.isArray(response) ? response : (response.data || [])
  } catch (error) {
    console.error('Error fetching services:', error)
    services.value = []
  } finally {
    loading.value = false
  }
}

const handleDelete = (service) => {
  selectedService.value = service
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  if (!selectedService.value) return
  
  try {
    await api.delete(`/services/${selectedService.value.id}`)
    await fetchServices()
    showDeleteModal.value = false
    selectedService.value = null
    successTitle.value = 'Service Deleted'
    successMessage.value = 'Service has been deleted successfully.'
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error deleting service:', error)
    errorTitle.value = 'Delete Failed'
    errorMessage.value = error?.response?.data?.message || error?.message || 'Failed to delete service. Please try again.'
    showErrorModal.value = true
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

