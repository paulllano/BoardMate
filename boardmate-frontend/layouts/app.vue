<template>
  <div class="min-h-screen" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa;">
    <div class="flex min-h-screen">
      <!-- Sidebar -->
      <aside class="w-[280px] flex-shrink-0 bg-gradient-to-b from-white to-gray-50 shadow-lg border-r border-gray-200 sticky top-0 h-screen overflow-y-auto" style="box-shadow: 2px 0 15px rgba(0,0,0,0.08); display: block !important; visibility: visible !important; opacity: 1 !important; z-index: 10;">
        <!-- Logo -->
        <div 
          class="sidebar-logo flex items-center px-6 py-7" 
          :style="{
            background: isAdmin 
              ? 'linear-gradient(135deg, #0d6efd 0%, #0a58ca 50%, #084298 100%)' 
              : (isBoarder 
                ? 'linear-gradient(135deg, #20c997 0%, #198754 50%, #146c43 100%)' 
                : 'linear-gradient(135deg, #0d6efd 0%, #20c997 50%, #fd7e14 100%)'
              ),
            borderBottom: 'none'
          }"
        >
          <img 
            src="/logo.png" 
            alt="BoardMate Logo"
            class="mr-3"
            style="width: 3rem; height: 3rem; object-fit: contain; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"
          />
          <NuxtLink :to="isAdmin ? '/dashboard/admin' : (isBoarder ? '/dashboard/boarder' : '/dashboard/admin')" class="text-2xl font-extrabold text-white no-underline" style="text-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            BoardMate
          </NuxtLink>
        </div>

        <!-- Navigation -->
        <div class="py-4">
          <!-- Admin Navigation -->
          <template v-if="isAdmin">
            <div class="px-6 py-6 text-xs uppercase tracking-wider text-gray-500 font-bold" style="font-size: 0.7rem; letter-spacing: 0.12em;">OWNER</div>
            <nav>
              <NuxtLink to="/dashboard/admin" class="nav-link" :class="{ active: $route.path === '/dashboard/admin' }">
                <i class="fas fa-gauge"></i>
                <span>Dashboard</span>
              </NuxtLink>
              <NuxtLink to="/boarding-houses" class="nav-link" :class="{ active: $route.path.startsWith('/boarding-houses') }">
                <i class="fas fa-building"></i>
                <span>Boarding Houses</span>
              </NuxtLink>
              <NuxtLink to="/applications" class="nav-link" :class="{ active: $route.path.startsWith('/applications') }">
                <i class="fas fa-clipboard-list"></i>
                <span>Applications</span>
              </NuxtLink>
              <NuxtLink to="/services" class="nav-link" :class="{ active: $route.path.startsWith('/services') }">
                <i class="fas fa-concierge-bell"></i>
                <span>Services</span>
              </NuxtLink>
              <NuxtLink to="/contracts" class="nav-link" :class="{ active: $route.path.startsWith('/contracts') }">
                <i class="fas fa-file-contract"></i>
                <span>Contracts</span>
              </NuxtLink>
              <NuxtLink to="/payments" class="nav-link" :class="{ active: $route.path.startsWith('/payments') }">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
              </NuxtLink>
              <NuxtLink to="/reviews" class="nav-link" :class="{ active: $route.path.startsWith('/reviews') }">
                <i class="fas fa-star"></i>
                <span>Reviews</span>
              </NuxtLink>
              <NuxtLink to="/boarders" class="nav-link" :class="{ active: $route.path.startsWith('/boarders') }">
                <i class="fas fa-users"></i>
                <span>Boarders</span>
              </NuxtLink>
            </nav>
            <div class="px-6 py-6 text-xs uppercase tracking-wider text-gray-500 font-bold" style="font-size: 0.7rem; letter-spacing: 0.12em;">SETTINGS</div>
            <nav>
              <NuxtLink to="/settings/profile" class="nav-link" :class="{ active: $route.path.startsWith('/settings') }">
                <i class="fas fa-user-gear"></i>
                <span>Profile</span>
              </NuxtLink>
              <button
                @click="showLogoutModal = true"
                class="nav-link logout-link w-full text-left"
                style="border: none; background: none; cursor: pointer;"
              >
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </button>
            </nav>
          </template>

          <!-- Boarder Navigation -->
          <template v-else-if="isBoarder">
            <div class="px-6 py-6 text-xs uppercase tracking-wider text-gray-500 font-bold" style="font-size: 0.7rem; letter-spacing: 0.12em;">MY ACCOUNT</div>
            <nav class="boarder-nav">
              <NuxtLink to="/dashboard/boarder" class="nav-link" :class="{ active: $route.path === '/dashboard/boarder' }">
                <i class="fas fa-gauge"></i>
                <span>Dashboard</span>
              </NuxtLink>
              <NuxtLink to="/boarders/my-house" class="nav-link" :class="{ active: $route.path.startsWith('/boarders/my-house') }">
                <i class="fas fa-home"></i>
                <span>My Boarding House</span>
              </NuxtLink>
              <NuxtLink to="/contracts" class="nav-link" :class="{ active: $route.path.startsWith('/contracts') }">
                <i class="fas fa-file-contract"></i>
                <span>My Contracts</span>
              </NuxtLink>
              <NuxtLink to="/payments" class="nav-link" :class="{ active: $route.path.startsWith('/payments') }">
                <i class="fas fa-credit-card"></i>
                <span>My Payments</span>
              </NuxtLink>
              <NuxtLink to="/reviews" class="nav-link" :class="{ active: $route.path.startsWith('/reviews') }">
                <i class="fas fa-star"></i>
                <span>My Reviews</span>
              </NuxtLink>
            </nav>
            <div class="px-6 py-6 text-xs uppercase tracking-wider text-gray-500 font-bold" style="font-size: 0.7rem; letter-spacing: 0.12em;">FIND HOUSING</div>
            <nav class="boarder-nav">
              <NuxtLink to="/boarding-houses/browse" class="nav-link" :class="{ active: $route.path.startsWith('/boarding-houses/browse') }">
                <i class="fas fa-search-location"></i>
                <span>Browse Houses</span>
              </NuxtLink>
            </nav>
            <div class="px-6 py-6 text-xs uppercase tracking-wider text-gray-500 font-bold" style="font-size: 0.7rem; letter-spacing: 0.12em;">SETTINGS</div>
            <nav class="boarder-nav">
              <NuxtLink to="/settings/profile" class="nav-link" :class="{ active: $route.path.startsWith('/settings') }">
                <i class="fas fa-user-gear"></i>
                <span>Profile</span>
              </NuxtLink>
              <button
                @click="showLogoutModal = true"
                class="nav-link logout-link w-full text-left"
                style="border: none; background: none; cursor: pointer;"
              >
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </button>
            </nav>
          </template>
          
          <!-- Fallback - Always show admin nav if route is /dashboard/admin -->
          <template v-else>
            <div class="px-6 py-6 text-xs uppercase tracking-wider text-gray-500 font-bold" style="font-size: 0.7rem; letter-spacing: 0.12em;">OWNER</div>
            <nav>
              <NuxtLink to="/dashboard/admin" class="nav-link" :class="{ active: $route.path === '/dashboard/admin' }">
                <i class="fas fa-gauge"></i>
                <span>Dashboard</span>
              </NuxtLink>
              <NuxtLink to="/boarding-houses" class="nav-link" :class="{ active: $route.path.startsWith('/boarding-houses') }">
                <i class="fas fa-building"></i>
                <span>Boarding Houses</span>
              </NuxtLink>
              <NuxtLink to="/applications" class="nav-link" :class="{ active: $route.path.startsWith('/applications') }">
                <i class="fas fa-clipboard-list"></i>
                <span>Applications</span>
              </NuxtLink>
              <NuxtLink to="/services" class="nav-link" :class="{ active: $route.path.startsWith('/services') }">
                <i class="fas fa-concierge-bell"></i>
                <span>Services</span>
              </NuxtLink>
              <NuxtLink to="/contracts" class="nav-link" :class="{ active: $route.path.startsWith('/contracts') }">
                <i class="fas fa-file-contract"></i>
                <span>Contracts</span>
              </NuxtLink>
              <NuxtLink to="/payments" class="nav-link" :class="{ active: $route.path.startsWith('/payments') }">
                <i class="fas fa-credit-card"></i>
                <span>Payments</span>
              </NuxtLink>
              <NuxtLink to="/reviews" class="nav-link" :class="{ active: $route.path.startsWith('/reviews') }">
                <i class="fas fa-star"></i>
                <span>Reviews</span>
              </NuxtLink>
              <NuxtLink to="/boarders" class="nav-link" :class="{ active: $route.path.startsWith('/boarders') }">
                <i class="fas fa-users"></i>
                <span>Boarders</span>
              </NuxtLink>
            </nav>
            <div class="px-6 py-6 text-xs uppercase tracking-wider text-gray-500 font-bold" style="font-size: 0.7rem; letter-spacing: 0.12em;">SETTINGS</div>
            <nav>
              <NuxtLink to="/settings/profile" class="nav-link" :class="{ active: $route.path.startsWith('/settings') }">
                <i class="fas fa-user-gear"></i>
                <span>Profile</span>
              </NuxtLink>
              <button
                @click="showLogoutModal = true"
                class="nav-link logout-link w-full text-left"
                style="border: none; background: none; cursor: pointer;"
              >
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
              </button>
            </nav>
          </template>
        </div>

      </aside>

      <!-- Main Content -->
      <div class="flex-1 flex flex-col min-w-0">
        <!-- Topbar -->
        <header class="bg-white border-b border-gray-200 shadow-sm px-4 py-3" style="box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <h5 class="mb-0 font-bold text-gray-900">
                <i v-if="isAdmin" class="fas fa-user-shield mr-2 text-blue-600"></i>
                <i v-else-if="isBoarder" class="fas fa-user mr-2 text-green-600"></i>
                <span v-if="userName">{{ userName }}'s Dashboard</span>
                <span v-else-if="isAdmin">Admin Dashboard</span>
                <span v-else-if="isBoarder">Boarder Dashboard</span>
                <span v-else>Dashboard</span>
              </h5>
            </div>
            <div class="flex items-center gap-3">
              <!-- Notifications placeholder (can be implemented later) -->
              <button 
                v-if="false"
                class="btn btn-sm btn-outline-primary relative"
                type="button"
              >
                <i class="fas fa-bell"></i>
              </button>
              
              <!-- User Badge -->
              <span 
                v-if="isAdmin"
                class="bg-blue-600 text-white px-3 py-2 rounded-lg text-sm font-semibold flex items-center"
              >
                <i class="fas fa-user-shield mr-1"></i>Admin
              </span>
              <span 
                v-else-if="isBoarder"
                class="bg-green-600 text-white px-3 py-2 rounded-lg text-sm font-semibold flex items-center"
              >
                <i class="fas fa-user mr-1"></i>Boarder
              </span>
            </div>
          </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 p-8 overflow-y-auto" style="background: #f8f9fa;">
          <slot />
        </main>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <ConfirmModal
      v-model:show="showLogoutModal"
      title="Confirm Logout"
      message="Are you sure you want to logout? You will need to login again to access your account."
      variant="warning"
      confirm-text="Logout"
      @confirm="handleLogout"
      :disabled="loggingOut"
    />

    <!-- Logout Loading Modal -->
    <SuccessModal
      v-model:show="showLogoutLoadingModal"
      title="Logging Out..."
      message="Please wait while we log you out..."
      :loading="true"
      loading-text="Logging out..."
      :closeOnBackdrop="false"
    />
  </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth'
