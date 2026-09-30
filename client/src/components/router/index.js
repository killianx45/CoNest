import Commandes from '@/components/views/commandes/AllCommande.vue'
import MentionsLegales from '@/components/views/MentionsLegales.vue'
import Requis from '@/components/views/RequisView.vue'
import Concours from '@/components/views/users/ConcoursView.vue'
import Login from '@/components/views/users/LoginView.vue'
import Register from '@/components/views/users/RegisterView.vue'
import { isAuthenticated } from '@/services/api'
import { createRouter, createWebHistory } from 'vue-router'
import Home from '../HomeView.vue'

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
      component: Login,
      beforeEnter: (to, from, next) => {
        if (isAuthenticated()) {
          next({ path: '/' })
        } else {
          next()
        }
      },
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      beforeEnter: (to, from, next) => {
        if (isAuthenticated()) {
          next({ path: '/' })
        } else {
          next()
        }
      },
    },
    {
      path: '/mentions-legales',
      name: 'mentionsLegales',
      component: MentionsLegales,
    },
    {
      path: '/requis',
      name: 'requis',
      component: Requis,
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
      path: '/commandes',
      name: 'allCommandes',
      component: Commandes,
      beforeEnter: (to, from, next) => {
        if (!isAuthenticated()) {
          next({ path: '/login' })
        } else {
          next()
        }
      },
    },
    {
      path: '/commandes/:id',
      name: 'showCommande',
      component: () => import('../views/commandes/ShowCommande.vue'),
      props: true,
    },
    {
      path: '/commandes/create',
      name: 'createCommande',
      component: () => import('../views/commandes/createCommande.vue'),
    },
    {
      path: '/concours',
      name: 'concours',
      component: Concours,
      beforeEnter: (to, from, next) => {
        if (!isAuthenticated()) {
          next({ path: '/login' })
        } else {
          next()
        }
      },
    },
    {
      path: '/:pathMatch(.*)*',
      name: 'notFound',
      component: () => import('../views/NotFound.vue'),
    },
  ],
})

export default router
