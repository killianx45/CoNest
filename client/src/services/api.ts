import axios from 'axios'

const API_URL = 'http://localhost:8000/api'

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

// Interface pour la réponse paginée de l'API Platform
export interface ApiResponse<T> {
  '@context'?: string
  '@id'?: string
  '@type'?: string
  totalItems?: number
  member: T[]
}

export const getAllProduits = async (): Promise<Produit[]> => {
  try {
    const response = await axios.get<ApiResponse<Produit>>(`${API_URL}/produits`, {
      headers: {
        Accept: 'application/ld+json',
      },
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
