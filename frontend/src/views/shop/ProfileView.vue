<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">👤 Hồ Sơ Cá Nhân</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Sidebar -->
      <div class="md:col-span-1">
        <div class="bg-white rounded-2xl border p-5 text-center">
          <div class="relative inline-block mb-3">
            <div class="w-20 h-20 rounded-full bg-primary-100 flex items-center justify-center text-3xl font-bold text-primary-600 mx-auto overflow-hidden">
              <img v-if="authStore.user?.avatar" :src="authStore.user.avatar" class="w-full h-full object-cover" />
              <span v-else>{{ authStore.userName[0]?.toUpperCase() }}</span>
            </div>
          </div>
          <p class="font-bold text-gray-800">{{ authStore.userName }}</p>
          <p class="text-sm text-gray-500">{{ authStore.user?.email }}</p>
          <span class="mt-2 inline-block badge" :class="authStore.isAdmin ? 'badge-purple' : 'badge-info'">
            {{ authStore.isAdmin ? '👑 Admin' : '👤 Khách hàng' }}
          </span>
        </div>

        <!-- Quick links -->
        <div class="bg-white rounded-2xl border mt-4 overflow-hidden">
          <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm text-left transition border-b border-gray-50 last:border-0"
            :class="activeTab === tab.key ? 'bg-primary-50 text-primary-600 font-semibold' : 'text-gray-600 hover:bg-gray-50'">
            <span>{{ tab.icon }}</span> {{ tab.label }}
          </button>
        </div>
      </div>

      <!-- Content -->
      <div class="md:col-span-2">

        <!-- Tab: Thông tin -->
        <div v-if="activeTab === 'info'" class="bg-white rounded-2xl border p-6">
          <h3 class="font-bold text-gray-800 mb-5">Thông Tin Cá Nhân</h3>

          <div v-if="success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
            ✅ {{ success }}
          </div>

          <form @submit.prevent="updateProfile" class="space-y-4">
            <div>
              <label class="form-label">Họ và tên</label>
              <input v-model="profileForm.name" class="form-input" placeholder="Họ và tên" />
            </div>
            <div>
              <label class="form-label">Email</label>
              <input :value="authStore.user?.email" class="form-input bg-gray-50" disabled />
              <p class="form-error text-gray-400 text-xs">Email không thể thay đổi</p>
            </div>
            <div>
              <label class="form-label">Số điện thoại</label>
              <input v-model="profileForm.phone" class="form-input" placeholder="0912 345 678" />
            </div>
            <div>
              <label class="form-label">Địa chỉ</label>
              <textarea v-model="profileForm.address" class="form-input" rows="2" placeholder="Địa chỉ của bạn"></textarea>
            </div>
            <button type="submit" :disabled="saving" class="btn-primary">
              {{ saving ? 'Đang lưu...' : '💾 Lưu Thay Đổi' }}
            </button>
          </form>
        </div>

        <!-- Tab: Đổi mật khẩu -->
        <div v-if="activeTab === 'password'" class="bg-white rounded-2xl border p-6">
          <h3 class="font-bold text-gray-800 mb-5">🔒 Đổi Mật Khẩu</h3>

          <div v-if="pwError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-600">{{ pwError }}</div>
          <div v-if="pwSuccess" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">✅ {{ pwSuccess }}</div>

          <form @submit.prevent="changePassword" class="space-y-4">
            <div>
              <label class="form-label">Mật khẩu hiện tại</label>
              <input v-model="pwForm.current_password" type="password" class="form-input" required />
            </div>
            <div>
              <label class="form-label">Mật khẩu mới</label>
              <input v-model="pwForm.password" type="password" class="form-input" placeholder="Tối thiểu 8 ký tự" required />
            </div>
            <div>
              <label class="form-label">Xác nhận mật khẩu mới</label>
              <input v-model="pwForm.password_confirmation" type="password" class="form-input" required />
            </div>
            <button type="submit" :disabled="pwSaving" class="btn-primary">
              {{ pwSaving ? 'Đang đổi...' : '🔐 Đổi Mật Khẩu' }}
            </button>
          </form>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

const authStore = useAuthStore()
const toast     = useToast()
const activeTab = ref('info')

const tabs = [
  { key: 'info',     icon: '👤', label: 'Thông tin cá nhân' },
  { key: 'password', icon: '🔒', label: 'Đổi mật khẩu' },
]

// Profile form
const saving      = ref(false)
const success     = ref('')
const profileForm = ref({ name: '', phone: '', address: '' })

onMounted(() => {
  profileForm.value = {
    name:    authStore.user?.name    || '',
    phone:   authStore.user?.phone   || '',
    address: authStore.user?.address || '',
  }
})

async function updateProfile() {
  saving.value  = true
  success.value = ''
  try {
    const res = await api.put('/profile', profileForm.value)
    authStore.updateUser(res.data.data)
    success.value = 'Cập nhật thông tin thành công!'
  } catch (e) { toast.error(e.response?.data?.message || 'Cập nhật thất bại.') }
  finally { saving.value = false }
}

// Password form
const pwSaving  = ref(false)
const pwError   = ref('')
const pwSuccess = ref('')
const pwForm    = ref({ current_password: '', password: '', password_confirmation: '' })

async function changePassword() {
  pwError.value   = ''
  pwSuccess.value = ''
  if (pwForm.value.password !== pwForm.value.password_confirmation) {
    pwError.value = 'Mật khẩu xác nhận không khớp.'; return
  }
  pwSaving.value = true
  try {
    await api.post('/profile/change-password', pwForm.value)
    pwSuccess.value = 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.'
    pwForm.value = { current_password: '', password: '', password_confirmation: '' }
    setTimeout(() => authStore.logout(), 2000)
  } catch (e) {
    pwError.value = e.response?.data?.errors?.current_password?.[0] || e.response?.data?.message || 'Đổi mật khẩu thất bại.'
  } finally { pwSaving.value = false }
}
</script>