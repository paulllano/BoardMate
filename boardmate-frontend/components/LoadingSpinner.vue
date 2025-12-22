<template>
  <div class="text-center py-12">
    <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
    <p class="text-gray-600">{{ message || 'Loading...' }}</p>
  </div>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: 'Loading...'
  }
})
</script>

