<script setup lang="ts">
import { login } from '@/services/api'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import Formulaire from '@/components/Formulaire.vue'

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
  <Formulaire
    titre="Connexion"
    v-model:email="email"
    v-model:password="password"
    :showName="false"
    :showTelephone="false"
    :errorMessage="errorMessage"
    :isLoading="isLoading"
    buttonText="Se connecter"
    redirectText="Pas encore membre ?"
    redirectPath="/register"
    redirectLinkText="Inscrivez-vous"
    @submit="handleSubmit"
  />
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

* {
  font-family: 'Poppins', sans-serif;
}
</style>
