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
      component: () => import('../views/OneProduit.vue'),
      props: true,
    },
  ],
})

export default router
