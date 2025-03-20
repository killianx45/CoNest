<script setup lang="ts">
import { getConcoursEligibleCount, getConcoursStatus, updateConcoursStatus } from '@/services/api'
import { computed, onMounted, onUnmounted, ref } from 'vue'

const concoursStatus = ref(false)
const userName = ref('')
const message = ref('')
const isLoading = ref(true)
const isUpdating = ref(false)
const error = ref('')
const participantsEligibles = ref(0)

// Timer
const jours = ref(0)
const heures = ref(0)
const minutes = ref(0)
const secondes = ref(0)
let timerInterval: number | null = null

// Calcul de la date de fin du mois
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

onMounted(async () => {
  await checkConcoursStatus()
  await fetchEligibleCount()
  updateTimer()
  timerInterval = window.setInterval(updateTimer, 1000)
})

onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval)
  }
})

async function checkConcoursStatus() {
  isLoading.value = true
  error.value = ''

  try {
    const response = await getConcoursStatus()
    concoursStatus.value = response.concours
    userName.value = response.user
  } catch (err: any) {
    error.value =
      err.response?.data?.message || 'Erreur lors de la récupération du statut du concours'
    console.error('Erreur:', err)
  } finally {
    isLoading.value = false
  }
}

async function updateStatus() {
  isUpdating.value = true
  message.value = ''
  error.value = ''

  try {
    const response = await updateConcoursStatus()
    concoursStatus.value = response.concours
    message.value = response.message || 'Statut mis à jour avec succès'
    await fetchEligibleCount()
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Erreur lors de la mise à jour du statut'
    console.error('Erreur:', err)
  } finally {
    isUpdating.value = false
  }
}
</script>

<template>
  <div class="container px-4 pt-24 mx-auto">
    <h1 class="mb-6 text-3xl font-bold text-center">Concours Mensuel</h1>

    <div v-if="isLoading" class="flex justify-center">
      <div
        class="w-16 h-16 border-4 border-blue-500 rounded-full animate-spin border-t-transparent"
      ></div>
    </div>

    <div v-else class="max-w-2xl mx-auto">
      <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
        <h2 class="mb-4 text-2xl font-semibold text-center">Tirage dans</h2>
        <div class="flex justify-center gap-4 text-center">
          <div class="w-20">
            <div class="text-3xl font-bold">{{ jours }}</div>
            <div class="text-gray-600">Jours</div>
          </div>
          <div class="w-20">
            <div class="text-3xl font-bold">{{ heures }}</div>
            <div class="text-gray-600">Heures</div>
          </div>
          <div class="w-20">
            <div class="text-3xl font-bold">{{ minutes }}</div>
            <div class="text-gray-600">Minutes</div>
          </div>
          <div class="w-20">
            <div class="text-3xl font-bold">{{ secondes }}</div>
            <div class="text-gray-600">Secondes</div>
          </div>
        </div>
        <div class="mt-4 text-center">
          <p class="text-blue-600">
            {{ participantsEligibles }} participant{{
              participantsEligibles !== 1 ? 's' : ''
            }}
            éligible{{ participantsEligibles !== 1 ? 's' : '' }} ce mois-ci
          </p>
        </div>
      </div>

      <div class="p-6 mb-6 bg-white rounded-lg shadow-md">
        <h2 class="mb-4 text-2xl font-semibold">Bonjour {{ userName }}</h2>

        <div v-if="concoursStatus" class="p-4 mb-4 bg-green-100 border-l-4 border-green-500">
          <h3 class="text-xl font-medium text-green-700">Félicitations!</h3>
          <p class="mt-2 text-green-700">
            Vous êtes éligible au concours de ce mois! Vous avez réservé plus de 12 heures d'espaces
            de coworking.
          </p>
          <p class="mt-4 text-green-700">
            Restez à l'écoute pour plus d'informations sur les prix et comment participer au tirage
            au sort.
          </p>
        </div>

        <div v-else class="p-4 mb-4 bg-yellow-100 border-l-4 border-yellow-500">
          <h3 class="text-xl font-medium text-yellow-700">Pas encore éligible</h3>
          <p class="mt-2 text-yellow-700">
            Vous n'êtes pas encore éligible au concours de ce mois. Pour participer, vous devez
            réserver au moins 12 heures d'espaces de coworking pendant le mois en cours.
          </p>
          <p class="mt-4 text-yellow-700">
            Continuez à réserver des espaces pour atteindre le seuil requis!
          </p>
        </div>

        <div v-if="message" class="p-4 mb-4 text-blue-700 bg-blue-100 rounded-lg">
          {{ message }}
        </div>

        <div v-if="error" class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg">
          {{ error }}
        </div>

        <button
          @click="updateStatus"
          class="w-full px-4 py-2 mt-4 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
          :disabled="isUpdating"
        >
          {{ isUpdating ? 'Mise à jour en cours...' : 'Mettre à jour mon statut' }}
        </button>
      </div>

      <div class="p-6 bg-white rounded-lg shadow-md">
        <h2 class="mb-4 text-2xl font-semibold">À propos du concours</h2>
        <p class="mb-3">
          Chaque mois, nous organisons un concours exclusif pour nos utilisateurs les plus actifs.
        </p>
        <p class="mb-3">
          Pour être éligible, vous devez réserver au moins 12 heures d'espaces de coworking pendant
          le mois en cours.
        </p>
        <p class="mb-3">
          Les gagnants seront tirés au sort parmi tous les participants éligibles à la fin du mois.
        </p>
        <p>
          Les prix varient chaque mois, mais peuvent inclure des heures gratuites, des réductions ou
          des cadeaux exclusifs.
        </p>
      </div>
    </div>
  </div>
</template>
