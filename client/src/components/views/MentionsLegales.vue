<script setup lang="ts">
import { ref } from 'vue'

const contactEmail = ref('contact@conest.fr')
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-[#FFF1E9]">
    <fieldset
      class="fieldset w-full max-w-3xl mx-auto bg-[#FDF9F6] border border-base-200 p-8 md:p-16 rounded-xl"
    >
      <img class="mx-auto mb-8 logoconest h-14" src="@/assets/logo_CoNest.svg" alt="Logo" />

      <h1 class="mx-auto mb-6 text-2xl font-semibold text-center">Mentions Légales</h1>

      <div class="text-left">
        <div class="mb-8">
          <h2 class="mb-4 text-xl font-semibold text-[#FF8238]">Éditeur du site</h2>
          <p class="mb-2">Email : {{ contactEmail }}</p>
          <p>Directeur de la publication : L'équipe CoNest</p>
        </div>

        <div class="mb-8">
          <h2 class="mb-4 text-xl font-semibold text-[#FF8238]">Propriété intellectuelle</h2>
          <p class="mb-2">
            L'ensemble des éléments composant le site CoNest (textes, graphismes, logiciels,
            photographies, images, vidéos, sons, plans, logos, marques, etc.) sont la propriété
            exclusive de CoNest. Ces éléments sont protégés par les lois françaises et
            internationales relatives à la propriété intellectuelle.
          </p>
          <p>
            Toute reproduction totale ou partielle de ce site ou d'un de ses éléments sans
            autorisation expresse et préalable de CoNest est interdite.
          </p>
        </div>

        <div class="mb-8">
          <h2 class="mb-4 text-xl font-semibold text-[#FF8238]">
            Protection des données personnelles
          </h2>
          <p>
            Conformément à la loi n° 78-17 du 6 janvier 1978, relative à l'Informatique, aux
            Fichiers et aux Libertés, et au Règlement Général sur la Protection des Données (RGPD),
            vous disposez d'un droit d'accès, de rectification et de suppression des données vous
            concernant. Pour exercer ce droit, veuillez nous contacter à l'adresse :
            {{ contactEmail }}
          </p>
        </div>

        <div>
          <h2 class="mb-4 text-xl font-semibold text-[#FF8238]">Cookies</h2>
          <p>
            Le site CoNest utilise des cookies pour améliorer l'expérience utilisateur. En naviguant
            sur notre site, vous acceptez l'utilisation de cookies conformément à notre politique de
            confidentialité.
          </p>
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

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

* {
  font-family: 'Poppins', sans-serif;
}
</style>
