<template>
  <div class="min-h-screen bg-blue-500 flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-xl">
      <h1 class="text-3xl font-bold text-gray-900 mb-4">Tailwind CSS Test</h1>
      <p class="text-gray-600">If you see this styled correctly, Tailwind is working!</p>
      <button class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
        Test Button
      </button>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: false
})
</script>

