// vite.config.js
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  plugins: [vue()],

  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },

  server: {
    host: '0.0.0.0',
    port: 5173,
    // Proxy API calls đến Laravel (tránh CORS khi dev)
    proxy: {
      '/api': {
        target: 'http://nginx:80',   // Tên service nginx trong Docker
        changeOrigin: true,
        secure: false,
      },
    },
    watch: {
      usePolling: true,  // Cần thiết cho hot-reload trong Docker volume
      interval: 500,
    },
  },

  build: {
    outDir: 'dist',
    sourcemap: false,
    chunkSizeWarningLimit: 1600,
  },
})
