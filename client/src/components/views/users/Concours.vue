<script setup lang="ts">
import type { Produit } from '@/services/api'
import {
  getAllProduits,
  getConcoursEligibleCount,
  getConcoursStatus,
  updateConcoursStatus,
} from '@/services/api'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import ConfettiExplosion from 'vue-confetti-explosion'

const concoursStatus = ref(false)
const userName = ref('')
const message = ref('')
const isLoading = ref(true)
const isUpdating = ref(false)
const error = ref('')
const participantsEligibles = ref(0)
const jours = ref(0)
const heures = ref(0)
const minutes = ref(0)
const secondes = ref(0)
let timerInterval: number | null = null
const produits = ref<Produit[]>([])
const showConfetti = ref(false)
const buttonRef = ref<HTMLElement | null>(null)
const stageHeight = ref(window.innerHeight)
const stageWidth = ref(window.innerWidth)

const dateFin = computed(() => {
  const now = new Date()
  const dernierJour = new Date(now.getFullYear(), now.getMonth() + 1, 0)
  dernierJour.setHours(23, 59, 59, 999)
  return dernierJour
})

function updateTimer() {
  const now = new Date()
  const diff = dateFin.value.getTime() - now.getTime()

  if (diff <= 0) {
    jours.value = 0
    heures.value = 0
    minutes.value = 0
    secondes.value = 0

    if (timerInterval) {
      clearInterval(timerInterval)
      timerInterval = null
    }
    return
  }

  jours.value = Math.floor(diff / (1000 * 60 * 60 * 24))
  heures.value = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
  minutes.value = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
  secondes.value = Math.floor((diff % (1000 * 60)) / 1000)
}

async function fetchEligibleCount() {
  try {
    participantsEligibles.value = await getConcoursEligibleCount()
  } catch (err) {
    console.error('Erreur lors de la récupération du nombre de participants éligibles:', err)
  }
}

async function fetchProduits() {
  const allProduits = await getAllProduits()
  produits.value = allProduits.sort(() => 0.5 - Math.random()).slice(0, 4)
}

onMounted(async () => {
  await checkConcoursStatus()
  await fetchEligibleCount()
  updateTimer()
  timerInterval = window.setInterval(updateTimer, 1000)
  fetchProduits()
  window.addEventListener('resize', updateStageDimensions)
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
  window.removeEventListener('resize', updateStageDimensions)
})

function updateStageDimensions() {
  stageHeight.value = window.innerHeight
  stageWidth.value = window.innerWidth
}

const confettiProps = {
  particleCount: 150,
  particleSize: 8,
  particleSizeVariation: 3,
  force: 0.6,
  colors: ['#FFD700', '#FF6347', '#4169E1', '#32CD32', '#9932CC', '#FF1493'],
  estimatedDuration: 5000,
}

function triggerConfetti() {
  if (concoursStatus.value) {
    showConfetti.value = true
    setTimeout(() => {
      showConfetti.value = false
    }, confettiProps.estimatedDuration)
  }
}

async function updateStatus() {
  isUpdating.value = true
  message.value = ''
  error.value = ''
  const prevStatus = concoursStatus.value

  try {
    const response = await updateConcoursStatus()
    concoursStatus.value = response.concours
    message.value = response.message || 'Statut mis à jour avec succès'
    await fetchEligibleCount()
    triggerConfetti()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la mise à jour du statut'
    console.error('Erreur:', err)
  } finally {
    isUpdating.value = false
  }
}

