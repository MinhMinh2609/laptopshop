<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-blue-100 flex items-center justify-center px-4">
    <div class="w-full max-w-md">

      <div class="text-center mb-8">
        <RouterLink to="/" class="inline-flex items-center gap-2">
          <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
            <span class="text-white font-bold">LS</span>
          </div>
          <span class="text-2xl font-bold text-gray-900">Laptop<span class="text-primary-600">Shop</span></span>
        </RouterLink>
      </div>

      <div class="bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-2">🔑 Đặt Lại Mật Khẩu</h1>
        <p class="text-gray-500 text-sm mb-6">Nhập mật khẩu mới cho tài khoản của bạn.</p>

        <div v-if="errorMsg" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ errorMsg }}</div>
        <div v-if="success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
          ✅ Đặt lại mật khẩu thành công! <RouterLink to="/login" class="underline font-semibold">Đăng nhập ngay</RouterLink>
        </div>

        <form v-if="!success" @submit.prevent="handleReset" class="space-y-4">
          <div>
            <label class="form-label">Email</label>
            <input v-model="form.email" type="email" class="form-input" placeholder="email@example.com" required />
          </div>
          <div>
            <label class="form-label">Mật khẩu mới</label>
            <input v-model="form.password" type="password" class="form-input" placeholder="Tối thiểu 8 ký tự" required />
          </div>
          <div>
            <label class="form-label">Xác nhận mật khẩu mới</label>
            <input v-model="form.password_confirmation" type="password" class="form-input" required />
            <p v-if="mismatch" class="form-error">Mật khẩu không khớp.</p>
          </div>
          <button type="submit" :disabled="loading || mismatch" class="btn-primary w-full flex items-center justify-center gap-2">
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            {{ loading ? 'Đang xử lý...' : 'Đặt Lại Mật Khẩu' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-5">
          <RouterLink to="/login" class="text-primary-600 hover:underline">← Quay lại đăng nhập</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import api from '@/services/api'

const route    = useRoute()
const loading  = ref(false)
const success  = ref(false)
const errorMsg = ref('')

const form = ref({
  email: route.query.email || '',
  token: route.query.token || '',
  password: '',
  password_confirmation: '',
})

const mismatch = computed(() =>
  form.value.password_confirmation.length > 0 &&
  form.value.password !== form.value.password_confirmation
)

async function handleReset() {
  if (mismatch.value) return
  loading.value  = true
  errorMsg.value = ''
  try {
    await api.post('/auth/reset-password', form.value)
    success.value = true
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Token không hợp lệ hoặc đã hết hạn.'
  } finally { loading.value = false }
}
</script>