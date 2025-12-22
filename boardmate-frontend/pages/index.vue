<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="text-center">
      <h1 class="text-2xl font-bold mb-4">Loading...</h1>
      <p class="text-gray-600">Redirecting to role selection...</p>
    </div>
  </div>
</template>

<script setup>
// Redirect to role selection on page load
onMounted(() => {
  navigateTo('/auth/role')
})
</script>

