<script lang="ts">
export default {
  name: 'NavBar',
  data() {
    return {
      currentLanguage: 'FR',
      isMenuOpen: false,
      menuItems: [
        { text: 'louer', route: '#' },
        { text: 'contact', route: '#' },
        { text: 'carte', route: '#' },
        { text: 'favoris', route: '#' },
        { text: 'mon compte', route: '/login' },
      ],
    }
  },
  methods: {
    toggleLanguage() {
      this.currentLanguage = this.currentLanguage === 'FR' ? 'EN' : 'FR'
    },
    toggleMenu() {
      this.isMenuOpen = !this.isMenuOpen
    },
  },
}
</script>

<template>
  <nav class="fixed top-0 left-0 w-full bg-white shadow-md z-[1000]">
    <!-- Menu mobile -->
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
      <button
        type="button"
        class="bg-[#ff5a5f] hover:bg-[#ff4245] text-white border-none py-2 px-4 rounded-[50px] cursor-pointer w-[90%] mx-auto block mb-2"
      >
        + mettre une annonce
      </button>
      <ul class="p-0 m-0 list-none">
        <li
          v-for="(item, index) in menuItems"
          :key="index"
          class="p-2 border-b border-gray-200"
          :class="{ 'border-b-0': index === menuItems.length - 1 }"
        >
          <router-link :to="item.route" class="no-underline text-[#333] font-bold block">{{
            item.text
          }}</router-link>
        </li>
        <li class="p-2">
          <button
            @click="toggleLanguage"
            class="bg-transparent border-none cursor-pointer text-base flex items-center gap-2 p-1 rounded hover:bg-[#f0f0f0] w-full"
          >
            🌍 {{ currentLanguage }}
          </button>
        </li>
      </ul>
    </div>

    <!-- Menu desktop -->
    <ul class="hidden md:flex list-none m-0 p-[0.2rem] justify-between items-center">
      <div class="flex items-center gap-4 p-2 left-menu">
        <router-link to="/">
          <img src="../assets/logo_CoNest.svg" alt="Conest logo" class="h-10 m-2" />
        </router-link>
        <button
          type="button"
          class="bg-[#ff5a5f] hover:bg-[#ff4245] text-white border-none py-2 px-4 rounded-[50px] cursor-pointer annonce-button"
        >
          + mettre une annonce
        </button>
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
        <li
          v-for="(item, index) in menuItems.slice(3)"
          :key="index + 3"
          class="m-0 hover:border-b-2 hover:border-[#333] transition-all duration-300"
        >
          <router-link :to="item.route" class="no-underline text-[#333] font-bold">{{
            item.text
          }}</router-link>
        </li>
        <li class="m-0">
          <button
            @click="toggleLanguage"
            class="bg-transparent border-none cursor-pointer text-base flex items-center gap-2 p-1 rounded hover:bg-[#f0f0f0] language-btn"
          >
            🌍 {{ currentLanguage }}
          </button>
        </li>
      </div>
    </ul>
  </nav>
</template>
