<script lang="ts">
import { register } from '@/services/api'
import { defineComponent } from 'vue'
import { useRouter } from 'vue-router'

export default defineComponent({
  name: 'Register',
  data() {
    return {
      email: '',
      password: '',
      name: '',
      telephone: '',
      errorMessage: '',
      isLoading: false,
    }
  },
  setup() {
    const router = useRouter()
    return { router }
  },
  methods: {
    async handleSubmit() {
      this.isLoading = true
      this.errorMessage = ''

      try {
        if (!this.email || !this.password || !this.name || !this.telephone) {
          this.errorMessage = 'Veuillez remplir tous les champs'
          this.isLoading = false
          return
        }

        await register(this.email, this.password, this.name, this.telephone)
        this.router.push('/login')
      } catch (error: any) {
        this.errorMessage = error.response?.data?.message || "Erreur lors de l'inscription"
        console.error("Erreur d'inscription:", error)
      } finally {
        this.isLoading = false
      }
    },
  },
})
</script>

<template>
  <div class="container px-4 pt-24 mx-auto">
    <h1 class="mb-6 text-3xl font-bold">Inscription</h1>
    <form @submit.prevent="handleSubmit" class="max-w-md mx-auto">
      <div class="mb-4">
        <label for="name" class="block mb-2 font-medium">Nom</label>
        <input
          type="text"
          id="name"
          v-model="name"
          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />
      </div>
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
      <div class="mb-4">
        <label for="telephone" class="block mb-2 font-medium">Téléphone</label>
        <input
          type="tel"
          id="telephone"
          v-model="telephone"
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
        {{ isLoading ? 'Inscription en cours...' : "S'inscrire" }}
      </button>
      <div class="mt-4 text-center">
        <p>
          Vous avez déjà un compte ?
          <router-link to="/login" class="text-blue-600 hover:underline">Se connecter</router-link>
        </p>
      </div>
    </form>
  </div>
</template>
