<script setup lang="ts">
import type { Produit } from '@/services/api'
import ArticleCard from './ArticleCard.vue'

const props = defineProps<{
  produits: Produit[]
  selectedCategory: number | null
  error: string | null
  loading: boolean
}>()
</script>

<template>
  <div class="py-8 px-4 md:p-12 bg-[#FFF1E9] rounded-xl">
    <div v-if="error" class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded">
      {{ error }}
    </div>

    <div v-if="loading" class="flex items-center justify-center py-8">
      <div
        class="w-12 h-12 border-t-2 border-b-2 border-orange-500 rounded-full animate-spin"
      ></div>
    </div>

    <div v-else-if="produits.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <ArticleCard v-for="produit in produits" :key="produit.id" :produit="produit" />
    </div>

    <p v-else class="py-8 text-center text-gray-500">
      {{
        selectedCategory
          ? 'Aucun produit dans cette catégorie.'
          : 'Aucun produit disponible pour le moment.'
      }}
    </p>

    <div class="relative mt-8">
      <div class="w-full h-16 bg-gradient-to-b from-transparent to-white/80 rounded-b-3xl"></div>
    </div>
  </div>
</template>
