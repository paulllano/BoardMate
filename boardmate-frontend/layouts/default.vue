<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex items-center">
            <NuxtLink :to="auth.user?.type === 'admin' ? '/dashboard/admin' : (auth.user?.type === 'boarder' ? '/dashboard/boarder' : '/dashboard/admin')" class="text-xl font-bold text-gray-900">
              BoardMate
            </NuxtLink>
          </div>
          
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600" v-if="auth.user">
              Welcome, {{ auth.user.name }}
            </span>
            <button
              @click="handleLogout"
              class="text-sm text-red-600 hover:text-red-700 font-medium"
            >
              Logout
            </button>
          </div>
        </div>
      </div>
    </nav>
    
    <!-- Main Content -->
    <main>
      <slot />
    </main>
  </div>
</template>

<script setup>
import { useAuth } from '~/composables/useAuth'

const auth = useAuth()

// Check authentication on mount
onMounted(async () => {
  await auth.checkAuth()
})

const handleLogout = async () => {
  await auth.logout()
}
</script>

