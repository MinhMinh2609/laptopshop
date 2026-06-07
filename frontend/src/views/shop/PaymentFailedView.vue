<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center max-w-md">
      <div class="w-24 h-24 bg-red-100 rounded-full flex items-center justify-center text-5xl mx-auto mb-6">❌</div>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Thanh Toán Thất Bại</h1>
      <p class="text-gray-500 mb-2">Mã đơn hàng: <span class="font-mono font-bold text-gray-600">{{ orderCode }}</span></p>
      <p class="text-gray-400 text-sm mb-8">Giao dịch không thành công. Đơn hàng vẫn được giữ, bạn có thể thử lại.</p>
      <div class="flex gap-3 justify-center">
        <RouterLink :to="`/orders/${orderCode}`" class="btn-primary">Thử Lại Thanh Toán</RouterLink>
        <RouterLink to="/" class="btn-outline">Về Trang Chủ</RouterLink>
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