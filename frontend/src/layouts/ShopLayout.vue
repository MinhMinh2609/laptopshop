<template>
  <div class="flex min-h-screen flex-col bg-slate-50 text-slate-900">
    <header class="sticky top-0 z-50 border-b border-slate-200 bg-white/95 backdrop-blur">
      <div class="hidden border-b border-slate-100 bg-slate-950 text-white sm:block">
        <div class="mx-auto flex h-9 max-w-7xl items-center justify-between px-4 text-xs sm:px-6 lg:px-8">
          <p class="font-medium">Hỗ trợ trả góp | So sánh cấu hình | Tư vấn chọn laptop theo nhu cầu</p>
          <button v-if="false" @click="compareStore.openPopup()" class="hidden font-semibold text-orange-300 hover:text-orange-200 sm:block">
            So sánh laptop
          </button>
        </div>
      </div>

      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-auto flex-wrap items-center justify-between gap-3 py-3 md:h-16 md:flex-nowrap md:gap-4 md:py-0">
          <RouterLink to="/" class="flex min-w-0 shrink-0 items-center gap-2 sm:gap-3">
            <img
                :src="logoUrl"
                alt="Laptop Shop"
                class="h-8 w-8 rounded-lg object-contain sm:h-10 sm:w-10"
              />
            <div class="min-w-0">
              <p class="truncate text-base font-black leading-5 tracking-tight text-slate-950 sm:text-lg">Laptop Shop</p>
              <p class="hidden text-xs font-medium text-slate-500 sm:block">Laptop chính hãng</p>
            </div>
          </RouterLink>

          <div class="hidden flex-1 md:block">
            <div class="relative mx-auto max-w-2xl" ref="desktopSearchRef">
              <input
                v-model="searchQuery"
                @input="handleSearchInput"
                @focus="openSearchDropdown"
                @keyup.enter="goSearch"
                type="text"
                placeholder="Tìm laptop theo tên, CPU, RAM, SSD..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-4 pr-12 text-sm text-slate-900 placeholder:text-slate-400 focus:border-orange-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-orange-100"
              />
              <button
                @click="goSearch"
                class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md bg-slate-950 text-white transition hover:bg-orange-600"
                aria-label="Tìm kiếm"
              >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.2-5.2m1.7-4.3a6 6 0 11-12 0 6 6 0 0112 0z" />
                </svg>
              </button>
              <div v-if="showSearchDropdown" class="absolute left-0 right-0 top-full z-50 mt-2 max-h-[70vh] overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl">
                <div v-if="searchLoading" class="px-4 py-3 text-sm text-slate-500">
                  Đang tìm sản phẩm...
                </div>
                <template v-else-if="searchResults.length">
                  <button
                    v-for="product in searchResults"
                    :key="product.id"
                    @mousedown.prevent="selectSearchProduct(product)"
                    class="flex w-full items-center gap-3 border-b border-slate-100 px-4 py-3 text-left transition last:border-0 hover:bg-slate-50"
                  >
                    <img
                      :src="productImage(product)"
                      :alt="product.name"
                      class="h-12 w-12 shrink-0 rounded-md bg-slate-50 object-contain p-1"
                      @error="$event.target.src = '/placeholder.svg'"
                    />
                    <div class="min-w-0 flex-1">
                      <p class="line-clamp-1 text-sm font-bold text-slate-900">{{ product.name }}</p>
                      <p class="mt-0.5 line-clamp-1 text-xs text-slate-500">{{ productMeta(product) }}</p>
                    </div>
                    <p class="shrink-0 text-sm font-black text-orange-600">
                      {{ formatPrice(product.sale_price || product.price) }}
                    </p>
                  </button>
                  <button
                    @mousedown.prevent="goSearch"
                    class="w-full bg-slate-50 px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:text-orange-600"
                  >
                    Xem tất cả kết quả cho "{{ searchQuery.trim() }}"
                  </button>
                </template>
                <div v-else-if="searchQuery.trim().length >= 2" class="px-4 py-3 text-sm text-slate-500">
                  Không tìm thấy sản phẩm phù hợp
                </div>
              </div>
            </div>
          </div>

          <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            <button @click="compareStore.openPopup()" class="hidden rounded-md px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 hover:text-orange-600 sm:inline-flex">
              So sánh
            </button>

            <RouterLink to="/cart" class="relative flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 text-slate-700 hover:border-orange-300 hover:text-orange-600 sm:h-10 sm:w-10">
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2 2v1h12m-8 4a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z" />
              </svg>
              <span
                v-if="cartStore.totalItems > 0"
                class="absolute -right-2 -top-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-bold text-white"
              >
                {{ cartStore.totalItems > 99 ? '99+' : cartStore.totalItems }}
              </span>
            </RouterLink>

            <template v-if="authStore.isLoggedIn">
              <div class="relative" ref="userMenuRef">
                <button
                  @click="userMenuOpen = !userMenuOpen"
                  class="flex h-9 w-9 items-center justify-center rounded-md bg-orange-50 text-sm font-bold text-orange-700 hover:bg-orange-100 sm:h-10 sm:w-10"
                >
                  {{ authStore.userName[0]?.toUpperCase() }}
                </button>

                <div v-if="userMenuOpen" class="absolute right-0 z-50 mt-2 w-56 rounded-lg border border-slate-200 bg-white py-2 shadow-xl">
                  <RouterLink
                    v-if="authStore.isAdmin"
                    to="/admin/dashboard"
                    class="block px-4 py-2 text-sm font-bold text-orange-700 hover:bg-orange-50"
                    @click="userMenuOpen = false"
                  >
                    Trang quản trị
                  </RouterLink>
                  <div v-if="authStore.isAdmin" class="my-1 border-t border-slate-100"></div>
                  <RouterLink to="/profile" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" @click="userMenuOpen = false">
                    Hồ sơ cá nhân
                  </RouterLink>
                  <RouterLink to="/orders" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" @click="userMenuOpen = false">
                    Đơn hàng của tôi
                  </RouterLink>
                  <RouterLink to="/wishlist" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50" @click="userMenuOpen = false">
                    Sản phẩm yêu thích
                  </RouterLink>
                  <div class="my-1 border-t border-slate-100"></div>
                  <button @click="handleLogout" class="block w-full px-4 py-2 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                    Đăng xuất
                  </button>
                </div>
              </div>
            </template>

            <template v-else>
              <RouterLink to="/login" class="hidden text-sm font-semibold text-slate-700 hover:text-orange-600 sm:inline-flex">
                Đăng nhập
              </RouterLink>
              <RouterLink to="/register" class="rounded-md bg-orange-500 px-3 py-2 text-xs font-bold text-white transition hover:bg-orange-600 sm:px-4 sm:text-sm">
                Đăng ký
              </RouterLink>
            </template>
          </div>
        </div>

        <div class="relative pb-3 md:hidden" ref="mobileSearchRef">
          <input
            v-model="searchQuery"
            @input="handleSearchInput"
            @focus="openSearchDropdown"
            @keyup.enter="goSearch"
            type="text"
            placeholder="Tìm laptop..."
            class="w-full rounded-lg border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm focus:border-orange-400 focus:outline-none focus:ring-2 focus:ring-orange-100"
          />
          <div v-if="showSearchDropdown" class="absolute left-0 right-0 top-[calc(100%-0.5rem)] z-50 max-h-[65vh] overflow-y-auto rounded-lg border border-slate-200 bg-white shadow-xl">
            <div v-if="searchLoading" class="px-4 py-3 text-sm text-slate-500">
              Đang tìm sản phẩm...
            </div>
            <template v-else-if="searchResults.length">
              <button
                v-for="product in searchResults"
                :key="product.id"
                @mousedown.prevent="selectSearchProduct(product)"
                class="flex w-full flex-wrap items-center gap-2 border-b border-slate-100 px-3 py-3 text-left transition last:border-0 hover:bg-slate-50"
              >
                <img
                  :src="productImage(product)"
                  :alt="product.name"
                  class="h-10 w-10 shrink-0 rounded-md bg-slate-50 object-contain p-1"
                  @error="$event.target.src = '/placeholder.svg'"
                />
                <div class="min-w-0 flex-1">
                  <p class="line-clamp-1 text-sm font-bold text-slate-900">{{ product.name }}</p>
                  <p class="mt-0.5 line-clamp-1 text-xs text-slate-500">{{ productMeta(product) }}</p>
                </div>
                <p class="w-full pl-12 text-right text-xs font-black text-orange-600">
                  {{ formatPrice(product.sale_price || product.price) }}
                </p>
              </button>
              <button
                @mousedown.prevent="goSearch"
                class="w-full bg-slate-50 px-4 py-2.5 text-center text-sm font-bold text-slate-700 hover:text-orange-600"
              >
                Xem tất cả kết quả
              </button>
            </template>
            <div v-else-if="searchQuery.trim().length >= 2" class="px-4 py-3 text-sm text-slate-500">
              Không tìm thấy sản phẩm phù hợp
            </div>
          </div>
        </div>
      </div>

      <nav class="border-t border-slate-100 bg-white">
        <div class="mx-auto flex h-11 max-w-7xl items-center gap-2 overflow-x-auto px-4 text-sm sm:px-6 lg:px-8">
          <RouterLink v-for="item in navItems" :key="item.to" :to="item.to" class="whitespace-nowrap rounded-md px-3 py-2 font-semibold text-slate-600 hover:bg-slate-100 hover:text-orange-600">
            {{ item.label }}
          </RouterLink>
        </div>
      </nav>
    </header>

    <main class="flex-1">
      <RouterView />
    </main>

    <footer class="mt-12 bg-slate-950 text-slate-300">
      <div class="mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 md:grid-cols-4 lg:px-8">
        <div class="md:col-span-1">
          <h3 class="text-lg font-black text-white">Laptop Shop</h3>
          <p class="mt-3 text-sm leading-6 text-slate-400">
            Cửa hàng laptop cho học tập, văn phòng, gaming và đồ họa. Ưu tiên tư vấn đúng nhu cầu, thông tin giá và cấu hình minh bạch.
          </p>
        </div>
        <div>
          <h4 class="font-bold text-white">Sản phẩm</h4>
          <ul class="mt-3 space-y-2 text-sm">
            <li><RouterLink to="/products?category_slug=gaming" class="hover:text-white">Laptop Gaming</RouterLink></li>
            <li><RouterLink to="/products?category_slug=van-phong" class="hover:text-white">Laptop Văn phòng</RouterLink></li>
            <li><RouterLink to="/products?category_slug=sinh-vien" class="hover:text-white">Laptop Sinh viên</RouterLink></li>
            <li><button @click="compareStore.openPopup()" class="hover:text-white">So sánh laptop</button></li>
          </ul>
        </div>
        <div>
          <h4 class="font-bold text-white">Hỗ trợ</h4>
          <ul class="mt-3 space-y-2 text-sm">
            <li><a href="#" class="hover:text-white">Hỗ trợ mua hàng</a></li>
            <li><a href="#" class="hover:text-white">Đổi trả trong 7 ngày</a></li>
            <li><a href="#" class="hover:text-white">Hướng dẫn mua hàng</a></li>
          </ul>
        </div>
        <div>
          <h4 class="font-bold text-white">Liên hệ</h4>
          <div class="mt-3 space-y-2 text-sm">
            <p>Email: minh955371@gmail.com</p>
            <p>Hotline: 0915 738 757</p>
            <p>Tư vấn: 8:00 - 21:00 hằng ngày</p>
          </div>
        </div>
      </div>
      <div class="border-t border-white/10 py-4 text-center text-xs text-slate-500">
        © 2026 Laptop Shop. Giá và khuyến mãi có thể thay đổi theo từng thời điểm.
      </div>
    </footer>

    <ChatbotWidget />
    <CompareTray />
  </div>
