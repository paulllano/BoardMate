<template>
  <span :class="[
    'px-3 py-1 rounded-full text-xs font-semibold',
    getStatusClass(status)
  ]">
    {{ status }}
  </span>
</template>

<script setup>
const props = defineProps({
  status: {
    type: String,
    required: true
  }
})

const getStatusClass = (status) => {
  const statusLower = status.toLowerCase()
  const classes = {
    // Application statuses
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    // Contract statuses
    active: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800',
    // Payment statuses
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[statusLower] || 'bg-gray-100 text-gray-800'
}
</script>

