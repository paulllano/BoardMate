<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading boarding house details...</p>
    </div>

    <div v-else-if="house">
      <!-- Header -->
      <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-3">
          <h1 class="text-xl font-semibold mb-0">
            <i class="fas fa-building mr-2"></i>{{ house.name || 'Boarding House Details' }}
          </h1>
          <span 
            v-if="house.gender_preference"
            :class="{
              'bg-blue-100 text-blue-800': house.gender_preference === 'male',
              'bg-pink-100 text-pink-800': house.gender_preference === 'female',
              'bg-purple-100 text-purple-800': house.gender_preference === 'everyone'
            }"
            class="text-xs font-semibold px-3 py-1.5 rounded-full flex items-center"
          >
            <i 
              v-if="house.gender_preference === 'male'" 
              class="fas fa-mars mr-1.5"
            ></i>
            <i 
              v-else-if="house.gender_preference === 'female'" 
              class="fas fa-venus mr-1.5"
            ></i>
            <i 
              v-else 
              class="fas fa-venus-mars mr-1.5"
            ></i>
            {{ house.gender_preference === 'male' ? 'Male Only' : house.gender_preference === 'female' ? 'Female Only' : 'Everyone' }}
          </span>
        </div>
        <div class="flex items-center gap-2">
          <button
            v-if="isBoarder && canApply"
            @click="showApplyModal = true"
            class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-200 flex items-center hover:-translate-y-0.5 hover:shadow-lg"
          >
            <i class="fas fa-paper-plane mr-1"></i>Apply Now
          </button>
          <div
            v-else-if="isBoarder && !canApply"
            class="bg-gray-100 text-gray-600 font-semibold py-2 px-4 rounded-lg flex items-center"
          >
            <i class="fas fa-info-circle mr-1"></i>{{ applicationStatus }}
          </div>
          <NuxtLink
            v-if="isAdmin"
            :to="`/boarding-houses/${house.id || route.params.id}/edit`"
            class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-edit mr-1"></i>Edit
          </NuxtLink>
          <NuxtLink
            :to="isAdmin ? '/boarding-houses' : '/boarding-houses/browse'"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-arrow-left mr-1"></i>Back
          </NuxtLink>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Main Content (2/3 width) -->
        <div class="md:col-span-2 space-y-4">
          <!-- Details Card -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-info-circle mr-2"></i>Boarding House Details
              </h5>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <p><strong>ID:</strong> <span v-if="houseId && houseId !== 'N/A'">{{ houseId }}</span><span v-else class="text-gray-400">N/A</span></p>
                  <p><strong>Name:</strong> <span v-if="houseName && houseName !== 'N/A'">{{ houseName }}</span><span v-else class="text-gray-400">N/A</span></p>
                  <p><strong>Address:</strong></p>
                  <p class="text-gray-600" v-if="houseAddress && houseAddress !== 'N/A'">{{ houseAddress }}</p>
                  <p class="text-gray-400" v-else>N/A</p>
                </div>
                <div>
                  <p v-if="house.description"><strong>Description:</strong></p>
                  <p class="text-gray-600" v-if="house.description">{{ house.description }}</p>
                </div>
              </div>
              
              <!-- Admin/Manager Information Section -->
              <div class="mt-6 pt-6 border-t border-gray-200">
                <h6 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                  <i class="fas fa-user-tie mr-2 text-blue-600"></i>Property Manager
                </h6>
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="space-y-2">
                    <div>
                      <span class="text-sm text-gray-500">Name:</span>
                      <span class="ml-2 font-semibold text-gray-900">{{ houseAdmin || 'N/A' }}</span>
                    </div>
                    <div v-if="house?.admin?.email">
                      <span class="text-sm text-gray-500">Email:</span>
                      <span class="ml-2 text-gray-700">{{ house.admin.email }}</span>
                    </div>
                    <div v-if="house?.admin?.phone">
                      <span class="text-sm text-gray-500">Phone:</span>
                      <span class="ml-2 text-gray-700">
                        <i class="fas fa-phone mr-1 text-gray-400"></i>{{ house.admin.phone }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Boarders Card -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-users mr-2"></i>Boarders ({{ boarders.length }})
              </h5>
            </div>
            <div class="p-6">
              <div v-if="boarders.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Name</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Email</th>
                      <th class="px-4 py-3 text-left font-semibold text-gray-700">Phone</th>
                      <th v-if="isAdmin" class="px-4 py-3 text-left font-semibold text-gray-700">Actions</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-200">
                    <tr v-for="boarder in boarders" :key="boarder.id" class="hover:bg-gray-50">
                      <td class="px-4 py-3">{{ boarder.name || 'N/A' }}</td>
                      <td class="px-4 py-3">{{ boarder.email || 'N/A' }}</td>
                      <td class="px-4 py-3">{{ boarder.phone || 'N/A' }}</td>
                      <td v-if="isAdmin" class="px-4 py-3">
                        <NuxtLink
                          :to="`/boarders/${boarder.id}`"
                          class="bg-blue-50 hover:bg-blue-100 text-blue-600 p-2 rounded-lg transition inline-flex items-center justify-center"
                          style="min-width: 36px; min-height: 36px;"
                          title="View"
                        >
                          <i class="fas fa-eye" style="font-size: 14px;"></i>
                        </NuxtLink>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                <p>No boarders found for this boarding house.</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Sidebar (1/3 width) -->
        <div class="space-y-4">
          <!-- Statistics Card -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-chart-bar mr-2"></i>Statistics
              </h5>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-2 gap-4 text-center">
                <div class="border-r border-gray-200 pr-4">
                  <h4 class="text-2xl font-bold text-blue-600">{{ boarders.length }}</h4>
                  <small class="text-gray-600">Boarders</small>
                </div>
                <div>
                  <h4 class="text-2xl font-bold text-green-600">{{ contractsCount }}</h4>
                  <small class="text-gray-600">Contracts</small>
                </div>
                <div class="border-r border-gray-200 pr-4 pt-4 border-t border-gray-200">
                  <h4 class="text-2xl font-bold text-yellow-600">{{ reviewsCount }}</h4>
                  <small class="text-gray-600">Reviews</small>
                </div>
                <div class="pt-4 border-t border-gray-200">
                  <h4 class="text-2xl font-bold text-cyan-600">{{ averageRating }}</h4>
                  <small class="text-gray-600">Avg Rating</small>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Services Card -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-concierge-bell mr-2"></i>Services ({{ services.length }})
              </h5>
            </div>
            <div class="p-6">
              <div v-if="services.length > 0" class="space-y-3">
                <div v-for="service in services.slice(0, 5)" :key="service.id" class="pb-3 border-b border-gray-200 last:border-0 last:pb-0">
                  <div class="flex justify-between items-start mb-1">
                    <div class="flex-1">
                      <h6 class="font-semibold text-gray-900 text-sm mb-1">{{ service.name || service.service_name }}</h6>
                      <p v-if="service.description" class="text-gray-600 text-xs mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        {{ service.description }}
                      </p>
                    </div>
                  </div>
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <span class="text-green-600 font-bold text-sm">₱{{ formatCurrency(service.price) }}</span>
                      <span v-if="service.category" class="bg-gray-100 text-gray-700 text-xs font-semibold px-2 py-0.5 rounded-full">
                        {{ service.category }}
                      </span>
                      <span :class="getAvailabilityBadgeClass(service.availability)" class="text-xs font-semibold px-2 py-0.5 rounded-full">
                        {{ service.availability === 'available' ? 'Available' : service.availability === 'unavailable' ? 'Unavailable' : 'Limited' }}
                      </span>
                    </div>
                    <NuxtLink
                      v-if="isAdmin"
                      :to="`/services/${service.id}`"
                      class="text-blue-600 hover:text-blue-700 text-xs font-semibold"
                      title="View Service"
                    >
                      <i class="fas fa-eye"></i>
                    </NuxtLink>
                  </div>
                </div>
                <div v-if="services.length > 5" class="pt-2 text-center">
                  <NuxtLink
                    to="/services"
                    class="text-blue-600 hover:text-blue-700 text-sm font-semibold"
                  >
                    View All Services <i class="fas fa-arrow-right ml-1"></i>
                  </NuxtLink>
                </div>
              </div>
              <div v-else class="text-center py-4 text-gray-500">
                <p class="text-sm">No services available.</p>
              </div>
            </div>
          </div>
          
          <!-- Recent Reviews Card -->
          <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
            <div class="bg-gradient-to-b from-white to-gray-50 border-b-2 border-gray-200 p-6">
              <h5 class="font-bold text-gray-900 mb-0">
                <i class="fas fa-star mr-2"></i>Recent Reviews
              </h5>
            </div>
            <div class="p-6">
              <div v-if="reviews.length > 0" class="space-y-4">
                <div v-for="review in reviews.slice(0, 3)" :key="review.id" class="pb-4 border-b border-gray-200 last:border-0 last:pb-0">
                  <div class="flex justify-between items-start mb-2">
                    <strong class="text-sm">{{ review.is_anonymous ? 'Anonymous' : (review.boarder?.name || 'Anonymous') }}</strong>
                    <span class="text-yellow-500 text-xs">
                      <i v-for="i in 5" :key="i" :class="i <= review.rating ? 'fas fa-star' : 'far fa-star'"></i>
                    </span>
                  </div>
                  <p class="text-gray-600 text-xs mb-0">
                    {{ review.comment && review.comment.length > 100 ? review.comment.substring(0, 100) + '...' : review.comment }}
                  </p>
                </div>
              </div>
              <div v-else class="text-center py-4 text-gray-500">
                <p class="text-sm">No reviews yet.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-12">
      <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
      <h4 class="text-xl font-semibold text-gray-600 mb-2">Boarding House Not Found</h4>
      <p class="text-gray-500 mb-4">The boarding house you're looking for doesn't exist.</p>
      <NuxtLink
        to="/boarding-houses"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 inline-flex items-center"
      >
        <i class="fas fa-arrow-left mr-2"></i>Back to Boarding Houses
      </NuxtLink>
    </div>

    <!-- Apply Modal -->
    <Modal :show="showApplyModal" title="Apply for Boarding House" @update:show="showApplyModal = false">
      <template #default>
        <form @submit.prevent="handleApply" class="space-y-4">
          <div v-if="applyError" class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded">
            {{ applyError }}
          </div>

          <div v-if="house">
            <h6 class="text-sm font-semibold text-gray-500 mb-2">Boarding House</h6>
            <div class="bg-gray-50 p-3 rounded-lg">
              <h5 class="font-bold text-gray-900 mb-1">{{ house.name }}</h5>
              <p class="text-gray-600 text-sm">{{ house.address }}</p>
            </div>
          </div>

          <!-- Advance Payment Section -->
          <div v-if="house && house.advance_payment_amount > 0" class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
            <h6 class="text-sm font-semibold text-gray-700 mb-3">
              <i class="fas fa-money-bill-wave mr-2 text-blue-600"></i>
              Advance Payment Required
            </h6>
            <div class="mb-4">
              <p class="text-gray-700 mb-3">
                <strong>Amount:</strong> <span class="text-lg font-bold text-blue-600">₱{{ formatCurrency(house.advance_payment_amount) }}</span>
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
                    v-model="applicationForm.advance_payment_method"
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
                    <span v-if="applicationForm.advance_payment_method === 'GCash'" class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="applicationForm.advance_payment_reference"
                    type="text"
                    :required="applicationForm.advance_payment_method === 'GCash'"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Enter reference number"
                  />
                  <p class="text-xs text-gray-500 mt-1">
                    <span v-if="applicationForm.advance_payment_method === 'GCash'">Required for GCash payments</span>
                    <span v-else>Optional for Cash payments</span>
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Policies Section -->
          <div v-if="house" class="border-2 border-gray-300 rounded-lg p-4">
            <h6 class="text-sm font-semibold text-gray-700 mb-3">
              <i class="fas fa-file-contract mr-2 text-green-600"></i>
              House Policies & Terms <span class="text-red-500">*</span>
            </h6>
            <div v-if="house.policies" class="bg-gray-50 border border-gray-200 rounded p-4 max-h-64 overflow-y-auto mb-4">
              <pre class="text-sm text-gray-700 whitespace-pre-wrap font-sans">{{ house.policies }}</pre>
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
                v-model="applicationForm.policies_accepted"
                type="checkbox"
                required
                class="mt-1 mr-2 w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
              />
              <label for="policies_accepted" class="text-sm text-gray-700">
                I have read and accept the policies and terms & conditions stated above. <span class="text-red-500">*</span>
              </label>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-comment mr-2 text-green-600"></i>
              Message to Property Owner (Optional)
            </label>
            <textarea
              v-model="applicationForm.message"
              rows="4"
              class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition"
              placeholder="Tell the property owner why you'd like to stay here..."
            ></textarea>
            <p class="text-sm text-gray-500 mt-1">
              This message will be sent to the property owner along with your application.
            </p>
          </div>

          <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 px-4 py-3 rounded text-sm">
            <strong>Note:</strong> Your application will be reviewed by the property owner. You will be notified of the decision.
            <span v-if="house && house.advance_payment_amount > 0" class="block mt-1">
              If your application is rejected, your advance payment will be automatically refunded.
            </span>
          </div>
        </form>
      </template>
      
      <template #footer>
        <button
          type="button"
          @click="showApplyModal = false"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition duration-200"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="handleApply"
          :disabled="applying || !applicationForm.policies_accepted || (house && house.advance_payment_amount > 0 && !applicationForm.advance_payment_method)"
          class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 disabled:opacity-50 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200 flex items-center"
        >
          <i v-if="!applying" class="fas fa-paper-plane mr-2"></i>
          <i v-else class="fas fa-spinner fa-spin mr-2"></i>
          {{ applying ? 'Submitting...' : 'Submit Application' }}
        </button>
      </template>
    </Modal>

    <!-- Success Modal -->
    <Modal :show="showSuccessModal" title="Application Submitted" @update:show="showSuccessModal = false">
      <template #default>
        <div class="text-center py-4">
          <div class="mb-4">
            <i class="fas fa-check-circle text-6xl text-green-500"></i>
          </div>
          <h3 class="text-xl font-semibold text-gray-800 mb-2">Application Submitted Successfully!</h3>
          <p class="text-gray-600 mb-4">
            Your application has been submitted to the property owner. They will review your application and notify you of their decision.
          </p>
          <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-700 px-4 py-3 rounded text-sm text-left">
            <strong>What's next?</strong>
            <ul class="list-disc list-inside mt-2 space-y-1">
              <li>Wait for the property owner to review your application</li>
              <li>You will be notified once a decision is made</li>
              <li>Check your applications status in your dashboard</li>
            </ul>
          </div>
        </div>
      </template>
      
      <template #footer>
        <button
          type="button"
          @click="showSuccessModal = false"
          class="bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md transition duration-200 flex items-center mx-auto"
        >
          <i class="fas fa-check mr-2"></i>
          Got it!
        </button>
      </template>
    </Modal>
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import Modal from '~/components/Modal.vue'

definePageMeta({
  middleware: 'auth',
  layout: 'app'
})

const api = useApi()
const auth = useAuth()
const route = useRoute()

// Get route params - handle both Nuxt 2 and 3 style
const routeParams = computed(() => {
  // Nuxt 3 uses route.params, but check both
  return route.params || {}
})

// Helper function to safely access error properties
const getErrorStatus = (err) => {
  if (err && typeof err === 'object') {
    const e = err
    return e.status || (e.response && e.response.status)
  }
  return undefined
}

const house = ref(null)
const boarders = ref([])
const reviews = ref([])
const services = ref([])
const contractsCount = ref(0)
const reviewsCount = ref(0)
const averageRating = ref('0.0')
const loading = ref(true)

// Application modal state
const showApplyModal = ref(false)
const showSuccessModal = ref(false)
const applying = ref(false)
const applyError = ref('')
const applicationForm = ref({
  message: '',
  advance_payment_method: '',
  advance_payment_reference: '',
  advance_payment_amount: 0,
  policies_accepted: false
})

// Check if user is boarder
const isBoarder = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'boarder'
})

