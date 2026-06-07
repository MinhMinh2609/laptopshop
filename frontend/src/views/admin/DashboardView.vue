<template>
  <div class="min-h-screen bg-gradient-to-br from-primary-50 to-blue-100 flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md">

      <!-- Logo -->
      <div class="text-center mb-8">
        <RouterLink to="/" class="inline-flex items-center gap-2">
          <div class="w-10 h-10 bg-primary-600 rounded-xl flex items-center justify-center">
            <span class="text-white font-bold">LS</span>
          </div>
          <span class="text-2xl font-bold text-gray-900">Laptop<span class="text-primary-600">Shop</span></span>
        </RouterLink>
        <p class="text-gray-500 mt-2 text-sm">Tạo tài khoản để mua sắm dễ dàng hơn</p>
      </div>

      <!-- Form Card -->
      <div class="bg-white rounded-2xl shadow-lg p-8">
        <h1 class="text-xl font-bold text-gray-800 mb-6">Đăng Ký Tài Khoản</h1>

        <!-- Error -->
        <div v-if="errorMsg" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">
          {{ errorMsg }}
        </div>

        <form @submit.prevent="handleRegister" class="space-y-4">
          <!-- Họ tên -->
          <div>
            <label class="form-label">Họ và tên</label>
            <input v-model="form.name" type="text" placeholder="Nguyễn Văn A"
              class="form-input" :class="{ 'border-red-400': errors.name }" required />
            <p v-if="errors.name" class="form-error">{{ errors.name[0] }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="form-label">Email</label>
            <input v-model="form.email" type="email" placeholder="email@example.com"
              class="form-input" :class="{ 'border-red-400': errors.email }" required />
            <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
          </div>

          <!-- Phone -->
          <div>
            <label class="form-label">Số điện thoại <span class="text-gray-400">(tùy chọn)</span></label>
            <input v-model="form.phone" type="tel" placeholder="0912 345 678"
              class="form-input" />
          </div>

          <!-- Password -->
          <div>
            <label class="form-label">Mật khẩu</label>
            <div class="relative">
              <input v-model="form.password" :type="showPass ? 'text' : 'password'"
                placeholder="Tối thiểu 8 ký tự" class="form-input pr-10"
                :class="{ 'border-red-400': errors.password }" required />
              <button type="button" @click="showPass = !showPass"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-lg">
                {{ showPass ? '🙈' : '👁️' }}
              </button>
            </div>
            <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
          </div>

          <!-- Confirm Password -->
          <div>
            <label class="form-label">Xác nhận mật khẩu</label>
            <input v-model="form.password_confirmation" :type="showPass ? 'text' : 'password'"
              placeholder="Nhập lại mật khẩu" class="form-input"
              :class="{ 'border-red-400': passwordMismatch }" required />
            <p v-if="passwordMismatch" class="form-error">Mật khẩu xác nhận không khớp.</p>
          </div>

          <button type="submit" :disabled="loading || passwordMismatch"
            class="btn-primary w-full flex items-center justify-center gap-2 mt-2">
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            {{ loading ? 'Đang đăng ký...' : 'Tạo Tài Khoản' }}
          </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-5">
          Đã có tài khoản?
          <RouterLink to="/login" class="text-primary-600 font-semibold hover:underline">Đăng nhập</RouterLink>
        </p>
      </div>

      <p class="text-center mt-4">
        <RouterLink to="/" class="text-sm text-gray-500 hover:text-primary-600">← Về trang chủ</RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'

const router    = useRouter()
const authStore = useAuthStore()
const toast     = useToast()

const form     = ref({ name: '', email: '', phone: '', password: '', password_confirmation: '' })
const loading  = ref(false)
const showPass = ref(false)
const errorMsg = ref('')
const errors   = ref({})

const passwordMismatch = computed(() =>
  form.value.password_confirmation.length > 0 &&
  form.value.password !== form.value.password_confirmation
)

async function handleRegister() {
  if (passwordMismatch.value) return
  loading.value  = true
  errorMsg.value = ''
  errors.value   = {}
  try {
    const user = await authStore.register(form.value)
    toast.success(`Đăng ký thành công! Chào mừng ${user.name}!`)
    router.push('/')
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value   = err.response.data.errors || {}
      errorMsg.value = err.response.data.message || 'Thông tin không hợp lệ.'
    } else {
      errorMsg.value = 'Đăng ký thất bại. Vui lòng thử lại.'
    }
  } finally {
    loading.value = false
  }
}
</script>