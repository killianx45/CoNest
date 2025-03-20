<script setup lang="ts">
import type { Category, Produit } from '@/services/api'
import { getAllCategories, getAllProduits } from '@/services/api'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Header from './Header.vue'
import NavBar from './NavBar.vue'

const router = useRouter()
const produits = ref<Produit[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const selectedCategory = ref<number | null>(null)

function formatPrix(prix: string | number): string {
  return typeof prix === 'string' ? prix : prix.toString()
}

function voirProduit(produitId: number) {
  router.push(`/produit/${produitId}`)
}

function filterByCategory(categoryId: number | null) {
  selectedCategory.value = selectedCategory.value === categoryId ? null : categoryId
}

const filteredProduits = computed(() => {
  if (!selectedCategory.value) return produits.value
  return produits.value.filter((produit) => {
    return produit.categories?.some((cat) => {
      if (typeof cat === 'string') {
        const categoryId = parseInt((cat as string).split('/').pop() || '0', 10)
        return categoryId === selectedCategory.value
      } else {
        return (cat as Category).id === selectedCategory.value
      }
    })
  })
})

onMounted(async () => {
  try {
    loading.value = true
    try {
      produits.value = await getAllProduits()
    } catch (err: any) {
      console.error('Erreur lors du chargement des produits:', err)
      error.value = 'Erreur lors du chargement des produits. Veuillez réessayer.'
    }

    try {
      categories.value = await getAllCategories()
    } catch (err: any) {
      console.error('Erreur lors du chargement des catégories:', err)
      categories.value = []
    }
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="flex flex-col items-center justify-center min-h-screen">
    <NavBar />
    <div class="mt-20">
      <Header />
    </div>
    <div class="w-full max-w-4xl px-4 mt-8">
      <div class="mb-6">
        <h2 class="mb-4 text-xl font-bold">Catégories</h2>
        <div class="flex flex-wrap gap-2">
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
      </div>

      <h2 class="mb-4 text-xl font-bold">Aperçu des offres</h2>
      <div
        v-if="error"
        class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded"
      >
        {{ error }}
      </div>
      <div v-if="loading" class="flex items-center justify-center py-8">
        <div
          class="w-12 h-12 border-t-2 border-b-2 border-blue-500 rounded-full animate-spin"
        ></div>
      </div>
      <div
        v-else-if="filteredProduits.length"
        class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3"
      >
        <div
          v-for="produit in filteredProduits"
          :key="produit.id"
          class="p-4 transition-shadow border rounded shadow cursor-pointer hover:shadow-lg"
          @click="voirProduit(produit.id)"
        >
          <div class="flex flex-col h-full">
            <div v-if="produit.images && produit.images.length > 0" class="mb-2">
              <img
                :src="`http://localhost:8000/${produit.images[0]}`"
                :alt="produit.nom"
                class="object-cover w-full h-40 rounded"
              />
            </div>
            <h3 class="text-lg font-bold">{{ produit.nom }}</h3>
            <p class="flex-grow my-2 text-sm">{{ produit.description }}</p>
            <div class="mt-auto">
              <p class="text-lg font-semibold text-blue-600">{{ formatPrix(produit.prix) }} €</p>
              <p class="text-xs text-gray-600">{{ produit.disponibilite }}</p>
            </div>
          </div>
        </div>
      </div>
      <p v-else class="py-8 text-center text-gray-500">
        {{
          selectedCategory
            ? 'Aucun produit dans cette catégorie.'
            : 'Aucun produit disponible pour le moment.'
        }}
      </p>
    </div>
  </div>
</template>
