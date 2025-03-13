import axios from 'axios'

const API_URL = 'http://localhost:8000/api'
const TOKEN_KEY = 'auth_token'

export const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/ld+json',
    Accept: 'application/ld+json',
  },
})

export const setupInterceptors = () => {
  api.interceptors.request.use(
    (config) => {
      const token = localStorage.getItem(TOKEN_KEY)
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }
      // S'assurer que les headers sont toujours présents
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
  image?: string
  disponibilite?: string
  createdAt?: string
  updatedAt?: string
  user?: string
  commandes?: string[]
  categories?: any[]
}

export interface User {
  id: number
  email: string
  password: string
  roles: string[]
  telephone: string
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

export const getAuthHeaders = () => {
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

export const getProduitById = async (id: number): Promise<Produit> => {
  try {
    const response = await api.get<Produit>(`/produits/${id}`)
    return response.data
  } catch (error) {
    console.error(`Erreur lors de la récupération du produit ${id}:`, error)
    throw error
  }
}

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

    const response = await api.get<User>('/auth/me')

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