async function checkConcoursStatus() {
  isLoading.value = true
  error.value = ''

  try {
    const response = await getConcoursStatus()
    concoursStatus.value = response.concours
    userName.value = response.user
    setTimeout(() => {
      triggerConfetti()
    }, 500)
  } catch (err: any) {
    error.value =
      err.response?.data?.message || 'Erreur lors de la récupération du statut du concours'
    console.error('Erreur:', err)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen pt-24 bg-[#FFF1E9]">
    <div class="container px-4 mx-auto">
      <div class="p-6 mb-6 text-center bg-orange-100 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-blue-950">Tirage dans</h2>
        <div class="flex justify-center gap-4 mt-4">
          <div class="text-3xl font-bold text-blue-950">{{ jours }} Jours</div>
          <div class="text-3xl font-bold text-blue-950">{{ heures }} Heures</div>
          <div class="text-3xl font-bold text-blue-950">{{ minutes }} Minutes</div>
          <div class="text-3xl font-bold text-blue-950">{{ secondes }} Secondes</div>
        </div>
        <p class="mt-4 font-medium text-blue-800">
          {{ participantsEligibles }} participant{{
            participantsEligibles !== 1 ? 's' : ''
          }}
          éligible{{ participantsEligibles !== 1 ? 's' : '' }} ce mois-ci
        </p>
      </div>

      <div class="flex flex-col gap-6 md:flex-row">
        <div class="w-full p-6 bg-orange-100 rounded-lg shadow-md md:w-1/2">
          <h2 class="mb-4 text-2xl font-semibold text-blue-950">Bonjour {{ userName }}</h2>
          <div
            class="p-4 mb-4 transition-colors duration-500"
            :class="
              concoursStatus
                ? 'bg-green-100 border-l-4 border-green-500'
                : 'bg-yellow-100 border-l-4 border-yellow-500'
            "
          >
            <h3
              class="text-xl font-medium transition-colors duration-500"
              :class="concoursStatus ? 'text-green-700' : 'text-yellow-700'"
            >
              {{ concoursStatus ? 'Félicitations! 🎉' : 'Pas encore éligible 😢' }}
            </h3>
            <p
              class="mt-2 transition-colors duration-500"
              :class="concoursStatus ? 'text-green-700' : 'text-yellow-700'"
            >
              {{
                concoursStatus
                  ? 'Vous êtes éligible au concours de ce mois!'
                  : "Vous n'êtes pas encore éligible au concours de ce mois."
              }}
            </p>
          </div>

          <div class="relative">
            <button
              ref="buttonRef"
              @click="updateStatus"
              class="w-full px-6 py-3 mt-6 text-white transition-colors duration-300 bg-orange-500 rounded-lg hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 disabled:opacity-50"
              :disabled="isUpdating"
            >
              {{ isUpdating ? 'Mise à jour en cours...' : 'Mettre à jour mon statut' }}
            </button>

            <div v-if="showConfetti" class="absolute top-0 left-0 w-full">
              <ConfettiExplosion
                :particleCount="confettiProps.particleCount"
                :particleSize="confettiProps.particleSize"
                :particleSizeVariation="confettiProps.particleSizeVariation"
                :force="confettiProps.force"
                :colors="confettiProps.colors"
                :stageHeight="stageHeight"
                :stageWidth="stageWidth"
                class="absolute top-0 transform -translate-x-1/2 left-1/2"
              />
            </div>
          </div>

          <div
            v-if="message"
            class="p-4 mt-6 mb-4 text-blue-700 transition-all duration-300 bg-blue-100 rounded-lg"
          >
            {{ message }}
          </div>
          <div
            v-if="error"
            class="p-4 mt-6 mb-4 text-red-700 transition-all duration-300 bg-red-100 rounded-lg"
          >
            {{ error }}
          </div>
        </div>

        <div class="w-full p-6 bg-orange-100 rounded-lg shadow-md md:w-1/2">
          <h2 class="mb-4 text-2xl font-semibold text-blue-950">À propos du concours</h2>
          <p class="mb-3 text-blue-950">
            Chaque mois, nous organisons un concours exclusif pour nos utilisateurs les plus actifs.
          </p>
          <p class="mb-3 text-blue-950">
            Pour être éligible, vous devez réserver au moins 12 heures d'espaces de coworking
            pendant le mois en cours.
          </p>
          <p class="mb-3 text-blue-950">
            Les gagnants seront tirés au sort parmi tous les participants éligibles à la fin du
            mois.
          </p>
          <p class="text-blue-950">
            Les prix varient chaque mois, mais peuvent inclure des heures gratuites, des réductions
            ou des cadeaux exclusifs.
          </p>
        </div>
      </div>

      <div class="mt-10">
        <h2 class="mb-6 text-2xl font-semibold text-blue-950">Produits phares</h2>
        <p class="mb-6 text-blue-950">
          Voici les produits phares du mois. Vous pouvez tenter de gagner l'un d'eux en réservant 12
          heures d'espaces de coworking pendant le mois en cours.
        </p>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
          <div
            v-for="produit in produits"
            :key="produit.id"
            class="p-6 transition-transform bg-orange-100 rounded-lg shadow-md hover:scale-105"
          >
            <h3 class="font-bold text-blue-950">{{ produit.nom }}</h3>
            <p class="my-2 text-blue-900">{{ produit.description }}</p>
            <p class="text-lg font-semibold text-orange-600">{{ produit.prix }} €/h</p>
            <img
              v-if="produit.images && produit.images.length > 0"
              :src="`http://localhost:8000/${produit.images[0]}`"
              alt="Image du produit"
              class="object-cover w-full h-40 mt-4 rounded-lg"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
