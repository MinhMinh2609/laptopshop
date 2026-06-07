<template>
  <div class="min-h-screen flex flex-col bg-gray-50">

    <!-- ─── HEADER / NAVBAR ─────────────────────────────── -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

          <!-- Logo -->
          <RouterLink to="/" class="flex items-center gap-2">
            <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center">
              <span class="text-white font-bold text-sm">LS</span>
            </div>
            <span class="font-bold text-xl text-gray-900">Laptop<span class="text-primary-600">Shop</span></span>
          </RouterLink>

          <!-- Search Bar -->
          <div class="hidden md:flex flex-1 max-w-xl mx-8">
            <div class="relative w-full">
              <input
                v-model="searchQuery"
                @keyup.enter="goSearch"
                type="text"
                placeholder="Tìm kiếm laptop..."
                class="w-full pl-4 pr-12 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
              />
              <button @click="goSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-primary-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
              </button>
            </div>
          </div>

          <!-- Nav Actions -->
          <div class="flex items-center gap-4">

            <!-- So sánh -->
            <RouterLink to="/compare" class="hidden sm:flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              So sánh
            </RouterLink>

            <!-- Giỏ hàng -->
            <RouterLink to="/cart" class="relative flex items-center gap-1 text-gray-600 hover:text-primary-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
              </svg>
              <span v-if="cartStore.totalItems > 0"
                class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                {{ cartStore.totalItems > 99 ? '99+' : cartStore.totalItems }}
              </span>
            </RouterLink>

            <!-- User Menu -->
            <template v-if="authStore.isLoggedIn">
              <div class="relative" ref="userMenuRef">
                <button @click="userMenuOpen = !userMenuOpen"
                  class="flex items-center gap-2 text-sm text-gray-700 hover:text-primary-600">
                  <div class="w-8 h-8 bg-primary-100 rounded-full flex items-center justify-center">
                    <span class="text-primary-700 font-semibold text-sm">{{ authStore.userName[0]?.toUpperCase() }}</span>
                  </div>
                </button>

                <!-- Dropdown Menu -->
                <div v-if="userMenuOpen"
                  class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">

                  <!-- Admin Panel Link (chỉ admin) -->
                  <RouterLink v-if="authStore.isAdmin" to="/admin/dashboard"
                    class="flex items-center gap-2 px-4 py-2 text-sm text-purple-700 font-semibold hover:bg-purple-50"
                    @click="userMenuOpen = false">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                    Trang Quản Trị
                  </RouterLink>

                  <div v-if="authStore.isAdmin" class="border-t my-1"></div>

                  <RouterLink to="/profile" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="userMenuOpen = false">
                    Hồ sơ cá nhân
                  </RouterLink>
                  <RouterLink to="/orders" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="userMenuOpen = false">
                    Đơn hàng của tôi
                  </RouterLink>
                  <RouterLink to="/wishlist" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50" @click="userMenuOpen = false">
                    Sản phẩm yêu thích
                  </RouterLink>
                  <div class="border-t my-1"></div>
                  <button @click="handleLogout" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 w-full text-left">
                    Đăng xuất
                  </button>
                </div>
              </div>
            </template>

            <!-- Chưa đăng nhập -->
            <template v-else>
              <RouterLink to="/login" class="text-sm text-gray-600 hover:text-primary-600 font-medium">Đăng nhập</RouterLink>
              <RouterLink to="/register"
                class="hidden sm:inline-flex text-sm bg-primary-600 text-white px-4 py-2 rounded-full hover:bg-primary-700 transition font-medium">
                Đăng ký
              </RouterLink>
            </template>
          </div>
        </div>
      </div>

      <!-- Category Nav -->
      <nav class="border-t bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex items-center gap-6 h-10 text-sm overflow-x-auto">
            <RouterLink to="/products?category_slug=gaming" class="whitespace-nowrap text-gray-600 hover:text-primary-600 font-medium">🎮 Gaming</RouterLink>
            <RouterLink to="/products?category_slug=van-phong" class="whitespace-nowrap text-gray-600 hover:text-primary-600 font-medium">💼 Văn Phòng</RouterLink>
            <RouterLink to="/products?category_slug=do-hoa" class="whitespace-nowrap text-gray-600 hover:text-primary-600 font-medium">🎨 Đồ Họa</RouterLink>
            <RouterLink to="/products?category_slug=mong-nhe" class="whitespace-nowrap text-gray-600 hover:text-primary-600 font-medium">🪶 Mỏng Nhẹ</RouterLink>
            <RouterLink to="/products?category_slug=sinh-vien" class="whitespace-nowrap text-gray-600 hover:text-primary-600 font-medium">🎓 Sinh Viên</RouterLink>
            <RouterLink to="/compare" class="whitespace-nowrap text-primary-600 font-semibold hover:underline">⚖️ So Sánh Laptop</RouterLink>
          </div>
        </div>
      </nav>
    </header>

    <!-- ─── MAIN CONTENT ─────────────────────────────────── -->
    <main class="flex-1">
      <RouterView />
    </main>

    <!-- ─── FOOTER ───────────────────────────────────────── -->
    <footer class="bg-gray-900 text-gray-300 mt-16">
      <div class="max-w-7xl mx-auto px-4 py-12 grid grid-cols-2 md:grid-cols-4 gap-8">
        <div>
          <h3 class="text-white font-bold text-lg mb-4">LaptopShop</h3>
          <p class="text-sm leading-relaxed">Website thương mại điện tử bán laptop hướng đến đối tượng sinh viên. ĐATN - Đào Duy Minh</p>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4">Sản Phẩm</h4>
          <ul class="space-y-2 text-sm">
            <li><RouterLink to="/products?category_slug=gaming" class="hover:text-white">Laptop Gaming</RouterLink></li>
            <li><RouterLink to="/products?category_slug=van-phong" class="hover:text-white">Laptop Văn Phòng</RouterLink></li>
            <li><RouterLink to="/products?category_slug=sinh-vien" class="hover:text-white">Laptop Sinh Viên</RouterLink></li>
            <li><RouterLink to="/compare" class="hover:text-white">So Sánh Laptop</RouterLink></li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4">Hỗ Trợ</h4>
          <ul class="space-y-2 text-sm">
            <li><a href="#" class="hover:text-white">Chính sách đổi trả</a></li>
            <li><a href="#" class="hover:text-white">Chính sách bảo hành</a></li>
            <li><a href="#" class="hover:text-white">Hướng dẫn mua hàng</a></li>
          </ul>
        </div>
        <div>
          <h4 class="text-white font-semibold mb-4">Liên Hệ</h4>
          <p class="text-sm">📧 minh955371@gmail.com</p>
          <p class="text-sm mt-1">📞 0915 738 757</p>
        </div>
      </div>
      <div class="border-t border-gray-800 py-4 text-center text-xs text-gray-500">
        © 2024 Laptop Shop DATN - Đào Duy Minh - 2251162071 - TLU
      </div>
    </footer>

    <!-- ─── AI Chatbot Widget ─────────────────────────────── -->
    <ChatbotWidget />
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import ChatbotWidget from '@/components/ChatbotWidget.vue'

const router       = useRouter()
const authStore    = useAuthStore()
const cartStore    = useCartStore()
const searchQuery  = ref('')
const userMenuOpen = ref(false)
const userMenuRef  = ref(null)

function goSearch() {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value.trim() } })
    searchQuery.value = ''
  }
}

async function handleLogout() {
  userMenuOpen.value = false
  await authStore.logout()
  router.push({ name: 'home' })
}

// Đóng dropdown khi click ngoài
function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => document.removeEventListener('click', handleClickOutside))
</script>
