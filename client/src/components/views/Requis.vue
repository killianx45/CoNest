<script setup lang="ts">
import { ref } from 'vue'

interface Section {
  id: number
  titre: string
  contenu: string
  isOpen: boolean
}

const sections = ref<Section[]>([
  {
    id: 1,
    titre: 'Documents requis pour les réservations',
    contenu:
      "Pour effectuer une réservation, vous devez fournir une pièce d'identité valide et accepter les conditions générales d'utilisation. Pour certains espaces premium, une caution peut être demandée via carte bancaire (non débitée).",
    isOpen: true,
  },
  {
    id: 2,
    titre: 'Informations nécessaires pour les loueurs',
    contenu:
      "Si vous souhaitez mettre en location votre espace, vous devrez fournir : une pièce d'identité, un justificatif de domicile de moins de 3 mois, des photos de qualité de l'espace, une description détaillée incluant les équipements disponibles, et vos coordonnées bancaires pour recevoir vos paiements.",
    isOpen: false,
  },
  {
    id: 3,
    titre: 'Modalités de réservation',
    contenu:
      "Les réservations sont confirmées immédiatement après paiement. Vous recevrez un email de confirmation avec les détails de votre réservation. L'accès à l'espace se fait selon les instructions du propriétaire (code, clé, badge) qui vous seront communiquées avant votre arrivée.",
    isOpen: false,
  },
  {
    id: 4,
    titre: "Politique d'annulation",
    contenu:
      "Annulation gratuite jusqu'à 48h avant le début de la réservation avec remboursement à 100%. Entre 48h et 24h, remboursement à 50%. Moins de 24h avant ou non-présentation, aucun remboursement. Les cas exceptionnels (maladie avec certificat médical) seront étudiés individuellement.",
    isOpen: false,
  },
  {
    id: 5,
    titre: "Règles d'utilisation des espaces",
    contenu:
      "Respectez les horaires réservés. Laissez l'espace propre et rangé après utilisation. Les dégradations constatées seront facturées. L'accès est strictement réservé aux personnes mentionnées lors de la réservation. Le non-respect de ces règles peut entraîner l'annulation des réservations futures.",
    isOpen: false,
  },
])

function toggleSection(id: number) {
  const index = sections.value.findIndex((s) => s.id === id)
  if (index !== -1) {
    sections.value[index].isOpen = !sections.value[index].isOpen
  }
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-[#FFF1E9]">
    <fieldset
      class="fieldset w-full max-w-3xl mx-auto bg-[#FDF9F6] border border-base-200 p-8 md:p-16 my-10 rounded-xl"
    >
      <img class="mx-auto mb-8 logoconest h-14" src="@/assets/logo_CoNest.svg" alt="Logo" />

      <h1 class="mb-8 text-2xl font-semibold text-center">Informations et Conditions Requises</h1>

      <div class="space-y-4">
        <div
          v-for="section in sections"
          :key="section.id"
          class="overflow-hidden bg-white shadow-sm rounded-xl"
        >
          <div
            class="flex items-center justify-between p-4 cursor-pointer hover:bg-gray-50"
            @click="toggleSection(section.id)"
          >
            <h3 class="font-semibold text-[#FF8238]">{{ section.titre }}</h3>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5 transition-transform duration-300 text-[#FF8238]"
              :class="{ 'rotate-180': section.isOpen }"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div v-show="section.isOpen" class="p-4 text-gray-700 border-t bg-white/80">
            <p>{{ section.contenu }}</p>
          </div>
        </div>
      </div>

      <div class="mt-10 text-center">
        <router-link
          to="/"
          class="px-6 py-3 font-medium text-white bg-[#FF8238] rounded-xl hover:bg-[#e67530] focus:outline-none"
        >
          Retour à l'accueil
        </router-link>
      </div>
    </fieldset>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

* {
  font-family: 'Poppins', sans-serif;
}
</style>
