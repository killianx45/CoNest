<script setup lang="ts">
import { getCurrentUser, isAuthenticated, logout } from '@/services/api'
import { computed, onBeforeUnmount, ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const currentLanguage = ref('FR')
const isMenuOpen = ref(false)
const isAccountMenuOpen = ref(false)
const menuItems = ref([
  { text: 'contact', route: '/#faq-section' },
  { text: 'carte', route: '/#carte-section' },
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

function handleClick(route: string) {
  if (route.startsWith('/#')) {
    const targetId = route.substring(2)
    const element = document.getElementById(targetId)
    if (element) {
      element.scrollIntoView({ behavior: 'smooth' })
    } else if (window.location.pathname !== '/') {
      router.push(route)
    }
  }
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
  <nav class="fixed top-0 left-0 w-full bg-[#FFF1E9] shadow-lg z-[1000]">
    <div class="flex items-center justify-between p-3 md:hidden">
      <router-link to="/">
        <img src="../assets/logo_CoNest.svg" alt="Conest logo" class="m-1 h-9" />
      </router-link>
      <button
        @click="toggleMenu"
        aria-label="Ouvrir le menu principal"
        class="p-2 rounded-xl focus:outline-none hover:bg-white/30"
      >
        <span class="block w-6 h-0.5 bg-gray-800 mb-1.5"></span>
        <span class="block w-6 h-0.5 bg-gray-800 mb-1.5"></span>
        <span class="block w-6 h-0.5 bg-gray-800"></span>
      </button>
    </div>

    <div :class="{ hidden: !isMenuOpen }" class="py-3 px-4 bg-[#FFF1E9] md:hidden">
      <router-link to="/produit/create">
        <button
          type="button"
          aria-label="Mettre une annonce"
          class="bg-[#FF8238] hover:bg-[#e67530] text-white border-none py-2.5 px-5 rounded-xl cursor-pointer w-full mx-auto block mb-3 font-medium"
        >
          + mettre une annonce
        </button>
      </router-link>
      <div class="p-3 bg-white shadow-md rounded-xl">
        <div
          @click="toggleAccountMenu"
          class="flex items-center justify-between mb-2 cursor-pointer"
        >
          <span class="font-semibold text-gray-800">Mon compte</span>
          <span
            :class="{ 'transform rotate-180': isAccountMenuOpen }"
            class="transition-transform duration-300"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              />
            </svg>
          </span>
        </div>
        <div v-if="isAccountMenuOpen" class="pt-2 pl-2 mt-2 border-t">
          <div v-if="isLoggedIn" class="flex flex-col">
            <span class="py-2 text-gray-700">{{ userName }}</span>
            <router-link to="/commandes" class="py-2 text-gray-700 hover:text-gray-900">
              Commande
            </router-link>
            <router-link to="/concours" class="py-2 text-gray-700 hover:text-gray-900">
              Concours Mensuel
            </router-link>
            <button
              @click="handleLogout"
              class="py-2 text-left text-gray-700 bg-transparent border-none hover:text-gray-900"
            >
              Déconnexion
            </button>
          </div>
          <div v-else>
            <router-link to="/login" class="block py-2 text-gray-700 hover:text-gray-900">
              Connexion
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <div class="hidden px-6 py-3 md:block">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-5">
          <router-link to="/">
            <img src="../assets/logo_CoNest.svg" alt="Conest logo" class="h-10" />
          </router-link>
          <router-link
            v-for="(item, index) in menuItems"
            :key="index"
            :to="item.route"
            class="font-medium text-gray-800 hover:text-[#FF8238] transition-colors"
            @click.native.prevent="handleClick(item.route)"
          >
            {{ item.text }}
          </router-link>
        </div>

        <div class="flex items-center gap-5">
          <router-link to="/produit/create">
            <button
              type="button"
              aria-label="Mettre une annonce"
              class="bg-[#FF8238] hover:bg-[#e67530] text-white border-none py-2 px-5 rounded-xl cursor-pointer font-medium transition-colors"
            >
              + mettre une annonce
            </button>
          </router-link>

          <div class="relative">
            <div
              @click="toggleAccountMenu"
              class="flex items-center gap-1 cursor-pointer font-medium text-gray-800 hover:text-[#FF8238] transition-colors"
            >
              <span>Mon compte</span>
              <span
                :class="{ 'transform rotate-180': isAccountMenuOpen }"
                class="transition-transform duration-300"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-5 h-5"
                  viewBox="0 0 20 20"
                  fill="currentColor"
                >
                  <path
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  />
                </svg>
              </span>
            </div>
            <div
              v-if="isAccountMenuOpen"
              class="absolute right-0 z-10 w-48 mt-2 overflow-hidden bg-white shadow-lg rounded-xl"
            >
              <div v-if="isLoggedIn" class="py-1">
                <span class="block px-4 py-2 font-medium text-gray-700">{{ userName }}</span>
                <router-link
                  to="/commandes"
                  class="block px-4 py-2 text-gray-700 hover:bg-[#FFF1E9]"
                >
                  Commande
                </router-link>
                <router-link
                  to="/concours"
                  class="block px-4 py-2 text-gray-700 hover:bg-[#FFF1E9]"
                >
                  Concours Mensuel
                </router-link>
                <button
                  @click="handleLogout"
                  class="block w-full text-left px-4 py-2 text-gray-700 hover:bg-[#FFF1E9] bg-transparent border-none"
                >
                  Déconnexion
                </button>
              </div>
              <div v-else>
                <router-link to="/login" class="block px-4 py-2 text-gray-700 hover:bg-[#FFF1E9]">
                  Connexion
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>
