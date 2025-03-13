<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent, onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import type { Commande } from '../../../services/api'
import { deleteCommande, getCommandeById } from '../../../services/api'
import NavBar from '../../NavBar.vue'

export default defineComponent({
  name: 'ShowCommandePage',
  components: { NavBar },
  props: {
    id: {
      type: [String, Number] as PropType<string | number>,
      default: null,
    },
  },
  setup(props) {
    const route = useRoute()
    const router = useRouter()
    const commande = ref<Commande | null>(null)
    const loading = ref(true)
    const error = ref<string | null>(null)
    const deleting = ref(false)

    onMounted(async () => {
      try {
        // Utiliser l'ID des props s'il est disponible, sinon utiliser l'ID de la route
        const commandeId = props.id ? Number(props.id) : Number(route.params.id)
        if (isNaN(commandeId)) {
          throw new Error('ID de commande invalide')
        }
        commande.value = await getCommandeById(commandeId)
      } catch (err) {
        error.value = 'Erreur lors du chargement de la commande'
        console.error(err)
      } finally {
        loading.value = false
      }
    })

    // Fonction pour formater le nom complet du client
    const clientName = (commande: Commande) => {
      return commande.client ? commande.client.name || 'Non spécifié' : 'Non spécifié'
    }

    interface ReservationInfo {
      date: string | null
      heureDebut: string | null
      heureFin: string | null
    }

    const getReservationInfo = (commande: Commande): ReservationInfo => {
      // Si la commande a des produits avec des données pivot, on utilise le premier produit
      if (commande.produits && commande.produits.length > 0 && commande.produits[0].pivot) {
        return {
          date: commande.produits[0].pivot.date_reservation || null,
          heureDebut: commande.produits[0].pivot.heure_debut || null,
          heureFin: commande.produits[0].pivot.heure_fin || null,
        }
      }
      // Sinon on retourne des valeurs nulles
      return {
        date: null,
        heureDebut: null,
        heureFin: null,
      }
    }

    const supprimerCommande = async () => {
      if (!commande.value) return

      if (confirm('Êtes-vous sûr de vouloir supprimer cette commande ?')) {
        try {
          deleting.value = true
          await deleteCommande(commande.value.id)
          router.push('/commandes')
        } catch (err) {
          error.value = 'Erreur lors de la suppression de la commande'
          console.error(err)
        } finally {
          deleting.value = false
        }
      }
    }

    return {
      commande,
      loading,
      error,
      deleting,
      clientName,
      getReservationInfo,
      supprimerCommande,
    }
  },
})
</script>

<template>
  <div>
    <NavBar />
    <div
      class="max-w-4xl p-6 mx-auto mt-20 bg-white border-2 border-orange-300 rounded-lg shadow-md"
    >
      <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">
        Commande #{{ commande?.id }}
      </h1>

      <div v-if="loading" class="py-8 text-center">
        <div
          class="inline-block w-8 h-8 border-t-2 border-b-2 border-orange-500 rounded-full animate-spin"
        ></div>
        <p class="mt-2 text-gray-600">Chargement...</p>
      </div>

      <div v-else-if="error" class="p-4 mb-4 text-red-700 bg-red-100 border-l-4 border-red-500">
        {{ error }}
      </div>

      <div v-else-if="commande" class="grid grid-cols-1 gap-4">
        <div class="p-4 rounded-md bg-orange-50">
          <p class="text-black"><span class="font-semibold">Prix:</span> {{ commande.prix }}€</p>
        </div>

        <div class="p-4 rounded-md bg-orange-50">
          <p class="text-black"><span class="font-semibold">Produits:</span></p>
          <div v-if="commande.produits && commande.produits.length > 0" class="mt-2 ml-4">
            <div v-for="produit in commande.produits" :key="produit.id" class="mb-2">
              <p>{{ produit.nom }}</p>
              <div v-if="produit.pivot" class="ml-4 text-sm text-gray-600">
                <p v-if="produit.pivot.date_reservation">
                  Date: {{ new Date(produit.pivot.date_reservation).toLocaleDateString() }}
                </p>
                <p v-if="produit.pivot.heure_debut">
                  Heure de début: {{ produit.pivot.heure_debut }}
                </p>
                <p v-if="produit.pivot.heure_fin">Heure de fin: {{ produit.pivot.heure_fin }}</p>
              </div>
            </div>
          </div>
          <div v-else class="mt-2 ml-4 text-gray-500">Aucun produit associé</div>
        </div>

        <div class="p-4 rounded-md bg-orange-50">
          <p class="text-black">
            <span class="font-semibold">Utilisateur:</span> {{ clientName(commande) }}
          </p>
        </div>

        <div class="p-4 rounded-md bg-orange-50">
          <p class="text-black">
            <span class="font-semibold">Date de réservation:</span>
            <span v-if="getReservationInfo(commande).date">
              {{ new Date(getReservationInfo(commande).date || '').toLocaleDateString() }}
            </span>
            <span v-else>Non spécifiée</span>
          </p>
        </div>

        <div class="p-4 rounded-md bg-orange-50">
          <p class="text-black">
            <span class="font-semibold">Heure de début:</span>
            <span v-if="getReservationInfo(commande).heureDebut">
              {{ getReservationInfo(commande).heureDebut }}
            </span>
            <span v-else>Non spécifiée</span>
          </p>
        </div>

        <div class="p-4 rounded-md bg-orange-50">
          <p class="text-black">
            <span class="font-semibold">Heure de fin:</span>
            <span v-if="getReservationInfo(commande).heureFin">
              {{ getReservationInfo(commande).heureFin }}
            </span>
            <span v-else>Non spécifiée</span>
          </p>
        </div>
      </div>

      <div v-if="commande" class="flex mt-6 space-x-4">
        <button
          class="px-4 py-2 text-black bg-orange-100 border border-orange-300 rounded hover:bg-orange-200"
          @click="supprimerCommande"
        >
          Supprimer
        </button>
      </div>
    </div>
  </div>
</template>
