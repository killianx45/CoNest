<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import NavBar from '../../NavBar.vue'
import {
  getAllCategories,
  createProduit,
  type Category,
  type ProduitCreateData,
  getCurrentUser,
  isAuthenticated,
} from '../../../services/api'

const router = useRouter()
const categories = ref<Category[]>([])
const loading = ref(false)
const error = ref('')
const success = ref(false)
const imagePreview = ref('')
const hasPermission = ref(true)
const form = reactive({
  nom: '',
  description: '',
  prix: 0,
  image: null as File | null,
  categories: [] as number[],
  date_debut: '',
  date_fin: '',
})

const errors = reactive({
  nom: false,
  description: false,
  prix: false,
  image: false,
  categories: false,
  date_debut: false,
  date_fin: false,
  general: '',
})

const checkUserPermission = async () => {
  if (!isAuthenticated()) {
    error.value = 'Vous devez être connecté pour créer un produit.'
    hasPermission.value = false
    return
  }

  try {
    const user = await getCurrentUser()
    if (!user || !(user.role === 'ROLE_LOUEUR' || user.role === 'ROLE_ADMIN')) {
      error.value =
        "Vous n'avez pas les droits nécessaires pour créer un produit. Seuls les loueurs et administrateurs peuvent le faire."
      hasPermission.value = false
    }
  } catch (err) {
    error.value = "Impossible de vérifier vos droits d'accès."
    hasPermission.value = false
  }
}

const fetchCategories = async () => {
  try {
    loading.value = true
    categories.value = await getAllCategories()
  } catch (err: any) {
    error.value = err.message || 'Erreur lors du chargement des catégories. Veuillez réessayer.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    form.image = target.files[0]
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(form.image)
  }
}

const validateForm = (): boolean => {
  let isValid = true
  errors.general = ''
  errors.nom = !form.nom.trim()
  if (errors.nom) isValid = false
  errors.description = !form.description.trim()
  if (errors.description) isValid = false
  errors.prix = form.prix <= 0
  if (errors.prix) isValid = false
  errors.image = !form.image
  if (errors.image) isValid = false
  errors.categories = form.categories.length === 0
  if (errors.categories) isValid = false
  errors.date_debut = !form.date_debut
  if (errors.date_debut) isValid = false
  errors.date_fin = !form.date_fin
  if (errors.date_fin) isValid = false
  if (form.date_debut && form.date_fin && form.date_debut > form.date_fin) {
    errors.date_fin = true
    errors.general = 'La date de fin doit être après la date de début'
    isValid = false
  }

  return isValid
}

const submitForm = async () => {
  if (!validateForm()) {
    return
  }

  try {
    loading.value = true

    if (!form.image) {
      throw new Error("L'image est requise")
    }

    const produitData: ProduitCreateData = {
      nom: form.nom,
      description: form.description,
      prix: form.prix,
      image: form.image,
      categories: form.categories,
      date_debut: form.date_debut,
      date_fin: form.date_fin,
    }

    await createProduit(produitData)
    success.value = true

    setTimeout(() => {
      router.push('/')
    }, 2000)
  } catch (err: any) {
    if (err.response && err.response.data && err.response.data.message) {
      error.value = err.response.data.message
    } else {
      error.value = 'Une erreur est survenue lors de la création du produit.'
    }
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await checkUserPermission()
  if (hasPermission.value) {
    await fetchCategories()
  }
})
</script>

