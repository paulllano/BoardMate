<template>
  <div class="bg-white rounded-xl shadow-md border-0 p-12 text-center">
    <i :class="['fas', icon, 'text-5xl text-gray-400 mb-4']"></i>
    <h4 class="text-xl font-semibold text-gray-600 mb-2">{{ title }}</h4>
    <p class="text-gray-500 mb-4">{{ message }}</p>
    <slot name="action" />
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: 'fa-inbox'
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  }
})
</script>

