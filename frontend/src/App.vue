<template>
  <RouterView />
</template>

<script setup>
import { onMounted } from 'vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'

const authStore = useAuthStore()
const cartStore = useCartStore()

onMounted(async () => {
  // Khôi phục auth state khi app khởi động
  await authStore.initialize()
  // Load giỏ hàng nếu đã đăng nhập
  if (authStore.isLoggedIn) {
    await cartStore.fetchCart()
  }
})
</script>
