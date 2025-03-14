<script lang="ts">
import NavBar from '../../NavBar.vue'
import { defineComponent, ref, reactive, onMounted, PropType } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import {
  getAllCategories,
  getProduitById,
  updateProduit,
  Category,
  Produit,
  ProduitUpdateData,
  getCurrentUser,
  isAuthenticated,
} from '../../../services/api'

export default defineComponent({
  name: 'EditProduitPage',
  components: { NavBar },
  props: {
    id: {
      type: [String, Number],
      default: null,
    },
  },
  setup(props) {
    const router = useRouter()
    const route = useRoute()
    // Utiliser l'ID des props s'il est fourni, sinon utiliser l'ID de la route
    const produitId = props.id ? parseInt(props.id.toString()) : parseInt(route.params.id as string)

    const categories = ref<Category[]>([])
    const loading = ref(false)
    const error = ref('')
    const success = ref(false)
    const imagePreview = ref('')
    const hasPermission = ref(true) // Par défaut, on suppose que l'utilisateur a les permissions
    const imageChanged = ref(false)
    const produit = ref<Produit | null>(null)

    // Formulaire réactif pour le produit
    const form = reactive({
      nom: '',
      description: '',
      prix: 0,
      image: null as File | null,
      categories: [] as number[],
      date_debut: '',
      date_fin: '',
    })

    // Validation des erreurs
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

    // Vérifier si l'utilisateur a les droits nécessaires
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

    // Charger le produit à modifier
    const fetchProduit = async () => {
      try {
        loading.value = true
        produit.value = await getProduitById(produitId)

        // Remplir le formulaire avec les données du produit
        form.nom = produit.value.nom
        form.description = produit.value.description
        form.prix =
          typeof produit.value.prix === 'string'
            ? parseFloat(produit.value.prix)
            : produit.value.prix

        // Extraire les dates de disponibilité
        if (produit.value.disponibilite) {
          const dates = produit.value.disponibilite.split('-')
          if (dates.length >= 2) {
            // Formater les dates au format yyyy-MM-dd
            const formatDate = (dateStr: string) => {
              // Si la date est déjà au format yyyy-MM-dd, la retourner telle quelle
              if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
                return dateStr
              }

              // Sinon, essayer de la formater
              try {
                const parts = dateStr.match(/(\d{4})(\d{2})(\d{2})/) || dateStr.split(/[^\d]/)
                if (parts && parts.length >= 3) {
                  return `${parts[1]}-${parts[2]}-${parts[3]}`
                }

                // Si on ne peut pas formater, créer une date à partir de la chaîne
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

        // Récupérer les IDs des catégories
        if (produit.value.categories && Array.isArray(produit.value.categories)) {
          form.categories = produit.value.categories.map((cat) =>
            typeof cat === 'object' && cat !== null ? cat.id : parseInt(cat.toString()),
          )
        }

        // Prévisualisation de l'image
        if (produit.value.image) {
          imagePreview.value = produit.value.image
        }
      } catch (err: any) {
        error.value = err.message || `Erreur lors de la récupération du produit #${produitId}`
      } finally {
        loading.value = false
      }
    }

    // Charger toutes les catégories disponibles
    const fetchCategories = async () => {
      try {
        loading.value = true
        categories.value = await getAllCategories()
      } catch (err: any) {
        // Afficher le message d'erreur spécifique retourné par l'API
        error.value = err.message || 'Erreur lors du chargement des catégories. Veuillez réessayer.'
      } finally {
        loading.value = false
      }
    }

    // Gérer le changement d'image
    const handleImageChange = (event: Event) => {
      const target = event.target as HTMLInputElement
      if (target.files && target.files.length > 0) {
        form.image = target.files[0]
        imageChanged.value = true

        // Créer une prévisualisation de l'image
        const reader = new FileReader()
        reader.onload = (e) => {
          imagePreview.value = e.target?.result as string
        }
        reader.readAsDataURL(form.image)
      }
    }

    // Valider le formulaire
    const validateForm = (): boolean => {
      let isValid = true
      errors.general = ''

      // Valider le nom
      errors.nom = !form.nom.trim()
      if (errors.nom) isValid = false

      // Valider la description
      errors.description = !form.description.trim()
      if (errors.description) isValid = false

      // Valider le prix
      errors.prix = form.prix <= 0
      if (errors.prix) isValid = false

      // Valider l'image (seulement si elle a été changée)
      if (imageChanged.value) {
        errors.image = !form.image
        if (errors.image) isValid = false
      }

      // Valider les catégories
      errors.categories = form.categories.length === 0
      if (errors.categories) isValid = false

      // Valider la date de début
      errors.date_debut = !form.date_debut
      if (errors.date_debut) isValid = false

      // Valider la date de fin
      errors.date_fin = !form.date_fin
      if (errors.date_fin) isValid = false

      // Vérifier si la date de fin est après la date de début
      if (form.date_debut && form.date_fin && form.date_debut > form.date_fin) {
        errors.date_fin = true
        errors.general = 'La date de fin doit être après la date de début'
        isValid = false
      }

      return isValid
    }

    // Soumettre le formulaire
    const submitForm = async () => {
      if (!validateForm()) {
        return
      }

      try {
        loading.value = true

        if (imageChanged.value && !form.image) {
          throw new Error("L'image est requise")
        }

        // Formater les dates au format YYYY-MM-DD
        const formatDate = (dateStr: string) => {
          if (!dateStr) return ''

          // Si la date est déjà au format YYYY-MM-DD, la retourner telle quelle
          if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
            return dateStr
          }

          try {
            const date = new Date(dateStr)
            if (!isNaN(date.getTime())) {
              return date.toISOString().split('T')[0]
            }
          } catch (e) {
            // Ignorer les erreurs de formatage
          }

          return dateStr
        }

        // Préparer les données pour l'API
        const produitData: ProduitUpdateData = {
          id: produitId,
          nom: form.nom,
          description: form.description,
          prix: form.prix,
          image: form.image,
          categories: form.categories,
          date_debut: formatDate(form.date_debut),
          date_fin: formatDate(form.date_fin),
          image_changed: imageChanged.value,
        }

        await updateProduit(produitData)
        success.value = true

        // Rediriger vers la liste des produits après 2 secondes
        setTimeout(() => {
          router.push('/')
        }, 2000)
      } catch (err: any) {
        if (err.response && err.response.data && err.response.data.message) {
          error.value = err.response.data.message
        } else {
          error.value = err.message || 'Une erreur est survenue lors de la modification du produit.'
        }
      } finally {
        loading.value = false
      }
    }

    // Charger les données au montage du composant
    onMounted(async () => {
      await checkUserPermission()
      if (hasPermission.value) {
        await Promise.all([fetchProduit(), fetchCategories()])
      }
    })

    return {
      categories,
      form,
      errors,
      loading,
      error,
      success,
      imagePreview,
      imageChanged,
      handleImageChange,
      submitForm,
      hasPermission,
      produit,
    }
  },
})
</script>

<template>
  <NavBar />
  <div class="max-w-4xl p-6 mx-auto mt-20 bg-white border-2 border-orange-300 rounded-lg shadow-md">
    <h1 class="pb-2 mb-6 text-3xl font-bold text-black border-b-2 border-orange-200">
      Modifier un produit
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

      <form @submit.prevent="submitForm" class="space-y-4">
        <div class="mb-4">
          <label for="nom" class="block mb-2 font-semibold text-black">Nom</label>
          <input
            type="text"
            id="nom"
            v-model="form.nom"
            class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300"
            :class="{ 'border-red-500': errors.nom }"
          />
          <p v-if="errors.nom" class="mt-1 text-sm text-red-600">Le nom est requis</p>
        </div>

        <div class="mb-4">
          <label for="description" class="block mb-2 font-semibold text-black">Description</label>
          <textarea
            id="description"
            v-model="form.description"
            class="w-full h-32 p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300"
            :class="{ 'border-red-500': errors.description }"
          ></textarea>
          <p v-if="errors.description" class="mt-1 text-sm text-red-600">
            La description est requise
          </p>
        </div>

        <div class="mb-4">
          <label for="prix" class="block mb-2 font-semibold text-black">Prix</label>
          <input
            type="number"
            id="prix"
            v-model="form.prix"
            step="0.01"
            min="0"
            class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300"
            :class="{ 'border-red-500': errors.prix }"
          />
          <p v-if="errors.prix" class="mt-1 text-sm text-red-600">
            Le prix doit être supérieur à 0
          </p>
        </div>

        <div class="mb-4">
          <label for="image" class="block mb-2 font-semibold text-black">Image</label>

          <!-- Image actuelle -->
          <div v-if="imagePreview && !imageChanged" class="mb-4">
            <p class="mb-2 text-sm text-gray-600">Image actuelle :</p>
            <img :src="imagePreview" alt="Image actuelle" class="max-w-xs rounded-md max-h-48" />
          </div>

          <input
            type="file"
            id="image"
            @change="handleImageChange"
            accept="image/*"
            class="w-full p-2 bg-white border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300"
            :class="{ 'border-red-500': errors.image }"
          />
          <p class="mt-1 text-sm text-gray-500">
            Laissez ce champ vide pour conserver l'image actuelle
          </p>
          <p v-if="errors.image" class="mt-1 text-sm text-red-600">L'image est requise</p>

          <!-- Prévisualisation de la nouvelle image -->
          <div v-if="imagePreview && imageChanged" class="mt-2">
            <p class="mb-2 text-sm text-gray-600">Nouvelle image :</p>
            <img :src="imagePreview" alt="Prévisualisation" class="max-w-xs rounded-md max-h-48" />
          </div>
        </div>

        <div class="mb-4">
          <label for="categories" class="block mb-2 font-semibold text-black">Catégories</label>
          <select
            id="categories"
            v-model="form.categories"
            multiple
            class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300"
            :class="{ 'border-red-500': errors.categories }"
          >
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          <p class="mt-1 text-sm text-gray-500">
            Maintenez la touche Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs catégories
          </p>
          <p v-if="errors.categories" class="mt-1 text-sm text-red-600">
            Veuillez sélectionner au moins une catégorie
          </p>
        </div>

        <div class="mb-4">
          <label for="disponibilite" class="block mb-2 font-semibold text-black"
            >Disponibilité</label
          >
          <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
              <label for="date_debut" class="block mb-1 text-sm font-medium text-gray-700">
                Date de début
              </label>
              <input
                type="date"
                id="date_debut"
                v-model="form.date_debut"
                class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300"
                :class="{ 'border-red-500': errors.date_debut }"
              />
              <p v-if="errors.date_debut" class="mt-1 text-sm text-red-600">
                La date de début est requise
              </p>
            </div>
            <div>
              <label for="date_fin" class="block mb-1 text-sm font-medium text-gray-700">
                Date de fin
              </label>
              <input
                type="date"
                id="date_fin"
                v-model="form.date_fin"
                class="w-full p-2 border border-orange-200 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-300"
                :class="{ 'border-red-500': errors.date_fin }"
                :min="form.date_debut"
              />
              <p v-if="errors.date_fin" class="mt-1 text-sm text-red-600">
                La date de fin est requise
              </p>
            </div>
          </div>
          <p class="mt-1 text-sm text-gray-500">
            La période de disponibilité sera enregistrée au format "AAAA-MM-JJ-AAAA-MM-JJ"
          </p>
        </div>

        <div class="flex justify-end">
          <button
            type="button"
            @click="router.push('/produits')"
            class="px-4 py-2 mr-2 font-semibold text-gray-700 transition-colors bg-gray-200 rounded-md hover:bg-gray-300"
          >
            Annuler
          </button>
          <button
            type="submit"
            class="px-4 py-2 font-semibold text-white transition-colors bg-orange-500 rounded-md hover:bg-orange-600"
            :disabled="loading"
          >
            {{ loading ? 'Modification en cours...' : 'Modifier' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
