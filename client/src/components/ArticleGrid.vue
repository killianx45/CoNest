<script setup lang="ts">
import type { Produit } from '@/services/api'
import { computed, onMounted, ref, watch } from 'vue'
import ArticleCard from './ArticleCard.vue'

const props = defineProps<{
  produits: Produit[]
  selectedCategory: number | null
  error: string | null
  loading: boolean
}>()

const itemsPerBatch = 6
const visibleCount = ref(itemsPerBatch)

const visibleProduits = computed(() => {
  return props.produits.slice(0, visibleCount.value)
})

let observer: IntersectionObserver | null = null

function loadMoreItems() {
  if (visibleCount.value < props.produits.length) {
    visibleCount.value = Math.min(visibleCount.value + itemsPerBatch, props.produits.length)
  }
}

watch(
  () => props.produits,
  () => {
    visibleCount.value = itemsPerBatch
  },
)

onMounted(() => {
  observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting && visibleCount.value < props.produits.length) {
        loadMoreItems()
      }
    },
    {
      root: null,
      rootMargin: '0px',
      threshold: 0.1,
    },
  )

  const sentinel = document.querySelector('.loading-sentinel')
  if (sentinel) {
    observer.observe(sentinel)
  }
})
</script>

<template>
  <div class="py-8 px-4 md:p-12 bg-[#FFF1E9] rounded-xl">
    <div
      v-if="error"
      class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded-xl"
    >
      {{ error }}
    </div>

    <div v-if="loading" class="flex items-center justify-center py-8">
      <div class="w-12 h-12 border-t-2 border-b-2 border-orange-500 rounded-xl animate-spin"></div>
    </div>

    <div v-else-if="produits.length" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <ArticleCard
        v-for="produit in visibleProduits"
        :key="produit.id"
        :produit="produit"
        class="transition-opacity duration-300 ease-in-out opacity-0 animate-fadeIn"
      />

      <div v-if="visibleCount < produits.length" class="h-8 loading-sentinel"></div>
    </div>

    <div v-if="!loading && visibleCount < produits.length" class="flex justify-center mt-6">
      <button
        @click="loadMoreItems"
        class="z-10 px-4 py-2 text-white transition-colors bg-orange-400 rounded-xl hover:bg-orange-500"
      >
        Voir plus
      </button>
    </div>

    <p v-else-if="!loading && produits.length === 0" class="py-8 text-center text-gray-500">
      {{
        selectedCategory
          ? 'Aucun produit dans cette catégorie.'
          : 'Aucun produit disponible pour le moment.'
      }}
    </p>

    <div v-if="visibleCount < produits.length" class="relative mt-8">
      <div
        class="absolute bottom-0 left-0 w-full h-160 bg-gradient-to-t from-[#FFF1E9] to-transparent pointer-events-none"
      ></div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease-out forwards;
}
</style>
