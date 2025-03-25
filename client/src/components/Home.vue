<script setup lang="ts">
import type { Category, Produit } from '@/services/api'
import { getAllCategories, getAllProduits } from '@/services/api'
import { defineAsyncComponent, onMounted, ref } from 'vue'
import ArticleGrid from './ArticleGrid.vue'
import Header from './Header.vue'
import NavBar from './NavBar.vue'

const Carte = defineAsyncComponent(() => import('./Carte.vue'))
const FAQ = defineAsyncComponent(() => import('./FAQ.vue'))

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

const showCarteSection = ref(false)
const showFAQSection = ref(false)

function handleFilterCategory(categoryId: number | null) {
  selectedCategory.value = categoryId
}

function handleSearch(filters: { availabilityDate: string; maxPrice: number | null }) {
  searchFilters.value = filters
}

function handleFilteredProducts(products: Produit[]) {
  filteredProduits.value = products
}

function setupLazyLoading() {
  const observerOptions = {
    root: null,
    rootMargin: '100px',
    threshold: 0.1,
  }

  const carteSection = document.querySelector('#carte-section')
  const faqSection = document.querySelector('#faq-section')

  const sectionObserver = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        if (entry.target.id === 'carte-section') {
          showCarteSection.value = true
        } else if (entry.target.id === 'faq-section') {
          showFAQSection.value = true
        }
        sectionObserver.unobserve(entry.target)
      }
    })
  }, observerOptions)

  if (carteSection) sectionObserver.observe(carteSection)
  if (faqSection) sectionObserver.observe(faqSection)
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

    setupLazyLoading()

    setTimeout(async () => {
      try {
        categories.value = await getAllCategories()
      } catch (err: any) {
        console.error('Erreur lors du chargement des catégories:', err)
        categories.value = []
      }
    }, 200)
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

    <div id="carte-section" class="min-h-[100px] bg-white">
      <Carte v-if="showCarteSection" :produits="filteredProduits" />
      <div v-else class="w-full max-w-4xl py-8 mx-auto">
        <h2 class="mb-6 text-3xl font-semibold text-center">CoNest sur carte !</h2>
        <div class="flex justify-center h-[450px] bg-gray-100 rounded-xl">
          <div class="flex items-center justify-center">
            <div
              class="w-8 h-8 border-t-2 border-b-2 border-orange-500 rounded-xl animate-spin"
            ></div>
          </div>
        </div>
      </div>
    </div>

    <div id="faq-section" class="min-h-[100px] bg-white">
      <FAQ v-if="showFAQSection" />
      <div v-else class="w-full max-w-4xl py-8 mx-auto">
        <h2 class="mb-6 text-3xl font-semibold text-center">FAQ</h2>
        <div class="flex justify-center">
          <div class="flex items-center justify-center">
            <div
              class="w-8 h-8 border-t-2 border-b-2 border-orange-500 rounded-xl animate-spin"
            ></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
