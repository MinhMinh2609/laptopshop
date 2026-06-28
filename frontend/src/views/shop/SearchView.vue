<template>
  <div class="mx-auto max-w-7xl px-4 py-8">
    <div v-if="searched">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-bold text-gray-800">
          Kết quả cho "<span class="text-primary-600">{{ currentKeyword }}</span>"
          <span class="ml-2 text-sm font-normal text-gray-400">({{ total }} sản phẩm)</span>
        </h2>
      </div>

      <div v-if="loading" class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div v-for="i in 8" :key="i" class="h-64 animate-pulse rounded-2xl bg-gray-100"></div>
      </div>

      <div v-else-if="products.length" class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <ProductCard v-for="p in products" :key="p.id" :product="p" />
      </div>

      <div v-else class="py-16 text-center">
        <p class="mb-2 text-lg font-semibold text-gray-600">Không tìm thấy kết quả</p>
        <p class="text-sm text-gray-400">Thử tìm với từ khóa khác ở thanh tìm kiếm phía trên</p>
      </div>
    </div>

    <div v-else class="py-16 text-center text-gray-400">
      <p class="text-lg">Dùng thanh tìm kiếm phía trên để tìm laptop</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import ProductCard from '@/components/ProductCard.vue'
import api from '@/services/api'

const route = useRoute()

const currentKeyword = ref('')
const loading = ref(false)
const products = ref([])
const total = ref(0)
const searched = ref(false)

let searchRequestId = 0

function resetSearch() {
  searchRequestId++
  currentKeyword.value = ''
  loading.value = false
  products.value = []
  total.value = 0
  searched.value = false
}

async function performSearch(q) {
  if (!q || q.length < 2) {
    resetSearch()
    return
  }

  const requestId = ++searchRequestId
  currentKeyword.value = q
  searched.value = true
  loading.value = true

  try {
    const res = await api.get('/shop/products/search', { params: { q, per_page: 20 } })
    if (requestId !== searchRequestId) return

    products.value = res.data.data.data
    total.value = res.data.data.total
  } catch {
    if (requestId !== searchRequestId) return
    products.value = []
    total.value = 0
  } finally {
    if (requestId === searchRequestId) loading.value = false
  }
}

onMounted(() => {
  const q = route.query.q?.toString().trim() || ''
  if (q) performSearch(q)
})

watch(() => route.query.q, (value) => {
  const q = value?.toString().trim() || ''
  if (q === currentKeyword.value) return

  if (q) performSearch(q)
  else resetSearch()
})
</script>
