<template>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="text-center">
      <p class="text-8xl font-black text-gray-200 mb-4">403</p>
      <h1 class="text-2xl font-bold text-gray-800 mb-2">Không Có Quyền Truy Cập</h1>
      <p class="text-gray-400 mb-8">Bạn không có quyền truy cập vào trang này.</p>
      <RouterLink to="/" class="btn-primary">← Về Trang Chủ</RouterLink>
    </div>
  </div>
</template>
<script setup>
import { RouterLink } from 'vue-router'
</script>