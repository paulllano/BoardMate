import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware(async (to, from) => {
  const auth = useAuth()
  
  // Check authentication first
  await auth.checkAuth()
  
  // Wait a tick to ensure auth state is fully loaded
  await nextTick()
  
  // Get user (handle both ref and direct access)
  const user = auth.user?.value || auth.user
  
  // Check if user is authenticated
  if (!user) {
    // Redirect to role selection if not authenticated
    return navigateTo('/auth/role')
  }
  
  // Check if user is admin
  if (user?.type !== 'admin') {
    // Redirect to appropriate dashboard based on user type
    if (user?.type === 'boarder') {
      return navigateTo('/dashboard/boarder')
    } else {
      return navigateTo('/dashboard/admin')
    }
  }
})

