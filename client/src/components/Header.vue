<script setup lang="ts">
import type { Category } from '@/services/api'
import { getAllCategories } from '@/services/api'
import { onMounted, ref } from 'vue'

const categories = ref<Category[]>([])
const loading = ref(true)

const emit = defineEmits(['filterCategory'])

const selectedCategory = ref<number | null>(null)

function filterByCategory(categoryId: number) {
  selectedCategory.value = selectedCategory.value === categoryId ? null : categoryId
  emit('filterCategory', selectedCategory.value)
}

function resetCategory() {
  selectedCategory.value = null
  emit('filterCategory', null)
}

onMounted(async () => {
  try {
    categories.value = await getAllCategories()
  } catch (err) {
    console.error('Erreur lors du chargement des catégories:', err)
    categories.value = []
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="bg-[#FFF1E9] flex flex-col justify-start px-5 py-8">
    <h1 class="mb-4 text-4xl font-semibold text-black">Bienvenue sur CoNest</h1>
    <h2 class="mb-8 text-2xl text-black/80">Les meilleurs espaces pour vos projets</h2>

    <div
      v-if="!loading"
      class="flex flex-wrap items-center justify-start gap-3 p-4 mb-6 overflow-x-auto bg-white shadow-lg filter rounded-xl"
    >
      <button
        class="px-3 py-2 text-sm font-medium transition-colors bg-gray-200 rounded-xl hover:bg-gray-300"
        @click="resetCategory"
      >
        ×
      </button>
      <button
        v-for="category in categories"
        :key="category.id"
        @click="filterByCategory(category.id)"
        class="px-4 py-2 text-sm font-medium transition-colors border rounded-full"
        :class="{
          'bg-orange-500 text-white border-orange-500': selectedCategory === category.id,
          'bg-white text-gray-700 border-gray-300 hover:bg-orange-50':
            selectedCategory !== category.id,
        }"
      >
        {{ category.name }}
      </button>
    </div>

    <div
      v-else
      class="flex flex-wrap items-center justify-start gap-3 p-4 mb-6 bg-white shadow-lg filter rounded-xl"
    >
      <div class="flex items-center justify-center w-full py-4">
        <div
          class="w-8 h-8 border-t-2 border-b-2 border-orange-500 rounded-full animate-spin"
        ></div>
      </div>
    </div>
  </div>
</template>
