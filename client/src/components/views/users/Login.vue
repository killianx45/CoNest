<script setup lang="ts">
import { login } from '@/services/api'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isLoading = ref(false)
const redirectPath = ref('/')

onMounted(() => {
  if (route.query.redirect) {
    redirectPath.value = route.query.redirect as string
  }
})

async function handleSubmit() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    if (!email.value || !password.value) {
      errorMessage.value = 'Veuillez remplir tous les champs'
      isLoading.value = false
      return
    }

    await login(email.value, password.value)
    router.push(redirectPath.value)
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la connexion'
    console.error('Erreur de connexion:', error)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-[#FFF1E9]">
    <fieldset
      class="fieldset w-lg bg-[#FDF9F6] border border-base-200 p-20 rounded-box text-center"
    >
      <img class="mx-auto mb-8 logoconest" src="@/assets/logoconest.svg" alt="Logo" />

      <h1 class="mx-auto mb-6 text-2xl font-semibold">Connexion</h1>

      <form @submit.prevent="handleSubmit" class="flex flex-col items-center">
        <div class="w-full max-w-[400px] mb-6">
          <label for="email" class="block mb-2 text-left text-gray-700">Email</label>
          <input
            type="email"
            id="email"
            v-model="email"
            class="w-full px-4 py-3 placeholder-black border border-gray-300 rounded-lg input"
            placeholder="Votre email"
            required
          />
        </div>

        <div class="w-full max-w-[400px] mb-8">
          <label for="password" class="block mb-2 text-left text-gray-700">Mot de passe</label>
          <input
            type="password"
            id="password"
            v-model="password"
            class="w-full px-4 py-3 placeholder-black border border-gray-300 rounded-lg input"
            placeholder="Votre mot de passe"
            required
          />
        </div>

        <div
          v-if="errorMessage"
          class="p-3 mb-6 text-red-700 bg-red-100 rounded-lg max-w-[400px] w-full"
        >
          {{ errorMessage }}
        </div>

        <button
          type="submit"
          class="btn btn-neutral rounded-lg w-full max-w-[400px] mx-auto bg-[#FF8238] text-white border-none shadow-none h-12 text-lg"
          :disabled="isLoading"
        >
          {{ isLoading ? 'Connexion en cours...' : 'Se connecter' }}
        </button>
      </form>

      <p class="mt-8 text-center">
        Pas encore membre ?
        <router-link to="/register" class="text-[#2F20B2] font-medium">Inscrivez-vous</router-link>
      </p>
    </fieldset>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

* {
  font-family: 'Poppins', sans-serif;
}
</style>
