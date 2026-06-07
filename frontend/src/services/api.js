// src/services/api.js
import axios from 'axios'
import { useToast } from 'vue-toastification'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL ?? '/api',
  timeout: 30000,
  headers: {
    'Content-Type': 'application/json',
    'Accept':       'application/json',
    'X-Requested-With': 'XMLHttpRequest',
  },
  withCredentials: true,
})

// ─── Request Interceptor ───────────────────────────────────
// Tự động gắn token vào mọi request
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// ─── Response Interceptor ──────────────────────────────────
// Xử lý lỗi tập trung
api.interceptors.response.use(
  (response) => response,
  (error) => {
    const toast = useToast()
    const status = error.response?.status

    if (status === 401) {
      // Token hết hạn → xóa và redirect login
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      delete api.defaults.headers.common['Authorization']

      if (window.location.pathname !== '/login') {
        window.location.href = '/login?session=expired'
      }
      return Promise.reject(error)
    }

    if (status === 403) {
      toast.error('Bạn không có quyền thực hiện thao tác này.')
      return Promise.reject(error)
    }

    if (status === 422) {
      // Validation errors - xử lý ở component
      return Promise.reject(error)
    }

    if (status === 404) {
      toast.error('Không tìm thấy tài nguyên yêu cầu.')
      return Promise.reject(error)
    }

    if (status >= 500) {
      toast.error('Lỗi máy chủ. Vui lòng thử lại sau.')
      return Promise.reject(error)
    }

    return Promise.reject(error)
  }
)

export default api
