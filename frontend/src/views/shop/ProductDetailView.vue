<template>
  <div class="max-w-7xl mx-auto px-4 py-8">

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="w-10 h-10 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Product Detail -->
    <div v-else-if="product">
      <!-- Breadcrumb -->
      <nav class="text-sm text-gray-500 mb-6 flex items-center gap-2">
        <RouterLink to="/" class="hover:text-primary-600">Trang chủ</RouterLink>
        <span>/</span>
        <RouterLink to="/products" class="hover:text-primary-600">Sản phẩm</RouterLink>
        <span>/</span>
        <span class="text-gray-800 font-medium line-clamp-1">{{ product.name }}</span>
      </nav>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">

        <!-- Images -->
        <div>
          <div class="bg-gray-50 rounded-2xl overflow-hidden mb-3">
            <img
              :src="mainImage"
              :alt="product.name"
              class="w-full h-80 object-contain p-4"
              @error="$event.target.src='/placeholder.svg'"
            />
          </div>
          <!-- Thumbnails -->
          <div v-if="product.images?.length > 1" class="flex gap-2 overflow-x-auto pb-2">
            <button v-for="img in product.images" :key="img.id"
              @click="mainImage = imageUrl(img.image_path)"
              class="flex-shrink-0 w-16 h-16 rounded-xl border-2 overflow-hidden transition"
              :class="mainImage === imageUrl(img.image_path) ? 'border-primary-500' : 'border-gray-200'">
              <img :src="imageUrl(img.image_path)" class="w-full h-full object-contain p-1"
                @error="$event.target.src='/placeholder.svg'" />
            </button>
          </div>
        </div>

        <!-- Info -->
        <div class="space-y-4">
          <div>
            <span class="text-sm font-semibold text-primary-600 uppercase">{{ product.brand?.name }}</span>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ product.name }}</h1>
          </div>

          <!-- Rating -->
          <div class="flex items-center gap-3">
            <div class="flex text-yellow-400">
              <span v-for="i in 5" :key="i">{{ i <= Math.round(product.rating_avg) ? '★' : '☆' }}</span>
            </div>
            <span class="text-sm text-gray-500">{{ product.rating_avg || 0 }}/5 ({{ product.review_count || 0 }} đánh giá)</span>
            <span class="text-sm text-gray-400">| {{ product.views }} lượt xem</span>
          </div>

          <!-- Price -->
          <div class="bg-red-50 rounded-2xl p-4">
            <div class="flex items-end gap-3">
              <span class="text-3xl font-bold text-red-600">{{ formatPrice(product.sale_price || product.price) }}</span>
              <span v-if="product.sale_price" class="text-gray-400 line-through text-lg">{{ formatPrice(product.price) }}</span>
              <span v-if="product.sale_price" class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                -{{ discountPercent }}%
              </span>
            </div>
          </div>

          <!-- Quick specs -->
          <div class="grid grid-cols-2 gap-2">
            <div v-if="product.cpu" class="bg-gray-50 rounded-xl p-3">
              <p class="text-xs text-gray-500">CPU</p>
              <p class="text-sm font-semibold text-gray-800">{{ product.cpu }}</p>
            </div>
            <div v-if="product.ram" class="bg-gray-50 rounded-xl p-3">
              <p class="text-xs text-gray-500">RAM</p>
              <p class="text-sm font-semibold text-gray-800">{{ product.ram }}</p>
            </div>
            <div v-if="product.storage" class="bg-gray-50 rounded-xl p-3">
              <p class="text-xs text-gray-500">Ổ cứng</p>
              <p class="text-sm font-semibold text-gray-800">{{ product.storage }}</p>
            </div>
            <div v-if="product.display" class="bg-gray-50 rounded-xl p-3">
              <p class="text-xs text-gray-500">Màn hình</p>
              <p class="text-sm font-semibold text-gray-800">{{ product.display }}</p>
            </div>
          </div>

          <!-- Stock -->
          <div>
            <span v-if="product.stock > 0" class="badge-success">✅ Còn hàng ({{ product.stock }})</span>
            <span v-else class="badge-danger">❌ Hết hàng</span>
          </div>

          <!-- Actions -->
          <div class="flex gap-3 pt-2">
            <button @click="addToCart" :disabled="product.stock === 0 || adding"
              class="flex-1 btn-primary flex items-center justify-center gap-2">
              <span v-if="adding" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
              🛒 {{ adding ? 'Đang thêm...' : 'Thêm Vào Giỏ' }}
            </button>
            <button @click="toggleWishlist"
              class="w-12 h-12 rounded-xl border-2 flex items-center justify-center text-xl transition"
              :class="inWishlist ? 'border-red-400 bg-red-50 text-red-500' : 'border-gray-200 text-gray-400 hover:border-red-300'">
              {{ inWishlist ? '❤️' : '🤍' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="mt-12">
        <div class="flex border-b border-gray-200 gap-4 mb-6">
          <button v-for="tab in tabs" :key="tab.key" @click="activeTab = tab.key"
            class="pb-3 px-2 text-sm font-semibold transition border-b-2 -mb-px"
            :class="activeTab === tab.key
              ? 'border-primary-600 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'">
            {{ tab.label }}
          </button>
        </div>

        <!-- Mô tả -->
        <div v-if="activeTab === 'desc'" class="prose max-w-none text-gray-700 leading-relaxed">
          <p v-if="product.description" v-html="product.description"></p>
          <p v-else class="text-gray-400">Chưa có mô tả.</p>
        </div>

        <!-- Thông số -->
        <div v-if="activeTab === 'specs'" class="bg-white rounded-2xl border overflow-hidden">
          <table class="w-full text-sm">
            <tbody>
              <tr v-for="spec in specRows" :key="spec.key" class="border-b border-gray-50">
                <td class="px-4 py-3 bg-gray-50 font-medium text-gray-600 w-1/3">{{ spec.label }}</td>
                <td class="px-4 py-3 text-gray-800">{{ product[spec.key] || '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Đánh giá -->
        <div v-if="activeTab === 'reviews'">
          <div v-if="product.reviews?.length" class="space-y-4">
            <div v-for="r in product.reviews" :key="r.id" class="bg-white rounded-2xl border p-4">
              <div class="flex items-center gap-3 mb-2">
                <div class="w-9 h-9 bg-primary-100 rounded-full flex items-center justify-center font-semibold text-primary-700">
                  {{ r.user?.name?.[0] }}
                </div>
                <div>
                  <p class="font-semibold text-gray-800 text-sm">{{ r.user?.name }}</p>
                  <div class="flex text-yellow-400 text-xs">
                    <span v-for="i in 5" :key="i">{{ i <= r.rating ? '★' : '☆' }}</span>
                  </div>
                </div>
                <span class="ml-auto text-xs text-gray-400">{{ formatDate(r.created_at) }}</span>
              </div>
              <p class="text-sm text-gray-600">{{ r.comment }}</p>
            </div>
          </div>
          <div v-else class="text-center py-12 text-gray-400">
            <p class="text-3xl mb-2">💬</p>
            <p>Chưa có đánh giá nào.</p>
          </div>
        </div>
      </div>

      <!-- Related products -->
      <div v-if="product.related?.length" class="mt-12">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Sản Phẩm Liên Quan</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <ProductCard v-for="p in product.related" :key="p.id" :product="p" />
        </div>
      </div>
    </div>

    <!-- Not found -->
    <div v-else class="text-center py-20 text-gray-400">
      <p class="text-4xl mb-3">😕</p>
      <p class="text-lg">Không tìm thấy sản phẩm</p>
      <RouterLink to="/products" class="mt-4 inline-block btn-primary">← Xem tất cả</RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { imageUrl } from '@/utils/image'
import ProductCard from '@/components/ProductCard.vue'
import api from '@/services/api'

const route     = useRoute()
const router    = useRouter()
const toast     = useToast()
const authStore = useAuthStore()
const cartStore = useCartStore()

const loading    = ref(true)
const product    = ref(null)
const mainImage  = ref('/placeholder.svg')
const adding     = ref(false)
const inWishlist = ref(false)
const activeTab  = ref('desc')

const tabs = [
  { key: 'desc',    label: 'Mô Tả' },
  { key: 'specs',   label: 'Thông Số Kỹ Thuật' },
  { key: 'reviews', label: 'Đánh Giá' },
]

const specRows = [
  { key: 'cpu',     label: 'Bộ vi xử lý' },
  { key: 'ram',     label: 'RAM' },
  { key: 'storage', label: 'Ổ cứng' },
  { key: 'display', label: 'Màn hình' },
  { key: 'gpu',     label: 'Card đồ họa' },
  { key: 'os',      label: 'Hệ điều hành' },
  { key: 'battery', label: 'Pin' },
  { key: 'weight',  label: 'Trọng lượng' },
]

const discountPercent = computed(() => {
  if (!product.value?.sale_price) return 0
  return Math.round((1 - product.value.sale_price / product.value.price) * 100)
})

const formatPrice = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v ?? 0)
const formatDate  = (d) => new Date(d).toLocaleDateString('vi-VN')

async function loadProduct() {
  loading.value = true
  try {
    const res     = await api.get(`/shop/products/${route.params.slug}`)
    product.value = res.data.data

    // Set ảnh chính dùng imageUrl helper
    const rawImage = product.value.thumbnail
      || product.value.images?.[0]?.image_path
      || null
    mainImage.value = imageUrl(rawImage)

  } catch {
    product.value = null
  } finally {
    loading.value = false
  }
}

async function addToCart() {
  if (!authStore.isLoggedIn) { router.push('/login'); return }
  adding.value = true
  try {
    await cartStore.addToCart(product.value.id)
    toast.success('Đã thêm vào giỏ hàng!')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Không thể thêm.')
  } finally {
    setTimeout(() => adding.value = false, 1500)
  }
}

async function toggleWishlist() {
  if (!authStore.isLoggedIn) { router.push('/login'); return }
  try {
    const res = await api.post(`/wishlist/${product.value.id}`)
    inWishlist.value = res.data.in_wishlist
    toast.success(res.data.message)
  } catch {}
}

async function checkWishlist() {
  if (!authStore.isLoggedIn) return
  try {
    const res = await api.get('/wishlist')
    inWishlist.value = res.data.data.some((item) => item.product_id === product.value.id)
  } catch {}
}

onMounted(async () => {
  await loadProduct()
  if (product.value) await checkWishlist()
})
</script>