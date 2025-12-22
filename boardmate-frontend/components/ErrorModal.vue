<template>
  <Modal :show="show" :title="title" @update:show="handleClose">
    <div class="text-center">
      <div class="mb-4">
        <div class="bg-red-100 rounded-full inline-flex items-center justify-center" style="width: 80px; height: 80px;">
          <i class="fas fa-exclamation-circle text-red-600 text-3xl"></i>
        </div>
      </div>
      <h4 class="text-xl font-bold text-gray-900 mb-3">{{ title }}</h4>
      <p class="text-gray-600 mb-4">{{ message }}</p>
    </div>
    
    <template #footer>
      <button
        @click="handleClose"
        class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200"
      >
        <i class="fas fa-times mr-2"></i>Close
      </button>
    </template>
  </Modal>
</template>

<script setup>
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Error'
  },
  message: {
    type: String,
    required: true
  }
})

const emit = defineEmits(['update:show', 'close'])

const handleClose = () => {
  emit('update:show', false)
  emit('close')
}
</script>

