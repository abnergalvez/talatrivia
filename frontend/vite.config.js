import { fileURLToPath, URL } from 'node:url'

import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'


// https://vitejs.dev/config/
export default defineConfig({
  plugins: [
    vue(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  // AÑADE ESTA SECCIÓN PARA CONFIGURAR EL PUERTO
  server: {
    port: 8083, // ¡Este es el puerto deseado!
    strictPort: true, // Opcional: Si el puerto está ocupado, falla en lugar de usar el siguiente.
    watch: {
      usePolling: true, // Habilitar el sondeo
      interval: 1000,   // Revisar cada 1000ms (1 segundo)
      // Opcional, si tienes problemas de CPU, limita la profundidad de la búsqueda
      // ignored: ['**/node_modules/**'] 
    },
  }
})