// Check if user is admin
const isAdmin = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.type === 'admin'
})

// Check if boarder can apply
const canApply = computed(() => {
  if (!isBoarder.value || !house.value) return false
  
  const user = auth.user?.value || auth.user
  if (!user) return false
  
  // Check if boarder is already assigned to this boarding house
  const userBoardingHouseId = user.boarding_house_id || user.boardingHouse?.id || user.boarding_house?.id
  if (userBoardingHouseId === house.value.id) {
    return false
  }
  
  // We'll check for existing applications/contracts in the handleApply function
  // For now, allow the button to show
  return true
})

// Application status message
const applicationStatus = computed(() => {
  if (!isBoarder.value || !house.value) return ''
  
  const user = auth.user?.value || auth.user
  if (!user) return ''
  
  const userBoardingHouseId = user.boarding_house_id || user.boardingHouse?.id || user.boarding_house?.id
  if (userBoardingHouseId === house.value.id) {
    return 'Already assigned to this house'
  }
  
  return 'Cannot apply'
})

// Computed properties for better reactivity
const houseId = computed(() => {
  const id = house.value?.id
  return id !== null && id !== undefined ? id : (routeParams.value.id || route.params?.id || 'N/A')
})
const houseName = computed(() => {
  const name = house.value?.name
  return name !== null && name !== undefined && name !== '' ? name : 'N/A'
})
const houseAddress = computed(() => {
  const address = house.value?.address
  return address !== null && address !== undefined && address !== '' ? address : 'N/A'
})
const houseAdmin = computed(() => {
  const admin = house.value?.admin
  if (admin && admin.name) {
    return admin.name
  }
  const adminName = house.value?.admin_name
  if (adminName) {
    return adminName
  }
  return 'N/A'
})

