import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware(async (to, from) => {
  const auth = useAuth()
  
  // Check authentication first
  await auth.checkAuth()
  
  // Wait a tick to ensure auth state is fully loaded
  await nextTick()
  
  // Get user (handle both ref and direct access)
  // useAuth returns user as readonly ref, so access via .value
  let user = auth.user?.value || auth.user
  
  // If user is not loaded yet, try fetching again
  if (!user) {
    await auth.fetchUser()
    await nextTick()
    user = auth.user?.value || auth.user
  }
  
  // Check if user is authenticated
  if (!user) {
    // Redirect to role selection if not authenticated
    return navigateTo('/auth/role')
  }
  
  // Check if user is admin - explicitly check the type field
  const userType = user?.type
  
  if (userType !== 'admin') {
    // Redirect to appropriate dashboard based on user type
    if (userType === 'boarder') {
      return navigateTo('/dashboard/boarder')
    } else {
      // If user type is unknown or null, redirect to role selection
      return navigateTo('/auth/role')
    }
  }
  
  // User is admin, allow access
})

