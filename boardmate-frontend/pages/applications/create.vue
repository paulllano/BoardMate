<template>
  <div>
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-green-600 mb-4"></i>
      <p class="text-gray-600">Loading...</p>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Form -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-xl shadow-md border-0 p-6">
          <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
              <i class="fas fa-paper-plane mr-2 text-green-600"></i>
              Apply for {{ boardingHouse?.name || 'Boarding House' }}
            </h1>
            <NuxtLink
              v-if="boardingHouse"
              :to="`/boarding-houses/${boardingHouse.id}`"
              class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
            >
              <i class="fas fa-arrow-left mr-2"></i>Back
            </NuxtLink>
          </div>

          <form @submit.prevent="handleSubmit" class="space-y-6">
            <div v-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
              {{ error }}
            </div>

            <!-- Transfer Warning -->
            <div v-if="currentBoardingHouse" class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
              <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 mt-1"></i>
                <div>
                  <strong class="text-yellow-800">Transfer Notice:</strong>
                  <p class="text-yellow-700 text-sm mt-1">
                    You are currently assigned to <strong>{{ currentBoardingHouse.name }}</strong>. 
                    If this application is approved, you will be transferred to <strong>{{ boardingHouse?.name }}</strong>.
                  </p>
                </div>
              </div>
            </div>

            <!-- Boarding House Info -->
            <div v-if="boardingHouse">
              <h6 class="text-sm font-semibold text-gray-500 mb-3">Boarding House Details</h6>
              <div class="bg-gray-50 p-4 rounded-lg">
                <h5 class="font-bold text-gray-900 mb-1">{{ boardingHouse.name }}</h5>
                <p class="text-gray-600">{{ boardingHouse.address }}</p>
              </div>
            </div>

            <!-- User Info -->
            <div>
              <h6 class="text-sm font-semibold text-gray-500 mb-3">Your Information</h6>
              <div class="bg-gray-50 p-4 rounded-lg">
                <p class="mb-1"><strong>Name:</strong> {{ auth.user?.name }}</p>
                <p class="mb-1"><strong>Email:</strong> {{ auth.user?.email }}</p>
                <p class="mb-0"><strong>Phone:</strong> {{ auth.user?.phone || 'Not provided' }}</p>
              </div>
            </div>

            <!-- Advance Payment Section -->
            <div v-if="boardingHouse && boardingHouse.advance_payment_amount > 0" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
              <h6 class="text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-money-bill-wave mr-2 text-blue-600"></i>
                Advance Payment Required
              </h6>
              <div class="mb-4">
                <p class="text-gray-700 mb-3">
                  <strong>Amount:</strong> <span class="text-lg font-bold text-blue-600">₱{{ formatCurrency(boardingHouse.advance_payment_amount) }}</span>
                </p>
                <p class="text-sm text-gray-600 mb-3">
                  This advance payment serves as a reservation fee and will be used as credit toward your first month's rent when your contract is created.
                </p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                      <i class="fas fa-credit-card mr-2 text-blue-600"></i>
                      Payment Method <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.advance_payment_method"
                      required
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    >
                      <option value="">Select payment method</option>
                      <option value="Cash">Cash</option>
                      <option value="GCash">GCash</option>
                    </select>
                  </div>
                  
                  <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                      <i class="fas fa-hashtag mr-2 text-blue-600"></i>
                      Reference Number
                      <span v-if="form.advance_payment_method === 'GCash'" class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.advance_payment_reference"
                      type="text"
                      :required="form.advance_payment_method === 'GCash'"
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                      placeholder="Enter reference number"
                    />
                    <p class="text-xs text-gray-500 mt-1">
                      <span v-if="form.advance_payment_method === 'GCash'">Required for GCash payments</span>
                      <span v-else>Optional for Cash payments</span>
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Policies Section -->
            <div v-if="boardingHouse" class="border-2 border-gray-300 rounded-lg p-4">
              <h6 class="text-sm font-semibold text-gray-700 mb-3">
                <i class="fas fa-file-contract mr-2 text-green-600"></i>
                House Policies & Terms <span class="text-red-500">*</span>
              </h6>
              <div v-if="boardingHouse.policies" class="bg-gray-50 border border-gray-200 rounded p-4 max-h-64 overflow-y-auto mb-4">
                <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ boardingHouse.policies }}</pre>
              </div>
              <div v-else class="bg-yellow-50 border border-yellow-200 rounded p-4 mb-4">
                <p class="text-sm text-yellow-800">
                  <i class="fas fa-info-circle mr-2"></i>
                  No specific policies have been set for this boarding house. By submitting this application, you agree to follow standard boarding house rules and regulations.
                </p>
              </div>
              <div class="flex items-start">
                <input
                  id="policies_accepted"
                  v-model="form.policies_accepted"
                  type="checkbox"
                  required
                  class="mt-1 mr-2 w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                />
                <label for="policies_accepted" class="text-sm text-gray-700">
                  I have read and accept the policies and terms & conditions stated above. <span class="text-red-500">*</span>
                </label>
              </div>
            </div>

            <!-- Message -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-comment mr-2 text-green-600"></i>
                Message to Property Owner (Optional)
              </label>
              <textarea
                v-model="form.message"
                rows="4"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                placeholder="Tell the property owner why you'd like to stay here..."
              ></textarea>
              <p class="text-sm text-gray-500 mt-1">
                This message will be sent to the property owner along with your application.
              </p>
            </div>

            <!-- Info Alert -->
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
              <div class="flex items-start">
                <i class="fas fa-info-circle text-green-600 mr-3 mt-1"></i>
                <div>
                  <strong class="text-blue-800">Note:</strong>
                  <p class="text-blue-700 text-sm mt-1">
                    Your application will be reviewed by the property owner. You will be notified of the decision via email.
                    <span v-if="boardingHouse && boardingHouse.advance_payment_amount > 0">
                      If your application is rejected, your advance payment will be automatically refunded.
                    </span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-between">
              <NuxtLink
                v-if="boardingHouse"
                :to="`/boarding-houses/${boardingHouse.id}`"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-200 flex items-center"
              >
                <i class="fas fa-times mr-2"></i>Cancel
              </NuxtLink>
              <button
                type="submit"
                :disabled="loading || submitting || !boardingHouse || (boardingHouse && boardingHouse.advance_payment_amount > 0 && !form.advance_payment_method) || !form.policies_accepted"
                class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center ml-auto"
              >
                <i v-if="!submitting" class="fas fa-paper-plane mr-2"></i>
                <i v-else class="fas fa-spinner fa-spin mr-2"></i>
                {{ submitting ? 'Submitting...' : 'Submit Application' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Sidebar Info -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-md border-0 p-6 sticky top-6">
          <h5 class="font-bold text-gray-900 mb-4">
            <i class="fas fa-info-circle mr-2 text-green-600"></i>Application Process
          </h5>
          <div class="space-y-4">
            <div class="flex items-start">
              <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                1
              </div>
              <div>
                <h6 class="font-semibold text-gray-900 mb-1">Submit Application</h6>
                <p class="text-sm text-gray-600">Fill out the form and submit your application.</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                2
              </div>
              <div>
                <h6 class="font-semibold text-gray-900 mb-1">Review Process</h6>
                <p class="text-sm text-gray-600">Property owner will review your application.</p>
              </div>
            </div>
            <div class="flex items-start">
              <div class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-sm mr-3 flex-shrink-0">
                3
              </div>
              <div>
                <h6 class="font-semibold text-gray-900 mb-1">Notification</h6>
                <p class="text-sm text-gray-600">You'll receive an email about the decision.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()
const route = useRoute()

const boardingHouse = ref(null)
const currentBoardingHouse = ref(null)
const loading = ref(true)
const submitting = ref(false)
const error = ref('')

const form = ref({
  message: '',
  advance_payment_method: '',
  advance_payment_reference: '',
  advance_payment_amount: 0,
  policies_accepted: false
})

onMounted(async () => {
  await auth.checkAuth()
  if (auth.user?.type !== 'boarder') {
    navigateTo('/dashboard/boarder')
  }
  
  // Check if boarder has a current boarding house
  const user = auth.user?.value || auth.user
  if (user?.boarding_house_id) {
    try {
      const houseData = await api.get(`/boarding-houses/${user.boarding_house_id}`)
      currentBoardingHouse.value = houseData?.data || houseData
    } catch (err) {
      console.error('Error fetching current boarding house:', err)
    }
  }
  
  const houseId = route.query.boarding_house_id
  if (houseId) {
    await fetchBoardingHouse(houseId)
  } else {
    error.value = 'No boarding house specified'
    loading.value = false
  }
  
  // Set advance payment amount when boarding house is loaded
  watch(() => boardingHouse.value, (house) => {
    if (house && house.advance_payment_amount) {
      form.value.advance_payment_amount = house.advance_payment_amount
    }
  })
})

const fetchBoardingHouse = async (id) => {
  loading.value = true
  try {
    const response = await api.get(`/boarding-houses/${id}/view`)
    // Handle different response formats
    boardingHouse.value = response?.data || response
    // Set advance payment amount in form
    if (boardingHouse.value && boardingHouse.value.advance_payment_amount) {
      form.value.advance_payment_amount = boardingHouse.value.advance_payment_amount
    }
  } catch (err) {
    // Try regular show endpoint if view fails
    try {
      const response = await api.get(`/boarding-houses/${id}`)
      boardingHouse.value = response?.data || response
      if (boardingHouse.value && boardingHouse.value.advance_payment_amount) {
        form.value.advance_payment_amount = boardingHouse.value.advance_payment_amount
      }
    } catch (err2) {
      error.value = 'Failed to load boarding house information.'
      console.error(err2)
    }
  } finally {
    loading.value = false
  }
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const handleSubmit = async () => {
  submitting.value = true
  error.value = ''
  
  // Validate advance payment if required
  if (boardingHouse.value && boardingHouse.value.advance_payment_amount > 0) {
    if (!form.value.advance_payment_method) {
      error.value = 'Please select a payment method for the advance payment.'
      submitting.value = false
      return
    }
    if (form.value.advance_payment_method === 'GCash' && !form.value.advance_payment_reference) {
      error.value = 'Please enter a reference number for GCash payment.'
      submitting.value = false
      return
    }
  }
  
  // Validate policies acceptance
  if (!form.value.policies_accepted) {
    error.value = 'You must accept the policies and terms & conditions to submit your application.'
    submitting.value = false
    return
  }
  
  try {
    const payload = {
      boarding_house_id: boardingHouse.value.id,
      message: form.value.message,
      policies_accepted: form.value.policies_accepted
    }
    
    // Add advance payment info if required
    if (boardingHouse.value && boardingHouse.value.advance_payment_amount > 0) {
      payload.advance_payment_method = form.value.advance_payment_method
      payload.advance_payment_reference = form.value.advance_payment_reference || null
      payload.advance_payment_amount = form.value.advance_payment_amount
    }
    
    const response = await api.post(`/applications`, payload)
    
    // Update current boarding house if transfer info is provided
    if (response.is_transfer && response.current_boarding_house) {
      currentBoardingHouse.value = response.current_boarding_house
    }
    
    navigateTo('/applications')
  } catch (err) {
    error.value = err?.message || 'Failed to submit application. Please try again.'
  } finally {
    submitting.value = false
  }
}
</script>

