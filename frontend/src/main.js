// src/main.js
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Toast, { POSITION } from 'vue-toastification'
import 'vue-toastification/dist/index.css'

import App from './App.vue'
import router from './router'
import './assets/main.css'

const app = createApp(App)

// ─── Plugins ───────────────────────────────────────────────
app.use(createPinia())
app.use(router)
app.use(Toast, {
  position:     POSITION.TOP_RIGHT,
  timeout:      3000,
  closeOnClick: true,
  pauseOnHover: true,
  maxToasts:    5,
})

// ─── Global Filters ────────────────────────────────────────
app.config.globalProperties.$filters = {
  // Format tiền VNĐ: 25000000 → "25.000.000 ₫"
  currency(value) {
    if (!value && value !== 0) return ''
    return new Intl.NumberFormat('vi-VN', {
      style:    'currency',
      currency: 'VND',
    }).format(value)
  },
  // Format ngày: "2024-01-15T..." → "15/01/2024"
  date(value) {
    if (!value) return ''
    return new Date(value).toLocaleDateString('vi-VN')
  },
  // Format ngày giờ
  datetime(value) {
    if (!value) return ''
    return new Date(value).toLocaleString('vi-VN')
  },
}

app.mount('#app')
