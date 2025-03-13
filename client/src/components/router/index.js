import { createRouter, createWebHistory } from 'vue-router'
import Home from '../Home.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
    },
    {
      path: '/login',
      name: 'login',
      component: () => import('../views/Login.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../views/Register.vue'),
    },
    {
      path: '/produit/:id',
      name: 'produit',
      component: () => import('../views/produits/ShowProduit.vue'),
      props: true,
    },
    {
      path: '/produit/create',
      name: 'createProduit',
      component: () => import('../views/produits/CreateProduit.vue'),
    },
    {
      path: '/produit/:id/edit',
      name: 'editProduit',
      component: () => import('../views/produits/EditProduit.vue'),
      props: true,
    },
    {
      path: '/commande/create',
      name: 'createCommande',
      component: () => import('../views/commandes/CreateCommande.vue'),
    },
    {
      path: '/commande/:id',
      name: 'showCommande',
      component: () => import('../views/commandes/ShowCommande.vue'),
      props: true,
    },
  ],
})

export default router
