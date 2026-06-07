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

        <!-- Bước 1: Nhập email -->
        <div v-if="!sent">
          <h1 class="text-xl font-bold text-gray-800 mb-2">🔐 Quên Mật Khẩu</h1>
          <p class="text-gray-500 text-sm mb-6">Nhập email của bạn, chúng tôi sẽ gửi link đặt lại mật khẩu.</p>

          <div v-if="errorMsg" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
            {{ errorMsg }}
          </div>

          <form @submit.prevent="sendReset" class="space-y-4">
            <div>
              <label class="form-label">Email</label>
              <input v-model="email" type="email" class="form-input"
                placeholder="email@example.com" required />
            </div>
            <button type="submit" :disabled="loading" class="btn-primary w-full flex items-center justify-center gap-2">
              <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              {{ loading ? 'Đang gửi...' : 'Gửi Link Đặt Lại Mật Khẩu' }}
            </button>
          </form>
        </div>

        <!-- Bước 2: Đã gửi email -->
        <div v-else class="text-center">
          <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center text-3xl mx-auto mb-4">📧</div>
          <h2 class="text-xl font-bold text-gray-800 mb-2">Kiểm Tra Email!</h2>
          <p class="text-gray-500 text-sm mb-2">Chúng tôi đã gửi link đặt lại mật khẩu đến:</p>
          <p class="font-semibold text-primary-600 mb-6">{{ email }}</p>
          <p class="text-xs text-gray-400 mb-6">Không thấy email? Kiểm tra thư mục Spam hoặc thử lại sau vài phút.</p>
          <button @click="sent = false" class="btn-outline w-full">← Thử email khác</button>
        </div>

        <p class="text-center text-sm text-gray-500 mt-5">
          <RouterLink to="/login" class="text-primary-600 hover:underline">← Quay lại đăng nhập</RouterLink>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'

const email    = ref('')
const loading  = ref(false)
const sent     = ref(false)
const errorMsg = ref('')

async function sendReset() {
  loading.value  = true
  errorMsg.value = ''
  try {
    await api.post('/auth/forgot-password', { email: email.value })
    sent.value = true
  } catch (e) {
    errorMsg.value = e.response?.data?.message || 'Không tìm thấy email này trong hệ thống.'
  } finally {
    loading.value = false
  }
}
</script>