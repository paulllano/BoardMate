import { useApi } from './useApi'

export const useAuth = () => {
  const api = useApi()
  
  // User state
  const user = useState<any>('user', () => null)
  const isAuthenticated = computed(() => !!user.value)
  
  // Admin login
  const adminLogin = async (email: string, password: string) => {
    try {
      const response = await api.post('/admin/login', { email, password })
      
      if (response.success && response.token) {
        api.setToken(response.token)
        user.value = response.user
        return { success: true, user: response.user }
      }
      
      return { success: false, message: response.message || 'Invalid credentials. Please check your email and password.' }
    } catch (error: any) {
      console.error('Admin login error:', error)
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        data: error.data,
        isNetworkError: error.isNetworkError
      })
      
      // Extract detailed error message
      let errorMessage = 'An error occurred. Please try again.'
      
      if (error.isNetworkError) {
        errorMessage = error.message || 'Unable to connect to server. Please make sure the backend is running at http://localhost:8000'
      } else if (error.data && error.data.message) {
        errorMessage = error.data.message
      } else if (error.data && error.data.error) {
        errorMessage = error.data.error
      } else if (error.message) {
        errorMessage = error.message
      }
      
      return { success: false, message: errorMessage }
    }
  }
  
  // Boarder login
  const boarderLogin = async (email: string, password: string) => {
    try {
      const response = await api.post('/boarder/login', { email, password })
      
      if (response.success && response.token) {
        api.setToken(response.token)
        user.value = response.user
        return { success: true, user: response.user }
      }
      
      return { success: false, message: response.message || 'Invalid credentials. Please check your email and password.' }
    } catch (error: any) {
      console.error('Boarder login error:', error)
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        data: error.data,
        isNetworkError: error.isNetworkError
      })
      
      // Extract detailed error message
      let errorMessage = 'An error occurred. Please try again.'
      
      if (error.isNetworkError) {
        errorMessage = error.message || 'Unable to connect to server. Please make sure the backend is running at http://localhost:8000'
      } else if (error.data && error.data.message) {
        errorMessage = error.data.message
      } else if (error.data && error.data.error) {
        errorMessage = error.data.error
      } else if (error.message) {
        errorMessage = error.message
      }
      
      return { success: false, message: errorMessage }
    }
  }
  
  // Admin register
  const adminRegister = async (data: {
    name: string
    email: string
    password: string
    password_confirmation: string
    role: string
    phone?: string
  }) => {
    try {
      const response = await api.post('/admin/register', data)
      console.log('Admin registration response:', response)
      
      if (response.success && response.token) {
        api.setToken(response.token)
        user.value = response.user
        return { success: true, user: response.user }
      }
      
      return { success: false, message: response.message || 'Registration failed' }
    } catch (error: any) {
      console.error('Admin registration error:', error)
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        data: error.data,
        isNetworkError: error.isNetworkError
      })
      
      // Extract detailed error message
      let errorMessage = 'Registration failed. Please try again.'
      
      if (error.isNetworkError) {
        errorMessage = error.message || 'Unable to connect to server. Please make sure the backend is running at http://localhost:8000'
      } else if (error.data && error.data.message) {
        errorMessage = error.data.message
      } else if (error.data && error.data.errors) {
        // Handle validation errors
        const errors = error.data.errors
        const firstError = Object.values(errors)[0]
        errorMessage = Array.isArray(firstError) ? firstError[0] : String(firstError)
      } else if (error.data && error.data.error) {
        errorMessage = error.data.error
      } else if (error.message) {
        errorMessage = error.message
      }
      
      return { success: false, message: errorMessage }
    }
  }
  
  // Boarder register
  const boarderRegister = async (data: {
    name: string
    email: string
    password: string
    password_confirmation: string
    phone?: string
    date_of_birth?: string
    address?: string
  }) => {
    try {
      const response = await api.post('/boarder/register', data)
      console.log('Boarder registration response:', response)
      
      if (response.success && response.token) {
        api.setToken(response.token)
        user.value = response.user
        return { success: true, user: response.user }
      }
      
      return { success: false, message: response.message || 'Registration failed' }
    } catch (error: any) {
      console.error('Boarder registration error:', error)
      console.error('Error details:', {
        message: error.message,
        status: error.status,
        data: error.data,
        isNetworkError: error.isNetworkError
      })
      
      // Extract detailed error message
      let errorMessage = 'Registration failed. Please try again.'
      
      if (error.isNetworkError) {
        errorMessage = error.message || 'Unable to connect to server. Please make sure the backend is running at http://localhost:8000'
      } else if (error.data && error.data.message) {
        errorMessage = error.data.message
      } else if (error.data && error.data.errors) {
        // Handle validation errors
        const errors = error.data.errors
        const firstError = Object.values(errors)[0]
        errorMessage = Array.isArray(firstError) ? firstError[0] : String(firstError)
      } else if (error.data && error.data.error) {
        errorMessage = error.data.error
      } else if (error.message) {
        errorMessage = error.message
      }
      
      return { success: false, message: errorMessage }
    }
  }
  
  // Logout
  const logout = async () => {
    try {
      await api.post('/logout')
    } catch (error) {
      // Continue with logout even if API call fails
    } finally {
      api.removeToken()
      user.value = null
      if (typeof window !== 'undefined') {
        navigateTo('/auth/role')
      }
    }
  }
  
  // Get current user
  const fetchUser = async () => {
    try {
      const response = await api.get('/user')
      console.log('API User Response:', response)
      if (response && response.user) {
        user.value = response.user
        console.log('User set:', response.user)
        return response.user
      }
    } catch (error: any) {
      console.error('Error fetching user:', error)
      // User not authenticated
      user.value = null
    }
    return null
  }
  
  // Check if user is authenticated on page load
  const checkAuth = async () => {
    const token = api.getToken()
    if (token) {
      await fetchUser()
    }
  }
  
  return {
    user: readonly(user),
    isAuthenticated,
    adminLogin,
    boarderLogin,
    adminRegister,
    boarderRegister,
    logout,
    fetchUser,
    checkAuth,
  }
}

