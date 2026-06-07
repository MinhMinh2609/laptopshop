<template>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Search bar lớn -->
    <div class="max-w-2xl mx-auto mb-8">
      <div class="relative">
        <input v-model="query" @keyup.enter="doSearch" type="text"
          placeholder="Tìm kiếm laptop theo tên, CPU, RAM, hãng..."
          class="form-input pl-5 pr-14 py-4 text-base rounded-2xl shadow-card" />
        <button @click="doSearch"
          class="absolute right-3 top-1/2 -translate-y-1/2 w-10 h-10 bg-primary-600 text-white rounded-xl flex items-center justify-center hover:bg-primary-700">
          🔍
        </button>
      </div>
      <!-- Gợi ý tìm nhanh -->
      <div class="flex flex-wrap gap-2 mt-3 justify-center">
        <button v-for="s in suggestions" :key="s" @click="query = s; doSearch()"
          class="text-xs px-3 py-1.5 bg-white border border-gray-200 rounded-full hover:border-primary-400 hover:text-primary-600 transition">
          {{ s }}
        </button>
      </div>
    </div>

    <!-- Kết quả -->
    <div v-if="searched">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800">
          Kết quả cho "<span class="text-primary-600">{{ currentKeyword }}</span>"
          <span class="text-gray-400 font-normal text-sm ml-2">({{ total }} sản phẩm)</span>
        </h2>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="i in 8" :key="i" class="bg-gray-100 rounded-2xl h-64 animate-pulse"></div>
      </div>

      <!-- Products -->
      <div v-else-if="products.length" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>

      <!-- Không có -->
      <div v-else class="text-center py-16">
        <p class="text-4xl mb-3">🔍</p>
        <p class="text-lg font-semibold text-gray-600 mb-2">Không tìm thấy kết quả</p>
        <p class="text-gray-400 text-sm">Thử tìm với từ khóa khác hoặc kiểm tra chính tả</p>
      </div>
    </div>

    <!-- Chưa tìm -->
    <div v-else class="text-center py-16 text-gray-400">
      <p class="text-5xl mb-4">🔍</p>
      <p class="text-lg">Nhập từ khóa để tìm kiếm laptop</p>
      <p class="text-sm mt-2">Ví dụ: "Asus gaming", "i5 16GB", "dưới 15 triệu"</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProductCard from '@/components/ProductCard.vue'
import api from '@/services/api'

const route   = useRoute()
const router  = useRouter()

const query          = ref(route.query.q || '')
const currentKeyword = ref('')
const loading        = ref(false)
const products       = ref([])
const total          = ref(0)
const searched       = ref(false)

const suggestions = ['Laptop gaming', 'Laptop sinh viên', 'Asus ROG', 'Dell XPS', 'MacBook', 'i5 16GB SSD', 'RTX 4060', 'Dưới 15 triệu']

async function doSearch() {
  const q = query.value.trim()
  if (!q) return
  router.replace({ query: { q } })
  currentKeyword.value = q
  searched.value       = true
  loading.value        = true
  try {
    const res      = await api.get('/shop/products/search', { params: { q, per_page: 20 } })
    products.value = res.data.data.data
    total.value    = res.data.data.total
  } catch { products.value = [] }
  finally { loading.value = false }
}

onMounted(() => { if (query.value) doSearch() })
watch(() => route.query.q, (v) => { if (v) { query.value = v; doSearch() } })
</script>