</template>

<script setup>
import logoUrl from '@/assets/logo.png'
import { ref, onMounted, onUnmounted } from 'vue'
import { RouterLink, RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useCompareStore } from '@/stores/compare'
import ChatbotWidget from '@/components/ChatbotWidget.vue'
import CompareTray from '@/components/CompareTray.vue'
import api from '@/services/api'
import { imageUrl } from '@/utils/image'

const router = useRouter()
const authStore = useAuthStore()
const cartStore = useCartStore()
const compareStore = useCompareStore()
const searchQuery = ref('')
const searchResults = ref([])
const searchLoading = ref(false)
const showSearchDropdown = ref(false)
const userMenuOpen = ref(false)
const userMenuRef = ref(null)
const desktopSearchRef = ref(null)
const mobileSearchRef = ref(null)
let searchTimer
let searchRequestId = 0

const navItems = [
  { label: 'Tất cả laptop', to: '/products' },
  { label: 'Gaming', to: '/products?category_slug=gaming' },
  { label: 'Văn phòng', to: '/products?category_slug=van-phong' },
  { label: 'Đồ họa', to: '/products?category_slug=do-hoa' },
  { label: 'Mỏng nhẹ', to: '/products?category_slug=mong-nhe' },
  { label: 'Sinh viên', to: '/products?category_slug=sinh-vien' },
]

