<template>
  <div>
    <div class="bg-white rounded-xl shadow-md border-0 p-6">
      <h1 class="text-2xl font-bold text-gray-900 mb-6">
        <i class="fas fa-star mr-2 text-yellow-600"></i>
        Write a Review
      </h1>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
          {{ error }}
        </div>

        <!-- Boarding House Selection -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-building mr-2 text-green-600"></i>Boarding House *
          </label>
          <select
            v-model="form.boarding_house_id"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
          >
            <option value="">Select a boarding house</option>
            <option
              v-for="house in boardingHouses"
              :key="house.id"
              :value="house.id"
            >
              {{ house.name }} - {{ house.address }}
            </option>
          </select>
        </div>

        <!-- Rating -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-star mr-2 text-yellow-600"></i>Rating *
          </label>
          <div class="flex items-center gap-2">
            <button
              v-for="i in 5"
              :key="i"
              type="button"
              @click="form.rating = i"
              :class="[
                'text-3xl transition duration-200',
                i <= form.rating ? 'text-yellow-500' : 'text-gray-300'
              ]"
            >
              <i class="fas fa-star"></i>
            </button>
            <span class="ml-4 text-gray-600">({{ form.rating }}/5)</span>
          </div>
        </div>

        <!-- Comment -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="fas fa-comment mr-2 text-green-600"></i>Comment
          </label>
          <textarea
            v-model="form.comment"
            rows="5"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
            placeholder="Share your experience..."
          ></textarea>
        </div>

        <!-- Anonymous Option -->
        <div class="flex items-center">
          <input
            v-model="form.is_anonymous"
            type="checkbox"
            id="anonymous"
            class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
          />
          <label for="anonymous" class="ml-2 text-sm text-gray-700">
            Post as anonymous
          </label>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
          <NuxtLink
            to="/reviews"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-times mr-2"></i>Cancel
          </NuxtLink>
          <button
            type="submit"
            :disabled="loading"
            class="bg-gradient-to-r from-yellow-600 to-orange-500 hover:from-yellow-700 hover:to-orange-600 disabled:opacity-50 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center ml-auto"
          >
            <i v-if="!loading" class="fas fa-paper-plane mr-2"></i>
            <i v-else class="fas fa-spinner fa-spin mr-2"></i>
            {{ loading ? 'Submitting...' : 'Submit Review' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      v-model:show="showSuccessModal"
      title="Review Submitted Successfully!"
      message="Your review has been submitted successfully. Thank you for sharing your experience!"
    />
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import SuccessModal from '~/components/SuccessModal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()

const boardingHouses = ref([])
const loading = ref(false)
const error = ref('')
const showSuccessModal = ref(false)

const form = ref({
  boarding_house_id: '',
  rating: 0,
  comment: '',
  is_anonymous: false
})

onMounted(async () => {
  await auth.checkAuth()
  
  // Wait a tick to ensure auth state is fully loaded
  await nextTick()
  
  // Handle both ref and direct access (user is a readonly ref from useState)
  const user = auth.user?.value || auth.user
  
  // Only allow boarders to create reviews
  if (!user || user?.type !== 'boarder') {
    // Redirect to appropriate dashboard based on user type
    if (user?.type === 'admin') {
      navigateTo('/dashboard/admin')
    } else {
      navigateTo('/dashboard/boarder')
    }
    return
  }
  
  await fetchBoardingHouses()
})

const fetchBoardingHouses = async () => {
  try {
    const response = await api.get('/boarding-houses')
    console.log('Boarding houses API response:', response)
    
    // Handle paginated response from Laravel
    if (response && response.data && Array.isArray(response.data)) {
      // Paginated response: { data: [...], current_page: 1, ... }
      boardingHouses.value = response.data
    } else if (Array.isArray(response)) {
      // Direct array response
      boardingHouses.value = response
    } else {
      boardingHouses.value = []
    }
    
    console.log('Boarding houses loaded:', boardingHouses.value.length)
  } catch (error) {
    console.error('Error fetching boarding houses:', error)
    boardingHouses.value = []
  }
}

const handleSubmit = async () => {
  if (form.value.rating === 0) {
    error.value = 'Please select a rating.'
    return
  }

  loading.value = true
  error.value = ''
  
  try {
    // Get authenticated user
    const user = auth.user?.value || auth.user
    
    // Prepare review data
    const reviewData = { ...form.value }
    
    // Set boarder_id from authenticated user (for boarders)
    if (user && user.type === 'boarder') {
      reviewData.boarder_id = user.id
    }
    
    // Ensure is_anonymous is explicitly set as boolean
    // Vue checkbox sends true/false, but ensure it's properly formatted
    reviewData.is_anonymous = Boolean(reviewData.is_anonymous || false)
    
    await api.post('/reviews', reviewData)
    
    // Show success modal
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo('/reviews')
    }, 2000)
  } catch (err) {
    console.error('Error submitting review:', err)
    // Handle validation errors
    if (err?.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      error.value = errors.join(', ')
    } else {
      error.value = err?.response?.data?.message || err?.message || 'Failed to submit review. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>

