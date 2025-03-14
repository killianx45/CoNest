<script setup lang="ts">
import { useRouter } from 'vue-router'
import NavBar from '../NavBar.vue'

const router = useRouter()

const retourAccueil = () => {
  router.push('/')
}
</script>

<template>
  <NavBar />
  <div class="flex flex-col items-center justify-center min-h-screen px-4 py-16 bg-gray-50">
    <div class="w-full max-w-lg p-8 bg-white border-2 border-orange-300 rounded-lg shadow-md">
      <div class="flex flex-col items-center text-center">
        <h1 class="mb-4 text-6xl font-bold text-orange-500">404</h1>
        <h2 class="mb-6 text-2xl font-semibold text-gray-800">Page non trouvée</h2>
        <p class="mb-8 text-gray-600">
          La page que vous recherchez n'existe pas ou a été déplacée.
        </p>
        <button
          @click="retourAccueil"
          class="px-6 py-3 text-white transition-colors bg-orange-500 rounded-md hover:bg-orange-600"
        >
          Retour à l'accueil
        </button>
      </div>
    </div>
  </div>
</template>
