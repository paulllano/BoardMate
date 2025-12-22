<template>
  <div class="container py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-5">
      <div>
        <h1 class="text-2xl font-bold mb-2">
          <i class="fas fa-star mr-2" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>Reviews
        </h1>
        <p class="text-gray-600 mb-0">{{ isAdmin ? 'View and manage boarding house reviews' : 'View your reviews' }}</p>
      </div>
      <NuxtLink
        v-if="!isAdmin"
        to="/reviews/create"
        :class="isAdmin ? 'bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600' : 'bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600'" class="text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition-all duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
      >
        <i class="fas fa-plus mr-2"></i>Add New Review
      </NuxtLink>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div v-if="loading" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-4xl mb-4" :class="isAdmin ? 'text-blue-600' : 'text-green-600'"></i>
        <p class="text-gray-600">Loading reviews...</p>
      </div>

      <div v-else-if="reviews.length > 0" class="overflow-x-auto">
        <table class="w-full">
          <thead :class="isAdmin ? 'bg-gradient-to-r from-blue-600 to-teal-500' : 'bg-gradient-to-r from-green-600 to-teal-500'" class="text-white">
            <tr>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">ID</th>
              <th v-if="isAdmin" class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Boarder</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Boarding House</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Rating</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Comment</th>
              <th class="px-6 py-4 text-left text-sm font-semibold uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="review in reviews" :key="review.id" class="hover:bg-gray-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ review.id }}</td>
              <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ review.is_anonymous ? 'Anonymous' : (review.boarder?.name || review.boarder_id || 'N/A') }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ getBoardingHouseName(review) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <span class="text-yellow-500 mr-2">
                    <i
                      v-for="i in 5"
                      :key="i"
                      :class="['fas', i <= review.rating ? 'fa-star' : 'far fa-star']"
                    ></i>
                  </span>
                  <span class="text-sm text-gray-600">({{ review.rating }}/5)</span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 max-w-xs truncate">
                  {{ review.comment && review.comment.length > 50 ? review.comment.substring(0, 50) + '...' : (review.comment || 'No comment') }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <div class="flex items-center gap-2">
                  <NuxtLink
                    :to="`/reviews/${review.id}`"
                    :class="isAdmin ? 'bg-blue-50 hover:bg-blue-100 text-blue-600' : 'bg-green-50 hover:bg-green-100 text-green-600'" class="p-2 rounded-lg transition inline-flex items-center justify-center"
                    style="min-width: 36px; min-height: 36px;"
                    title="View"
                  >
                    <i class="fas fa-eye" style="font-size: 14px;"></i>
                  </NuxtLink>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="text-center py-12">
        <i class="fas fa-star text-5xl text-gray-400 mb-4"></i>
        <h4 class="text-xl font-semibold text-gray-600 mb-2">No data yet</h4>
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

const reviews = ref([])
const loading = ref(true)

// Check if user is admin
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchReviews()
})

const fetchReviews = async () => {
  loading.value = true
  try {
    const response = await api.get('/reviews')
    console.log('Reviews API response:', response)
    
    // Handle paginated response from Laravel
    let allReviews = []
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, ... }
      allReviews = response.data
    } else if (Array.isArray(response)) {
      // Direct array response
      allReviews = response
    } else {
      allReviews = []
    }
    
    // Frontend filtering as safety measure - ensure boarders only see their own reviews
    const user = auth.user?.value || auth.user
    if (user && user.type === 'boarder' && !isAdmin.value) {
      reviews.value = allReviews.filter(review => {
        const reviewBoarderId = review.boarder_id || review.boarder?.id
        return reviewBoarderId === user.id
      })
    } else {
      reviews.value = allReviews
    }
    
    console.log('Reviews loaded:', reviews.value.length)
    console.log('First review sample:', reviews.value[0])
  } catch (error) {
    console.error('Error fetching reviews:', error)
    reviews.value = []
  } finally {
    loading.value = false
  }
}

const getBoardingHouseName = (review) => {
  // Handle both snake_case and camelCase formats
  if (review.boarding_house && review.boarding_house.name) {
    return review.boarding_house.name
  }
  if (review.boardingHouse && review.boardingHouse.name) {
    return review.boardingHouse.name
  }
  return 'N/A'
}
</script>

