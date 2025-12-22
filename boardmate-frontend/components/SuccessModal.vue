<template>
  <Modal :show="show" @update:show="handleClose" :closeOnBackdrop="closeOnBackdrop">
    <div class="text-center">
      <div class="mb-4">
        <div :class="[
          'rounded-full inline-flex items-center justify-center',
          'bg-green-100'
        ]" style="width: 80px; height: 80px;">
          <i class="fas fa-check-circle text-green-600 text-3xl"></i>
        </div>
      </div>
      <h4 class="text-xl font-bold text-gray-900 mb-3">{{ title }}</h4>
      <p class="text-gray-600 mb-4">{{ message }}</p>
      <div v-if="loading" class="flex items-center justify-center">
        <i class="fas fa-spinner fa-spin text-green-600 text-2xl mb-2"></i>
        <p class="text-gray-500 text-sm ml-3">{{ loadingText }}</p>
      </div>
    </div>
    
    <template #footer>
      <button
        v-if="!loading"
        @click="handleClose"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200"
      >
        <i class="fas fa-check mr-2"></i>OK
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
    default: 'Success!'
  },
  message: {
    type: String,
    required: true
  },
  loading: {
    type: Boolean,
    default: false
  },
  loadingText: {
    type: String,
    default: 'Redirecting...'
  },
  closeOnBackdrop: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:show', 'close'])

const handleClose = () => {
  emit('update:show', false)
  emit('close')
}
</script>

