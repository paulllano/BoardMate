export const useApi = () => {
  // Base URL for Laravel API
  const apiBase = 'http://localhost:8000/api'
  
  // Get token from localStorage
  const getToken = (): string | null => {
    if (typeof window !== 'undefined') {
      return localStorage.getItem('auth_token')
    }
    return null
  }
  
  // Set token in localStorage
  const setToken = (token: string): void => {
    if (typeof window !== 'undefined') {
      localStorage.setItem('auth_token', token)
    }
  }
  
  // Remove token from localStorage
  const removeToken = (): void => {
    if (typeof window !== 'undefined') {
      localStorage.removeItem('auth_token')
    }
  }
  
  // Get headers with authentication
  const getHeaders = (): HeadersInit => {
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
    }
    
    const token = getToken()
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }
    
    return headers
  }
  
  // API request wrapper
  const request = async <T = any>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<T> => {
    const url = `${apiBase}${endpoint}`
    
    try {
      const response = await fetch(url, {
        ...options,
        headers: {
          ...getHeaders(),
          ...(options.headers || {}),
        },
      })
      
      // Handle errors
      if (!response.ok) {
        let errorData: any = {}
        try {
          errorData = await response.json()
        } catch (e) {
          // If response is not JSON, create a default error message
          errorData = { 
            message: response.status === 401 
              ? 'Invalid credentials. Please check your email and password.' 
              : response.status === 422
              ? 'Validation failed. Please check your input.'
              : response.status === 500
              ? 'Server error. Please try again later.'
              : response.status === 404
              ? 'Endpoint not found. Please check the API configuration.'
              : `HTTP error! status: ${response.status}`
          }
        }
        
        // Only redirect on 401 if it's not a login/register attempt
        if (response.status === 401 && !endpoint.includes('/login') && !endpoint.includes('/register')) {
          removeToken()
          if (typeof window !== 'undefined') {
            window.location.href = '/auth/role'
          }
        }
        
        // Extract error message - handle validation errors specially
        let errorMessage = errorData.message || errorData.error || `HTTP error! status: ${response.status}`
        
        // If validation errors exist, format them nicely
        if (errorData.errors && typeof errorData.errors === 'object') {
          const errorKeys = Object.keys(errorData.errors)
          if (errorKeys.length > 0) {
            const firstErrorKey = errorKeys[0]
            if (firstErrorKey) {
              const errorsObj = errorData.errors as Record<string, any>
              const firstError = errorsObj[firstErrorKey]
              if (Array.isArray(firstError) && firstError.length > 0) {
                errorMessage = firstError[0]
              } else if (typeof firstError === 'string') {
                errorMessage = firstError
              }
            }
          }
        }
        
        const error = new Error(errorMessage)
        // Attach response for better error handling
        ;(error as any).response = response
        ;(error as any).status = response.status
        ;(error as any).data = errorData
        throw error
      }
      
      return response.json()
    } catch (error: any) {
      // Handle network errors (connection refused, timeout, etc.)
      if (error.name === 'TypeError' || error.message.includes('fetch')) {
        const networkError = new Error('Unable to connect to server. Please make sure the backend is running at http://localhost:8000')
        ;(networkError as any).isNetworkError = true
        throw networkError
      }
      // Re-throw other errors
      throw error
    }
  }
  
  // GET request
  const get = <T = any>(endpoint: string): Promise<T> => {
    return request<T>(endpoint, { method: 'GET' })
  }
  
  // POST request
  const post = <T = any>(endpoint: string, data?: any): Promise<T> => {
    return request<T>(endpoint, {
      method: 'POST',
      body: data ? JSON.stringify(data) : undefined,
    })
  }
  
  // PUT request
  const put = <T = any>(endpoint: string, data?: any): Promise<T> => {
    return request<T>(endpoint, {
      method: 'PUT',
      body: data ? JSON.stringify(data) : undefined,
    })
  }
  
  // DELETE request
  const del = <T = any>(endpoint: string): Promise<T> => {
    return request<T>(endpoint, { method: 'DELETE' })
  }
  
  // DELETE alias (for consistency)
  const deleteRequest = del
  
  return {
    apiBase,
    getToken,
    setToken,
    removeToken,
    get,
    post,
    put,
    delete: del,
    deleteRequest: del,
  }
}

