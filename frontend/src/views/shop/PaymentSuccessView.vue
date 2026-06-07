<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md">
      <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center text-5xl mx-auto mb-6">✅</div>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Thanh Toán Thành Công!</h1>
      <p class="text-gray-500 mb-2">Mã đơn hàng: <span class="font-mono font-bold text-primary-600">{{ orderCode }}</span></p>
      <p class="text-gray-400 text-sm mb-8">Chúng tôi sẽ xử lý và giao hàng sớm nhất có thể. Cảm ơn bạn!</p>
      <div class="flex gap-3 justify-center">
        <RouterLink :to="`/orders/${orderCode}`" class="btn-primary">Xem Đơn Hàng</RouterLink>
        <RouterLink to="/products" class="btn-outline">Tiếp Tục Mua Sắm</RouterLink>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
const route     = useRoute()
const orderCode = computed(() => route.query.order || '')
</script>