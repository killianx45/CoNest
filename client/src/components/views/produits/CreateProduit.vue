<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import {
  createProduit,
  getAllCategories,
  getCurrentUser,
  isAuthenticated,
  type Category,
  type ProduitCreateData,
} from '../../../services/api'
import NavBar from '../../NavBar.vue'

const router = useRouter()
const categories = ref<Category[]>([])
const loading = ref(false)
const error = ref('')
const success = ref(false)
const imagePreviews = ref<string[]>([])
const hasPermission = ref(true)
const form = reactive({
  nom: '',
  description: '',
  prix: 0,
  adresse: '',
  images: [] as File[],
  categories: [] as number[],
  date_debut: '',
  date_fin: '',
})

const errors = reactive({
  nom: false,
  description: false,
  prix: false,
  adresse: false,
  images: false,
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
    form.images = Array.from(target.files)
    imagePreviews.value = []

    Array.from(target.files).forEach((file) => {
      const reader = new FileReader()
      reader.onload = (e) => {
        if (e.target?.result) {
          imagePreviews.value.push(e.target.result as string)
        }
      }
      reader.readAsDataURL(file)
    })
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
  errors.adresse = !form.adresse.trim()
  if (errors.adresse) isValid = false
  errors.images = form.images.length === 0
  if (errors.images) isValid = false
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

    if (form.images.length === 0) {
      throw new Error('Au moins une image est requise')
    }

    const produitData: ProduitCreateData = {
      nom: form.nom,
      description: form.description,
      prix: form.prix,
      adresse: form.adresse,
      images: form.images,
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
  <section class="flex items-center justify-center min-h-screen bg-[#FFF1E9] formu">
    <div class="w-full max-w-3xl p-12 mx-8 mt-10 mb-10 bg-orange-100 shadow-md rounded-xl">
      <a href="/" class="block mb-4 text-gray-500"><p>&lt; retour</p></a>
      <h1 class="mb-10 text-2xl font-semibold text-center text-blue-950">
        Ajouter un nouveau bien
      </h1>

      <div v-if="loading" class="flex justify-center my-10">
        <div class="w-12 h-12 border-b-2 border-orange-500 rounded-xl animate-spin"></div>
      </div>

      <div
        v-else-if="success"
        class="px-6 py-4 mb-6 text-green-700 bg-green-100 border border-green-400 rounded-xl"
      >
        <p>Produit créé avec succès ! Redirection en cours...</p>
      </div>

      <div
        v-else-if="!hasPermission"
        class="px-6 py-4 mb-6 text-red-700 bg-red-100 border border-red-400 rounded-xl"
      >
        <p>{{ error }}</p>
        <p class="mt-4">
          <a href="/login" class="text-blue-600 underline hover:text-blue-800">Se connecter</a>
          pour accéder à cette fonctionnalité.
        </p>
      </div>

      <div v-else>
        <div
          v-if="error"
          class="px-6 py-4 mb-6 text-red-700 bg-red-100 border border-red-400 rounded-xl"
        >
          <p>{{ error }}</p>
        </div>
        <div
          v-if="errors.general"
          class="px-6 py-4 mb-6 text-red-700 bg-red-100 border border-red-400 rounded-xl"
        >
          <p>{{ errors.general }}</p>
        </div>

        <form @submit.prevent="submitForm" class="space-y-8">
          <div>
            <label class="block mb-2 font-medium text-blue-950" for="nom">Nom du bien</label>
            <input
              type="text"
              id="nom"
              v-model="form.nom"
              class="w-full p-3 border border-gray-300 rounded-md bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
              :class="{ 'border-red-500': errors.nom }"
            />
            <p v-if="errors.nom" class="mt-2 text-sm text-red-600">Le nom est requis</p>
          </div>

          <div>
            <label class="block mb-2 font-medium text-blue-950" for="adresse">Adresse</label>
            <input
              type="text"
              id="adresse"
              v-model="form.adresse"
              class="w-full p-3 border border-gray-300 rounded-md bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
              :class="{ 'border-red-500': errors.adresse }"
            />
            <p v-if="errors.adresse" class="mt-2 text-sm text-red-600">L'adresse est requise</p>
          </div>

          <div>
            <label class="block mb-2 font-medium text-blue-950" for="prix"
              >Prix (tarif par heures)</label
            >
            <input
              type="number"
              id="prix"
              v-model="form.prix"
              min="0"
              step="0.01"
              class="w-full p-3 border border-gray-300 rounded-md bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
              :class="{ 'border-red-500': errors.prix }"
            />
            <p v-if="errors.prix" class="mt-2 text-sm text-red-600">
              Le prix doit être supérieur à 0
            </p>
          </div>

          <div>
            <fieldset class="space-y-3">
              <legend class="mb-2 font-medium text-blue-950">Type de bureaux</legend>
              <div class="px-5 space-y-3">
                <div v-for="category in categories" :key="category.id" class="mb-2">
                  <label class="flex items-center space-x-2 text-blue-950">
                    <input
                      type="checkbox"
                      :value="category.id"
                      v-model="form.categories"
                      class="w-5 h-5 text-orange-600 border-gray-300 rounded"
                    />
                    <span class="ml-2">{{ category.name }}</span>
                  </label>
                </div>
              </div>
              <p v-if="errors.categories" class="px-5 mt-2 text-sm text-red-600">
                Sélectionnez au moins une catégorie
              </p>
            </fieldset>
          </div>

          <div>
            <label class="block mb-2 font-medium text-blue-950" for="description"
              >Description du bien</label
            >
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              class="w-full p-3 border border-gray-300 rounded-md bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
              :class="{ 'border-red-500': errors.description }"
            ></textarea>
            <p v-if="errors.description" class="mt-2 text-sm text-red-600">
              La description est requise
            </p>
          </div>

          <div>
            <label class="block mb-2 font-medium text-blue-950" for="images">Image</label>
            <input
              type="file"
              id="images"
              accept="image/*"
              @change="handleImageChange"
              multiple
              class="w-full p-3 border border-gray-300 rounded-md bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
              :class="{ 'border-red-500': errors.images }"
            />
            <p v-if="errors.images" class="mt-2 text-sm text-red-600">
              Au moins une image est requise
            </p>
            <div v-if="imagePreviews.length > 0" class="mt-4 space-y-2">
              <img
                v-for="(preview, index) in imagePreviews"
                :key="index"
                :src="preview"
                alt="Prévisualisation"
                class="object-cover w-full h-48 mb-4 rounded-md"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div>
              <label class="block mb-2 font-medium text-blue-950" for="date_debut"
                >Date de début</label
              >
              <input
                type="date"
                id="date_debut"
                v-model="form.date_debut"
                class="w-full p-3 border border-gray-300 rounded-md bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
                :class="{ 'border-red-500': errors.date_debut }"
              />
              <p v-if="errors.date_debut" class="mt-2 text-sm text-red-600">
                La date de début est requise
              </p>
            </div>

            <div>
              <label class="block mb-2 font-medium text-blue-950" for="date_fin">Date de fin</label>
              <input
                type="date"
                id="date_fin"
                v-model="form.date_fin"
                class="w-full p-3 border border-gray-300 rounded-md bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
                :class="{ 'border-red-500': errors.date_fin }"
              />
              <p v-if="errors.date_fin" class="mt-2 text-sm text-red-600">
                La date de fin est requise
              </p>
            </div>
          </div>

          <div class="flex justify-center pt-4 mt-8">
            <button
              type="submit"
              class="w-full max-w-md px-10 py-3 text-white transition bg-orange-500 rounded-xl hover:bg-orange-300"
              :disabled="loading"
            >
              {{ loading ? 'Création en cours...' : "Publier l'annonce" }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
