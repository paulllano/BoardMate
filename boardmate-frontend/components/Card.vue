<template>
  <div :class="[
    'bg-white rounded-xl shadow-md border-0 p-6',
    hover ? 'hover:shadow-xl transition-all duration-300 hover:-translate-y-1' : '',
    class
  ]">
    <slot />
  </div>
</template>

<script setup>
defineProps({
  hover: {
    type: Boolean,
    default: false
  },
  class: {
    type: String,
    default: ''
  }
})
</script>

