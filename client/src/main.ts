import './assets/main.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './components/router'

createApp(App).use(router).mount('#app')
