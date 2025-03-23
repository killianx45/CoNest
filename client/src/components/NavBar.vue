<script setup lang="ts">
import { getCurrentUser, isAuthenticated, logout } from '@/services/api'
import { computed, onBeforeUnmount, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentLanguage = ref('FR')
const isMenuOpen = ref(false)
const isAccountMenuOpen = ref(false)
const menuItems = ref([
  { text: 'contact', route: '#' },
  { text: 'carte', route: '#' },
])

const isLoggedIn = computed(() => isAuthenticated())

function toggleLanguage() {
  currentLanguage.value = currentLanguage.value === 'FR' ? 'EN' : 'FR'
}

function toggleMenu() {
  isMenuOpen.value = !isMenuOpen.value
}

function toggleAccountMenu() {
  isAccountMenuOpen.value = !isAccountMenuOpen.value
}

async function handleLogout() {
  try {
    await logout()
    router.push('/')
    window.location.reload()
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error)
  }
}

const userName = ref('')

onBeforeUnmount(() => {
  isMenuOpen.value = false
  isAccountMenuOpen.value = false
})

router.beforeEach(() => {
  isMenuOpen.value = false
  isAccountMenuOpen.value = false
})

getCurrentUser().then((user) => {
  userName.value = user?.name || ''
})
</script>

<template>
  <nav class="fixed top-0 left-0 w-full bg-white shadow-md z-[1000]">
    <div class="flex items-center justify-between p-2 md:hidden">
      <router-link to="/">
        <img src="../assets/logo_CoNest.svg" alt="Conest logo" class="h-8 m-1" />
      </router-link>
      <button @click="toggleMenu" class="p-2 focus:outline-none">
        <span class="block w-6 h-0.5 bg-gray-800 mb-1"></span>
        <span class="block w-6 h-0.5 bg-gray-800 mb-1"></span>
        <span class="block w-6 h-0.5 bg-gray-800"></span>
      </button>
    </div>

    <div :class="{ hidden: !isMenuOpen }" class="py-2 bg-white md:hidden">
      <router-link to="/produit/create">
        <button
          type="button"
          class="bg-[#ff5a5f] hover:bg-[#ff4245] text-white border-none py-2 px-4 rounded-[50px] cursor-pointer w-[90%] mx-auto block mb-2"
        >
          + mettre une annonce
        </button>
      </router-link>
      <ul class="p-0 m-0 list-none">
        <li class="p-2 mr-2 border-b border-gray-200">
          <div @click="toggleAccountMenu" class="flex items-center justify-between cursor-pointer">
            <span class="no-underline text-[#333] font-bold">Mon compte</span>
            <span>{{ isAccountMenuOpen ? '▲' : '▼' }}</span>
          </div>
          <div v-if="isAccountMenuOpen" class="pl-4 mt-2">
            <div v-if="isLoggedIn" class="flex flex-col">
              <span class="text-[#333] py-2">{{ userName }}</span>
              <router-link to="/commandes" class="no-underline text-[#333] py-2">
                Commande
              </router-link>
              <router-link to="/concours" class="no-underline text-[#333] py-2">
                Concours Mensuel
              </router-link>
              <button
                @click="handleLogout"
                class="no-underline text-[#333] text-left py-2 bg-transparent border-none"
              >
                Déconnexion
              </button>
            </div>
            <div v-else>
              <router-link to="/login" class="no-underline text-[#333] py-2 block">
                Connexion
              </router-link>
            </div>
          </div>
        </li>
      </ul>
    </div>
    <ul class="hidden md:flex list-none m-0 p-[0.2rem] justify-between items-center">
      <div class="flex items-center gap-4 p-2 left-menu">
        <router-link to="/">
          <img src="../assets/logo_CoNest.svg" alt="Conest logo" class="h-10 m-2" />
        </router-link>
        <router-link to="/produit/create">
          <button
            type="button"
            class="bg-[#ff5a5f] hover:bg-[#ff4245] text-white border-none py-2 px-4 rounded-[50px] cursor-pointer annonce-button"
          >
            + mettre une annonce
          </button>
        </router-link>
        <li
          v-for="(item, index) in menuItems.slice(0, 3)"
          :key="index"
          class="m-0 hover:border-b-2 hover:border-[#333] transition-all duration-300"
        >
          <router-link :to="item.route" class="no-underline text-[#333] font-bold">{{
            item.text
          }}</router-link>
        </li>
      </div>
      <div class="flex items-center gap-4 p-2 right-menu">
        <li class="relative m-0">
          <div
            @click="toggleAccountMenu"
            class="flex items-center gap-1 cursor-pointer hover:border-b-2 hover:border-[#333] transition-all duration-300"
          >
            <span class="no-underline text-[#333] font-bold">Mon compte</span>
            <span>{{ isAccountMenuOpen ? '▲' : '▼' }}</span>
          </div>
          <div
            v-if="isAccountMenuOpen"
            class="absolute right-0 z-10 w-48 py-1 mt-2 mr-2 bg-white rounded-md shadow-lg"
          >
            <div v-if="isLoggedIn">
              <span class="text-[#333] px-4 py-2">{{ userName }}</span>
              <router-link to="/commandes" class="block px-4 py-2 text-[#333] hover:bg-gray-100">
                Commande
              </router-link>
              <router-link to="/concours" class="block px-4 py-2 text-[#333] hover:bg-gray-100">
                Concours Mensuel
              </router-link>
              <button
                @click="handleLogout"
                class="block w-full text-left px-4 py-2 text-[#333] hover:bg-gray-100 bg-transparent border-none"
              >
                Déconnexion
              </button>
            </div>
            <div v-else>
              <router-link to="/login" class="block px-4 py-2 text-[#333] hover:bg-gray-100">
                Connexion
              </router-link>
            </div>
          </div>
        </li>
      </div>
    </ul>
  </nav>
</template>
