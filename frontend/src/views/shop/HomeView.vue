<template>
  <div>
    <!-- Hero Banner -->
    <section class="bg-gradient-to-r from-primary-700 to-primary-500 text-white py-16">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold mb-4">Laptop Chính Hãng — Giá Tốt Nhất</h1>
        <p class="text-blue-100 text-lg mb-8">Hàng ngàn mẫu laptop dành riêng cho sinh viên</p>
        <RouterLink to="/products" class="bg-white text-primary-700 font-bold px-8 py-3 rounded-full hover:bg-blue-50 transition">
          Xem Tất Cả Sản Phẩm →
        </RouterLink>
      </div>
    </section>

    <!-- Categories -->
    <section class="max-w-7xl mx-auto px-4 py-12">
      <h2 class="text-2xl font-bold text-gray-800 mb-6">Danh Mục Nổi Bật</h2>
      <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <RouterLink v-for="cat in categories" :key="cat.slug"
          :to="`/products?category_slug=${cat.slug}`"
          class="bg-white rounded-2xl p-4 text-center shadow-card hover:shadow-card-hover hover:-translate-y-1 transition-all">
          <div class="text-3xl mb-2">{{ cat.icon }}</div>
          <p class="text-sm font-medium text-gray-700">{{ cat.name }}</p>
        </RouterLink>
      </div>
    </section>

    <!-- Featured Products -->
    <section class="max-w-7xl mx-auto px-4 pb-12">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Sản Phẩm Nổi Bật</h2>
        <RouterLink to="/products" class="text-primary-600 hover:underline text-sm font-medium">Xem tất cả →</RouterLink>
      </div>

      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div v-for="i in 8" :key="i" class="bg-gray-100 rounded-2xl h-64 animate-pulse"></div>
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <ProductCard v-for="product in featured" :key="product.id" :product="product" />
      </div>
    </section>

    <!-- Banner CTA -->
    <section class="bg-gray-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 text-center">
        <h2 class="text-2xl font-bold mb-2">🤖 AI Tư Vấn Laptop 24/7</h2>
        <p class="text-gray-400 mb-4">Không biết chọn laptop nào? Chatbot AI sẽ tư vấn miễn phí cho bạn!</p>
        <p class="text-blue-400 text-sm">Nhấn vào icon 🤖 góc dưới bên phải để bắt đầu</p>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import ProductCard from '@/components/ProductCard.vue'

const loading  = ref(true)
const featured = ref([])

const categories = [
  { name: 'Gaming',    slug: 'gaming',    icon: '🎮' },
  { name: 'Văn Phòng', slug: 'van-phong', icon: '💼' },
  { name: 'Đồ Họa',   slug: 'do-hoa',    icon: '🎨' },
  { name: 'Mỏng Nhẹ', slug: 'mong-nhe',  icon: '🪶' },
  { name: 'Sinh Viên', slug: 'sinh-vien', icon: '🎓' },
]

onMounted(async () => {
  try {
    const res  = await api.get('/shop/products', { params: { per_page: 8 } })
    featured.value = res.data.data.data
  } catch { /* bỏ qua */ }
  finally { loading.value = false }
})
</script>