<script lang="ts">
import NavBar from '../../NavBar.vue'
import { defineComponent } from 'vue'

export default defineComponent({
  name: 'CreateCommande',
  components: {
    NavBar,
  },
})
</script>

<template>
  <NavBar />
  <div class="max-w-6xl p-6 mx-auto mt-20 bg-white border-2 border-orange-300 rounded-lg shadow-md">
    <h1 class="text-3xl font-bold text-black">Créer une commande</h1>
  </div>
</template>
