<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold mb-0">Boarder Dashboard</h1>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
      <div class="bg-white rounded-2xl shadow-lg border-0 h-full hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center">
            <i class="fas fa-search-location text-green-600 text-2xl mr-3"></i>
            <div>
              <div class="font-semibold text-gray-700">Browse Houses</div>
              <NuxtLink to="/boarding-houses/browse" class="text-sm text-green-600 hover:underline">Find nearby</NuxtLink>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg border-0 h-full hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center">
            <i class="fas fa-file-contract text-green-600 text-2xl mr-3"></i>
            <div>
              <div class="font-semibold text-gray-700">My Contracts</div>
              <NuxtLink to="/contracts" class="text-sm text-green-600 hover:underline">View</NuxtLink>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-white rounded-2xl shadow-lg border-0 h-full hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
        <div class="p-6">
          <div class="flex items-center">
            <i class="fas fa-credit-card text-yellow-600 text-2xl mr-3"></i>
            <div>
              <div class="font-semibold text-gray-700">My Payments</div>
              <NuxtLink to="/payments" class="text-sm text-green-600 hover:underline">View</NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6 font-bold text-gray-900">
        Quick actions
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
          <NuxtLink to="/boarding-houses/browse" 
                    class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-search-location mr-1 text-sm"></i>Browse Houses
          </NuxtLink>
          
          <NuxtLink to="/contracts" 
                    class="bg-gradient-to-r from-green-500 to-teal-500 hover:from-green-600 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-file-contract mr-1 text-sm"></i>My Contracts
          </NuxtLink>
          
          <NuxtLink to="/payments" 
                    class="bg-gradient-to-r from-green-500 to-teal-500 hover:from-green-600 hover:to-teal-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-credit-card mr-1 text-sm"></i>My Payments
          </NuxtLink>
          
          <NuxtLink to="/reviews/create" 
                    class="bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-star mr-2 text-sm"></i>Write Review
          </NuxtLink>
          
          <NuxtLink to="/reviews" 
                    class="bg-white border-2 border-green-500 text-green-600 hover:bg-green-50 font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-list mr-1 text-sm"></i>My Reviews
          </NuxtLink>
          
          <NuxtLink to="/settings/profile" 
                    class="bg-white border-2 border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center justify-center hover:-translate-y-0.5 hover:shadow-lg">
            <i class="fas fa-user-gear mr-1 text-sm"></i>Update Profile
          </NuxtLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const auth = useAuth()

onMounted(async () => {
  await auth.checkAuth()
})
</script>

