// src/main.js (Versión más segura)
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'

// Asegúrate de usar estas dos líneas de importación de ruta completa:
import 'bootstrap/dist/css/bootstrap.min.css' 
import 'bootstrap/dist/js/bootstrap.bundle.min.js' 
import 'bootstrap-icons/font/bootstrap-icons.css';

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

app.mount('#tala-trivia-app')