<script setup lang="ts">
import { ref } from 'vue'

interface Question {
  id: number
  question: string
  reponse: string
  isOpen: boolean
}

const questions = ref<Question[]>([
  {
    id: 1,
    question: 'Comment réserver un espace de coworking ?',
    reponse:
      'Pour réserver un espace de coworking, parcourez les offres disponibles, sélectionnez celle qui vous convient, choisissez une plage horaire disponible et confirmez votre réservation en effectuant le paiement sécurisé.',
    isOpen: false,
  },
  {
    id: 2,
    question: 'Comment mettre en location mon espace ?',
    reponse:
      'Pour proposer votre espace, créez un compte "loueur", cliquez sur "Mettre une annonce", remplissez le formulaire avec les détails de votre espace (photos, description, prix), définissez les disponibilités et publiez votre annonce.',
    isOpen: false,
  },
  {
    id: 3,
    question: 'Comment fonctionne le système de paiement ?',
    reponse:
      'Nous utilisons un système de paiement sécurisé qui protège à la fois les loueurs et les locataires. Le paiement est débité au moment de la réservation et transféré au loueur après la période de location, déduction faite des frais de service.',
    isOpen: false,
  },
  {
    id: 4,
    question: 'Que faire en cas de problème avec une réservation ?',
    reponse:
      "En cas de problème avec une réservation, contactez d'abord l'autre partie via notre messagerie intégrée. Si le problème persiste, notre service client est disponible pour vous aider à résoudre le litige via le formulaire de contact.",
    isOpen: false,
  },
  {
    id: 5,
    question: 'Comment participer au concours mensuel ?',
    reponse:
      "Pour participer au concours mensuel, vous devez avoir un compte actif et avoir effectué au moins une réservation dans le mois. Les participants éligibles sont automatiquement inscrits et seront notifiés par email s'ils sont sélectionnés comme gagnants.",
    isOpen: false,
  },
  {
    id: 6,
    question: 'Est-ce que mes informations personnelles sont sécurisées ?',
    reponse:
      "Nous utilisons des protocoles de chiffrement et d'autres mesures de sécurité pour protéger vos données personnelles. Votre vie privée est notre priorité.",
    isOpen: false,
  },
])

function toggleQuestion(id: number) {
  const index = questions.value.findIndex((q) => q.id === id)
  if (index !== -1) {
    questions.value[index].isOpen = !questions.value[index].isOpen
  }
}
</script>

<template>
  <div class="px-4 py-16 mx-auto">
    <h2 class="mt-10 mb-16 text-4xl font-semibold text-center">FAQ</h2>

    <div class="grid max-w-6xl grid-cols-1 gap-5 mx-auto md:grid-cols-2">
      <div
        v-for="question in questions"
        :key="question.id"
        class="p-4 border cursor-pointer border-base-300 bg-base-100 rounded-xl"
        @click="toggleQuestion(question.id)"
      >
        <div class="flex items-center justify-between">
          <div class="font-semibold">{{ question.question }}</div>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5 transition-transform duration-300"
            :class="{ 'rotate-180': question.isOpen }"
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
        <div v-show="question.isOpen" class="mt-2 text-sm transition-opacity duration-300">
          {{ question.reponse }}
        </div>
      </div>
    </div>
  </div>
</template>
