<script setup lang="ts">
import NavBar from './components/NavBar.vue'
</script>

<template>
  <NavBar />
  <router-view />
</template>

<style>
body {
  font-family: 'Montserrat', sans-serif;
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  background: #ffffff;
}
</style>
