<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-800">💻 Quản Lý Sản Phẩm</h1>
      <RouterLink to="/admin/products/create" class="btn-primary text-sm">+ Thêm Sản Phẩm</RouterLink>
    </div>

    <!-- Search + Filter -->
    <div class="bg-white rounded-2xl border p-4 flex flex-wrap gap-3">
      <input v-model="search" @input="debouncedFetch" type="text"
        placeholder="Tìm theo tên, SKU..." class="form-input max-w-xs" />
      <select v-model="filterBrand" @change="fetchProducts" class="form-input max-w-40">
        <option value="">Tất cả hãng</option>
        <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
      </select>
      <select v-model="filterActive" @change="fetchProducts" class="form-input max-w-44">
        <option value="">Tất cả trạng thái</option>
        <option value="1">Đang bán</option>
        <option value="0">Đã ẩn</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      </div>
      <table v-else class="table-admin">
        <thead>
          <tr>
            <th>Sản phẩm</th>
            <th>SKU</th>
            <th>Giá</th>
            <th>Kho</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!products.length">
            <td colspan="6" class="text-center py-12 text-gray-400">Không có sản phẩm nào</td>
          </tr>
          <tr v-for="p in products" :key="p.id">
            <td>
              <div class="flex items-center gap-3">
                <!-- Dùng imageUrl helper để hiện ảnh đúng -->
                <img
                  :src="getProductImage(p)"
                  :alt="p.name"
                  class="w-12 h-12 rounded-xl object-contain bg-gray-50 p-1 flex-shrink-0"
                  @error="$event.target.src='/placeholder.svg'"
                />
                <div>
                  <p class="font-medium text-gray-800 text-sm line-clamp-1 max-w-48">{{ p.name }}</p>
                  <p class="text-xs text-gray-400">{{ p.brand?.name }} | {{ p.category?.name }}</p>
                </div>
              </div>
            </td>
            <td class="font-mono text-xs text-gray-600">{{ p.sku }}</td>
            <td>
              <p class="font-semibold text-sm text-primary-600">{{ formatPrice(p.sale_price || p.price) }}</p>
              <p v-if="p.sale_price" class="text-xs text-gray-400 line-through">{{ formatPrice(p.price) }}</p>
            </td>
            <td>
              <span :class="p.stock <= 3 ? 'text-red-600 font-bold' : 'text-gray-700'">{{ p.stock }}</span>
            </td>
            <td>
              <button @click="toggleActive(p)"
                :class="p.is_active ? 'badge-success' : 'badge-danger'">
                {{ p.is_active ? '✅ Đang bán' : '❌ Đã ẩn' }}
              </button>
            </td>
            <td>
              <div class="flex gap-2">
                <RouterLink :to="`/admin/products/${p.id}/edit`"
                  class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">Sửa</RouterLink>
                <button @click="deleteProduct(p.id)"
                  class="text-xs px-3 py-1.5 bg-red-50 text-red-500 rounded-lg hover:bg-red-100">Xóa</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex justify-center gap-2 p-4 border-t">
        <button v-for="p in lastPage" :key="p" @click="currentPage = p; fetchProducts()"
          class="pagination-btn" :class="{ active: currentPage === p }">{{ p }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useToast } from 'vue-toastification'
import { imageUrl } from '@/utils/image'
import api from '@/services/api'

const toast       = useToast()
const loading     = ref(true)
const products    = ref([])
const brands      = ref([])
const search      = ref('')
const filterBrand = ref('')
const filterActive= ref('')
const currentPage = ref(1)
const lastPage    = ref(1)

const formatPrice = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v ?? 0)

// Lấy ảnh sản phẩm dùng imageUrl helper
function getProductImage(product) {
  const src = product.thumbnail
    || product.images?.[0]?.image_path
    || null
  return imageUrl(src)
}

async function fetchProducts() {
  loading.value = true
  try {
    const res = await api.get('/admin/products', {
      params: {
        search:      search.value || undefined,
        brand_id:    filterBrand.value || undefined,
        is_active:   filterActive.value !== '' ? filterActive.value : undefined,
        page:        currentPage.value,
      }
    })
    products.value = res.data.data.data
    lastPage.value = res.data.data.last_page
  } catch {
    products.value = []
  } finally {
    loading.value = false
  }
}

async function fetchBrands() {
  try {
    const res    = await api.get('/shop/brands')
    brands.value = res.data.data
  } catch {}
}

async function toggleActive(p) {
  try {
    await api.patch(`/admin/products/${p.id}/toggle-active`)
    p.is_active = !p.is_active
    useToast().success(p.is_active ? 'Đã hiện sản phẩm' : 'Đã ẩn sản phẩm')
  } catch {
    useToast().error('Thao tác thất bại.')
  }
}

async function deleteProduct(id) {
  if (!confirm('Xóa sản phẩm này? Hành động không thể hoàn tác!')) return
  try {
    await api.delete(`/admin/products/${id}`)
    toast.success('Đã xóa sản phẩm!')
    fetchProducts()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Không thể xóa.')
  }
}

let timer
function debouncedFetch() {
  clearTimeout(timer)
  timer = setTimeout(fetchProducts, 400)
}

onMounted(() => { fetchBrands(); fetchProducts() })
</script>