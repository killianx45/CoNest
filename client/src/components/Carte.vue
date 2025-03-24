<script setup lang="ts">
import type { Produit } from '@/services/api'
import { onMounted, ref } from 'vue'

defineProps<{
  produits?: Produit[]
}>()

const mapLoaded = ref(false)
const mapError = ref(false)
const showMap = ref(false)

onMounted(() => {
  const observer = new IntersectionObserver(
    (entries) => {
      if (entries[0].isIntersecting) {
        showMap.value = true
        observer.disconnect()
      }
    },
    { threshold: 0.1 },
  )

  const mapContainer = document.querySelector('.map-container')
  if (mapContainer) {
    observer.observe(mapContainer)
  }

  setTimeout(() => {
    const iframe = document.querySelector('.google-map-iframe')
    if (iframe && !mapLoaded.value) {
      mapError.value = true
    }
  }, 5000)
})

const handleMapLoad = () => {
  mapLoaded.value = true
  mapError.value = false
}
</script>

<template>
  <div class="w-full max-w-4xl py-8 mx-auto">
    <h2 class="mb-6 text-3xl font-semibold text-center">CoNest sur carte !</h2>
    <div class="flex justify-center mx-auto map-container rounded-xl" style="min-height: 450px">
      <div
        v-if="mapError"
        class="w-full h-[450px] flex items-center justify-center bg-gray-100 rounded-xl shadow-md"
      >
        <div class="p-6 text-center">
          <p class="mb-4 text-gray-600">La carte Google Maps n'a pas pu être chargée.</p>
          <p class="text-sm text-gray-500">
            Cela peut être dû à un bloqueur de contenu ou à des paramètres de confidentialité.
          </p>
        </div>
      </div>

      <div
        v-else-if="!showMap"
        class="w-full h-[450px] flex items-center justify-center bg-gray-100 rounded-xl shadow-md"
      >
        <div class="text-center">
          <div
            class="w-8 h-8 mx-auto mb-4 border-t-2 border-b-2 border-orange-500 rounded-full animate-spin"
          ></div>
          <p class="text-gray-600">Chargement de la carte...</p>
        </div>
      </div>

      <iframe
        v-else
        src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d428.6700538934743!2d4.847028601583465!3d45.75407641976787!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47f4eb6e87293eeb%3A0xb63d3b822421846b!2sLudilyon!5e0!3m2!1sfr!2sfr!4v1742738539387!5m2!1sfr!2sfr"
        class="w-full h-[450px] rounded-xl shadow-md google-map-iframe"
        style="border: 0"
        width="800"
        height="450"
        :allowfullscreen="true"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        crossorigin="anonymous"
        @load="handleMapLoad"
        title="CoNest sur carte"
      >
      </iframe>
    </div>
  </div>
</template>
