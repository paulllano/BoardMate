import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware(async (to, from) => {
  const auth = useAuth()
  
  // If user is already set, don't check again (avoids unnecessary API calls)
  if (!auth.isAuthenticated.value) {
    // Check authentication
    await auth.checkAuth()
    
    // Check if user is authenticated after check
    if (!auth.isAuthenticated.value) {
      // Redirect to role selection if not authenticated
      return navigateTo('/auth/role')
    }
  }
})

