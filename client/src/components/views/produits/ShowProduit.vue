<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Produit } from '@/services/api'
import { getProduitById, isAuthenticated } from '@/services/api'
import NavBar from '../../NavBar.vue'

const route = useRoute()
const router = useRouter()
const produit = ref<Produit | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const formatPrix = (prix: string | number): string => {
  return typeof prix === 'string' ? prix : prix.toString()
}

onMounted(async () => {
  if (!isAuthenticated()) {
    router.push({
      path: '/login',
      query: { redirect: route.fullPath },
    })
    return
  }

  try {
    const produitId = parseInt(route.params.id as string, 10)
    if (isNaN(produitId)) {
      error.value = 'ID de produit invalide'
      loading.value = false
      return
    }

    loading.value = true
    produit.value = await getProduitById(produitId)
    loading.value = false
  } catch (err) {
    loading.value = false
    if (err instanceof Error && err.message.includes('Authentification requise')) {
      error.value = 'Authentification requise pour accéder aux détails du produit'
      setTimeout(() => {
        router.push({
          path: '/login',
          query: { redirect: route.fullPath },
        })
      }, 2000)
    } else {
      error.value = 'Erreur lors du chargement du produit'
      console.error('Erreur lors du chargement du produit:', err)
    }
  }
})
</script>

<template>
  <div class="flex flex-col min-h-screen">
    <NavBar />

    <div class="container max-w-4xl px-4 mx-auto mt-24">
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
      <div v-else-if="produit" class="grid grid-cols-1 gap-8 md:grid-cols-2">
        <div class="grid grid-cols-2 gap-4">
          <div
            v-if="produit.images && produit.images.length > 0"
            v-for="image in produit.images"
            :key="image"
            class="overflow-hidden rounded-lg shadow-lg"
          >
            <img
              :src="`http://localhost:8000/${image}`"
              :alt="produit.nom"
              class="object-cover w-full h-32 md:h-48"
            />
          </div>
          <div
            v-else
            class="flex items-center justify-center w-full h-64 col-span-2 bg-gray-200 md:h-96"
          >
            <span class="text-gray-500">Aucune image disponible</span>
          </div>
        </div>

        <div class="flex flex-col">
          <h1 class="mb-4 text-2xl font-bold">{{ produit.nom }}</h1>
          <p class="mb-6 text-lg font-semibold text-blue-600">{{ formatPrix(produit.prix) }} €</p>
          <p class="mb-4 text-sm text-gray-600">{{ produit.disponibilite }}</p>

          <div class="p-4 mb-6 bg-gray-100 rounded-lg">
            <h2 class="mb-2 text-lg font-semibold">Description</h2>
            <p class="text-gray-700">{{ produit.description }}</p>
          </div>

          <button
            class="px-6 py-2 mt-auto font-semibold text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700"
          >
            Ajouter au panier
          </button>
          <router-link :to="`/produit/${produit.id}/edit`">
            <button
              class="px-6 py-2 mt-4 font-semibold text-white transition-colors bg-blue-600 rounded-lg hover:bg-blue-700"
            >
              Modifier
            </button>
          </router-link>
        </div>
      </div>
      <div v-else class="py-8 text-center text-gray-500">Produit non trouvé.</div>
    </div>
  </div>
</template>
