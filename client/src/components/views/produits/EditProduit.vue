<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import NavBar from '../../NavBar.vue'
import {
  getAllCategories,
  getProduitById,
  updateProduit,
  type Category,
  type Produit,
  type ProduitUpdateData,
  getCurrentUser,
  isAuthenticated,
} from '../../../services/api'

const props = defineProps({
  id: {
    type: [String, Number],
    default: null,
  },
})
const router = useRouter()
const route = useRoute()
const produitId = props.id ? parseInt(props.id.toString()) : parseInt(route.params.id as string)
const categories = ref<Category[]>([])
const loading = ref(false)
const error = ref('')
const success = ref(false)
const imagePreviews = ref<string[]>([])
const hasPermission = ref(true)
const imageChanged = ref(false)
const produit = ref<Produit | null>(null)
const form = reactive({
  nom: '',
  description: '',
  prix: 0,
  images: [] as File[],
  categories: [] as number[],
  date_debut: '',
  date_fin: '',
})

const errors = reactive({
  nom: false,
  description: false,
  prix: false,
  images: false,
  categories: false,
  date_debut: false,
  date_fin: false,
  general: '',
})

const checkUserPermission = async () => {
  if (!isAuthenticated()) {
    error.value = 'Vous devez être connecté pour modifier un produit.'
    hasPermission.value = false
    return
  }

  try {
    const user = await getCurrentUser()
    if (!user || !(user.role === 'ROLE_LOUEUR' || user.role === 'ROLE_ADMIN')) {
      error.value =
        "Vous n'avez pas les droits nécessaires pour modifier un produit. Seuls les loueurs et administrateurs peuvent le faire."
      hasPermission.value = false
    }
  } catch (err) {
    error.value = "Impossible de vérifier vos droits d'accès."
    hasPermission.value = false
  }
}

const fetchProduit = async () => {
  try {
    loading.value = true
    produit.value = await getProduitById(produitId)
    form.nom = produit.value.nom
    form.description = produit.value.description
    form.prix =
      typeof produit.value.prix === 'string' ? parseFloat(produit.value.prix) : produit.value.prix

    if (produit.value.disponibilite) {
      const dates = produit.value.disponibilite.split('-')
      if (dates.length >= 2) {
        const formatDate = (dateStr: string) => {
          if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
            return dateStr
          }
          try {
            const parts = dateStr.match(/(\d{4})(\d{2})(\d{2})/) || dateStr.split(/[^\d]/)
            if (parts && parts.length >= 3) {
              return `${parts[1]}-${parts[2]}-${parts[3]}`
            }
            const date = new Date(dateStr)
            if (!isNaN(date.getTime())) {
              return date.toISOString().split('T')[0]
            }
          } catch (e) {
            console.error('Erreur lors du formatage de la date:', e)
          }

          return ''
        }

        form.date_debut = formatDate(dates[0])
        form.date_fin = formatDate(dates[1])
      }
    }

    if (produit.value.categories && Array.isArray(produit.value.categories)) {
      form.categories = produit.value.categories.map((cat) =>
        typeof cat === 'object' && cat !== null ? cat.id : parseInt(cat.toString()),
      )
    }

    if (produit.value.images && Array.isArray(produit.value.images)) {
      form.images = produit.value.images.map((img) => new File([], img.name))
      imagePreviews.value = produit.value.images.map((img) => `http://localhost:8000/${img.path}`)
    }
  } catch (err: any) {
    error.value = err.message || `Erreur lors de la récupération du produit #${produitId}`
  } finally {
    loading.value = false
  }
}

const fetchCategories = async () => {
  try {
    loading.value = true
    categories.value = await getAllCategories()
  } catch (err: any) {
    error.value = err.message || 'Erreur lors du chargement des catégories. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}

const handleImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    form.images = Array.from(target.files)
    imageChanged.value = true
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
  if (imageChanged.value) {
    errors.images = form.images.length === 0
    if (errors.images) isValid = false
  }
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

    if (imageChanged.value && form.images.length === 0) {
      throw new Error('Au moins une image est requise')
    }
    const formatDate = (dateStr: string) => {
      if (!dateStr) return ''
      if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        return dateStr
      }

      try {
        const date = new Date(dateStr)
        if (!isNaN(date.getTime())) {
          return date.toISOString().split('T')[0]
        }
      } catch (e) {
        console.error('Erreur lors du formatage de la date:', e)
      }

      return dateStr
    }

    const produitData: ProduitUpdateData = {
      id: produitId,
      nom: form.nom,
      description: form.description,
      prix: form.prix,
      images: form.images,
      categories: form.categories,
      date_debut: formatDate(form.date_debut),
      date_fin: formatDate(form.date_fin),
      image_changed: imageChanged.value,
    }

    await updateProduit(produitData)
    success.value = true

    setTimeout(() => {
      router.push(`/produit/${produitId}`)
    }, 2000)
  } catch (err: any) {
    if (err.response && err.response.data && err.response.data.message) {
      error.value = err.response.data.message
    } else if (err.message) {
      error.value = err.message
    } else {
      error.value = 'Une erreur est survenue lors de la modification du produit.'
    }
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  await checkUserPermission()
  if (hasPermission.value) {
    await Promise.all([fetchProduit(), fetchCategories()])
  }
})
</script>

<template>
  <NavBar />
  <div class="max-w-4xl p-6 mx-auto mt-20 bg-white border-2 border-orange-300 rounded-lg shadow-md">
    <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">
      Modifier le produit
    </h1>

    <div v-if="loading" class="flex justify-center my-8">
      <div class="w-12 h-12 border-b-2 border-orange-500 rounded-full animate-spin"></div>
    </div>

    <div
      v-else-if="success"
      class="px-4 py-3 mb-4 text-green-700 bg-green-100 border border-green-400 rounded"
    >
      <p>Produit modifié avec succès ! Redirection en cours...</p>
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
          <label for="images" class="block mb-2 font-medium text-gray-700">Images</label>
          <input
            type="file"
            id="images"
            accept="image/*"
            multiple
            @change="handleImageChange"
            class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
            :class="{ 'border-red-500': errors.images }"
          />
          <p v-if="imageChanged && errors.images" class="mt-1 text-sm text-red-600">
            Au moins une image est requise si vous choisissez de la modifier
          </p>

          <div v-if="imagePreviews.length > 0" class="mt-2">
            <img
              v-for="(preview, index) in imagePreviews"
              :key="index"
              :src="preview"
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

        <div class="flex justify-between">
          <button
            type="button"
            class="px-6 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
            @click="router.push(`/produit/${produitId}`)"
          >
            Annuler
          </button>
          <button
            type="submit"
            class="px-6 py-2 text-white bg-orange-500 rounded-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
            :disabled="loading"
          >
            {{ loading ? 'Modification en cours...' : 'Enregistrer les modifications' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
