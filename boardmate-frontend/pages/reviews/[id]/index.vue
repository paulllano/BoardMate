<template>
  <div>
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading review details...</p>
    </div>

    <div v-else-if="review" class="space-y-6">
      <!-- Header -->
      <div class="bg-white rounded-xl shadow-md border-0 p-6">
        <div class="flex items-start justify-between mb-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Review Details</h1>
            <div class="flex items-center gap-2">
              <div class="text-yellow-500 text-2xl">
                <i
                  v-for="i in 5"
                  :key="i"
                  :class="['fas', i <= review.rating ? 'fa-star' : 'fa-star-o']"
                ></i>
              </div>
              <span class="text-lg font-semibold text-gray-700">({{ review.rating }}/5)</span>
            </div>
          </div>
          <div class="flex gap-2">
            <NuxtLink
              v-if="auth.user?.type === 'admin' || (auth.user?.type === 'boarder' && review?.boarder_id === auth.user?.id)"
              :to="`/reviews/${route.params.id}/edit`"
              class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-edit mr-1"></i>Edit
            </NuxtLink>
            <NuxtLink
              to="/reviews"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-arrow-left mr-2"></i>Back
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Review Details -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Review Information</h2>
          <div class="space-y-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Reviewer</div>
              <div class="font-semibold text-gray-900">
                {{ review.is_anonymous ? 'Anonymous' : (review.boarder?.name || 'N/A') }}
              </div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Rating</div>
              <div class="flex items-center gap-2">
                <div class="text-yellow-500">
                  <i
                    v-for="i in 5"
                    :key="i"
                    :class="['fas', i <= review.rating ? 'fa-star' : 'fa-star-o']"
                  ></i>
                </div>
                <span class="text-gray-700">({{ review.rating }}/5)</span>
              </div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Date</div>
              <div class="text-gray-700">{{ formatDate(review.created_at) }}</div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <h2 class="text-lg font-bold text-gray-900 mb-4">Boarding House</h2>
          <div class="space-y-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Name</div>
              <div class="font-semibold text-gray-900">{{ review.boarding_house?.name || review.boardingHouse?.name || 'N/A' }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Address</div>
              <div class="text-gray-700">{{ review.boarding_house?.address || review.boardingHouse?.address || 'N/A' }}</div>
            </div>
            <NuxtLink
              v-if="review.boarding_house || review.boardingHouse"
              :to="`/boarding-houses/${review.boarding_house?.id || review.boardingHouse?.id}`"
              class="text-blue-600 hover:text-blue-700 text-sm font-semibold inline-flex items-center"
            >
              View Details <i class="fas fa-arrow-right ml-1"></i>
            </NuxtLink>
          </div>
        </div>
      </div>

      <!-- Comment -->
      <div v-if="review.comment" class="bg-white rounded-xl shadow-md border-0 p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Comment</h2>
        <p class="text-gray-700 whitespace-pre-wrap">{{ review.comment }}</p>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
      <h4 class="text-xl font-semibold text-gray-600 mb-2">Review Not Found</h4>
      <NuxtLink
        to="/reviews"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
      >
        <i class="fas fa-arrow-left mr-2"></i>Back to Reviews
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

const review = ref(null)
const loading = ref(true)

onMounted(async () => {
  await auth.checkAuth()
  await fetchReview()
})

const fetchReview = async () => {
  loading.value = true
  try {
    const reviewId = route.params?.id || route.params.id
    
    if (!reviewId || reviewId === 'undefined' || reviewId === 'null') {
      console.error('No review ID provided in route')
      review.value = null
      loading.value = false
      return
    }
    
    console.log('Fetching review with ID:', reviewId)
    const response = await api.get(`/reviews/${reviewId}`)
    console.log('Review API response:', response)
    
    // Handle different response formats
    if (response && typeof response === 'object') {
      // If response has a data property (wrapped response)
      if (response.data && typeof response.data === 'object' && !Array.isArray(response.data)) {
        review.value = response.data
      } 
      // If response is the review object directly (has id, rating, or comment)
      else if (response.id || response.rating || response.comment || response.boarder || response.boardingHouse || response.boarding_house) {
        review.value = response
      }
      // If response is wrapped in another structure, try to extract
      else {
        review.value = response
      }
    } else {
      console.warn('Unexpected response format:', response)
      review.value = null
    }
    
    console.log('Review data set:', review.value)
    
    // Validate that we have review data
    if (!review.value || (!review.value.id && !review.value.rating && !review.value.boarder && !review.value.boardingHouse && !review.value.boarding_house)) {
      console.warn('Review data appears to be invalid or empty')
    }
  } catch (error) {
    console.error('Error fetching review:', error)
    review.value = null
  } finally {
    loading.value = false
  }
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
}
</script>

