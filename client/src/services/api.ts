import type { AxiosInstance, AxiosRequestConfig } from 'axios'
import axios from 'axios'

const API_URL = 'http://localhost:8000/api'
const TOKEN_KEY = 'auth_token'

export const api: AxiosInstance = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/ld+json',
    Accept: 'application/ld+json',
  },
})

export const setupInterceptors = (): void => {
  api.interceptors.request.use(
    (config) => {
      const token = localStorage.getItem(TOKEN_KEY)
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }
      config.headers['Content-Type'] = 'application/ld+json'
      config.headers.Accept = 'application/ld+json'
      return config
    },
    (error) => {
      return Promise.reject(error)
    },
  )

  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response && error.response.status === 401) {
        logout()
      }
      return Promise.reject(error)
    },
  )
}

setupInterceptors()

export interface Produit {
  id: number
  nom: string
  description: string
  prix: string | number
  images?: string[]
  disponibilite?: string
  createdAt?: string
  updatedAt?: string
  user?: string
  commandes?: string[]
  categories?: any[]
}

export interface ProduitCreateData {
  nom: string
  description: string
  prix: number
  images: File[]
  categories: number[]
  date_debut: string
  date_fin: string
}

export interface ProduitUpdateData extends Omit<ProduitCreateData, 'images'> {
  id: number
  image_changed: boolean
  images?: File[]
}

export interface User {
  id: number
  email: string
  password?: string
  role: string
  telephone?: string
  name: string
  createdAt?: string
  updatedAt?: string
}

export interface Commande {
  id: number
  prix: number
  id_user: number
  client?: User
  produits?: Array<
    Produit & {
      pivot?: {
        date_reservation: string
        heure_debut: string
        heure_fin: string
      }
    }
  >
  createdAt?: string
  updatedAt?: string
}

export interface Category {
  id: number
  name: string
  description?: string
  createdAt?: string
  updatedAt?: string
}

export interface AuthResponse {
  access_token: string
  token_type: string
  expires_in: number
  user: User
}

export interface ApiResponse<T> {
  '@context'?: string
  '@id'?: string
  '@type'?: string
  totalItems?: number
  member: T[]
}

export const getAuthHeaders = (): Record<string, string> => {
  const token = localStorage.getItem(TOKEN_KEY)
  return token ? { Authorization: `Bearer ${token}` } : {}
}

export const getAllProduits = async (): Promise<Produit[]> => {
  try {
    const response = await api.get<ApiResponse<Produit>>('/produits', {
      params: {
        page: 1,
      },
    })
    if (response.data && response.data.member) {
      return response.data.member
    }
    return response.data as unknown as Produit[]
  } catch (error) {
    console.error('Erreur lors de la récupération des produits:', error)
    throw error
  }
}

// PRODUITS

export const getProduitById = async (id: number): Promise<Produit> => {
  try {
    const response = await api.get<Produit>(`/produits/${id}`)
    return response.data
  } catch (error) {
    console.error(`Erreur lors de la récupération du produit ${id}:`, error)
    throw error
  }
}

export const createProduit = async (produitData: ProduitCreateData): Promise<Produit> => {
  try {
    if (!isAuthenticated()) {
      throw new Error('Vous devez être connecté pour créer un produit')
    }
    const formData = new FormData()
    formData.append('nom', produitData.nom)
    formData.append('description', produitData.description)
    formData.append('prix', produitData.prix.toString())
    formData.append('date_debut', produitData.date_debut)
    formData.append('date_fin', produitData.date_fin)

    produitData.images.forEach((image, index) => {
      formData.append(`images[${index}]`, image)
    })

    produitData.categories.forEach((categoryId) => {
      formData.append('categories[]', categoryId.toString())
    })

    const config: AxiosRequestConfig = {
      headers: {
        'Content-Type': 'multipart/form-data',
        Accept: 'application/ld+json',
        Authorization: `Bearer ${localStorage.getItem(TOKEN_KEY)}`,
      },
    }

    const response = await api.post<Produit>('/produits/create', formData, config)
    return response.data
  } catch (error) {
    console.error('Erreur lors de la création du produit:', error)
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      await logout()
      throw new Error('Votre session a expiré. Veuillez vous reconnecter.')
    }
    if (axios.isAxiosError(error) && error.response?.status === 400) {
      throw new Error(error.response.data.message || 'Erreur de validation des données')
    }
    throw error
  }
}

export const updateProduit = async (produitData: ProduitUpdateData): Promise<Produit> => {
  try {
    if (!isAuthenticated()) {
      throw new Error('Vous devez être connecté pour modifier un produit')
    }
    const formData = new FormData()
    formData.append('nom', produitData.nom)
    formData.append('description', produitData.description)
    formData.append('prix', produitData.prix.toString())
    formData.append('date_debut', produitData.date_debut)
    formData.append('date_fin', produitData.date_fin)
    formData.append('image_changed', produitData.image_changed ? '1' : '0')

    if (produitData.image_changed && produitData.images) {
      produitData.images.forEach((image, index) => {
        formData.append(`images[${index}]`, image)
      })
    }

    if (produitData.categories && produitData.categories.length > 0) {
      produitData.categories.forEach((categoryId) => {
        formData.append('categories[]', categoryId.toString())
      })
    }

    const config: AxiosRequestConfig = {
      headers: {
        'Content-Type': 'multipart/form-data',
        Accept: 'application/json',
        Authorization: `Bearer ${localStorage.getItem(TOKEN_KEY)}`,
      },
    }

    const response = await axios.post<Produit>(
      `${API_URL}/produits/update/${produitData.id}`,
      formData,
      config,
    )

    return response.data
  } catch (error) {
    console.error('Erreur lors de la modification du produit:', error)
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      await logout()
      throw new Error('Votre session a expiré. Veuillez vous reconnecter.')
    }
    if (axios.isAxiosError(error) && error.response?.status === 400) {
      const errorMessage = error.response.data?.message || 'Erreur de validation des données'
      throw new Error(errorMessage)
    }
    if (axios.isAxiosError(error) && error.response?.status === 403) {
      throw new Error("Vous n'avez pas les droits nécessaires pour modifier ce produit.")
    }
    if (axios.isAxiosError(error) && error.response?.status === 500) {
      const errorMessage =
        error.response.data?.message ||
        'Une erreur serveur est survenue lors de la modification du produit.'
      throw new Error(errorMessage)
    }
    throw error
  }
}

