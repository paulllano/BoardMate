<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold mb-0">Admin Dashboard</h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">
      <div class="bg-white rounded-2xl shadow-lg border-0 h-full hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center">
            <i class="fas fa-building text-blue-600 text-2xl mr-3"></i>
            <div>
              <div class="font-semibold text-gray-700">Boarding Houses</div>
              <NuxtLink to="/boarding-houses" class="text-sm text-blue-600 hover:underline">Manage</NuxtLink>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg border-0 h-full hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center">
            <i class="fas fa-concierge-bell text-green-600 text-2xl mr-3"></i>
            <div>
              <div class="font-semibold text-gray-700">Services</div>
              <NuxtLink to="/services" class="text-sm text-blue-600 hover:underline">Manage</NuxtLink>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg border-0 h-full hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center">
            <i class="fas fa-file-contract text-yellow-600 text-2xl mr-3"></i>
            <div>
              <div class="font-semibold text-gray-700">Contracts</div>
              <NuxtLink to="/contracts" class="text-sm text-blue-600 hover:underline">Manage</NuxtLink>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg border-0 h-full hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center">
            <i class="fas fa-credit-card text-red-600 text-2xl mr-3"></i>
            <div>
              <div class="font-semibold text-gray-700">Payments</div>
              <NuxtLink :to="`/payments${pendingPayments > 0 ? '?status=pending' : ''}`" class="text-sm text-blue-600 hover:underline">
                Manage
                <span v-if="pendingPayments > 0" class="ml-1 bg-yellow-500 text-yellow-900 text-xs px-2 py-0.5 rounded-full font-semibold">
                  {{ pendingPayments }}
                </span>
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Pending Items Alert -->
    <div v-if="pendingApplications > 0 || pendingPayments > 0" 
         class="bg-yellow-50 border-l-4 border-yellow-400 rounded-xl shadow-lg mb-4 p-5">
      <h5 class="font-bold text-yellow-800 mb-3 flex items-center">
        <i class="fas fa-exclamation-triangle mr-2"></i>Pending Items Requiring Attention
      </h5>
      <hr class="border-yellow-300 my-3">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div v-if="pendingApplications > 0">
          <p class="mb-2 text-sm text-yellow-700">
            <i class="fas fa-clipboard-list mr-2"></i>
            <strong>{{ pendingApplications }}</strong> pending application(s)
            <NuxtLink to="/applications?status=pending" 
                      class="ml-2 bg-yellow-500 hover:bg-yellow-600 text-yellow-900 text-xs px-3 py-1.5 rounded-lg font-semibold inline-block transition-all duration-200 hover:shadow-md">
              Review
            </NuxtLink>
          </p>
        </div>
        <div v-if="pendingPayments > 0">
          <p class="mb-2 text-sm text-yellow-700">
            <i class="fas fa-credit-card mr-2"></i>
            <strong>{{ pendingPayments }}</strong> pending payment(s)
            <NuxtLink to="/payments" 
                      class="ml-2 bg-yellow-500 hover:bg-yellow-600 text-yellow-900 text-xs px-3 py-1.5 rounded-lg font-semibold inline-block transition-all duration-200 hover:shadow-md">
              Review
            </NuxtLink>
          </p>
        </div>
      </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6 font-bold text-gray-900">
        Quick actions
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-2">
          <NuxtLink to="/boarding-houses/create" 
                    class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-building mr-2 text-sm"></i>Add Boarding House
          </NuxtLink>
          <NuxtLink to="/services/create" 
                    class="bg-gradient-to-r from-green-500 to-teal-500 hover:from-green-600 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-concierge-bell mr-2 text-sm"></i>Add Service
          </NuxtLink>
          <NuxtLink to="/contracts/create" 
                    class="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-file-contract mr-2 text-sm"></i>Create Contract
          </NuxtLink>
          <NuxtLink to="/payments/create" 
                    class="bg-gradient-to-r from-cyan-500 to-purple-500 hover:from-cyan-600 hover:to-purple-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-credit-card mr-2 text-sm"></i>Record Payment
          </NuxtLink>
          <NuxtLink to="/applications" 
                    class="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-clipboard-list mr-1 text-sm"></i>Review Applications
          </NuxtLink>
          <NuxtLink to="/boarders" 
                    class="bg-white border-2 border-blue-500 text-blue-600 hover:bg-blue-50 font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-users mr-1 text-sm"></i>Manage Boarders
          </NuxtLink>
          <NuxtLink to="/reviews" 
                    class="bg-white border-2 border-green-500 text-green-600 hover:bg-green-50 font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-star mr-1 text-sm"></i>View Reviews
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth'
import { useApi } from '~/composables/useApi'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const auth = useAuth()
const api = useApi()

const pendingApplications = ref(0)
const pendingPayments = ref(0)
const loading = ref(true)

onMounted(async () => {
  await auth.checkAuth()
  
  // Fetch statistics
  try {
    // Fetch pending applications count
    try {
      const applications = await api.get('/applications?status=pending')
      pendingApplications.value = Array.isArray(applications) ? applications.length : (applications.count || 0)
    } catch (err) {
      console.error('Error fetching applications:', err)
      pendingApplications.value = 0
    }
    
    // Fetch pending payments count
    try {
      const payments = await api.get('/payments?status=pending')
      pendingPayments.value = Array.isArray(payments) ? payments.length : (payments.count || 0)
    } catch (err) {
      console.error('Error fetching payments:', err)
      pendingPayments.value = 0
    }
  } catch (error) {
    console.error('Error fetching statistics:', error)
  } finally {
    loading.value = false
  }
})
</script>

