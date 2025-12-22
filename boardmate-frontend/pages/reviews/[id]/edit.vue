<template>
  <div class="container py-4">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-xl font-semibold mb-0">
        <i class="fas fa-edit mr-2"></i>Edit Review
      </h1>
      <NuxtLink
        :to="`/reviews/${route.params.id}`"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
      >
        <i class="fas fa-arrow-left mr-1"></i>Back to Review
      </NuxtLink>
    </div>

    <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
      <div class="p-6">
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded">
            {{ error }}
          </div>

          <div v-if="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Loading review...</p>
          </div>

          <div v-else class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Boarder <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.boarder_id"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                  <option value="">Select Boarder</option>
                  <option v-for="boarder in boarders" :key="boarder.id" :value="boarder.id">
                    {{ boarder.name }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                  Boarding House <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.boarding_house_id"
                  required
                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
                  <option value="">Select Boarding House</option>
                  <option v-for="house in boardingHouses" :key="house.id" :value="house.id">
                    {{ house.name }} - {{ house.address }}
                  </option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Rating <span class="text-red-500">*</span>
              </label>
              <div class="flex items-center gap-2">
                <button
                  v-for="i in 5"
                  :key="i"
                  type="button"
                  @click="form.rating = i"
                  :class="[
                    'text-3xl transition duration-200 cursor-pointer',
                    i <= form.rating ? 'text-yellow-500' : 'text-gray-300'
                  ]"
                >
                  <i class="fas fa-star"></i>
                </button>
                <span class="ml-4 text-gray-600">({{ form.rating }}/5)</span>
              </div>
              <small class="text-gray-500 mt-1 block">Click on a star to rate (1-5 stars)</small>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Review Comment <span class="text-red-500">*</span>
              </label>
              <textarea
                v-model="form.comment"
                rows="5"
                required
                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                placeholder="Share your experience..."
              ></textarea>
            </div>

            <div>
              <label class="flex items-center">
                <input
                  v-model="form.is_anonymous"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Post as anonymous review</span>
              </label>
            </div>

            <div class="flex justify-end gap-2 pt-4">
              <NuxtLink
                :to="`/reviews/${route.params.id}`"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2.5 px-4 rounded-lg transition duration-200 flex items-center"
              >
                <i class="fas fa-times mr-1"></i>Cancel
              </NuxtLink>
              <button
                type="submit"
                :disabled="submitting"
                class="bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-2.5 px-4 rounded-lg shadow-md transition duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
              >
                <i v-if="!submitting" class="fas fa-save mr-1"></i>
                <i v-else class="fas fa-spinner fa-spin mr-1"></i>
                {{ submitting ? 'Updating...' : 'Update Review' }}
              </button>
            </div>
          </div>
        </form>
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

const form = ref({
  boarder_id: '',
  boarding_house_id: '',
  rating: 0,
  comment: '',
  is_anonymous: false
})

const boarders = ref([])
const boardingHouses = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')

onMounted(async () => {
  await auth.checkAuth()
  await Promise.all([fetchReview(), fetchBoarders(), fetchBoardingHouses()])
})

const fetchReview = async () => {
  loading.value = true
  try {
    const response = await api.get(`/reviews/${route.params.id}`)
    const reviewData = response.data || response
    
    if (reviewData) {
      form.value = {
        boarder_id: reviewData.boarder_id || '',
        boarding_house_id: reviewData.boarding_house_id || '',
        rating: reviewData.rating || 0,
        comment: reviewData.comment || '',
        is_anonymous: reviewData.is_anonymous || false
      }
    }
  } catch (err) {
    console.error('Error fetching review:', err)
    error.value = 'Failed to load review. Please try again.'
  } finally {
    loading.value = false
  }
}

const fetchBoarders = async () => {
  try {
    const response = await api.get('/boarders')
    boarders.value = Array.isArray(response) ? response : (response.data || [])
  } catch (err) {
    console.error('Error fetching boarders:', err)
    boarders.value = []
  }
}

const fetchBoardingHouses = async () => {
  try {
    const response = await api.get('/boarding-houses')
    boardingHouses.value = Array.isArray(response) ? response : (response.data || [])
  } catch (err) {
    console.error('Error fetching boarding houses:', err)
    boardingHouses.value = []
  }
}

const handleSubmit = async () => {
  if (form.value.rating === 0) {
    error.value = 'Please select a rating.'
    return
  }

  submitting.value = true
  error.value = ''
  
  try {
    await api.put(`/reviews/${route.params.id}`, form.value)
    navigateTo(`/reviews/${route.params.id}`)
  } catch (err) {
    error.value = err?.message || 'Failed to update review. Please try again.'
    if (err?.response?.errors) {
      const errors = Object.values(err.response.errors).flat()
      error.value = errors.join(', ')
    }
  } finally {
    submitting.value = false
  }
}
</script>

