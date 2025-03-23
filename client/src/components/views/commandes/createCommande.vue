<script setup lang="ts">
import { onMounted, reactive, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  createCommande,
  getAllProduits,
  isAuthenticated,
  verifierDisponibilite,
  type CommandeCreateData,
  type Produit,
} from '../../../services/api'
import NavBar from '../../NavBar.vue'

const router = useRouter()
const route = useRoute()
const produits = ref<Produit[]>([])
const loading = ref(false)
const error = ref('')
const success = ref(false)
const checkingAvailability = ref(false)

interface ProduitError {
  id: boolean
  date: boolean
  heure_debut: boolean
  heure_fin: boolean
  disponibilite: boolean
}

const form = reactive<{
  produits: Array<{ id: number; date: string; heure_debut: string; heure_fin: string }>
}>({
  produits: [{ id: 0, date: '', heure_debut: '', heure_fin: '' }],
})

const errors = reactive<{ produits: ProduitError[]; general: string }>({
  produits: [
    { id: false, date: false, heure_debut: false, heure_fin: false, disponibilite: false },
  ],
  general: '',
})

const fetchProduits = async () => {
  try {
    loading.value = true
    produits.value = await getAllProduits()
  } catch (err) {
    error.value = 'Erreur lors du chargement des espaces de coworking. Veuillez réessayer.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const addProduit = () => {
  form.produits.push({ id: 0, date: '', heure_debut: '', heure_fin: '' })
  errors.produits.push({
    id: false,
    date: false,
    heure_debut: false,
    heure_fin: false,
    disponibilite: false,
  })
}

const removeProduit = (index: number) => {
  if (form.produits.length > 1) {
    form.produits.splice(index, 1)
    errors.produits.splice(index, 1)
  }
}

const checkAvailability = async (index: number) => {
  const produit = form.produits[index]
  if (produit.id && produit.date && produit.heure_debut && produit.heure_fin) {
    try {
      checkingAvailability.value = true
      errors.produits[index].disponibilite = false

      const disponible = await verifierDisponibilite(
        Number(produit.id),
        produit.date,
        produit.heure_debut,
        produit.heure_fin,
      )

      if (!disponible) {
        errors.produits[index].disponibilite = true
      }
    } catch (err) {
      console.error('Erreur lors de la vérification de disponibilité:', err)
    } finally {
      checkingAvailability.value = false
    }
  }
}

watch(
  () => [...form.produits],
  (newProduits) => {
    newProduits.forEach((_, index) => {
      const produit = form.produits[index]
      if (produit.id && produit.date && produit.heure_debut && produit.heure_fin) {
        errors.produits[index].disponibilite = false
      }
    })
  },
  { deep: true },
)

const validateForm = (): boolean => {
  let isValid = true
  errors.general = ''

  form.produits.forEach((produit, index) => {
    errors.produits[index].id = produit.id === 0
    errors.produits[index].date = !produit.date
    errors.produits[index].heure_debut = !produit.heure_debut
    errors.produits[index].heure_fin = !produit.heure_fin
    if (produit.heure_debut && produit.heure_fin && produit.heure_debut >= produit.heure_fin) {
      errors.produits[index].heure_fin = true
      errors.general = "L'heure de fin doit être après l'heure de début"
      isValid = false
    }

    if (
      errors.produits[index].id ||
      errors.produits[index].date ||
      errors.produits[index].heure_debut ||
      errors.produits[index].heure_fin ||
      errors.produits[index].disponibilite
    ) {
      isValid = false
    }
  })

  return isValid
}

const checkAllAvailabilities = async (): Promise<boolean> => {
  let allAvailable = true
  checkingAvailability.value = true

  for (let i = 0; i < form.produits.length; i++) {
    const produit = form.produits[i]
    if (produit.id && produit.date && produit.heure_debut && produit.heure_fin) {
      try {
        const disponible = await verifierDisponibilite(
          Number(produit.id),
          produit.date,
          produit.heure_debut,
          produit.heure_fin,
        )

        if (!disponible) {
          errors.produits[i].disponibilite = true
          allAvailable = false
        }
      } catch (err) {
        console.error('Erreur lors de la vérification de disponibilité:', err)
        allAvailable = false
      }
    }
  }

  checkingAvailability.value = false
  return allAvailable
}

const submitForm = async () => {
  if (!validateForm()) {
    return
  }

  const allAvailable = await checkAllAvailabilities()
  if (!allAvailable) {
    errors.general = 'Un ou plusieurs créneaux sélectionnés ne sont pas disponibles.'
    return
  }

  try {
    loading.value = true
    const commandeData: CommandeCreateData = {
      produits: form.produits.map((p) => Number(p.id)),
      dates: form.produits.map((p) => p.date),
      heures_debut: form.produits.map((p) => p.heure_debut),
      heures_fin: form.produits.map((p) => p.heure_fin),
    }

    await createCommande(commandeData)
    success.value = true

    setTimeout(() => {
      router.push('/commandes')
    }, 2000)
  } catch (err: any) {
    if (err.response && err.response.data && err.response.data.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Une erreur est survenue lors de la création de la réservation.'
    }
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  if (!isAuthenticated()) {
    router.push({
      path: '/login',
      query: { redirect: route.fullPath },
    })
    return
  }

  fetchProduits().then(() => {
    const produitId = route.query.produitId
    if (produitId) {
      form.produits[0].id = Number(produitId)
    }
  })
})
</script>

<template>
  <NavBar />
  <div class="max-w-6xl p-6 mx-auto mt-20 bg-white border-2 border-orange-300 rounded-lg shadow-md">
    <h1 class="mb-6 text-3xl font-bold text-black">Créer une réservation</h1>
    <div v-if="loading || checkingAvailability" class="flex justify-center my-8">
      <div class="w-12 h-12 border-b-2 border-orange-500 rounded-full animate-spin"></div>
      <p v-if="checkingAvailability" class="ml-3 text-gray-600">
        Vérification de la disponibilité...
      </p>
    </div>
    <div
      v-else-if="success"
      class="px-4 py-3 mb-4 text-green-700 bg-green-100 border border-green-400 rounded"
    >
      <p>Réservation créée avec succès ! Redirection en cours...</p>
    </div>
    <div v-else>
      <div
        v-if="error"
        class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded"
      >
        <p>{{ error }}</p>
      </div>

      <div
        v-if="errors.general"
        class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded"
      >
        <p>{{ errors.general }}</p>
      </div>
      <form @submit.prevent="submitForm" class="space-y-6">
        <div
          v-for="(produit, index) in form.produits"
          :key="index"
          class="p-4 border border-gray-200 rounded-lg"
        >
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Espace {{ index + 1 }}</h3>
            <button
              v-if="form.produits.length > 1"
              type="button"
              @click="removeProduit(index)"
              class="text-red-500 hover:text-red-700"
            >
              Supprimer
            </button>
          </div>

          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700"
                >Espace de coworking</label
              >
              <select
                v-model="form.produits[index].id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.produits[index].id }"
              >
                <option :value="0" disabled>Sélectionnez un espace</option>
                <option v-for="p in produits" :key="p.id" :value="p.id">
                  {{ p.nom }} - {{ p.prix }}€/h
                </option>
              </select>
              <p v-if="errors.produits[index].id" class="mt-1 text-sm text-red-600">
                Veuillez sélectionner un espace
              </p>
            </div>

            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700"
                >Date de réservation</label
              >
              <input
                type="date"
                v-model="form.produits[index].date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.produits[index].date }"
                :min="new Date().toISOString().split('T')[0]"
              />
              <p v-if="errors.produits[index].date" class="mt-1 text-sm text-red-600">
                Veuillez sélectionner une date
              </p>
            </div>

            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">Heure de début</label>
              <input
                type="time"
                v-model="form.produits[index].heure_debut"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.produits[index].heure_debut }"
                min="08:00"
                max="20:00"
                @change="checkAvailability(index)"
              />
              <p v-if="errors.produits[index].heure_debut" class="mt-1 text-sm text-red-600">
                Veuillez sélectionner une heure de début
              </p>
            </div>

            <div>
              <label class="block mb-1 text-sm font-medium text-gray-700">Heure de fin</label>
              <input
                type="time"
                v-model="form.produits[index].heure_fin"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-orange-500 focus:border-orange-500"
                :class="{ 'border-red-500': errors.produits[index].heure_fin }"
                min="08:00"
                max="20:00"
                @change="checkAvailability(index)"
              />
              <p v-if="errors.produits[index].heure_fin" class="mt-1 text-sm text-red-600">
                Veuillez sélectionner une heure de fin
              </p>
            </div>
          </div>

          <div
            v-if="errors.produits[index].disponibilite"
            class="px-4 py-2 mt-3 text-red-600 border border-red-200 rounded bg-red-50"
          >
            <p>Ce créneau n'est pas disponible. Veuillez choisir un autre créneau.</p>
          </div>
        </div>

        <div class="flex justify-center">
          <button
            type="button"
            @click="addProduit"
            class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            + Ajouter un espace
          </button>
        </div>

        <div class="flex justify-between">
          <router-link
            to="/commandes"
            class="px-6 py-3 text-white bg-red-500 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            Retour à la liste des réservations
          </router-link>
          <button
            type="submit"
            class="px-6 py-3 text-white bg-orange-500 rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500"
            :disabled="loading || checkingAvailability"
          >
            {{ loading ? 'Création en cours...' : 'Créer la réservation' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
