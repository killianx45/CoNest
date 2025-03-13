<script lang="ts">
import { login } from '@/services/api'
import { defineComponent } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export default defineComponent({
  name: 'Login',
  data() {
    return {
      email: '',
      password: '',
      errorMessage: '',
      isLoading: false,
      redirectPath: '/',
    }
  },
  setup() {
    const router = useRouter()
    const route = useRoute()
    return { router, route }
  },
  mounted() {
    if (this.route.query.redirect) {
      this.redirectPath = this.route.query.redirect as string
    }
  },
  methods: {
    async handleSubmit() {
      this.isLoading = true
      this.errorMessage = ''

      try {
        if (!this.email || !this.password) {
          this.errorMessage = 'Veuillez remplir tous les champs'
          this.isLoading = false
          return
        }

        await login(this.email, this.password)
        this.router.push(this.redirectPath)
      } catch (error: any) {
        this.errorMessage = error.response?.data?.message || 'Erreur lors de la connexion'
        console.error('Erreur de connexion:', error)
      } finally {
        this.isLoading = false
      }
    },
  },
})
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