// USERS

export const register = async (
  email: string,
  password: string,
  name: string,
  telephone: string,
): Promise<void> => {
  try {
    await api.post('/auth/register', {
      email,
      password,
      name,
      telephone,
    })
  } catch (error) {
    console.error("Erreur lors de l'inscription:", error)
    throw error
  }
}

export const login = async (
  email: string,
  password: string,
): Promise<{ user: User; token: string }> => {
  try {
    const response = await api.post<AuthResponse>('/auth/login', {
      email,
      password,
    })

    if (response.data && response.data.access_token) {
      localStorage.setItem(TOKEN_KEY, response.data.access_token)

      return {
        user: response.data.user,
        token: response.data.access_token,
      }
    }

    throw new Error("Aucun token d'authentification trouvé")
  } catch (error) {
    console.error('Erreur lors de la connexion:', error)
    throw error
  }
}

export const logout = async (): Promise<void> => {
  try {
    if (isAuthenticated()) {
      await api.post('/auth/logout')
    }
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error)
  } finally {
    localStorage.removeItem(TOKEN_KEY)
  }
}

export const isAuthenticated = (): boolean => {
  return localStorage.getItem(TOKEN_KEY) !== null
}

export const getCurrentUser = async (): Promise<User | null> => {
  try {
    if (!isAuthenticated()) {
      return null
    }

    const response = await api.get<User>('/me')
    return response.data
  } catch (error) {
    console.error('Erreur lors de la récupération des informations utilisateur:', error)
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      logout()
    }
    return null
  }
}

// COMMANDES
export interface CommandeCreateData {
  produits: number[]
  dates: string[]
  heures_debut: string[]
  heures_fin: string[]
}

export const getAllCommandes = async (): Promise<Commande[]> => {
  try {
    const response = await api.get<ApiResponse<Commande>>('/commandes_complete')
    if (response.data && response.data.member) {
      return response.data.member
    }
    return response.data as unknown as Commande[]
  } catch (error) {
    console.error('Erreur lors de la récupération des commandes:', error)
    throw error
  }
}

export const getCommandeById = async (id: number): Promise<Commande> => {
  try {
    const response = await api.get<Commande>(`/commandes_complete/${id}`)
    return response.data
  } catch (error) {
    console.error(`Erreur lors de la récupération de la commande ${id}:`, error)
    throw error
  }
}

export const deleteCommande = async (id: number): Promise<void> => {
  try {
    await api.delete(`/commandes/${id}`)
  } catch (error) {
    console.error(`Erreur lors de la suppression de la commande ${id}:`, error)
    throw error
  }
}

export const createCommande = async (commandeData: CommandeCreateData): Promise<Commande> => {
  try {
    if (!isAuthenticated()) {
      throw new Error('Vous devez être connecté pour créer une commande')
    }

    const response = await api.post<Commande>('/commandes/create', commandeData)
    return response.data
  } catch (error) {
    console.error('Erreur lors de la création de la commande:', error)
    if (axios.isAxiosError(error) && error.response?.status === 401) {
      await logout()
      throw new Error('Votre session a expiré. Veuillez vous reconnecter.')
    }
    if (axios.isAxiosError(error) && error.response?.status === 400) {
      throw new Error(error.response.data.message || 'Erreur de validation des données')
    }
    throw error
  }
}

export const verifierDisponibilite = async (
  produitId: number,
  date: string,
  heureDebut: string,
  heureFin: string,
): Promise<boolean> => {
  try {
    const response = await api.post('/commandes/verifier-disponibilite', {
      produit_id: produitId,
      date,
      heure_debut: heureDebut,
      heure_fin: heureFin,
    })
    return response.data.disponible
  } catch (error) {
    console.error('Erreur lors de la vérification de disponibilité:', error)
    return false
  }
}

// CATEGORIES

export const getAllCategories = async (): Promise<Category[]> => {
  try {
    const response = await api.get<ApiResponse<Category>>('/categories')
    if (response.data && response.data.member) {
      return response.data.member
    }
    return response.data as unknown as Category[]
  } catch (error) {
    console.error('Erreur lors de la récupération des catégories:', error)
    if (axios.isAxiosError(error)) {
      if (error.response?.status === 401) {
        throw new Error('Vous devez être connecté pour accéder aux catégories.')
      } else if (error.response?.status === 403) {
        throw new Error(
          "Vous n'avez pas les droits nécessaires pour accéder aux catégories. Seuls les loueurs et administrateurs peuvent y accéder.",
        )
      }
    }

    throw new Error('Erreur lors de la récupération des catégories. Veuillez réessayer.')
  }
}
