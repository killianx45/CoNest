<script setup lang="ts">
import type { Produit } from '@/services/api'
import { getAllProduits } from '@/services/api'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import Header from './Header.vue'
import NavBar from './NavBar.vue'

const router = useRouter()
const produits = ref<Produit[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

function formatPrix(prix: string | number): string {
  return typeof prix === 'string' ? prix : prix.toString()
}

function voirProduit(produitId: number) {
  router.push(`/produit/${produitId}`)
}

onMounted(async () => {
  try {
    loading.value = true
    produits.value = await getAllProduits()
    loading.value = false
  } catch (error) {
    loading.value = false
    error.value = 'Erreur lors du chargement des produits'
    console.error('Erreur lors du chargement des produits:', error)
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
      <div v-else-if="produits.length" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="produit in produits"
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
      <p v-else class="py-8 text-center text-gray-500">Aucun produit disponible pour le moment.</p>
    </div>
  </div>
</template>
