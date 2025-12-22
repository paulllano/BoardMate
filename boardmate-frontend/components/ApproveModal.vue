<template>
  <Modal :show="show" :title="title" @update:show="handleClose">
    <div>
      <div v-if="details" class="bg-green-50 border-l-4 border-green-400 p-4 rounded mb-4">
        <div class="flex items-start">
          <i class="fas fa-info-circle text-green-600 mr-3 mt-1"></i>
          <div>
            <strong class="text-green-800">Details:</strong>
            <div class="text-sm text-green-700 mt-1" v-html="details"></div>
          </div>
        </div>
      </div>
      
      <p class="text-gray-700 mb-4">{{ message }}</p>
      
      <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded">
        <div class="flex items-start">
          <i class="fas fa-exclamation-triangle text-blue-600 mr-3 mt-1"></i>
          <div class="text-sm text-blue-700">
            <strong>Note:</strong> {{ note }}
          </div>
        </div>
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
        @click="handleApprove"
        class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 flex items-center"
      >
        <i class="fas fa-check mr-2"></i>
        {{ approveText }}
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
    default: 'Approve Application'
  },
  message: {
    type: String,
    default: 'Are you sure you want to approve this application?'
  },
  details: {
    type: String,
    default: ''
  },
  note: {
    type: String,
    default: 'The boarder will be assigned to this boarding house upon approval.'
  },
  approveText: {
    type: String,
    default: 'Approve'
  }
})

const emit = defineEmits(['update:show', 'approve', 'cancel'])

const handleClose = () => {
  emit('update:show', false)
  emit('cancel')
}

const handleApprove = () => {
  emit('approve')
  emit('update:show', false)
}
</script>

