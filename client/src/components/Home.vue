<script setup lang="ts">
import type { Category, Produit } from '@/services/api'
import { getAllCategories, getAllProduits } from '@/services/api'
import { onMounted, ref } from 'vue'
import ArticleGrid from './ArticleGrid.vue'
import Carte from './Carte.vue'
import FAQ from './FAQ.vue'
import Header from './Header.vue'
import NavBar from './NavBar.vue'
import Requis from './Requis.vue'

const produits = ref<Produit[]>([])
const categories = ref<Category[]>([])
const loading = ref(true)
const error = ref<string | null>(null)
const selectedCategory = ref<number | null>(null)

function handleFilterCategory(categoryId: number | null) {
  selectedCategory.value = categoryId
}

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
  <div class="flex flex-col min-h-screen">
    <NavBar />

    <Requis />

    <div class="w-full">
      <Header @filter-category="handleFilterCategory" />
    </div>

    <ArticleGrid
      :produits="produits"
      :selectedCategory="selectedCategory"
      :error="error"
      :loading="loading"
    />

    <Carte :produits="produits" />

    <FAQ />
  </div>
</template>