import ConfirmModal from '~/components/ConfirmModal.vue'
import SuccessModal from '~/components/SuccessModal.vue'

const auth = useAuth()
const route = useRoute()

const showLogoutModal = ref(false)
const showLogoutLoadingModal = ref(false)
const loggingOut = ref(false)

// Determine user type from user data (not route, as route can be misleading)
const isAdmin = computed(() => {
  // auth.user is a readonly ref from useState, handle both ref and direct access
  const user = auth.user?.value || auth.user
  if (user && user.type === 'admin') {
    return true
  }
  return false
})

const isBoarder = computed(() => {
  // auth.user is a readonly ref from useState, handle both ref and direct access
  const user = auth.user?.value || auth.user
  if (user && user.type === 'boarder') {
    return true
  }
  return false
})

// Get user name for display
const userName = computed(() => {
  const user = auth.user?.value || auth.user
  return user?.name || null
})

// Check authentication on mount and watch for changes
onMounted(async () => {
  await auth.checkAuth()
  // Don't redirect here - let middleware handle it
})

// Watch for user changes to update UI
watch(() => auth.user, (newUser) => {
  console.log('User updated:', newUser)
}, { deep: true })

const handleLogout = async () => {
  loggingOut.value = true
  showLogoutModal.value = false
  showLogoutLoadingModal.value = true
  
  try {
    await auth.logout()
    // The logout function in useAuth will handle navigation
    // The loading modal will stay visible during the logout process
  } catch (error) {
    console.error('Logout error:', error)
    // Even if there's an error, continue with logout
    showLogoutLoadingModal.value = false
    await auth.logout()
  }
}

