<script setup lang="ts">
import Footer from './components/Footer.vue'
import NavBar from './components/NavBar.vue'
</script>

<template>
  <div class="flex flex-col min-h-screen bg-[#FFF1E9]">
    <NavBar />
    <main class="flex-1 mt-32">
      <router-view />
    </main>
    <div class="bg-[#FFF1E9]">
      <Footer />
    </div>
  </div>
</template>

<style>
body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}
</style>
