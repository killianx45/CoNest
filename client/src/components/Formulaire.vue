<template>
  <div class="flex items-center justify-center min-h-screen bg-[#FFF1E9]">
    <fieldset class="fieldset w-lg bg-[#FDF9F6] border border-base-100 p-20 rounded-xl text-center">
      <img class="mx-auto mb-8 logoconest" src="@/assets/logoconest.svg" alt="Logo" />

      <h1 class="mx-auto mb-6 text-2xl font-semibold">{{ titre }}</h1>

      <form @submit.prevent="$emit('submit')" class="flex flex-col items-center">
        <div v-if="showName" class="w-full max-w-[400px] mb-5">
          <label for="name" class="block mb-2 text-left text-gray-700">Nom</label>
          <input
            type="text"
            id="name"
            :value="name"
            @input="$emit('update:name', $event.target.value)"
            class="w-full px-4 py-3 placeholder-black border border-gray-300 rounded-lg input"
            placeholder="Votre nom"
            required
          />
        </div>

        <div class="w-full max-w-[400px] mb-5">
          <label for="email" class="block mb-2 text-left text-gray-700">Email</label>
          <input
            type="email"
            id="email"
            :value="email"
            @input="$emit('update:email', $event.target.value)"
            class="w-full px-4 py-3 placeholder-black border border-gray-300 rounded-lg input"
            placeholder="Votre email"
            required
          />
        </div>

        <div v-if="showTelephone" class="w-full max-w-[400px] mb-5">
          <label for="telephone" class="block mb-2 text-left text-gray-700">Téléphone</label>
          <input
            type="tel"
            id="telephone"
            :value="telephone"
            @input="$emit('update:telephone', $event.target.value)"
            class="w-full px-4 py-3 placeholder-black border border-gray-300 rounded-lg input"
            placeholder="Votre téléphone"
            required
          />
        </div>

        <div class="w-full max-w-[400px] mb-8">
          <label for="password" class="block mb-2 text-left text-gray-700">Mot de passe</label>
          <input
            type="password"
            id="password"
            :value="password"
            @input="$emit('update:password', $event.target.value)"
            class="w-full px-4 py-3 placeholder-black border border-gray-300 rounded-lg input"
            placeholder="Votre mot de passe"
            required
          />
        </div>

        <div
          v-if="errorMessage"
          class="p-3 mb-6 text-red-700 bg-red-100 rounded-lg max-w-[400px] w-full"
        >
          {{ errorMessage }}
        </div>

        <button
          type="submit"
          class="btn btn-neutral rounded-lg w-full max-w-[400px] mx-auto bg-[#FF8238] text-white border-none shadow-none h-12 text-lg"
          :disabled="isLoading"
        >
          {{ buttonText }}
        </button>
      </form>

      <p class="mt-8 text-center">
        {{ redirectText }}
        <router-link :to="redirectPath" class="text-[#2F20B2] font-medium">{{
          redirectLinkText
        }}</router-link>
      </p>
    </fieldset>
  </div>
</template>

<script setup lang="ts">
defineProps({
  titre: {
    type: String,
    required: true,
  },
  email: {
    type: String,
    required: true,
  },
  password: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    default: '',
  },
  telephone: {
    type: String,
    default: '',
  },
  showName: {
    type: Boolean,
    default: false,
  },
  showTelephone: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: '',
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
  buttonText: {
    type: String,
    required: true,
  },
  redirectText: {
    type: String,
    required: true,
  },
  redirectPath: {
    type: String,
    required: true,
  },
  redirectLinkText: {
    type: String,
    required: true,
  },
})

defineEmits(['submit', 'update:email', 'update:password', 'update:name', 'update:telephone'])
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

* {
  font-family: 'Poppins', sans-serif;
}
</style>
