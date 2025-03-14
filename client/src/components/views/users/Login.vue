<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { login } from '@/services/api'

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
  <div class="container px-4 pt-24 mx-auto">
    <h1 class="mb-6 text-3xl font-bold">Connexion</h1>
    <p v-if="redirectPath !== '/'" class="mb-4 text-center text-blue-600">
      Veuillez vous connecter pour accéder à la page demandée
    </p>
    <form @submit.prevent="handleSubmit" class="max-w-md mx-auto">
      <div class="mb-4">
        <label for="email" class="block mb-2 font-medium">Email</label>
        <input
          type="email"
          id="email"
          v-model="email"
          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />
      </div>
      <div class="mb-6">
        <label for="password" class="block mb-2 font-medium">Mot de passe</label>
        <input
          type="password"
          id="password"
          v-model="password"
          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />
      </div>
      <div v-if="errorMessage" class="p-3 mb-4 text-red-700 bg-red-100 rounded-lg">
        {{ errorMessage }}
      </div>
      <button
        type="submit"
        class="w-full px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
        :disabled="isLoading"
      >
        {{ isLoading ? 'Connexion en cours...' : 'Connexion' }}
      </button>
      <div class="mt-4 text-center">
        <p>
          Vous n'avez pas de compte ?
          <router-link to="/register" class="text-blue-600 hover:underline">S'inscrire</router-link>
        </p>
      </div>
    </form>
  </div>
</template>
