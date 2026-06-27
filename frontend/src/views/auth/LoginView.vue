<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-blue-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md">

      <!-- Logo -->
      <div class="text-center mb-8">
        <RouterLink to="/" class="inline-flex items-center gap-2">
          <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
            <span class="text-white font-bold">LS</span>
          </div>
          <span class="text-2xl font-bold text-gray-900">Laptop<span class="text-primary-600">Shop</span></span>
        </RouterLink>
        <p class="text-gray-500 mt-2 text-sm">Đăng nhập để tiếp tục mua sắm</p>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Đăng Nhập</h1>

        <!-- Error -->
        <div v-if="errorMsg" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="form-label">Email</label>
            <input v-model="form.email" type="email" placeholder="email@example.com"
              class="form-input" :class="{ 'border-red-400': errors.email }" required />
            <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
          </div>

          <div>
            <label class="form-label">Mật khẩu</label>
            <div class="relative">
              <input v-model="form.password" :type="showPass ? 'text' : 'password'"
                placeholder="Nhập mật khẩu" class="form-input pr-10"
                :class="{ 'border-red-400': errors.password }" required />
              <button type="button" @click="showPass = !showPass"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-lg">
                {{ showPass ? '🙈' : '👁️' }}
              </button>
            </div>
            <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
          </div>

          <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            {{ loading ? 'Đang đăng nhập...' : 'Đăng Nhập' }}
          </button>
        </form>

        <div class="my-5 flex items-center gap-3">
          <div class="flex-1 h-px bg-gray-200"></div>
          <span class="text-xs text-gray-400">hoặc</span>
          <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <div class="flex justify-end mb-1">
          <RouterLink to="/forgot-password" class="text-xs text-primary-600 hover:underline">Quên mật khẩu?</RouterLink>
        </div>

        <p class="text-center text-sm text-gray-500">
          Chưa có tài khoản?
          <RouterLink to="/register" class="text-primary-600 font-semibold hover:underline">Đăng ký ngay</RouterLink>
        </p>
      </div>

      <p class="text-center mt-4">
        <RouterLink to="/" class="text-sm text-gray-500 hover:text-primary-600">← Về trang chủ</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink, useRouter, useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'

const router    = useRouter()
const route     = useRoute()
const authStore = useAuthStore()
const toast     = useToast()

const form     = ref({ email: '', password: '' })
const loading  = ref(false)
const showPass = ref(false)
const errorMsg = ref('')
const errors   = ref({})

async function handleLogin() {
  loading.value  = true
  errorMsg.value = ''
  errors.value   = {}
  try {
    const user = await authStore.login(form.value)
    toast.success(`Chào mừng ${user.name}!`)
    if (user.role === 'admin') {
      router.push('/admin/dashboard')
    } else {
      router.push(route.query.redirect || '/')
    }
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value   = err.response.data.errors || {}
      errorMsg.value = err.response.data.message || 'Thông tin không hợp lệ.'
    } else if (err.response?.status === 403) {
      errorMsg.value = 'Tài khoản đã bị khóa. Liên hệ Admin.'
    } else {
      errorMsg.value = 'Đăng nhập thất bại. Vui lòng thử lại.'
    }
  } finally {
    loading.value = false
  }
}
</script>