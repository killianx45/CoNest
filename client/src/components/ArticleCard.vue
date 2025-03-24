<script setup lang="ts">
import type { Produit } from '@/services/api'
import { useRouter } from 'vue-router'
import OptimizedImage from './OptimizedImage.vue'

const props = defineProps<{
  produit: Produit
}>()

const router = useRouter()

function formatPrix(prix: string | number): string {
  return typeof prix === 'string' ? prix : prix.toString()
}

function voirProduit(produitId: number) {
  router.push(`/produit/${produitId}`)
}
</script>

<template>
  <div class="w-full p-2 mx-auto cursor-pointer" @click="voirProduit(produit.id)">
    <div v-if="produit.images && produit.images.length > 0">
      <OptimizedImage
        :src="produit.images[0]"
        :alt="produit.nom"
        :width="300"
        :height="300"
        className="object-cover w-full aspect-square rounded-3xl"
      />
    </div>

    <div class="mx-1 mt-4">
      <section class="flex flex-row justify-between mb-1">
        <h3 class="text-lg font-semibold">{{ produit.nom }}</h3>

        <div class="flex flex-row items-center gap-1" v-if="produit.disponibilite">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
            class="w-4 h-4 text-orange-500"
            width="16"
            height="16"
            aria-hidden="true"
          >
            <path
              fill-rule="evenodd"
              d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.007 5.404.433c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.433 2.082-5.006z"
              clip-rule="evenodd"
            />
          </svg>
          <p class="text-dark-500">Disponible</p>
        </div>
      </section>

      <p class="mb-1 text-sm text-gray-600 line-clamp-2">{{ produit.description }}</p>
      <p class="mb-5 font-semibold text-gray-700">{{ formatPrix(produit.prix) }} €</p>
    </div>
  </div>
</template>