function goSearch() {
  if (searchQuery.value.trim()) {
    searchRequestId++
    closeSearchDropdown()
    router.push({ name: 'search', query: { q: searchQuery.value.trim() } })
    searchQuery.value = ''
    searchResults.value = []
  }
}

function openSearchDropdown() {
  if (searchQuery.value.trim().length >= 2) {
    showSearchDropdown.value = true
  }
}

function closeSearchDropdown() {
  showSearchDropdown.value = false
}

function handleSearchInput() {
  clearTimeout(searchTimer)
  const q = searchQuery.value.trim()

  if (q.length < 2) {
    searchRequestId++
    searchResults.value = []
    searchLoading.value = false
    closeSearchDropdown()
    return
  }

  showSearchDropdown.value = true
  searchLoading.value = true

  searchTimer = setTimeout(() => {
    fetchSearchSuggestions(q)
  }, 300)
}

async function fetchSearchSuggestions(q) {
  const requestId = ++searchRequestId

  try {
    const res = await api.get('/shop/products/search', {
      params: { q, per_page: 5 },
    })

    if (requestId !== searchRequestId) return
    searchResults.value = res.data.data.data
  } catch {
    if (requestId !== searchRequestId) return
    searchResults.value = []
  } finally {
    if (requestId === searchRequestId) searchLoading.value = false
  }
}

function selectSearchProduct(product) {
  searchRequestId++
  closeSearchDropdown()
  searchQuery.value = ''
  searchResults.value = []
  router.push(`/products/${product.slug}`)
}

function productImage(product) {
  return imageUrl(product.thumbnail || product.images?.[0]?.image_path || null)
}

function productMeta(product) {
  return [product.cpu, product.ram, product.storage]
    .filter(Boolean)
    .join(' | ') || product.brand?.name || product.category?.name || ''
}

const formatPrice = (value) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value ?? 0)

async function handleLogout() {
  userMenuOpen.value = false
  await authStore.logout()
  router.push({ name: 'home' })
}

function handleClickOutside(event) {
  if (userMenuRef.value && !userMenuRef.value.contains(event.target)) {
    userMenuOpen.value = false
  }

  const clickedDesktopSearch = desktopSearchRef.value?.contains(event.target)
  const clickedMobileSearch = mobileSearchRef.value?.contains(event.target)
  if (!clickedDesktopSearch && !clickedMobileSearch) {
    closeSearchDropdown()
  }
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onUnmounted(() => {
  clearTimeout(searchTimer)
  document.removeEventListener('click', handleClickOutside)
})
</script>
