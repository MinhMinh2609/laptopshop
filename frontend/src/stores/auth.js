// src/stores/auth.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  // ─── State ─────────────────────────────────────────────
  const user        = ref(null)
  const token       = ref(null)
  const initialized = ref(false)

  // ─── Getters ───────────────────────────────────────────
  const isLoggedIn = computed(() => !!token.value && !!user.value)
  const isAdmin    = computed(() => user.value?.role === 'admin')
  const isUser     = computed(() => user.value?.role === 'user')
  const userName   = computed(() => user.value?.name ?? '')
  const userAvatar = computed(() => user.value?.avatar ?? null)

  // ─── Actions ───────────────────────────────────────────

  // Khởi tạo từ localStorage (khi app load)
  async function initialize() {
    const savedToken = localStorage.getItem('auth_token')
    const savedUser  = localStorage.getItem('auth_user')

    if (savedToken && savedUser) {
      token.value = savedToken
      user.value  = JSON.parse(savedUser)

      // Xác thực token với server
      try {
        const res = await api.get('/auth/me')
        user.value = res.data.data
        localStorage.setItem('auth_user', JSON.stringify(user.value))
      } catch {
        // Token hết hạn → logout
        logout()
      }
    }

    initialized.value = true
  }

  // Đăng nhập
  async function login(credentials) {
    const res  = await api.post('/auth/login', credentials)
    const data = res.data.data

    token.value = data.token
    user.value  = data.user

    // Lưu vào localStorage
    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))

    // Set header cho axios
    api.defaults.headers.common['Authorization'] = `Bearer ${data.token}`

    return data.user
  }

  // Đăng ký
  async function register(userData) {
    const res  = await api.post('/auth/register', userData)
    const data = res.data.data

    token.value = data.token
    user.value  = data.user

    localStorage.setItem('auth_token', data.token)
    localStorage.setItem('auth_user', JSON.stringify(data.user))
    api.defaults.headers.common['Authorization'] = `Bearer ${data.token}`

    return data.user
  }

  // Đăng xuất
  async function logout() {
    try {
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch { /* bỏ qua lỗi */ }

    token.value = null
    user.value  = null

    localStorage.removeItem('auth_token')
    localStorage.removeItem('auth_user')
    delete api.defaults.headers.common['Authorization']
  }

  // Cập nhật thông tin user (sau khi sửa profile)
  function updateUser(newData) {
    user.value = { ...user.value, ...newData }
    localStorage.setItem('auth_user', JSON.stringify(user.value))
  }

  return {
    // State
    user, token, initialized,
    // Getters
    isLoggedIn, isAdmin, isUser, userName, userAvatar,
    // Actions
    initialize, login, register, logout, updateUser,
  }
})
