<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex gap-6">

      <!-- ─── SIDEBAR FILTER ─────────────────────────── -->
      <aside class="hidden lg:block w-56 flex-shrink-0">
        <div class="bg-white rounded-2xl shadow-card p-4 sticky top-24">
          <h3 class="font-bold text-gray-800 mb-4">Lọc Sản Phẩm</h3>

          <!-- Hãng -->
          <div class="mb-4">
            <p class="text-sm font-semibold text-gray-600 mb-2">Hãng</p>
            <div class="space-y-1">
              <label v-for="b in brands" :key="b.slug" class="flex items-center gap-2 cursor-pointer">
                <input type="radio" :value="b.slug" v-model="filters.brand_slug" class="accent-primary-600" />
                <span class="text-sm text-gray-700">{{ b.name }}</span>
              </label>
            </div>
          </div>

          <!-- Khoảng giá -->
          <div class="mb-4">
            <p class="text-sm font-semibold text-gray-600 mb-2">Khoảng Giá</p>
            <div class="space-y-1">
              <label v-for="p in priceRanges" :key="p.label" class="flex items-center gap-2 cursor-pointer">
                <input type="radio" :value="p" v-model="selectedPrice" class="accent-primary-600" />
                <span class="text-sm text-gray-700">{{ p.label }}</span>
              </label>
            </div>
          </div>

          <!-- Sắp xếp -->
          <div class="mb-4">
            <p class="text-sm font-semibold text-gray-600 mb-2">Sắp Xếp</p>
            <select v-model="filters.sort" class="form-input text-sm">
              <option value="">Mặc định</option>
              <option value="price_asc">Giá tăng dần</option>
              <option value="price_desc">Giá giảm dần</option>
              <option value="newest">Mới nhất</option>
              <option value="popular">Phổ biến nhất</option>
            </select>
          </div>

          <button @click="resetFilters" class="w-full text-sm text-primary-600 hover:underline">Xóa bộ lọc</button>
        </div>
      </aside>

      <!-- ─── PRODUCT GRID ───────────────────────────── -->
      <div class="flex-1">
        <div class="flex items-center justify-between mb-4">
          <h1 class="text-xl font-bold text-gray-800">
            {{ route.query.category_slug ? 'Danh Mục' : 'Tất Cả Laptop' }}
            <span class="text-gray-400 font-normal text-base ml-2">({{ total }} sản phẩm)</span>
          </h1>
        </div>

        <!-- Loading -->
        <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <div v-for="i in 8" :key="i" class="bg-gray-100 rounded-2xl h-64 animate-pulse"></div>
        </div>

        <!-- Products -->
        <div v-else-if="products.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
          <ProductCard v-for="p in products" :key="p.id" :product="p" />
        </div>

        <!-- Empty -->
        <div v-else class="text-center py-20 text-gray-400">
          <p class="text-4xl mb-3">🔍</p>
          <p class="text-lg">Không tìm thấy sản phẩm nào</p>
          <button @click="resetFilters" class="mt-4 btn-primary">Xóa bộ lọc</button>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="flex justify-center gap-2 mt-8">
          <button v-for="p in lastPage" :key="p" @click="currentPage = p"
            class="pagination-btn" :class="{ active: currentPage === p }">{{ p }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/services/api'
import ProductCard from '@/components/ProductCard.vue'

const route       = useRoute()
const loading     = ref(true)
const products    = ref([])
const brands      = ref([])
const total       = ref(0)
const lastPage    = ref(1)
const currentPage = ref(1)

const filters = ref({ brand_slug: '', category_slug: '', sort: '' })
const selectedPrice = ref(null)

const priceRanges = [
  { label: 'Dưới 10 triệu',    min: 0,         max: 10000000  },
  { label: '10 - 15 triệu',    min: 10000000,   max: 15000000  },
  { label: '15 - 20 triệu',    min: 15000000,   max: 20000000  },
  { label: '20 - 30 triệu',    min: 20000000,   max: 30000000  },
  { label: 'Trên 30 triệu',    min: 30000000,   max: 999999999 },
]

async function fetchProducts() {
  loading.value = true
  try {
    const params = {
      page: currentPage.value,
      per_page: 12,
      ...filters.value,
      price_min: selectedPrice.value?.min,
      price_max: selectedPrice.value?.max,
      category_slug: route.query.category_slug || filters.value.category_slug,
    }
    const res        = await api.get('/shop/products', { params })
    products.value   = res.data.data.data
    total.value      = res.data.data.total
    lastPage.value   = res.data.data.last_page
  } catch { products.value = [] }
  finally { loading.value = false }
}

async function fetchBrands() {
  try {
    const res  = await api.get('/shop/brands')
    brands.value = res.data.data
  } catch {}
}

function resetFilters() {
  filters.value      = { brand_slug: '', category_slug: '', sort: '' }
  selectedPrice.value = null
  currentPage.value  = 1
}

watch([filters, selectedPrice, currentPage], fetchProducts, { deep: true })
watch(() => route.query.category_slug, fetchProducts)
onMounted(() => { fetchBrands(); fetchProducts() })
</script>