// Watch for changes in reviews to update statistics
watch([reviews, boarders, house], () => {
  if (house.value) {
    fetchStatistics()
    // Update advance payment amount in form when house data is loaded
    if (house.value.advance_payment_amount) {
      applicationForm.value.advance_payment_amount = house.value.advance_payment_amount
    }
  }
}, { deep: true })

// Watch for modal opening to ensure form is updated
watch(showApplyModal, (isOpen) => {
  if (isOpen && house.value) {
    // Update advance payment amount when modal opens
    if (house.value.advance_payment_amount) {
      applicationForm.value.advance_payment_amount = house.value.advance_payment_amount
    }
  }
})

onMounted(async () => {
  await auth.checkAuth()
  await fetchHouse()
  if (house.value) {
    // Fetch boarders, reviews, and services in parallel (only if not already loaded)
    if (!boarders.value || boarders.value.length === 0) {
      await fetchBoarders()
    }
    if (!reviews.value || reviews.value.length === 0) {
      await fetchReviews()
    }
    if (!services.value || services.value.length === 0) {
      await fetchServices()
    }
    // Then calculate statistics after data is loaded
    await fetchStatistics()
  }
})

const fetchHouse = async () => {
  loading.value = true
  try {
    // Get house ID from route params - handle both string and number
    const houseId = routeParams.value.id || route.params?.id || route.params.id
    
    if (!houseId || houseId === 'undefined' || houseId === 'null') {
      console.error('No house ID provided in route')
      house.value = null
      loading.value = false
      return
    }
    
    // Ensure houseId is a string for the API call
    const houseIdStr = String(houseId).trim()
    if (!houseIdStr || houseIdStr === 'undefined' || houseIdStr === 'null') {
      console.error('Invalid house ID:', houseIdStr)
      house.value = null
      loading.value = false
      return
    }
    
    const response = await api.get(`/boarding-houses/${houseIdStr}`)
    
    // Handle both direct response and wrapped response
    let houseData = response
    
    if (response && typeof response === 'object' && !Array.isArray(response)) {
      if ('data' in response && response.data) {
        houseData = response.data
      } else {
        houseData = response
      }
    }
    
    // Accept any non-array object as valid house data
    if (houseData && typeof houseData === 'object' && !Array.isArray(houseData)) {
      house.value = {
        id: houseData.id ?? null,
        name: houseData.name ?? null,
        address: houseData.address ?? null,
        description: houseData.description ?? null,
        admin_id: houseData.admin_id ?? null,
        admin: houseData.admin ?? null,
        gender_preference: houseData.gender_preference ?? null,
        advance_payment_amount: houseData.advance_payment_amount ?? 0,
        policies: houseData.policies ?? null,
        boarders: Array.isArray(houseData.boarders) ? houseData.boarders : [],
        reviews: Array.isArray(houseData.reviews) ? houseData.reviews : [],
        contracts: Array.isArray(houseData.contracts) ? houseData.contracts : [],
        services: Array.isArray(houseData.services) ? houseData.services : []
      }
      
      // Set advance payment amount in form when modal opens
      if (house.value.advance_payment_amount) {
        applicationForm.value.advance_payment_amount = house.value.advance_payment_amount
      }
      
      // If boarders are already loaded in the response, use them
      if (houseData.boarders && Array.isArray(houseData.boarders) && houseData.boarders.length > 0) {
        boarders.value = houseData.boarders
      }
      
      // If reviews are already loaded in the response, use them
      if (houseData.reviews && Array.isArray(houseData.reviews) && houseData.reviews.length > 0) {
        reviews.value = houseData.reviews
      }
      
      // If services are already loaded in the response, use them
      if (houseData.services && Array.isArray(houseData.services) && houseData.services.length > 0) {
        services.value = houseData.services
      }
    } else {
      console.error('Invalid house data received:', houseData)
      house.value = null
    }
  } catch (error) {
    console.error('Error fetching boarding house:', error)
    
    const status = getErrorStatus(error)
    
    if (status === 404) {
      console.error('Boarding house not found (404)')
      house.value = null
    } else if (status === 401) {
      console.error('Unauthorized (401) - check authentication')
      house.value = null
    } else {
      console.error('Error occurred:', error)
      house.value = null
    }
  } finally {
    loading.value = false
  }
}

