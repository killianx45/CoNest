<script setup lang="ts">
import { register } from '@/services/api'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import Formulaire from '@/components/Formulaire.vue'

const router = useRouter()
const email = ref('')
const password = ref('')
const name = ref('')
const telephone = ref('')
const errorMessage = ref('')
const isLoading = ref(false)

async function handleSubmit() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    if (!email.value || !password.value || !name.value || !telephone.value) {
      errorMessage.value = 'Veuillez remplir tous les champs'
      isLoading.value = false
      return
    }

    await register(email.value, password.value, name.value, telephone.value)
    router.push('/login')
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || "Erreur lors de l'inscription"
    console.error("Erreur d'inscription:", error)
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <Formulaire
    titre="Inscription"
    v-model:email="email"
    v-model:password="password"
    v-model:name="name"
    v-model:telephone="telephone"
    :showName="true"
    :showTelephone="true"
    :errorMessage="errorMessage"
    :isLoading="isLoading"
    buttonText="S'inscrire"
    redirectText="Vous êtes déjà membre ?"
    redirectPath="/login"
    redirectLinkText="Connectez-vous"
    @submit="handleSubmit"
  />
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

* {
  font-family: 'Poppins', sans-serif;
}
</style>
