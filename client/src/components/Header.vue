<script setup lang="ts">
import type { Category } from '@/services/api'
import { getAllCategories } from '@/services/api'
import { onMounted, ref } from 'vue'

const categories = ref<Category[]>([])
const loading = ref(true)
const searchLocation = ref('')
const searchDate = ref('')

const emit = defineEmits(['filterCategory', 'search'])

const selectedCategory = ref<number | null>(null)

function filterByCategory(categoryId: number) {
  selectedCategory.value = selectedCategory.value === categoryId ? null : categoryId
  emit('filterCategory', selectedCategory.value)
}

function resetCategory() {
  selectedCategory.value = null
  emit('filterCategory', null)
}

function search() {
  emit('search', {
    location: searchLocation.value,
    date: searchDate.value,
  })
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
  <div class="bg-[#FFF1E9] flex flex-col justify-start px-5 py-8 mt-15">
    <div class="relative w-full p-8 mx-auto mb-6 bg-[#FF8238] rounded-3xl shadow-lg">
      <div class="mb-6 text-white">
        <h3 class="mb-3 text-2xl font-semibold">Trouvez votre espace idéal</h3>
        <p>Recherchez par localisation et date pour découvrir les meilleures offres</p>
      </div>

      <div class="flex flex-col gap-4 md:flex-row">
        <div class="relative flex-1">
          <svg
            class="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 pointer-events-none left-3 top-1/2"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 2C8.13 2 5 5.13 5 9c0 4.8 7 11 7 11s7-6.2 7-11c0-3.87-3.13-7-7-7z"
            />
          </svg>
          <input
            v-model="searchLocation"
            type="text"
            class="w-full p-4 pl-10 border rounded-xl"
            placeholder="Localisation"
          />
        </div>

        <div class="relative flex-1">
          <svg
            class="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 pointer-events-none left-3 top-1/2"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10m-10 4h5m-7 4h10m-5-8h2m2 8h2m-2-4h2"
            />
          </svg>
          <input
            v-model="searchDate"
            type="date"
            class="w-full px-3 py-4 pl-10 border rounded-xl"
          />
        </div>

        <button
          @click="search"
          class="px-6 py-4 font-semibold text-gray-800 bg-white shadow rounded-xl hover:bg-gray-100"
        >
          Rechercher
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="inline-block w-5 h-5 ml-2"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </button>
      </div>
    </div>

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
