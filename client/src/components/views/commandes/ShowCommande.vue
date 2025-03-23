<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Commande } from '../../../services/api'
import { deleteCommande, getCommandeById } from '../../../services/api'
import NavBar from '../../NavBar.vue'

const route = useRoute()
const router = useRouter()
const commande = ref<Commande | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const deleting = ref(false)
const commandeId = Number(route.params.id)

onMounted(async () => {
  if (isNaN(commandeId)) {
    error.value = 'ID de commande invalide'
    loading.value = false
    return
  }

  try {
    commande.value = await getCommandeById(commandeId)
  } catch (err) {
    error.value = 'Erreur lors du chargement de la commande'
    console.error(err)
  } finally {
    loading.value = false
  }
})

const clientName = () => {
  if (!commande.value || !commande.value.client) return '-'
  return commande.value.client.name || '-'
}

const getReservationInfo = () => {
  if (!commande.value || !commande.value.produits || commande.value.produits.length === 0) {
    return { date: '-', debut: '-', fin: '-' }
  }

  const produit = commande.value.produits[0]
  if (!produit.pivot) {
    return { date: '-', debut: '-', fin: '-' }
  }

  const date = produit.pivot.date_reservation
    ? new Date(produit.pivot.date_reservation).toLocaleDateString()
    : '-'
  const debut = produit.pivot.heure_debut || '-'
  const fin = produit.pivot.heure_fin || '-'

  return { date, debut, fin }
}

const supprimerCommande = async () => {
  if (!commande.value) return

  if (!confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')) {
    return
  }

  deleting.value = true
  try {
    await deleteCommande(commande.value.id)
    router.push('/commandes')
  } catch (err) {
    error.value = 'Erreur lors de la suppression de la commande'
    console.error(err)
    deleting.value = false
  }
}

const retourListe = () => {
  router.push('/commandes')
}
</script>

<template>
  <NavBar />
  <div class="flex items-center justify-center min-h-screen bg-[#FFF1E9]">
    <fieldset
      class="fieldset w-full max-w-4xl bg-[#FDF9F6] border border-base-200 p-8 md:p-12 rounded-box"
    >
      <div
        class="flex flex-col pb-4 mb-6 border-b border-gray-200 md:flex-row md:items-center md:justify-between"
      >
        <h1 class="mb-4 text-2xl font-semibold text-center md:mb-0 md:text-left">
          Détails de la réservation
        </h1>
        <button
          @click="retourListe"
          class="px-4 py-2 font-medium text-[#FF8238] bg-white border border-[#FF8238] rounded-lg hover:bg-[#FFF1E9] focus:outline-none focus:ring-2 focus:ring-[#FF8238]"
        >
          Retour
        </button>
      </div>

      <div v-if="loading" class="py-8 text-center">
        <div
          class="inline-block w-8 h-8 border-b-2 border-[#FF8238] rounded-full animate-spin"
        ></div>
        <p class="mt-2 text-gray-600">Chargement des détails...</p>
      </div>

      <div v-else-if="error" class="p-4 mb-4 text-red-700 bg-red-100 rounded-lg">
        {{ error }}
      </div>

      <div v-else-if="commande" class="space-y-6">
        <div class="p-6 rounded-lg bg-[#FFF9F4]">
          <h2 class="mb-4 text-xl font-semibold text-gray-800">Informations générales</h2>
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <p class="text-sm font-medium text-gray-500">Numéro de réservation</p>
              <p class="text-gray-800">#{{ commande.id }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Client</p>
              <p class="text-gray-800">{{ clientName() }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Date de réservation</p>
              <p class="text-gray-800">{{ getReservationInfo().date }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Horaires</p>
              <p class="text-gray-800">
                {{ getReservationInfo().debut }} - {{ getReservationInfo().fin }}
              </p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Prix total</p>
              <p class="text-lg font-semibold text-[#FF8238]">{{ commande.prix }}€</p>
            </div>
          </div>
        </div>

        <div class="p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
          <h2 class="mb-4 text-xl font-semibold text-gray-800">Espaces réservés</h2>
          <div v-if="commande.produits && commande.produits.length > 0">
            <div
              v-for="produit in commande.produits"
              :key="produit.id"
              class="p-4 mb-3 border border-gray-200 rounded-lg bg-gray-50"
            >
              <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                <div>
                  <h3 class="text-lg font-medium text-gray-800">{{ produit.nom }}</h3>
                  <p class="mt-1 text-sm text-gray-600">{{ produit.description }}</p>
                </div>
                <div class="mt-2 md:mt-0 md:text-right">
                  <p class="text-sm text-gray-500">Prix unitaire</p>
                  <p class="font-medium text-gray-800">{{ produit.prix }}€</p>
                </div>
              </div>
            </div>
          </div>
          <div v-else class="p-4 text-center text-gray-500">
            Aucun espace associé à cette réservation
          </div>
        </div>

        <div class="flex justify-end pt-4 mt-6 border-t border-gray-200">
          <button
            @click="supprimerCommande"
            :disabled="deleting"
            class="px-6 py-3 font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="deleting">Suppression en cours...</span>
            <span v-else>Supprimer cette réservation</span>
          </button>
        </div>
      </div>

      <div v-else class="py-8 text-center">
        <p class="text-gray-600">Cette réservation n'existe pas ou a été supprimée.</p>
        <button
          @click="retourListe"
          class="px-6 py-3 mt-4 font-medium text-[#FF8238] bg-white border border-[#FF8238] rounded-lg hover:bg-[#FFF1E9] focus:outline-none focus:ring-2 focus:ring-[#FF8238]"
        >
          Retour à la liste
        </button>
      </div>
    </fieldset>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

* {
  font-family: 'Poppins', sans-serif;
}
</style>
