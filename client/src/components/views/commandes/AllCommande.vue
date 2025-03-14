<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import type { Commande } from '../../../services/api'
import { getAllCommandes } from '../../../services/api'
import NavBar from '../../NavBar.vue'

const router = useRouter()
const commandes = ref<Commande[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    commandes.value = await getAllCommandes()
  } catch (err) {
    error.value = 'Erreur lors du chargement des commandes'
    console.error(err)
  } finally {
    loading.value = false
  }
})

const voirCommande = (id: number) => {
  router.push(`/commandes/${id}`)
}

const formatDate = (commande: Commande) => {
  if (
    !commande.produits ||
    commande.produits.length === 0 ||
    !commande.produits[0].pivot?.date_reservation
  ) {
    return '-'
  }
  return new Date(commande.produits[0].pivot.date_reservation).toLocaleDateString()
}

const getHoraires = (commande: Commande) => {
  if (!commande.produits || commande.produits.length === 0 || !commande.produits[0].pivot) {
    return { debut: null, fin: null }
  }

  const pivot = commande.produits[0].pivot
  return {
    debut: pivot.heure_debut || null,
    fin: pivot.heure_fin || null,
  }
}

const getEspaceName = (commande: Commande) => {
  if (!commande.produits || commande.produits.length === 0) {
    return '-'
  }
  return commande.produits[0].nom || '-'
}
</script>

<template>
  <NavBar />
  <div class="max-w-6xl p-6 mx-auto mt-20 bg-white border-2 border-orange-300 rounded-lg shadow-md">
    <div class="flex items-center justify-between pb-2 mb-6 border-b-2 border-orange-200">
      <h1 class="text-3xl font-bold text-black">Mes réservations</h1>
      <router-link
        to="/commandes/create"
        class="px-4 py-2 text-white bg-orange-500 rounded-md hover:bg-orange-600"
      >
        Nouvelle réservation
      </router-link>
    </div>
    <div v-if="loading" class="py-8 text-center">
      <div
        class="inline-block w-8 h-8 border-t-2 border-b-2 border-orange-500 rounded-full animate-spin"
      ></div>
      <p class="mt-2 text-gray-600">Chargement des réservations...</p>
    </div>
    <div v-else-if="error" class="p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500">
      {{ error }}
    </div>
    <div v-else-if="commandes.length === 0" class="py-8 text-center">
      <p class="text-gray-600">Vous n'avez pas encore de réservations.</p>
      <router-link
        to="/commandes/create"
        class="inline-block px-4 py-2 mt-4 text-white bg-orange-500 rounded-md hover:bg-orange-600"
      >
        Créer votre première réservation
      </router-link>
    </div>
    <div v-else class="overflow-x-auto">
      <table class="min-w-full bg-white">
        <thead class="bg-orange-50">
          <tr>
            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-700">ID</th>
            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-700">Espace</th>
            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-700">Date</th>
            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-700">Horaires</th>
            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-700">Prix</th>
            <th class="px-4 py-3 text-sm font-semibold text-left text-gray-700">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="commande in commandes" :key="commande.id" class="hover:bg-orange-50">
            <td class="px-4 py-3 text-sm text-gray-700">#{{ commande.id }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">
              <div v-if="commande.produits && commande.produits.length > 0">
                {{ getEspaceName(commande) }}
                <span v-if="commande.produits.length > 1" class="text-xs text-gray-500">
                  +{{ commande.produits.length - 1 }} autres
                </span>
              </div>
              <div v-else class="text-gray-500">-</div>
            </td>
            <td class="px-4 py-3 text-sm text-gray-700">
              {{ formatDate(commande) }}
            </td>
            <td class="px-4 py-3 text-sm text-gray-700">
              <span v-if="getHoraires(commande).debut && getHoraires(commande).fin">
                {{ getHoraires(commande).debut }} - {{ getHoraires(commande).fin }}
              </span>
              <span v-else class="text-gray-500">-</span>
            </td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ commande.prix }}€</td>
            <td class="px-4 py-3 text-sm">
              <button
                @click="voirCommande(commande.id)"
                class="px-3 py-1 mr-2 text-sm text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200"
              >
                Voir
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
