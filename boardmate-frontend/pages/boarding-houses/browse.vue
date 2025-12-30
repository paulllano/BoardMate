<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold mb-0">
        <i class="fas fa-search-location mr-2"></i>Browse Boarding Houses
      </h1>
    </div>

    <!-- Search Bar -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden mb-4">
      <div class="p-6">
        <form @submit.prevent="handleSearch" class="space-y-4">
          <div class="flex gap-2">
            <div class="flex-1">
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-search mr-2 text-green-600"></i>Search Boarding Houses
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <i class="fas fa-search text-gray-400"></i>
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
                  placeholder="Search by name, address, or description..."
                />
              </div>
            </div>
            <div class="flex items-end">
              <button
                type="submit"
                class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
              >
                <i class="fas fa-search mr-1"></i>Search
              </button>
            </div>
          </div>
          <div v-if="searchQuery" class="flex items-center gap-2">
            <button
              @click="clearSearch"
              type="button"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-semibold py-1.5 px-3 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-times mr-1"></i>Clear Search
            </button>
            <span class="text-gray-600 text-sm">
              Showing results for: <strong>"{{ searchQuery }}"</strong>
            </span>
          </div>
        </form>
      </div>
    </div>

    <!-- Boarding Houses Grid -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div class="p-6">
        <div v-if="loading" class="text-center py-12">
          <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
          <p class="text-gray-600">Loading boarding houses...</p>
        </div>

        <div v-else-if="houses.length > 0" class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div
            v-for="house in houses"
            :key="house.id"
            class="bg-white border-2 border-gray-200 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 hover:-translate-y-1 overflow-hidden"
          >
            <div class="p-6">
              <h5 class="font-bold text-gray-900 mb-2">{{ house.name }}</h5>
              <div class="mb-2">
                <span 
                  v-if="house.gender_preference"
                  :class="{
                    'bg-blue-100 text-blue-800': house.gender_preference === 'male',
                    'bg-pink-100 text-pink-800': house.gender_preference === 'female',
                    'bg-purple-100 text-purple-800': house.gender_preference === 'everyone'
                  }"
                  class="text-xs font-semibold px-2 py-1 rounded-full inline-flex items-center"
                >
                  <i 
                    v-if="house.gender_preference === 'male'" 
                    class="fas fa-mars mr-1"
                  ></i>
                  <i 
                    v-else-if="house.gender_preference === 'female'" 
                    class="fas fa-venus mr-1"
                  ></i>
                  <i 
                    v-else 
                    class="fas fa-venus-mars mr-1"
                  ></i>
                  {{ house.gender_preference === 'male' ? 'Male Only' : house.gender_preference === 'female' ? 'Female Only' : 'Everyone' }}
                </span>
              </div>
              <div class="text-gray-600 text-sm mb-2">{{ house.address }}</div>
              <p class="text-gray-700 text-sm mb-4 line-clamp-3">
                {{ house.description && house.description.length > 120 
                  ? house.description.substring(0, 120) + '...' 
                  : house.description || 'No description' }}
              </p>
              <NuxtLink
                :to="`/boarding-houses/${house.id}`"
                class="bg-green-50 hover:bg-green-100 text-green-600 font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center text-sm"
              >
                View details <i class="fas fa-arrow-right ml-1"></i>
              </NuxtLink>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-12">
          <i class="fas fa-search-location text-5xl text-gray-400 mb-4"></i>
          <h4 class="text-xl font-semibold text-gray-600 mb-2">
            {{ searchQuery ? 'No results found' : 'No boarding houses available' }}
          </h4>
          <p class="text-gray-500 mb-4">
            <span v-if="searchQuery">
              No boarding houses match your search for "<strong>{{ searchQuery }}</strong>"
            </span>
            <span v-else>Check back later for new listings.</span>
          </p>
          <button
            v-if="searchQuery"
            @click="clearSearch"
            class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
          >
            <i class="fas fa-arrow-left mr-2"></i>View All Boarding Houses
          </button>
        </div>

        <!-- Pagination -->
        <div v-if="houses.length > 0 && totalPages > 1" class="flex justify-center mt-6">
          <div class="flex gap-2">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200"
            >
              <i class="fas fa-chevron-left"></i>
            </button>
            <span class="bg-gray-100 text-gray-700 font-semibold py-2 px-4 rounded-lg">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="bg-gray-200 hover:bg-gray-300 disabled:opacity-50 disabled:cursor-not-allowed text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200"
            >
              <i class="fas fa-chevron-right"></i>
            </button>
          </div>
        </div>
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
const route = useRoute()

const houses = ref([])
const loading = ref(true)
const searchQuery = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

onMounted(async () => {
  await auth.checkAuth()
  
  // Redirect admins to their boarding houses management page
  const user = auth.user?.value || auth.user
  if (user?.type === 'admin') {
    await navigateTo('/boarding-houses')
    return
  }
  
  // Get search query from URL if present
  searchQuery.value = route.query.search || ''
  currentPage.value = parseInt(route.query.page) || 1
  await fetchHouses()
})

const fetchHouses = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (searchQuery.value) {
      params.append('search', searchQuery.value.trim())
    }
    if (currentPage.value > 1) {
      params.append('page', currentPage.value.toString())
    }
    // Set per_page for browse (9 items per page for grid layout)
    params.append('per_page', '9')
    
    const queryString = params.toString()
    // Use the public browse endpoint
    const endpoint = queryString ? `/boarding-houses/browse?${queryString}` : '/boarding-houses/browse?per_page=9'
    
    console.log('Fetching houses from:', endpoint)
    const response = await api.get(endpoint)
    console.log('Boarding houses API response:', response)
    
    // Handle paginated response from Laravel
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, last_page: 2, ... }
      houses.value = response.data
      totalPages.value = response.last_page || response.total ? Math.ceil((response.total || response.data.length) / 9) : 1
      currentPage.value = response.current_page || 1
    } else if (Array.isArray(response)) {
      // Direct array response
      houses.value = response
      totalPages.value = 1
      currentPage.value = 1
    } else {
      houses.value = []
      totalPages.value = 1
    }
    
    console.log('Houses loaded:', houses.value.length, 'Total pages:', totalPages.value)
  } catch (error) {
    console.error('Error fetching boarding houses:', error)
    houses.value = []
    totalPages.value = 1
  } finally {
    loading.value = false
  }
}

const handleSearch = async () => {
  currentPage.value = 1
  await fetchHouses()
  // Update URL with search query
  await navigateTo({
    path: '/boarding-houses/browse',
    query: searchQuery.value ? { search: searchQuery.value.trim() } : {}
  }, { replace: true })
}

const clearSearch = async () => {
  searchQuery.value = ''
  currentPage.value = 1
  await fetchHouses()
  navigateTo('/boarding-houses/browse')
}

const goToPage = async (page) => {
  if (page < 1 || page > totalPages.value) return
  currentPage.value = page
  await fetchHouses()
  navigateTo({
    path: '/boarding-houses/browse',
    query: {
      ...(searchQuery.value ? { search: searchQuery.value } : {}),
      page: page.toString()
    }
  })
}
</script>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>