const fetchBoarders = async () => {
  try {
    const houseId = String(routeParams.value.id || route.params?.id || house.value?.id || '')
    if (!houseId || houseId === 'undefined' || houseId === 'null') {
      console.error('No house ID for fetching boarders')
      return
    }
    const response = await api.get(`/boarding-houses/${houseId}/boarders`)
    boarders.value = Array.isArray(response) ? response : (response.data || [])
  } catch (error) {
    console.error('Error fetching boarders:', error)
    boarders.value = []
  }
}

const fetchReviews = async () => {
  try {
    const houseId = String(routeParams.value.id || route.params?.id || house.value?.id || '')
    if (!houseId || houseId === 'undefined' || houseId === 'null') {
      console.error('No house ID for fetching reviews')
      return
    }
    const response = await api.get(`/boarding-houses/${houseId}/reviews`)
    reviews.value = Array.isArray(response) ? response : (response.data || [])
  } catch (error) {
    console.error('Error fetching reviews:', error)
    reviews.value = []
  }
}

const fetchServices = async () => {
  try {
    // If services are already loaded from house data, skip fetching
    if (services.value && services.value.length > 0) {
      return
    }
    
    const houseId = String(routeParams.value.id || route.params?.id || house.value?.id || '')
    if (!houseId || houseId === 'undefined' || houseId === 'null') {
      console.error('No house ID for fetching services')
      return
    }
    
    // Try to get services from the services endpoint filtered by boarding house
    const response = await api.get(`/services`)
    const allServices = Array.isArray(response) ? response : (response.data || [])
    
    // Filter services by boarding house ID
    services.value = allServices.filter(service => {
      const serviceHouseId = service.boarding_house_id || service.boardingHouse?.id
      return serviceHouseId && String(serviceHouseId) === String(houseId)
    })
  } catch (error) {
    console.error('Error fetching services:', error)
    services.value = []
  }
}

