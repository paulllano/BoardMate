<template>
  <Modal :show="show" :title="title" @update:show="handleClose">
    <div>
      <div v-if="details" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded mb-4">
        <div class="flex items-start">
          <i class="fas fa-info-circle text-yellow-600 mr-3 mt-1"></i>
          <div>
            <strong class="text-yellow-800">Details:</strong>
            <div class="text-sm text-yellow-700 mt-1" v-html="details"></div>
          </div>
        </div>
      </div>
      
      <p class="text-gray-700 mb-4">{{ message }}</p>
      
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">
          {{ reasonLabel }} <span class="text-red-500">*</span>
        </label>
        <textarea
          v-model="reason"
          rows="3"
          required
          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent"
          :placeholder="reasonPlaceholder"
        ></textarea>
      </div>
    </div>
    
    <template #footer>
      <button
        @click="handleClose"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition duration-200"
      >
        Cancel
      </button>
      <button
        @click="handleReject"
        :disabled="!reason.trim()"
        class="bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold py-2 px-6 rounded-lg transition duration-200"
      >
        {{ rejectText }}
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
    default: 'Reject'
  },
  message: {
    type: String,
    default: 'Are you sure you want to reject this?'
  },
  details: {
    type: String,
    default: ''
  },
  reasonLabel: {
    type: String,
    default: 'Reason for rejection'
  },
  reasonPlaceholder: {
    type: String,
    default: 'Please provide a reason...'
  },
  rejectText: {
    type: String,
    default: 'Reject'
  }
})

const emit = defineEmits(['update:show', 'reject', 'cancel'])

const reason = ref('')

const handleClose = () => {
  reason.value = ''
  emit('update:show', false)
  emit('cancel')
}

const handleReject = () => {
  if (!reason.value.trim()) return
  emit('reject', reason.value)
  reason.value = ''
  emit('update:show', false)
}

// Reset reason when modal closes
watch(() => props.show, (newVal) => {
  if (!newVal) {
    reason.value = ''
  }
})
</script>

