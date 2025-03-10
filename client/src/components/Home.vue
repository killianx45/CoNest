<script lang="ts">
import HeaderComponent from './Header.vue'
import NavBar from './NavBar.vue'

export default {
  name: 'HomePage',
  components: {
    NavBar,
    HeaderComponent,
  },
}
</script>

<template>
  <div class="flex flex-col items-center justify-center h-screen">
    <NavBar />
    <HeaderComponent />
    <h1 class="mt-2 text-2xl text-center text-black">Bienvenue sur la page d'accueil de Conest</h1>
  </div>
</template>
