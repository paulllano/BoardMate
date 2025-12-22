<template>
  <div>
    <!-- Hero Section -->
    <section class="relative overflow-hidden text-white py-12" style="background: linear-gradient(135deg, #0d6efd 0%, #20c997 50%, #fd7e14 100%); opacity: 0.9;">
      <div class="container mx-auto px-4 relative z-0">
        <div class="flex flex-col lg:flex-row items-center min-h-[50vh] relative z-10">
          <div class="lg:w-2/3 mb-8 lg:mb-0 relative z-10">
            <div class="mb-4">
              <span class="bg-white text-blue-600 px-3 py-2 rounded-full text-sm font-semibold inline-block mb-3">
                🏠 Boarding House Management Platform
              </span>
            </div>
            <h1 class="text-5xl lg:text-6xl font-bold mb-4">
              Welcome to <span class="text-yellow-400">BoardMate</span>
            </h1>
            <p class="text-xl lg:text-2xl mb-8 leading-relaxed">
              The ultimate platform connecting boarding house owners with students and professionals. Streamline management, discover perfect homes.
            </p>
            
            <div class="flex flex-wrap gap-3 mb-8">
              <button
                @click="navigateToLogin('admin')"
                type="button"
                class="bg-white text-gray-900 hover:bg-gray-100 font-semibold px-6 py-4 rounded-lg shadow-lg transition duration-200 inline-flex items-center cursor-pointer relative z-10"
              >
                <i class="fas fa-user-shield mr-2"></i>
                I'm an Owner/Admin
              </button>
              <button
                @click="navigateToLogin('boarder')"
                type="button"
                class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 font-semibold px-6 py-4 rounded-lg transition duration-200 inline-flex items-center cursor-pointer relative z-10"
              >
                <i class="fas fa-user mr-2"></i>
                I'm Looking for a Place
              </button>
            </div>
            
            <div class="grid grid-cols-3 gap-4 text-center">
              <div>
                <div class="text-3xl font-bold text-yellow-400">10</div>
                <small class="text-white">Active Listings</small>
              </div>
              <div>
                <div class="text-3xl font-bold text-yellow-400">21</div>
                <small class="text-white">Happy Residents</small>
              </div>
              <div>
                <div class="text-3xl font-bold text-yellow-400">3.2★</div>
                <small class="text-white">Average Rating</small>
              </div>
            </div>
          </div>
          
          <!-- Logo Section - Right Side -->
          <div class="lg:w-1/3 text-center flex items-center justify-center">
            <img 
              src="/logo.png" 
              alt="BoardMate Logo" 
              class="w-64 h-64 lg:w-80 lg:h-80 xl:w-96 xl:h-96 object-contain"
              style="max-width: 100%; height: auto;"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-12 bg-gray-100">
      <div class="container mx-auto px-4">
        <div class="text-center mb-10">
          <h2 class="text-4xl lg:text-5xl font-bold mb-3">Why Choose BoardMate?</h2>
          <p class="text-xl text-gray-600">Built for modern boarding house management</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-0 p-6">
            <div class="bg-blue-100 rounded-full inline-flex items-center justify-center mb-4" style="width: 60px; height: 60px;">
              <i class="fas fa-building text-blue-600" style="font-size: 1.5rem;"></i>
            </div>
            <h5 class="font-bold text-lg mb-3">For Property Owners</h5>
            <p class="text-gray-600 mb-4">Manage multiple properties, track payments, handle contracts, and get reviews from residents all in one dashboard.</p>
            <ul class="list-none text-left space-y-2">
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">Digital contract management</span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">Automated payment tracking</span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">Review and rating system</span>
              </li>
            </ul>
          </div>
          
          <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-0 p-6">
            <div class="bg-green-100 rounded-full inline-flex items-center justify-center mb-4" style="width: 60px; height: 60px;">
              <i class="fas fa-search-location text-green-600" style="font-size: 1.5rem;"></i>
            </div>
            <h5 class="font-bold text-lg mb-3">For Students & Professionals</h5>
            <p class="text-gray-600 mb-4">Find the perfect boarding house near Caraga State University (CSU) or workplace. Compare prices, amenities, and read authentic reviews.</p>
            <ul class="list-none text-left space-y-2">
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">Location-based search</span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">Detailed property listings</span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">Easy application process</span>
              </li>
            </ul>
          </div>
          
          <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border-0 p-6">
            <div class="bg-yellow-100 rounded-full inline-flex items-center justify-center mb-4" style="width: 60px; height: 60px;">
              <i class="fas fa-shield-alt text-yellow-600" style="font-size: 1.5rem;"></i>
            </div>
            <h5 class="font-bold text-lg mb-3">Secure & Reliable</h5>
            <p class="text-gray-600 mb-4">Your data is protected with enterprise-grade security. Role-based access ensures privacy and data integrity.</p>
            <ul class="list-none text-left space-y-2">
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">End-to-end encryption</span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">Role-based permissions</span>
              </li>
              <li class="flex items-start">
                <i class="fas fa-check text-green-600 mr-2 mt-1"></i>
                <span class="text-gray-700">24/7 system monitoring</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-12">
      <div class="container mx-auto px-4">
        <div class="text-center mb-10">
          <h2 class="text-4xl lg:text-5xl font-bold mb-3">How It Works</h2>
          <p class="text-xl text-gray-600">Simple steps to get started</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="text-center">
            <div class="bg-blue-600 text-white rounded-full inline-flex items-center justify-center mb-4 w-20 h-20">
              <span class="text-3xl font-bold">1</span>
            </div>
            <h5 class="font-bold text-lg mb-2">Sign Up</h5>
            <p class="text-gray-600">Choose your role and create your account in seconds.</p>
          </div>
          
          <div class="text-center">
            <div class="bg-green-600 text-white rounded-full inline-flex items-center justify-center mb-4 w-20 h-20">
              <span class="text-3xl font-bold">2</span>
            </div>
            <h5 class="font-bold text-lg mb-2">List or Browse</h5>
            <p class="text-gray-600">Owners list properties, boarders browse and compare options.</p>
          </div>
          
          <div class="text-center">
            <div class="bg-yellow-600 text-white rounded-full inline-flex items-center justify-center mb-4 w-20 h-20">
              <span class="text-3xl font-bold">3</span>
            </div>
            <h5 class="font-bold text-lg mb-2">Connect</h5>
            <p class="text-gray-600">Apply for properties or manage applications through the platform.</p>
          </div>
          
          <div class="text-center">
            <div class="bg-cyan-600 text-white rounded-full inline-flex items-center justify-center mb-4 w-20 h-20">
              <span class="text-3xl font-bold">4</span>
            </div>
            <h5 class="font-bold text-lg mb-2">Manage</h5>
            <p class="text-gray-600">Handle contracts, payments, and reviews all in one place.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-12 bg-blue-600 text-white">
      <div class="container mx-auto px-4 text-center">
        <h2 class="text-4xl lg:text-5xl font-bold mb-3">Ready to Get Started?</h2>
        <p class="text-xl mb-6">Join thousands of property owners and residents already using BoardMate</p>
        <div class="flex justify-center gap-3 flex-wrap">
          <button
            @click="navigateToRegister('admin')"
            type="button"
            class="bg-white text-gray-900 hover:bg-gray-100 font-semibold px-6 py-3 rounded-lg transition duration-200 inline-flex items-center cursor-pointer relative z-10"
          >
            <i class="fas fa-user-plus mr-2"></i>
            Register as Owner
          </button>
          <button
            @click="navigateToRegister('boarder')"
            type="button"
            class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-gray-900 font-semibold px-6 py-3 rounded-lg transition duration-200 inline-flex items-center cursor-pointer relative z-10"
          >
            <i class="fas fa-user-plus mr-2"></i>
            Register as Boarder
          </button>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false
})

useHead({
  link: [
    {
      rel: 'stylesheet',
      href: 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css',
      crossorigin: 'anonymous'
    }
  ]
})

const navigateToLogin = (role) => {
  navigateTo(`/auth/${role}/login`)
}

const navigateToRegister = (role) => {
  navigateTo(`/auth/${role}/register`)
}
</script>

<style scoped>
.hover-lift {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-lift:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}
</style>
