<script setup lang="ts">
import type { Category, Produit } from '@/services/api'
import { getAllCategories, getAllProduits } from '@/services/api'
import { computed, onMounted, ref, watch } from 'vue'

const categories = ref<Category[]>([])
const produits = ref<Produit[]>([])
const filteredProduits = ref<Produit[]>([])
const loading = ref(true)
const searchDate = ref('')
const maxPrice = ref<number | null>(null)

const emit = defineEmits(['filterCategory', 'search', 'filteredProducts'])

const selectedCategory = ref<number | null>(null)

const categoriesCache = computed(() => {
  return categories.value.slice(0, 10)
})

function filterByCategory(categoryId: number) {
  selectedCategory.value = selectedCategory.value === categoryId ? null : categoryId
  emit('filterCategory', selectedCategory.value)
  applyFilters()
}

function resetCategory() {
  selectedCategory.value = null
  emit('filterCategory', null)
  applyFilters()
}

let searchTimeout: number | null = null
function search() {
  if (searchTimeout) {
    clearTimeout(searchTimeout)
  }

  searchTimeout = window.setTimeout(() => {
    applyFilters()
    emit('search', {
      availabilityDate: searchDate.value,
      maxPrice: maxPrice.value,
    })
    searchTimeout = null
  }, 300)
}

function handlePriceInput(event: Event) {
  const target = event.target as HTMLInputElement
  const value = target.value ? parseInt(target.value) : null
  maxPrice.value = isNaN(value as number) ? null : value
  search()
}

function applyFilters() {
  let result = [...produits.value]

  if (selectedCategory.value !== null) {
    result = result.filter((produit) => {
      if (produit.categories) {
        return produit.categories.some((cat) => {
          if (typeof cat === 'string') {
            const categoryId = parseInt((cat as string).split('/').pop() || '0', 10)
            return categoryId === selectedCategory.value
          } else if (typeof cat === 'object' && cat !== null) {
            return cat.id === selectedCategory.value
          }
          return false
        })
      }
      return false
    })
  }

  if (searchDate.value) {
    result = result.filter((produit) => {
      if (!produit.disponibilite) return false

      try {
        const dates = produit.disponibilite.split('-')
        if (dates.length < 2) return false

        let dateDebut, dateFin

        if (dates.length === 2) {
          dateDebut = new Date(dates[0])
          dateFin = new Date(dates[1])
        } else if (dates.length >= 6) {
          dateDebut = new Date(`${dates[0]}-${dates[1]}-${dates[2]}`)
          dateFin = new Date(`${dates[3]}-${dates[4]}-${dates[5]}`)
        } else {
          return false
        }

        const dateRecherche = new Date(searchDate.value)

        return dateRecherche >= dateDebut && dateRecherche <= dateFin
      } catch (e) {
        console.error('Erreur de parsing de date:', e)
        return false
      }
    })
  }

  if (maxPrice.value !== null) {
    result = result.filter((produit) => {
      const prix = typeof produit.prix === 'string' ? parseFloat(produit.prix) : produit.prix
      return prix <= maxPrice.value!
    })
  }

  filteredProduits.value = result
  emit('filteredProducts', result)
}

watch([searchDate], () => {
  search()
})

onMounted(async () => {
  try {
    const produitsData = await getAllProduits()
    produits.value = produitsData
    filteredProduits.value = produitsData
    emit('filteredProducts', produitsData)

    setTimeout(async () => {
      try {
        const categoriesData = await getAllCategories()
        categories.value = categoriesData
      } catch (err) {
        console.error('Erreur lors du chargement des catégories:', err)
        categories.value = []
      } finally {
        loading.value = false
      }
    }, 100)
  } catch (err) {
    console.error('Erreur lors du chargement des données:', err)
    categories.value = []
    produits.value = []
    filteredProduits.value = []
    loading.value = false
  }
})
</script>

<template>
  <div class="bg-[#FFF1E9] flex flex-col justify-start px-5 py-8 mt-15">
    <div class="relative w-full p-8 mx-auto mb-6 bg-[#FF8238] rounded-3xl shadow-lg">
      <div class="mb-6">
        <h1 class="mb-3 text-3xl font-bold text-white">Vos idées, vos espaces</h1>
      </div>

      <div class="flex flex-col gap-4 md:flex-row">
        <div class="relative flex-1">
          <svg
            class="absolute w-5 h-5 text-black transform -translate-y-1/2 pointer-events-none left-3 top-1/2"
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
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
            placeholder="Début"
            aria-label="Date de disponibilité"
          />
        </div>

        <div class="relative flex-1">
          <svg
            class="absolute w-5 h-5 text-black transform -translate-y-1/2 pointer-events-none left-3 top-1/2"
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <input
            :value="maxPrice !== null ? maxPrice : ''"
            @input="handlePriceInput"
            type="number"
            min="0"
            class="w-full p-4 pl-10 border rounded-xl"
            placeholder="Budget max"
            aria-label="Prix maximum"
          />
        </div>

        <button
          @click="search"
          aria-label="Rechercher des espaces de coworking"
          class="px-6 py-4 font-semibold text-gray-800 bg-white shadow rounded-xl hover:bg-gray-100"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="inline-block w-5 h-5"
            width="20"
            height="20"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
            aria-hidden="true"
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

    <h2 class="mb-4 text-2xl font-bold">Vos meilleurs espaces</h2>

    <div
      v-if="!loading"
      class="flex flex-wrap items-center justify-start gap-3 p-4 mb-6 overflow-x-auto bg-white shadow-lg filter rounded-xl"
    >
      <button
        class="px-3 py-2 text-sm font-medium transition-colors bg-gray-200 rounded-xl hover:bg-gray-300"
        @click="resetCategory"
        aria-label="Réinitialiser les filtres de catégorie"
      >
        ×
      </button>
      <button
        v-for="category in categoriesCache"
        :key="category.id"
        @click="filterByCategory(category.id)"
        class="px-4 py-2 text-sm font-medium transition-colors border rounded-full"
        :class="{
          'bg-orange-500 text-white border-orange-500': selectedCategory === category.id,
          'bg-white text-gray-700 border-gray-300 hover:bg-orange-50':
            selectedCategory !== category.id,
        }"
        :aria-label="'Filtrer par catégorie ' + category.name"
        :aria-pressed="selectedCategory === category.id"
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
