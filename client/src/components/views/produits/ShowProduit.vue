<script setup lang="ts">
import type { Produit } from '@/services/api'
import { getProduitById, isAuthenticated } from '@/services/api'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import NavBar from '../../NavBar.vue'

const route = useRoute()
const router = useRouter()
const produit = ref<Produit | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const activeImageIndex = ref(0)

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

const ajouterAuCommande = () => {
  if (produit.value) {
    router.push({
      name: 'createCommande',
      query: { produitId: produit.value.id.toString() },
    })
  }
}

const setActiveImage = (index: number) => {
  activeImageIndex.value = index
}
</script>

<template>
  <div class="flex flex-col min-h-screen bg-[#FFF1E9]">
    <NavBar />

    <div class="container max-w-5xl px-4 mx-auto mt-24 mb-10">
      <router-link
        to="/"
        class="inline-flex items-center mb-6 text-gray-600 hover:text-[#FF8238] transition-colors"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 mr-2"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
            clip-rule="evenodd"
          />
        </svg>
        Retour
      </router-link>

      <div
        v-if="error"
        class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded-xl"
      >
        {{ error }}
      </div>

      <div v-if="loading" class="flex items-center justify-center py-16">
        <div
          class="w-16 h-16 border-t-2 border-b-2 border-[#FF8238] rounded-full animate-spin"
        ></div>
      </div>

      <div v-else-if="produit" class="p-6 bg-white shadow-md rounded-3xl">
        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
          <div>
            <div v-if="produit.images && produit.images.length > 0" class="mb-4">
              <div class="overflow-hidden bg-white shadow-md rounded-3xl aspect-square">
                <img
                  :src="`http://localhost:8000/${produit.images[activeImageIndex]}`"
                  :alt="produit.nom"
                  class="object-cover w-full h-full"
                />
              </div>

              <div class="grid grid-cols-4 gap-2 mt-3">
                <div
                  v-for="(image, index) in produit.images"
                  :key="index"
                  class="overflow-hidden cursor-pointer rounded-xl aspect-square"
                  :class="{ 'ring-2 ring-[#FF8238]': activeImageIndex === index }"
                  @click="setActiveImage(index)"
                >
                  <img
                    :src="`http://localhost:8000/${image}`"
                    :alt="`${produit.nom} - ${index + 1}`"
                    class="object-cover w-full h-full"
                  />
                </div>
              </div>
            </div>
            <div
              v-else
              class="flex items-center justify-center bg-gray-100 rounded-3xl aspect-square"
            >
              <span class="text-gray-500">Aucune image disponible</span>
            </div>
          </div>

          <div class="flex flex-col">
            <div class="mb-6">
              <h1 class="mb-2 text-3xl font-bold text-gray-800">{{ produit.nom }}</h1>
              <p class="mb-3 text-2xl font-semibold text-[#FF8238]">
                {{ formatPrix(produit.prix) }} €
              </p>
              <div class="flex items-center">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 text-[#FF8238] mr-1"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                    clip-rule="evenodd"
                  />
                </svg>
                <p class="text-gray-600">{{ produit.disponibilite || 'Disponible' }}</p>
              </div>
            </div>

            <div class="p-5 mb-6 bg-[#f8f3f0] rounded-xl">
              <h2 class="mb-3 text-xl font-semibold text-gray-800">Description</h2>
              <p class="text-gray-700">{{ produit.description }}</p>
            </div>

            <div class="p-5 mb-6 bg-[#f8f3f0] rounded-xl">
              <h2 class="mb-3 text-xl font-semibold text-gray-800">Adresse</h2>
              <div class="flex items-start">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5 mt-1 mr-2 text-[#FF8238]"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                    clip-rule="evenodd"
                  />
                </svg>
                <p class="text-gray-700">{{ produit.adresse }}</p>
              </div>
            </div>

            <div class="flex flex-col mt-auto space-y-3 sm:flex-row sm:space-y-0 sm:space-x-3">
              <button
                class="px-6 py-3 font-medium text-white transition-colors rounded-xl bg-[#FF8238] hover:bg-[#e67530]"
                @click="ajouterAuCommande"
              >
                Réserver un créneau
              </button>
              <router-link :to="`/produit/${produit.id}/edit`" class="sm:flex-1">
                <button
                  class="w-full px-6 py-3 font-medium text-[#FF8238] transition-colors bg-white border border-[#FF8238] rounded-xl hover:bg-[#FFF1E9]"
                >
                  Modifier l'annonce
                </button>
              </router-link>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="p-8 text-center text-gray-500 bg-white rounded-3xl">
        Produit non trouvé.
      </div>
    </div>
  </div>
</template>