// Dynamic page title based on route
const pageTitle = computed(() => {
  const titles = {
    '/dashboard/admin': 'Admin Dashboard',
    '/dashboard/boarder': 'Boarder Dashboard',
    '/boarding-houses': 'Boarding Houses',
    '/boarders': 'Boarders',
    '/contracts': 'Contracts',
    '/payments': 'Payments',
    '/applications': 'Applications',
    '/reviews': 'Reviews'
  }
  return titles[route.path] || 'BoardMate'
})
</script>

<style scoped>
.nav-link {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem 1.5rem;
  margin: 0.25rem 0.75rem;
  border-left: 4px solid transparent;
  border-radius: 0.5rem;
  color: #495057;
  text-decoration: none;
  transition: all 0.2s ease;
}

.nav-link:hover {
  background: rgba(13, 110, 253, 0.08);
  border-left-color: #0d6efd;
  transform: translateX(4px);
  color: #0d6efd;
}

/* Boarder navigation - green theme */
.boarder-nav .nav-link:hover {
  background: rgba(34, 197, 94, 0.08);
  border-left-color: #22c55e;
  transform: translateX(4px);
  color: #22c55e;
}

.nav-link.active {
  background: linear-gradient(90deg, rgba(13, 110, 253, 0.15) 0%, rgba(32, 201, 151, 0.1) 100%);
  border-left-color: #20c997;
  color: #0d6efd;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(13, 110, 253, 0.1);
}

/* Boarder navigation active - green theme */
.boarder-nav .nav-link.active {
  background: linear-gradient(90deg, rgba(34, 197, 94, 0.15) 0%, rgba(32, 201, 151, 0.1) 100%);
  border-left-color: #22c55e;
  color: #22c55e;
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(34, 197, 94, 0.1);
}

.logout-link:hover {
  background: rgba(220, 38, 38, 0.1) !important;
  border-left-color: #dc2626 !important;
  color: #dc2626 !important;
}

.btn {
  border-radius: 0.625rem;
  font-weight: 600;
  padding: 0.5rem 1rem;
  transition: all 0.2s ease;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  border: 1px solid transparent;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.btn-sm {
  padding: 0.375rem 0.75rem;
  font-size: 0.875rem;
}

.btn-outline-primary {
  background: transparent;
  border-color: #0d6efd;
  color: #0d6efd;
}

.btn-outline-primary:hover {
  background: #0d6efd;
  color: white;
}

.btn-outline-danger {
  background: transparent;
  border-color: #dc3545;
  color: #dc3545;
}

.btn-outline-danger:hover {
  background: #dc3545;
  color: white;
}
</style>

