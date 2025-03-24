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
  <div class="bg-[#FFF1E9] py-20 px-4">
    <fieldset
      class="relative w-full max-w-6xl mx-auto bg-[#FF8238] p-8 md:p-10 rounded-[3rem] shadow-lg"
    >
      <div class="flex flex-col items-start justify-between md:flex-row">
        <h2 class="mb-8 text-2xl font-semibold text-white md:text-3xl lg:text-4xl">
          Vos idées, vos espaces
        </h2>
      </div>

      <div class="space-y-4">
        <div
          v-for="section in sections"
          :key="section.id"
          class="overflow-hidden bg-white rounded-xl"
        >
          <div
            class="flex items-center justify-between p-4 cursor-pointer"
            @click="toggleSection(section.id)"
          >
            <h3 class="font-semibold">{{ section.titre }}</h3>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5 transition-transform duration-300"
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
          <div v-show="section.isOpen" class="p-4 border-t bg-white/80">
            <p>{{ section.contenu }}</p>
          </div>
        </div>
      </div>

      <div class="mt-8">
        <div class="flex flex-col gap-4 sm:flex-row">
          <div class="relative flex-1">
            <svg
              class="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 pointer-events-none left-3 top-1/2"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 2C8.13 2 5 5.13 5 9c0 4.8 7 11 7 11s7-6.2 7-11c0-3.87-3.13-7-7-7z"
              />
            </svg>
            <input
              type="text"
              class="w-full p-4 pl-10 border rounded-xl"
              placeholder="Localisation"
            />
          </div>

          <div class="relative flex-1">
            <svg
              class="absolute w-5 h-5 text-gray-400 transform -translate-y-1/2 pointer-events-none left-3 top-1/2"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10m-10 4h5m-7 4h10m-5-8h2m2 8h2m-2-4h2"
              />
            </svg>
            <input type="date" class="w-full px-3 py-4 pl-10 border rounded-xl" />
          </div>
        </div>

        <div class="flex justify-center mt-6">
          <button
            class="px-6 py-3 font-semibold text-gray-800 bg-white rounded-full shadow hover:bg-gray-100"
          >
            Rechercher
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="inline-block w-5 h-5 ml-2"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
          </button>
        </div>
      </div>
    </fieldset>
  </div>
</template>
