<template>
  <div class="container py-4">
    <!-- Debug info (remove in production) -->
    <div v-if="false" class="mb-4 p-2 bg-gray-100 text-xs">
      <p>Loading: {{ loading }}</p>
      <p>Error: {{ error }}</p>
      <p>Form name: {{ form.name }}</p>
      <p>Route params: {{ JSON.stringify(route.params) }}</p>
    </div>
    
    <div v-if="loading" class="text-center py-12">
      <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
      <p class="text-gray-600">Loading boarding house data...</p>
      <p class="text-sm text-gray-500 mt-2">If this takes too long, please refresh the page.</p>
    </div>

    <div v-else>
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold mb-0">
          <i class="fas fa-edit mr-2"></i>Edit Boarding House
        </h1>
        <div class="flex items-center gap-2">
          <NuxtLink
            :to="`/boarding-houses/${route.params.id}`"
            class="bg-blue-50 hover:bg-blue-100 text-blue-600 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-eye mr-1"></i>View
          </NuxtLink>
          <NuxtLink
            to="/boarding-houses"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center"
          >
            <i class="fas fa-arrow-left mr-1"></i>Back to List
          </NuxtLink>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-lg border-0 overflow-hidden">
        <div class="p-6">
          <form @submit.prevent="handleSubmit" class="space-y-4">
            <div v-if="error" class="bg-red-50 border-l-4 border-red-400 text-red-700 px-4 py-3 rounded mb-4">
              <p class="font-semibold mb-1">Error:</p>
              <p>{{ error }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Left Column -->
              <div class="space-y-4">
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Admin <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.admin_id"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                  >
                    <option value="">Select an admin</option>
                    <option v-for="admin in admins" :key="admin.id" :value="String(admin.id)">
                      {{ admin.name }}
                    </option>
                  </select>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Address <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    v-model="form.address"
                    rows="3"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-y"
                    placeholder="Enter address"
                  ></textarea>
                </div>

                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Description
                  </label>
                  <textarea
                    v-model="form.description"
                    rows="4"
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition resize-y"
                    placeholder="Enter description (optional)"
                  ></textarea>
                </div>
              </div>

              <!-- Right Column -->
              <div>
                <div>
                  <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Boarding House Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                    placeholder="Enter boarding house name"
                  />
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-2 pt-4">
              <NuxtLink
                to="/boarding-houses"
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
                {{ submitting ? 'Updating...' : 'Update Boarding House' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
      :show="showSuccessModal"
      title="Boarding House Updated Successfully!"
      :message="successMessage"
      @update:show="showSuccessModal = false"
    />

    <!-- Error Modal -->
    <ErrorModal
      :show="showErrorModal"
      :title="errorTitle"
      :message="errorMessage"
      @update:show="showErrorModal = false"
    />
  </div>
</template>

<script setup>
import { useApi } from '~/composables/useApi'
import { useAuth } from '~/composables/useAuth'
import SuccessModal from '~/components/SuccessModal.vue'
import ErrorModal from '~/components/ErrorModal.vue'

definePageMeta({
  middleware: ['auth'],
  layout: 'app'
})

const api = useApi()
const auth = useAuth()
const route = useRoute()

const form = ref({
  admin_id: '',
  name: '',
  address: '',
  description: ''
})

const admins = ref([])
const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const showSuccessModal = ref(false)
const showErrorModal = ref(false)
const successMessage = ref('Boarding house has been updated successfully!')
const errorTitle = ref('Update Failed')
const errorMessage = ref('')

onMounted(async () => {
  console.log('=== EDIT PAGE COMPONENT MOUNTED ===')
  console.log('Edit page route path:', route.path)
  console.log('Edit page route fullPath:', route.fullPath)
  console.log('Edit page route name:', route.name)
  console.log('Edit page route params:', JSON.stringify(route.params, null, 2))
  console.log('Edit page route params.id:', route.params?.id)
  
  // Ensure loading starts as true
  loading.value = true
  
  try {
    await auth.checkAuth()
    
    // Handle both ref and direct access (user is a readonly ref from useState)
    const user = auth.user?.value || auth.user
    console.log('Auth check complete, user:', user)
    console.log('User type:', user?.type)
    
    if (!user || user?.type !== 'admin') {
      console.log('Not admin or not authenticated, redirecting...')
      console.log('User object:', user)
      loading.value = false
      navigateTo('/dashboard/admin')
      return
    }
    
    // Fetch admins for the dropdown (don't block on this)
    api.get('/admins')
      .then(response => {
        // Handle paginated response from Laravel
        if (response && response.data && Array.isArray(response.data)) {
          // Paginated response: { data: [...], current_page: 1, ... }
          admins.value = response.data
        } else if (Array.isArray(response)) {
          // Direct array response
          admins.value = response
        } else if (response && response.data && Array.isArray(response.data)) {
          // Another format with data property
          admins.value = response.data
        } else {
          admins.value = []
        }
        console.log('Admins loaded:', admins.value.length)
      })
      .catch(err => {
        console.error('Error fetching admins (non-blocking):', err)
        // Don't block form rendering if admins fail
        admins.value = []
      })
    
    // Fetch house data
    await fetchHouse()
  } catch (err) {
    console.error('Error in onMounted:', err)
    error.value = 'Failed to initialize edit page. Please refresh.'
    loading.value = false // Ensure loading is set to false on error
  }
})

const fetchHouse = async () => {
  loading.value = true
  error.value = ''
  
  // Safety timeout - ensure loading is set to false after 10 seconds
  const timeoutId = setTimeout(() => {
    if (loading.value) {
      console.warn('Fetch house timeout - forcing loading to false')
      loading.value = false
      if (!error.value) {
        error.value = 'Request timed out. Please try again.'
      }
    }
  }, 10000)
  
  try {
    // Extract house ID from route params - handle both Nuxt 2 and 3 style
    const houseId = route.params?.id || route.params.id
    console.log('Edit page - Route path:', route.path)
    console.log('Edit page - Route params:', route.params)
    console.log('Edit page - Fetching house for edit, ID:', houseId)
    
    if (!houseId || houseId === 'undefined' || houseId === 'null') {
      error.value = 'Invalid boarding house ID. Please check the URL.'
      loading.value = false
      return
    }
    
    // Ensure houseId is a string and doesn't include "edit"
    const houseIdStr = String(houseId).trim()
    if (houseIdStr.includes('edit')) {
      error.value = 'Invalid route parameter. Please navigate from the boarding houses list.'
      loading.value = false
      return
    }
    
    console.log('Calling API with house ID:', houseIdStr)
    const response = await api.get(`/boarding-houses/${houseIdStr}`)
    console.log('House data received (raw):', response)
    console.log('Response type:', typeof response)
    console.log('Is array:', Array.isArray(response))
    
    // Handle both direct response and wrapped response
    let house = null
    if (response) {
      if (typeof response === 'object' && !Array.isArray(response)) {
        // Check if response has a 'data' property
        if (response.data !== undefined && response.data !== null) {
          house = response.data
          console.log('Using response.data')
        } else if (response.id !== undefined) {
          // Response is the house object directly
          house = response
          console.log('Using response directly (has id property)')
        }
      }
    }
    
    console.log('Extracted house:', house)
    console.log('House has id:', house?.id)
    console.log('House has name:', house?.name)
    
    // Populate form if we have valid house data
    if (house && typeof house === 'object' && !Array.isArray(house)) {
      // Check if it looks like a house object (has at least id or name)
      if (house.id || house.name) {
        form.value = {
          admin_id: house.admin_id ? String(house.admin_id) : '',
          name: house.name || '',
          address: house.address || '',
          description: house.description || ''
        }
        console.log('✅ Form populated successfully:', form.value)
        error.value = '' // Clear any previous errors
      } else {
        error.value = 'House data missing required fields. You can still edit the form below.'
        console.error('❌ House data missing required fields:', house)
        // Still allow form to be shown
      }
    } else {
      // Even if data is invalid, show the form so user can manually enter data
      error.value = 'Failed to load boarding house data. Please enter the information manually.'
      console.error('❌ Invalid house data structure')
      console.error('House:', house)
      console.error('House type:', typeof house)
      console.error('Is array:', Array.isArray(house))
      // Don't prevent form from showing - user can still edit
    }
  } catch (err) {
    console.error('Error fetching house:', err)
    error.value = err?.message || err?.response?.data?.message || 'Failed to load boarding house. Please try again.'
    // Still set loading to false so the form can be shown with error
  } finally {
    clearTimeout(timeoutId)
    loading.value = false
    console.log('Fetch house complete - loading set to false')
  }
}

const handleSubmit = async () => {
  submitting.value = true
  error.value = ''
  
  try {
    await api.put(`/boarding-houses/${route.params.id}`, form.value)
    showSuccessModal.value = true
    
    // Navigate after a short delay to let user see the success message
    setTimeout(() => {
      navigateTo(`/boarding-houses/${route.params.id}`)
    }, 2000)
  } catch (err) {
    console.error('Error updating boarding house:', err)
    const errorMsg = err?.message || 'Failed to update boarding house. Please try again.'
    
    // Handle validation errors
    if (err?.response?.data?.errors) {
      const errors = Object.values(err.response.data.errors).flat()
      errorMessage.value = errors.join(', ')
    } else {
      errorMessage.value = err?.response?.data?.message || errorMsg
    }
    
    errorTitle.value = 'Update Failed'
    showErrorModal.value = true
    error.value = errorMessage.value
  } finally {
    submitting.value = false
  }
}
</script>

