<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { getAllCommandes, isAuthenticated, type Commande } from '../../../services/api'
import NavBar from '../../NavBar.vue'

const router = useRouter()
const commandes = ref<Commande[]>([])
const loading = ref(false)
const error = ref('')

const fetchCommandes = async () => {
  try {
    loading.value = true
    commandes.value = await getAllCommandes()
  } catch (err) {
    error.value = 'Une erreur est survenue lors du chargement des réservations.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const formatCommandeDate = (commande: Commande) => {
  if (
    !commande.produits ||
    commande.produits.length === 0 ||
    !commande.produits[0].pivot?.date_reservation
  ) {
    return '-'
  }
  return formatDate(commande.produits[0].pivot.date_reservation)
}

onMounted(() => {
  if (!isAuthenticated()) {
    router.push({
      path: '/login',
      query: { redirect: router.currentRoute.value.fullPath },
    })
    return
  }

  fetchCommandes()
})
</script>

<template>
  <NavBar />
  <div class="min-h-screen bg-[#FFF1E9] py-10">
    <div class="max-w-6xl px-4 mx-auto">
      <fieldset class="fieldset bg-[#FDF9F6] border border-base-200 p-8 md:p-12 rounded-box">
        <div class="flex items-center justify-between mb-8">
          <h1 class="text-3xl font-semibold text-gray-800">Mes réservations</h1>
          <router-link
            to="/commandes/create"
            class="px-6 py-3 text-white bg-[#FF8238] rounded-lg hover:bg-[#f87625] transition-colors duration-200 flex items-center"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5 mr-2"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                clip-rule="evenodd"
              />
            </svg>
            Nouvelle réservation
          </router-link>
        </div>

        <div v-if="loading" class="flex items-center justify-center my-16">
          <div class="w-16 h-16 border-b-4 border-[#FF8238] rounded-full animate-spin"></div>
          <p class="ml-4 text-lg text-gray-600">Chargement des réservations...</p>
        </div>

        <div
          v-else-if="error"
          class="p-4 my-8 text-red-700 bg-red-100 border border-red-400 rounded-lg"
        >
          <p>{{ error }}</p>
        </div>

        <div
          v-else-if="commandes.length === 0"
          class="p-8 text-center bg-white rounded-lg shadow-sm"
        >
          <p class="text-lg text-gray-600">Vous n'avez pas encore de réservations.</p>
          <router-link
            to="/commandes/create"
            class="inline-block px-6 py-3 mt-4 text-white bg-[#FF8238] rounded-lg hover:bg-[#f87625] transition-colors duration-200"
          >
            Créer votre première réservation
          </router-link>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full bg-white rounded-lg shadow-sm">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-4 text-sm font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Date de réservation
                </th>
                <th
                  class="px-6 py-4 text-sm font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Nombre de produits
                </th>
                <th
                  class="px-6 py-4 text-sm font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Client
                </th>
                <th
                  class="px-6 py-4 text-sm font-medium tracking-wider text-left text-gray-500 uppercase"
                >
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="commande in commandes" :key="commande.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                  {{ formatCommandeDate(commande) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                  {{ commande.produits?.length || 0 }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                  {{ commande.client?.name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                  <router-link
                    :to="`/commandes/${commande.id}`"
                    class="inline-flex items-center justify-center px-4 py-2 mr-2 text-sm font-medium text-blue-600 transition-colors duration-200 bg-blue-100 rounded-md hover:bg-blue-200"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-4 h-4 mr-1"
                      viewBox="0 0 20 20"
                      fill="currentColor"
                    >
                      <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                      <path
                        fill-rule="evenodd"
                        d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    Voir
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </fieldset>
    </div>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');

* {
  font-family: 'Poppins', sans-serif;
}
</style>
