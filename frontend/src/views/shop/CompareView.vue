<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">⚖️ So Sánh Laptop</h1>
    <p class="text-gray-500 text-sm mb-6">Chọn tối đa 4 sản phẩm để so sánh</p>

    <!-- Tìm và thêm sản phẩm -->
    <div class="bg-white rounded-2xl border p-5 mb-6">
      <div class="relative flex-1">
        <input v-model="searchQ" @input="searchProducts" type="text"
          placeholder="Tìm sản phẩm để so sánh..." class="form-input w-full" />

        <!-- Dropdown kết quả tìm kiếm -->
        <div v-if="searchResults.length && searchQ"
          class="absolute top-full left-0 right-0 mt-1 bg-white rounded-xl shadow-lg border z-10 max-h-60 overflow-y-auto">
          <button v-for="p in searchResults" :key="p.id" @click="addProduct(p)"
            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 text-left border-b border-gray-50 last:border-0">
            <!-- Dùng imageUrl cho ảnh dropdown -->
            <img
              :src="imageUrl(p.thumbnail || p.images?.[0]?.image_path)"
              class="w-10 h-10 rounded-lg object-contain bg-gray-50 p-1 flex-shrink-0"
              @error="$event.target.src='/placeholder.svg'"
            />
            <div>
              <p class="text-sm font-medium text-gray-800 line-clamp-1">{{ p.name }}</p>
              <p class="text-xs text-primary-600 font-semibold">{{ formatPrice(p.sale_price || p.price) }}</p>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Không có sản phẩm -->
    <div v-if="!selected.length" class="text-center py-16 text-gray-400">
      <p class="text-5xl mb-4">⚖️</p>
      <p class="text-lg">Chưa có sản phẩm nào để so sánh</p>
      <p class="text-sm mt-2">Tìm và thêm sản phẩm ở ô tìm kiếm phía trên</p>
    </div>

    <!-- Bảng so sánh -->
    <div v-else class="overflow-x-auto">
      <table class="w-full min-w-max">
        <thead>
          <tr>
            <td class="w-40 p-3 bg-gray-50 rounded-tl-2xl font-semibold text-gray-600 text-sm">Thông số</td>

            <td v-for="p in selected" :key="p.id"
              class="p-3 bg-white border-l border-gray-100 text-center min-w-52 align-top">
              <div class="relative">
                <!-- Nút xóa -->
                <button @click="removeProduct(p.id)"
                  class="absolute -top-1 -right-1 w-6 h-6 bg-red-100 text-red-500 rounded-full
                         text-xs hover:bg-red-200 flex items-center justify-center">✕</button>

                <!-- Ảnh sản phẩm — dùng imageUrl -->
                <img
                  :src="imageUrl(p.thumbnail || p.images?.[0]?.image_path)"
                  :alt="p.name"
                  class="w-28 h-28 object-contain mx-auto mb-2 rounded-xl bg-gray-50 p-2"
                  @error="$event.target.src='/placeholder.svg'"
                />

                <p class="text-sm font-semibold text-gray-800 line-clamp-2">{{ p.name }}</p>
                <p class="text-primary-600 font-bold mt-1 text-sm">{{ formatPrice(p.sale_price || p.price) }}</p>

                <button @click="addToCart(p)"
                  class="mt-2 text-xs px-4 py-1.5 bg-primary-600 text-white rounded-full hover:bg-primary-700 transition">
                  + Giỏ hàng
                </button>
              </div>
            </td>

            <!-- Slot trống -->
            <td v-if="selected.length < 4"
              class="p-3 bg-gray-50 border-l border-dashed border-gray-200 text-center min-w-40">
              <div class="py-8 text-gray-300">
                <p class="text-3xl mb-1">+</p>
                <p class="text-xs">Thêm sản phẩm</p>
              </div>
            </td>
          </tr>
        </thead>

        <tbody>
          <tr v-for="spec in specRows" :key="spec.key" class="border-t border-gray-100">
            <td class="p-3 bg-gray-50 text-sm font-medium text-gray-600">{{ spec.label }}</td>

            <td v-for="p in selected" :key="p.id"
              class="p-3 text-center text-sm border-l border-gray-100 text-gray-700">
              {{ p[spec.key] || '—' }}
            </td>

            <td v-if="selected.length < 4"
              class="border-l border-dashed border-gray-200 bg-gray-50"></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { imageUrl } from '@/utils/image'
import api from '@/services/api'

const toast     = useToast()
const cartStore = useCartStore()
const authStore = useAuthStore()
const router    = useRouter()

const searchQ       = ref('')
const searchResults = ref([])
const selected      = ref([])

const specRows = [
  { key: 'cpu',     label: 'Bộ vi xử lý' },
  { key: 'ram',     label: 'RAM' },
  { key: 'storage', label: 'Ổ cứng' },
  { key: 'display', label: 'Màn hình' },
  { key: 'gpu',     label: 'Card đồ họa' },
  { key: 'os',      label: 'Hệ điều hành' },
  { key: 'battery', label: 'Pin' },
  { key: 'weight',  label: 'Trọng lượng' },
]

const formatPrice = (v) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v ?? 0)

// Tìm kiếm sản phẩm (debounce 300ms)
let searchTimer
async function searchProducts() {
  clearTimeout(searchTimer)
  if (!searchQ.value.trim()) { searchResults.value = []; return }

  searchTimer = setTimeout(async () => {
    try {
      const res = await api.get('/shop/products/search', {
        params: { q: searchQ.value, per_page: 5 }
      })
      // Lọc bỏ sản phẩm đã có trong danh sách so sánh
      searchResults.value = res.data.data.data.filter(
        p => !selected.value.find(s => s.id === p.id)
      )
    } catch {
      searchResults.value = []
    }
  }, 300)
}

function addProduct(p) {
  if (selected.value.length >= 4) {
    toast.warning('Chỉ so sánh tối đa 4 sản phẩm!')
    return
  }
  if (selected.value.find(s => s.id === p.id)) {
    toast.warning('Sản phẩm đã có trong danh sách!')
    return
  }
  selected.value.push(p)
  searchQ.value       = ''
  searchResults.value = []
}

function removeProduct(id) {
  selected.value = selected.value.filter(p => p.id !== id)
}

async function addToCart(p) {
  if (!authStore.isLoggedIn) { router.push('/login'); return }
  try {
    await cartStore.addToCart(p.id)
    toast.success('Đã thêm vào giỏ!')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Không thể thêm vào giỏ.')
  }
}
</script>