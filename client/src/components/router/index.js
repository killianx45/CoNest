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
      component: () => import('../Login.vue'),
    },
    {
      path: '/register',
      name: 'register',
      component: () => import('../Register.vue'),
    },
    {
      path: '/louer',
      name: 'louer',
      component: () => import('../Louer.vue'),
    },
    {
      path: '/contact',
      name: 'contact',
      component: () => import('../Contact.vue'),
    },
    {
      path: '/carte',
      name: 'carte',
      component: () => import('../Carte.vue'),
    },
    {
      path: '/favoris',
      name: 'favoris',
      component: () => import('../Favoris.vue'),
    },
  ],
})

export default router
