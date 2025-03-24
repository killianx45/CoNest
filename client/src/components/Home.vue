<script setup lang="ts">
import type { Category, Produit } from '@/services/api'
import { getAllCategories, getAllProduits } from '@/services/api'
import { onMounted, ref } from 'vue'
import ArticleGrid from './ArticleGrid.vue'
import Carte from './Carte.vue'
import FAQ from './FAQ.vue'
import Header from './Header.vue'
import NavBar from './NavBar.vue'

const produits = ref<Produit[]>([])
const filteredProduits = ref<Produit[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const selectedCategory = ref<number | null>(null)
const searchFilters = ref({
  availabilityDate: '',
  maxPrice: null as number | null,
})

function handleFilterCategory(categoryId: number | null) {
  selectedCategory.value = categoryId
}

function handleSearch(filters: { availabilityDate: string; maxPrice: number | null }) {
  searchFilters.value = filters
}

function handleFilteredProducts(products: Produit[]) {
  filteredProduits.value = products
}

onMounted(async () => {
  try {
    loading.value = true
    try {
      produits.value = await getAllProduits()
      filteredProduits.value = produits.value
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
  <div class="flex flex-col min-h-screen">
    <NavBar />

    <div class="w-full">
      <Header
        @filter-category="handleFilterCategory"
        @search="handleSearch"
        @filtered-products="handleFilteredProducts"
      />
    </div>

    <ArticleGrid
      :produits="filteredProduits"
      :selectedCategory="selectedCategory"
      :error="error"
      :loading="loading"
    />

    <Carte :produits="filteredProduits" />

    <FAQ />
  </div>
</template>
