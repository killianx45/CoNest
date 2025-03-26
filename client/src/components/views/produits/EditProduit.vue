<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  getAllCategories,
  getCurrentUser,
  getProduitById,
  isAuthenticated,
  updateProduit,
  type Category,
  type Produit,
  type ProduitUpdateData,
} from '../../../services/api'
import NavBar from '../../NavBar.vue'

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
    form.nom = produit.value.nom || ''
    form.description = produit.value.description || ''
    form.prix =
      typeof produit.value.prix === 'string'
        ? parseFloat(produit.value.prix)
        : produit.value.prix || 0
    form.adresse = produit.value.adresse || ''

    if (produit.value.disponibilite) {
      try {
        const regex = /^(\d{4}-\d{2}-\d{2})-(\d{4}-\d{2}-\d{2})$/
        const matches = produit.value.disponibilite.match(regex)

        if (matches && matches.length >= 3) {
          form.date_debut = matches[1]
          form.date_fin = matches[2]
        } else {
          const disponibiliteParts = produit.value.disponibilite.split('-')

          if (disponibiliteParts.length === 6) {
            form.date_debut = `${disponibiliteParts[0]}-${disponibiliteParts[1]}-${disponibiliteParts[2]}`
            form.date_fin = `${disponibiliteParts[3]}-${disponibiliteParts[4]}-${disponibiliteParts[5]}`
          } else if (disponibiliteParts.length >= 2) {
            if (disponibiliteParts[0]) {
              const dateTemp = disponibiliteParts[0].trim()
              if (dateTemp.includes('/')) {
                const dateParts = dateTemp.split('/')
                if (dateParts.length === 3) {
                  form.date_debut = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`
                }
              } else if (/^\d{4}-\d{2}-\d{2}$/.test(dateTemp)) {
                form.date_debut = dateTemp
              } else {
                try {
                  const date = new Date(dateTemp)
                  if (!isNaN(date.getTime())) {
                    form.date_debut = date.toISOString().split('T')[0]
                  }
                } catch (e) {}
              }
            }

            if (disponibiliteParts[1]) {
              const dateTemp = disponibiliteParts[1].trim()
              if (dateTemp.includes('/')) {
                const dateParts = dateTemp.split('/')
                if (dateParts.length === 3) {
                  form.date_fin = `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`
                }
              } else if (/^\d{4}-\d{2}-\d{2}$/.test(dateTemp)) {
                form.date_fin = dateTemp
              } else {
                try {
                  const date = new Date(dateTemp)
                  if (!isNaN(date.getTime())) {
                    form.date_fin = date.toISOString().split('T')[0]
                  }
                } catch (e) {}
              }
            }
          }
        }
      } catch (e) {
        form.date_debut = ''
        form.date_fin = ''
      }
    }

    if (produit.value.categories) {
      let categoryIds: number[] = []

      if (typeof produit.value.categories === 'string') {
        try {
          const parsedCategories = JSON.parse(produit.value.categories)
          categoryIds = parsedCategories
            .map((cat: any) => (typeof cat === 'number' ? cat : cat.id || null))
            .filter((id: number | null) => id !== null)
        } catch (e) {}
      } else if (Array.isArray(produit.value.categories)) {
        categoryIds = produit.value.categories
          .map((cat: any) => {
            if (typeof cat === 'number') {
              return cat
            }

            if (typeof cat === 'object' && cat !== null && cat.id) {
              return cat.id
            }

            if (typeof cat === 'string' && cat.includes('/api/categories/')) {
              const matches = cat.match(/\/api\/categories\/(\d+)/)
              if (matches && matches[1]) {
                return parseInt(matches[1], 10)
              }
            }

            return null
          })
          .filter((id: number | null): id is number => id !== null)
      }

      form.categories = categoryIds
    } else {
      form.categories = []
    }

    if (produit.value.images && Array.isArray(produit.value.images)) {
      imagePreviews.value = produit.value.images.map(
        (img: string) => `http://localhost:8000/${img}`,
      )
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
  errors.adresse = !form.adresse.trim()
  if (errors.adresse) isValid = false
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
    error.value = ''
    if (imageChanged.value && form.images.length === 0) {
      error.value = 'Au moins une image est requise si vous choisissez de la modifier'
      return
    }
    const formatDate = (dateStr: string) => {
      if (!dateStr) return ''
      if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
        return dateStr
      }

      try {
        const date = new Date(dateStr)
        if (!isNaN(date.getTime())) {
          const year = date.getFullYear()
          const month = String(date.getMonth() + 1).padStart(2, '0')
          const day = String(date.getDate()).padStart(2, '0')
          return `${year}-${month}-${day}`
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
      adresse: form.adresse,
      categories: form.categories,
      date_debut: formatDate(form.date_debut),
      date_fin: formatDate(form.date_fin),
      image_changed: imageChanged.value,
    }

    if (imageChanged.value) {
      produitData.images = form.images
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
  <section class="flex items-center justify-center min-h-screen bg-[#FFF1E9] formu">
    <div class="w-full max-w-3xl p-12 mx-8 mt-10 mb-10 bg-orange-100 shadow-md rounded-xl">
      <a :href="'/produit/' + produitId" class="block mb-4 text-gray-500"><p>&lt; retour</p></a>
      <h1 class="mb-10 text-2xl font-semibold text-center text-blue-950">Modifier un bien</h1>

      <div v-if="loading" class="flex justify-center my-10">
        <div class="w-12 h-12 border-b-2 border-orange-500 rounded-xl animate-spin"></div>
      </div>

      <div
        v-else-if="success"
        class="px-6 py-4 mb-6 text-green-700 bg-green-100 border border-green-400 rounded-xl"
      >
        <p>Produit modifié avec succès ! Redirection en cours...</p>
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
              class="w-full p-3 border border-gray-300 rounded-xl bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
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
              class="w-full p-3 border border-gray-300 rounded-xl bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
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
              class="w-full p-3 border border-gray-300 rounded-xl bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
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
                      class="w-5 h-5 text-orange-600 border-gray-300 rounded-xl"
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
              class="w-full p-3 border border-gray-300 rounded-xl bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
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
              class="w-full p-3 border border-gray-300 rounded-xl bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
              :class="{ 'border-red-500': errors.images }"
            />
            <p v-if="imageChanged && errors.images" class="mt-2 text-sm text-red-600">
              Au moins une image est requise si vous choisissez de la modifier
            </p>

            <div v-if="imagePreviews.length > 0" class="mt-4 space-y-2">
              <img
                v-for="(preview, index) in imagePreviews"
                :key="index"
                :src="preview"
                alt="Prévisualisation"
                class="object-cover w-full h-48 mb-4 rounded-xl"
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
                class="w-full p-3 border border-gray-300 rounded-xl bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
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
                class="w-full p-3 border border-gray-300 rounded-xl bg-orange-50 focus:outline-none focus:ring-1 focus:ring-primary"
                :class="{ 'border-red-500': errors.date_fin }"
              />
              <p v-if="errors.date_fin" class="mt-2 text-sm text-red-600">
                La date de fin est requise
              </p>
            </div>
          </div>

          <div class="flex justify-between pt-4 mt-8">
            <button
              type="button"
              class="px-6 py-3 text-gray-700 bg-gray-200 rounded-xl hover:bg-gray-300 focus:outline-none"
              @click="router.push(`/produit/${produitId}`)"
            >
              Annuler
            </button>
            <button
              type="submit"
              class="px-6 py-3 text-white transition bg-orange-500 rounded-xl hover:bg-orange-300"
              :disabled="loading"
            >
              {{ loading ? 'Modification en cours...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </section>
</template>
