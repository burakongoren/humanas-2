import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { fileURLToPath } from 'url'
import { dirname, resolve } from 'path'

const __filename = fileURLToPath(import.meta.url)
const __dirname = dirname(__filename)

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      // Add an alias for axios to our dummy module
      'axios': resolve(__dirname, 'src/fix-axios-issue.js')
    }
  },
  server: {
    proxy: {
      '/backend': {
        target: 'http://humanas-backend.infinityfreeapp.com',
        changeOrigin: true
      }
    }
  }
})
