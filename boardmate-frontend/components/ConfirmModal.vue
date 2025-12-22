<template>
  <Modal :show="show" :title="title" @update:show="handleClose">
    <div class="text-center">
      <div class="mb-4">
        <div :class="[
          'rounded-full inline-flex items-center justify-center',
          iconBgClass
        ]" style="width: 80px; height: 80px;">
          <i :class="[icon, iconClass, 'text-3xl']"></i>
        </div>
      </div>
      <p class="text-gray-700 mb-6">{{ message }}</p>
    </div>
    
    <template #footer>
      <button
        @click="handleClose"
        class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-6 rounded-lg transition duration-200"
      >
        Cancel
      </button>
      <button
        @click="handleConfirm"
        :disabled="disabled"
        :class="[
          'font-semibold py-2 px-6 rounded-lg transition duration-200 text-white',
          confirmButtonClass,
          disabled ? 'opacity-50 cursor-not-allowed' : ''
        ]"
      >
        <i v-if="disabled" class="fas fa-spinner fa-spin mr-2"></i>
        {{ confirmText }}
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
    default: 'Confirm Action'
  },
  message: {
    type: String,
    required: true
  },
  confirmText: {
    type: String,
    default: 'Confirm'
  },
  variant: {
    type: String,
    default: 'danger', // danger, warning, info
    validator: (value) => ['danger', 'warning', 'info'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:show', 'confirm', 'cancel'])

const iconMap = {
  danger: 'fas fa-exclamation-triangle',
  warning: 'fas fa-exclamation-circle',
  info: 'fas fa-info-circle'
}

const iconClassMap = {
  danger: 'text-red-600',
  warning: 'text-yellow-600',
  info: 'text-blue-600'
}

const iconBgClassMap = {
  danger: 'bg-red-100',
  warning: 'bg-yellow-100',
  info: 'bg-blue-100'
}

const confirmButtonClassMap = {
  danger: 'bg-red-600 hover:bg-red-700',
  warning: 'bg-yellow-600 hover:bg-yellow-700',
  info: 'bg-blue-600 hover:bg-blue-700'
}

const icon = computed(() => iconMap[props.variant])
const iconClass = computed(() => iconClassMap[props.variant])
const iconBgClass = computed(() => iconBgClassMap[props.variant])
const confirmButtonClass = computed(() => confirmButtonClassMap[props.variant])

const handleClose = () => {
  emit('update:show', false)
  emit('cancel')
}

const handleConfirm = () => {
  emit('confirm')
  emit('update:show', false)
}
</script>

