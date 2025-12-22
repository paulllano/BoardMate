<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="[
      'font-semibold py-3 px-6 rounded-lg shadow-md transition duration-200 flex items-center justify-center',
      variant === 'primary' ? 'bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white' : '',
      variant === 'success' ? 'bg-gradient-to-r from-green-600 to-teal-500 hover:from-green-700 hover:to-teal-600 text-white' : '',
      variant === 'danger' ? 'bg-red-600 hover:bg-red-700 text-white' : '',
      variant === 'warning' ? 'bg-yellow-600 hover:bg-yellow-700 text-white' : '',
      variant === 'secondary' ? 'bg-gray-200 hover:bg-gray-300 text-gray-700' : '',
      disabled ? 'opacity-50 cursor-not-allowed' : '',
      class
    ]"
  >
    <i v-if="icon && !loading" :class="['fas', icon, iconPosition === 'left' ? 'mr-2' : 'ml-2']"></i>
    <i v-if="loading" class="fas fa-spinner fa-spin mr-2"></i>
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'button'
  },
  variant: {
    type: String,
    default: 'primary'
  },
  disabled: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  icon: {
    type: String,
    default: ''
  },
  iconPosition: {
    type: String,
    default: 'left'
  },
  class: {
    type: String,
    default: ''
  }
})
</script>

