<template>
  <RouterLink :to="`/products/${product.slug}`" class="product-card group">
    <div class="relative overflow-hidden bg-gray-50 rounded-t-2xl">
      <img
        :src="productImage"
        :alt="product.name"
        class="w-full h-44 object-contain p-2 group-hover:scale-105 transition-transform duration-300"
        @error="imgError = true"
      />
      <span v-if="product.sale_price"
        class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
        -{{ discountPercent }}%
      </span>
      <span v-if="product.is_featured"
        class="absolute top-2 right-2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-1 rounded-full">
        ⭐ Nổi bật
      </span>
    </div>

    <div class="p-3">
      <p class="text-sm font-semibold text-gray-800 line-clamp-2 min-h-[2.5rem]">{{ product.name }}</p>
      <div class="mt-1 flex flex-wrap gap-1">
        <span v-if="product.cpu"     class="text-xs bg-blue-50   text-blue-700   px-1.5 py-0.5 rounded">{{ shortCpu }}</span>
        <span v-if="product.ram"     class="text-xs bg-green-50  text-green-700  px-1.5 py-0.5 rounded">{{ product.ram }}</span>
        <span v-if="product.storage" class="text-xs bg-purple-50 text-purple-700 px-1.5 py-0.5 rounded">{{ product.storage }}</span>
      </div>
      <div class="mt-2 flex items-end justify-between">
        <div>
          <p class="price-current text-base">{{ formatPrice(product.sale_price || product.price) }}</p>
          <p v-if="product.sale_price" class="price-original text-xs">{{ formatPrice(product.price) }}</p>
        </div>
        <button @click.prevent="addToCart" :disabled="adding || product.stock === 0"
          class="w-8 h-8 bg-primary-600 text-white rounded-full flex items-center justify-center
                 hover:bg-primary-700 disabled:opacity-50 transition text-sm">
          {{ adding ? '✓' : '+' }}
        </button>
      </div>
      <p v-if="product.stock > 0 && product.stock <= 3" class="text-xs text-orange-500 mt-1">
        Còn {{ product.stock }} sản phẩm!
      </p>
      <p v-if="product.stock === 0" class="text-xs text-red-500 mt-1">Hết hàng</p>
    </div>
  </RouterLink>
</template>

<script setup>
import { ref, computed } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { imageUrl } from '@/utils/image'

const props     = defineProps({ product: { type: Object, required: true } })
const toast     = useToast()
const authStore = useAuthStore()
const cartStore = useCartStore()
const router    = useRouter()
const adding    = ref(false)
const imgError  = ref(false)

// Lấy ảnh sản phẩm — ưu tiên thumbnail, fallback sang images[0], rồi placeholder
const productImage = computed(() => {
  if (imgError.value) return '/placeholder.svg'
  const src = props.product.thumbnail
    || props.product.images?.[0]?.image_path
    || null
  return imageUrl(src)
})

const discountPercent = computed(() => {
  if (!props.product.sale_price) return 0
  return Math.round((1 - props.product.sale_price / props.product.price) * 100)
})

const shortCpu = computed(() => {
  if (!props.product.cpu) return ''
  return props.product.cpu
    .replace('Intel Core ', '')
    .replace('AMD Ryzen ', 'R')
    .substring(0, 16)
})

const formatPrice = (v) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v ?? 0)

async function addToCart() {
  if (!authStore.isLoggedIn) {
    toast.warning('Vui lòng đăng nhập để thêm vào giỏ hàng!')
    router.push('/login')
    return
  }
  if (props.product.stock === 0) {
    toast.error('Sản phẩm đã hết hàng!')
    return
  }
  adding.value = true
  try {
    await cartStore.addToCart(props.product.id)
    toast.success('Đã thêm vào giỏ hàng!')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Không thể thêm vào giỏ hàng.')
  } finally {
    setTimeout(() => { adding.value = false }, 1500)
  }
}
</script>