const fetchStatistics = async () => {
  try {
    if (house.value) {
      contractsCount.value = house.value.contracts?.length || house.value.contracts_count || 0
      reviewsCount.value = reviews.value.length || house.value.reviews?.length || house.value.reviews_count || 0
      
      if (reviews.value.length > 0) {
        const sum = reviews.value.reduce((acc, r) => acc + (parseInt(r.rating) || 0), 0)
        averageRating.value = (sum / reviews.value.length).toFixed(1)
      } else if (house.value.reviews && house.value.reviews.length > 0) {
        const sum = house.value.reviews.reduce((acc, r) => acc + (parseInt(r.rating) || 0), 0)
        averageRating.value = (sum / house.value.reviews.length).toFixed(1)
      } else {
        averageRating.value = '0.0'
      }
    }
  } catch (error) {
    console.error('Error fetching statistics:', error)
  }
}

const handleApply = async () => {
  if (!house.value) {
    applyError.value = 'Boarding house information not available'
    return
  }
  
  // Validate advance payment if required
  if (house.value && house.value.advance_payment_amount > 0) {
    if (!applicationForm.value.advance_payment_method) {
      applyError.value = 'Please select a payment method for the advance payment.'
      return
    }
    if (applicationForm.value.advance_payment_method === 'GCash' && !applicationForm.value.advance_payment_reference) {
      applyError.value = 'Please enter a reference number for GCash payment.'
      return
    }
  }
  
  // Validate policies acceptance
  if (!applicationForm.value.policies_accepted) {
    applyError.value = 'You must accept the policies and terms & conditions to submit your application.'
    return
  }
  
  applying.value = true
  applyError.value = ''
  
  try {
    const payload = {
      boarding_house_id: house.value.id,
      message: applicationForm.value.message || '',
      policies_accepted: applicationForm.value.policies_accepted
    }
    
    // Add advance payment info if required
    if (house.value && house.value.advance_payment_amount > 0) {
      payload.advance_payment_method = applicationForm.value.advance_payment_method
      payload.advance_payment_reference = applicationForm.value.advance_payment_reference || null
      payload.advance_payment_amount = house.value.advance_payment_amount
    }
    
    const response = await api.post('/applications', payload)
    
    console.log('Application submitted:', response)
    
    // Show success message
    if (response && (response.success || response.data)) {
      // Close apply modal and reset form
      showApplyModal.value = false
      applicationForm.value = {
        message: '',
        advance_payment_method: '',
        advance_payment_reference: '',
        advance_payment_amount: 0,
        policies_accepted: false
      }
      applyError.value = ''
      
      // Show success modal
      showSuccessModal.value = true
    } else {
      applyError.value = 'Application submitted but no confirmation received.'
    }
  } catch (err) {
    console.error('Error submitting application:', err)
    applyError.value = err?.message || err?.response?.data?.message || 'Failed to submit application. Please try again.'
    
    // Handle specific error cases
    if (err?.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      applyError.value = errors.join(', ')
    }
  } finally {
    applying.value = false
  }
}

const formatCurrency = (amount) => {
  if (!amount) return '0.00'
  return parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')
}

const getAvailabilityBadgeClass = (availability) => {
  const availLower = availability?.toLowerCase() || ''
  if (availLower === 'available') {
    return 'bg-green-100 text-green-800'
  } else if (availLower === 'unavailable') {
    return 'bg-red-100 text-red-800'
  } else {
    return 'bg-yellow-100 text-yellow-800'
  }
}
</script>