<template>
  <NavBar />
  <div class="max-w-4xl p-6 mx-auto mt-20 bg-white border-2 border-orange-300 rounded-lg shadow-md">
    <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">
      Créer un produit
    </h1>
    <div v-if="loading" class="flex justify-center my-8">
      <div class="w-12 h-12 border-b-2 border-orange-500 rounded-full animate-spin"></div>
    </div>
    <div
      v-else-if="success"
      class="px-4 py-3 mb-4 text-green-700 bg-green-100 border border-green-400 rounded"
    >
      <p>Produit créé avec succès ! Redirection en cours...</p>
    </div>
    <div
      v-else-if="!hasPermission"
      class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded"
    >
      <p>{{ error }}</p>
      <p class="mt-2">
        <a href="/login" class="text-blue-600 underline hover:text-blue-800">Se connecter</a>
        pour accéder à cette fonctionnalité.
      </p>
    </div>
    <div v-else>
      <div
        v-if="error"
        class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded"
      >
        <p>{{ error }}</p>
      </div>
      <div
        v-if="errors.general"
        class="px-4 py-3 mb-4 text-red-700 bg-red-100 border border-red-400 rounded"
      >
        <p>{{ errors.general }}</p>
      </div>

      <form @submit.prevent="submitForm" class="space-y-6">
        <div>
          <label for="nom" class="block mb-2 font-medium text-gray-700">Nom du produit *</label>
          <input
            type="text"
            id="nom"
            v-model="form.nom"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            :class="{ 'border-red-500': errors.nom }"
          />
          <p v-if="errors.nom" class="mt-1 text-sm text-red-600">Le nom est requis</p>
        </div>
        <div>
          <label for="description" class="block mb-2 font-medium text-gray-700"
            >Description *</label
          >
          <textarea
            id="description"
            v-model="form.description"
            rows="4"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            :class="{ 'border-red-500': errors.description }"
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">
            La description est requise
          </p>
        </div>
        <div>
          <label for="prix" class="block mb-2 font-medium text-gray-700">Prix (€) *</label>
          <input
            type="number"
            id="prix"
            v-model="form.prix"
            min="0"
            step="0.01"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            :class="{ 'border-red-500': errors.prix }"
          />
          <p v-if="errors.prix" class="mt-1 text-sm text-red-600">
            Le prix doit être supérieur à 0
          </p>
        </div>
        <div>
          <label for="image" class="block mb-2 font-medium text-gray-700">Image *</label>
          <input
            type="file"
            id="image"
            accept="image/*"
            @change="handleImageChange"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            :class="{ 'border-red-500': errors.image }"
          />
          <p v-if="errors.image" class="mt-1 text-sm text-red-600">L'image est requise</p>
          <div v-if="imagePreview" class="mt-2">
            <img
              :src="imagePreview"
              alt="Prévisualisation"
              class="object-cover w-full h-48 rounded-md"
            />
          </div>
        </div>
        <div>
          <label class="block mb-2 font-medium text-gray-700">Catégories *</label>
          <div class="p-3 border rounded-md" :class="{ 'border-red-500': errors.categories }">
            <div v-for="category in categories" :key="category.id" class="mb-2">
              <label class="inline-flex items-center">
                <input
                  type="checkbox"
                  :value="category.id"
                  v-model="form.categories"
                  class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500"
                />
                <span class="ml-2">{{ category.name }}</span>
              </label>
            </div>
          </div>
          <p v-if="errors.categories" class="mt-1 text-sm text-red-600">
            Sélectionnez au moins une catégorie
          </p>
        </div>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label for="date_debut" class="block mb-2 font-medium text-gray-700"
              >Date de début *</label
            >
            <input
              type="date"
              id="date_debut"
              v-model="form.date_debut"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
              :class="{ 'border-red-500': errors.date_debut }"
            />
            <p v-if="errors.date_debut" class="mt-1 text-sm text-red-600">
              La date de début est requise
            </p>
          </div>

          <div>
            <label for="date_fin" class="block mb-2 font-medium text-gray-700">Date de fin *</label>
            <input
              type="date"
              id="date_fin"
              v-model="form.date_fin"
              class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
              :class="{ 'border-red-500': errors.date_fin }"
            />
            <p v-if="errors.date_fin" class="mt-1 text-sm text-red-600">
              La date de fin est requise
            </p>
          </div>
        </div>
        <div class="flex justify-end">
          <button
            type="submit"
            class="px-6 py-2 text-white bg-orange-500 rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
            :disabled="loading"
          >
            {{ loading ? 'Création en cours...' : 'Créer le produit' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
