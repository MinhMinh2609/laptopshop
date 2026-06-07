<template>
  <div class="min-h-screen flex bg-gray-100">

    <!-- ─── SIDEBAR ─────────────────────────────────────── -->
    <aside :class="['fixed inset-y-0 left-0 z-50 w-64 bg-gray-900 transform transition-transform duration-300',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']">

      <!-- Logo -->
      <div class="flex items-center justify-between h-16 px-6 border-b border-gray-700">
        <RouterLink to="/admin/dashboard" class="flex items-center gap-2">
          <div class="w-7 h-7 bg-primary-500 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-xs">LS</span>
          </div>
          <span class="text-white font-bold">Admin Panel</span>
        </RouterLink>
        <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">✕</button>
      </div>

      <!-- Admin Info -->
      <div class="px-6 py-4 border-b border-gray-700">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-primary-600 rounded-full flex items-center justify-center">
            <span class="text-white font-semibold text-sm">{{ authStore.userName[0]?.toUpperCase() }}</span>
          </div>
          <div>
            <p class="text-white text-sm font-medium">{{ authStore.userName }}</p>
            <span class="text-xs bg-purple-700 text-purple-200 px-2 py-0.5 rounded-full">Administrator</span>
          </div>
        </div>
      </div>

      <!-- Navigation -->
      <nav class="px-3 py-4 overflow-y-auto max-h-[calc(100vh-180px)]">

        <!-- Dashboard -->
        <RouterLink to="/admin/dashboard"
          class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1"
          :class="isActive('/admin/dashboard') ? 'bg-primary-600 text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-gray-700'">
          <span>📊</span> Dashboard
        </RouterLink>

        <!-- Quản lý sản phẩm -->
        <p class="text-xs text-gray-500 uppercase font-semibold px-3 pt-4 pb-1">Quản Lý Sản Phẩm</p>

        <RouterLink to="/admin/products"
          class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1"
          :class="isActive('/admin/products') ? 'bg-primary-600 text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-gray-700'">
          <span>💻</span> Sản Phẩm
        </RouterLink>

        <RouterLink to="/admin/categories"
          class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1"
          :class="isActive('/admin/categories') ? 'bg-primary-600 text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-gray-700'">
          <span>🗂️</span> Danh Mục
        </RouterLink>

        <RouterLink to="/admin/brands"
          class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1"
          :class="isActive('/admin/brands') ? 'bg-primary-600 text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-gray-700'">
          <span>🏷️</span> Hãng / Brand
        </RouterLink>

        <!-- Kinh doanh -->
        <p class="text-xs text-gray-500 uppercase font-semibold px-3 pt-4 pb-1">Kinh Doanh</p>

        <RouterLink to="/admin/orders"
          class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1"
          :class="isActive('/admin/orders') ? 'bg-primary-600 text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-gray-700'">
          <span>📦</span> Đơn Hàng
        </RouterLink>

        <RouterLink to="/admin/reviews"
          class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1"
          :class="isActive('/admin/reviews') ? 'bg-primary-600 text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-gray-700'">
          <span>⭐</span> Đánh Giá
        </RouterLink>

        <!-- Hệ thống -->
        <p class="text-xs text-gray-500 uppercase font-semibold px-3 pt-4 pb-1">Hệ Thống</p>

        <RouterLink to="/admin/users"
          class="flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition-colors mb-1"
          :class="isActive('/admin/users') ? 'bg-primary-600 text-white font-medium' : 'text-gray-300 hover:text-white hover:bg-gray-700'">
          <span>👥</span> Người Dùng
        </RouterLink>

        <!-- Bottom links -->
        <div class="pt-4 mt-4 border-t border-gray-700">
          <RouterLink to="/"
            class="flex items-center gap-3 px-3 py-2.5 text-sm text-gray-400 hover:text-white rounded-lg hover:bg-gray-700 transition mb-1">
            <span>🌐</span> Về Trang Shop
          </RouterLink>
          <button @click="handleLogout"
            class="w-full flex items-center gap-3 px-3 py-2.5 text-sm text-red-400 hover:text-red-300 rounded-lg hover:bg-red-900/30 transition">
            <span>🚪</span> Đăng Xuất
          </button>
        </div>
      </nav>
    </aside>

    <!-- ─── MAIN CONTENT ─────────────────────────────────── -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">

      <!-- Top Bar -->
      <header class="sticky top-0 z-40 bg-white border-b shadow-sm h-16 flex items-center justify-between px-6">
        <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <div class="hidden lg:block text-sm text-gray-500">
          Admin / <span class="text-gray-800 font-medium">{{ currentPageTitle }}</span>
        </div>

        <div class="flex items-center gap-3 text-sm text-gray-600">
          <span>{{ authStore.userName }}</span>
          <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">Admin</span>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 p-6">
        <RouterView />
      </main>
    </div>

    <!-- Sidebar Overlay (mobile) -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false"
      class="fixed inset-0 bg-black/50 z-40 lg:hidden"></div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const authStore   = useAuthStore()
const router      = useRouter()
const route       = useRoute()
const sidebarOpen = ref(false)

const currentPageTitle = computed(() => route.meta.title?.replace(' - Admin', '') ?? 'Dashboard')

// Kiểm tra route active (hỗ trợ cả sub-routes)
function isActive(path) {
  if (path === '/admin/dashboard') return route.path === path
  return route.path.startsWith(path)
}

async function handleLogout() {
  await authStore.logout()
  router.push({ name: 'login' })
}
</script>