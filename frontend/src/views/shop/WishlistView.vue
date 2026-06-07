<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">❤️ Sản Phẩm Yêu Thích</h1>

    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="bg-gray-100 rounded-2xl h-64 animate-pulse"></div>
    </div>

    <div v-else-if="!items.length" class="text-center py-20">
      <p class="text-5xl mb-4">🤍</p>
      <p class="text-lg font-semibold text-gray-600 mb-2">Chưa có sản phẩm yêu thích</p>
      <RouterLink to="/products" class="btn-primary mt-4 inline-block">Khám Phá Sản Phẩm</RouterLink>
    </div>

    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="item in items" :key="item.id" class="product-card relative">
        <button @click="removeWishlist(item.product_id)"
          class="absolute top-2 right-2 z-10 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">
          ✕
        </button>
        <ProductCard :product="item.product" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useToast } from 'vue-toastification'
import ProductCard from '@/components/ProductCard.vue'
import api from '@/services/api'

const toast   = useToast()
const loading = ref(true)
const items   = ref([])

async function fetchWishlist() {
  loading.value = true
  try { const res = await api.get('/wishlist'); items.value = res.data.data }
  catch { items.value = [] }
  finally { loading.value = false }
}

async function removeWishlist(productId) {
  try {
    await api.post(`/wishlist/${productId}`)
    items.value = items.value.filter(i => i.product_id !== productId)
    toast.success('Đã xóa khỏi yêu thích!')
  } catch {}
}

onMounted(fetchWishlist)
</script>