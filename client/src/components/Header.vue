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
  <div class="bg-[#FFF1E9] px-4 md:pl-[1vw] py-8 md:py-[5vh] lg:py-[5vh] mt-10">
    <fieldset
      class="relative fieldset w-full md:w-[86vw] mx-auto bg-[#FF8238] p-6 md:pl-20 md:pr-100 md:py-10 rounded-xl shadow-lg"
    >
      <div class="flex flex-col md:flex-row md:justify-between md:items-start">
        <h2 class="text-2xl md:text-[3vw] font-semibold mb-6 md:mb-10 text-white">
          Vos idées, vos espaces
        </h2>
        <img
          class="hidden md:block z-[10] absolute bottom-10 right-[-50px] w-[20vw] h-auto"
          src="@/assets/img_header.png"
          alt="Image d'en-tête"
        />
      </div>

      <div class="flex flex-col gap-4 md:flex-row">
        <div class="relative flex-1">
          <svg
            class="absolute w-5 h-5 text-black transform -translate-y-1/2 pointer-events-none left-3 top-1/2"
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
            :aria-label="'Rechercher par date'"
            v-model="searchDate"
            type="date"
            class="w-full px-3 py-3 pl-10 border input rounded-xl md:py-2 bg-orange-50"
          />
        </div>

        <div class="relative flex-1">
          <img
            src="@/assets/coin.svg"
            alt="Icône de pièce"
            class="absolute w-5 h-5 text-black transform -translate-y-1/2 pointer-events-none left-3 top-1/2"
          />
          <input
            :value="maxPrice !== null ? maxPrice : ''"
            :aria-label="'Rechercher par budget'"
            @input="handlePriceInput"
            type="number"
            min="0"
            class="w-full px-3 py-3 pl-10 text-black border input rounded-xl md:py-2 bg-orange-50"
            placeholder="Budget (€)"
          />
        </div>

        <div class="flex justify-center">
          <button
            @click="search"
            class="w-full px-6 py-3 transition-colors bg-orange-50 md:w-auto md:py-2 rounded-xl"
            aria-label="Rechercher"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-6 h-6 mx-auto"
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
    </fieldset>

    <div class="mt-8 w-full md:w-[86vw] mx-auto px-4 md:px-0">
      <h2 class="mb-4 text-xl font-bold md:text-2xl">Vos meilleurs espaces</h2>

      <div
        v-if="!loading"
        class="flex items-center gap-3 p-4 mb-6 overflow-x-auto bg-white shadow-lg flex-nowrap md:flex-wrap filter rounded-xl"
      >
        <button
          class="px-3 py-2 text-sm font-medium transition-colors bg-gray-200 rounded-xl hover:bg-gray-300 whitespace-nowrap"
          @click="resetCategory"
          aria-label="Réinitialiser les filtres de catégorie"
        >
          ×
        </button>
        <button
          v-for="category in categoriesCache"
          :key="category.id"
          @click="filterByCategory(category.id)"
          class="px-4 py-2 text-sm font-medium transition-colors border rounded-xl whitespace-nowrap"
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
            class="w-8 h-8 border-t-2 border-b-2 border-orange-500 rounded-xl animate-spin"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>
