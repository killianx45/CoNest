<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'

const props = defineProps<{
  src: string
  alt: string
  width?: number
  height?: number
  className?: string
}>()

const isLoaded = ref(false)
const hasError = ref(false)
const imageRef = ref<HTMLImageElement | null>(null)

const fullSrc = computed(() => {
  if (props.src.startsWith('http')) {
    return props.src
  }
  return `http://localhost:8000/${props.src}`
})

const placeholderSrc = computed(() => {
  return `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 ${props.width || 300} ${props.height || 300}'%3E%3Crect width='100%25' height='100%25' fill='%23f3f4f6'/%3E%3C/svg%3E`
})

const classes = computed(() => {
  return `${props.className || ''} ${isLoaded.value ? 'opacity-100' : 'opacity-0'} transition-opacity duration-300`
})

function handleLoad() {
  isLoaded.value = true
}

function handleError() {
  hasError.value = true
  isLoaded.value = true
}

onMounted(() => {
  if (imageRef.value?.complete) {
    isLoaded.value = true
  }

  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && imageRef.value) {
        imageRef.value.src = fullSrc.value
        observer.disconnect()
      }
    },
    { threshold: 0.1, rootMargin: '200px' },
  )

  if (imageRef.value) {
    observer.observe(imageRef.value)
  }

  return () => {
    observer.disconnect()
  }
})
</script>

<template>
  <div
    class="relative overflow-hidden"
    :style="{ aspectRatio: `${props.width || 1}/${props.height || 1}` }"
  >
    <div v-if="hasError" class="flex items-center justify-center w-full h-full bg-gray-100">
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        class="text-gray-400"
      >
        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
        <path d="M9 9h6v6H9z"></path>
      </svg>
    </div>

    <img
      v-else
      ref="imageRef"
      :src="placeholderSrc"
      :data-src="fullSrc"
      :alt="alt"
      :width="width"
      :height="height"
      :class="classes"
      @load="handleLoad"
      @error="handleError"
      loading="lazy"
      decoding="async"
    />

    <div
      v-if="!isLoaded && !hasError"
      class="absolute inset-0 flex items-center justify-center bg-gray-100 bg-opacity-50"
    >
      <div
        class="w-5 h-5 border-2 border-orange-500 rounded-full border-t-transparent animate-spin"
      ></div>
    </div>
  </div>
</template>
