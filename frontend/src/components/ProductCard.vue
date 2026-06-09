<template>
  <RouterLink :to="`/products/${product.slug}`" class="product-card group">
    <div class="relative overflow-hidden bg-white">
      <img
        :src="productImage"
        :alt="product.name"
        class="h-48 w-full object-contain p-4 transition duration-300 group-hover:scale-105"
        @error="imgError = true"
      />

      <div class="absolute left-3 top-3 flex flex-col gap-2">
        <span v-if="product.sale_price" class="w-fit rounded bg-red-600 px-2 py-1 text-xs font-bold text-white">
          -{{ discountPercent }}%
        </span>
      </div>

      <span v-if="product.is_featured" class="absolute right-3 top-3 rounded bg-orange-100 px-2 py-1 text-xs font-bold text-orange-700">
        Bán chạy
      </span>
    </div>

    <div class="flex flex-1 flex-col border-t border-slate-100 p-4">
      <p class="min-h-[2.75rem] text-sm font-bold leading-5 text-slate-900 line-clamp-2">
        {{ product.name }}
      </p>

      <div class="mt-3 flex min-h-[3.75rem] flex-wrap content-start gap-1.5">
        <span v-if="product.cpu" class="spec-chip">{{ shortCpu }}</span>
        <span v-if="product.ram" class="spec-chip">{{ product.ram }}</span>
        <span v-if="product.storage" class="spec-chip">{{ product.storage }}</span>
      </div>

      <div class="mt-4">
        <p class="price-current">{{ formatPrice(product.sale_price || product.price) }}</p>
        <div class="mt-1 flex min-h-5 items-center gap-2">
          <p v-if="product.sale_price" class="price-original">{{ formatPrice(product.price) }}</p>
          <span v-if="product.sale_price" class="text-xs font-medium text-red-600">
            Tiết kiệm {{ formatPrice(product.price - product.sale_price) }}
          </span>
        </div>
      </div>

      <div class="mt-auto grid grid-cols-2 gap-2 pt-4">
        <button
          @click.stop.prevent="addCompare"
          class="h-9 rounded-md border border-slate-300 px-3 text-xs font-bold text-slate-700 transition hover:border-orange-300 hover:bg-orange-50 hover:text-orange-700"
        >
          {{ inCompare ? 'Đã chọn' : 'So sánh' }}
        </button>

        <p v-if="product.stock > 0" class="flex h-9 items-center justify-end text-xs font-medium text-emerald-700">
          Còn hàng
        </p>
        <p v-else class="flex h-9 items-center justify-end text-xs font-medium text-red-600">
          Hết hàng
        </p>

        <button
          @click.stop.prevent="addToCart"
          :disabled="adding || product.stock === 0"
          class="col-span-2 h-9 rounded-md bg-slate-950 px-3 text-xs font-bold text-white transition hover:bg-orange-600 disabled:cursor-not-allowed disabled:bg-slate-300"
        >
          {{ adding ? 'Đã thêm' : 'Thêm giỏ' }}
        </button>
      </div>
    </div>
  </RouterLink>
</template>

<script setup>
import { computed, ref } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useAuthStore } from '@/stores/auth'
import { useCartStore } from '@/stores/cart'
import { useCompareStore } from '@/stores/compare'
import { imageUrl } from '@/utils/image'

const props = defineProps({ product: { type: Object, required: true } })
const toast = useToast()
const authStore = useAuthStore()
const cartStore = useCartStore()
const compareStore = useCompareStore()
const router = useRouter()
const adding = ref(false)
const imgError = ref(false)

const productImage = computed(() => {
  if (imgError.value) return '/placeholder.svg'
  const src = props.product.thumbnail || props.product.images?.[0]?.image_path || null
  return imageUrl(src)
})

const discountPercent = computed(() => {
  if (!props.product.sale_price) return 0
  return Math.round((1 - props.product.sale_price / props.product.price) * 100)
})

const inCompare = computed(() => compareStore.has(props.product.id))

const shortCpu = computed(() => {
  if (!props.product.cpu) return ''
  return props.product.cpu
    .replace('Intel Core ', '')
    .replace('AMD Ryzen ', 'R')
    .substring(0, 16)
})

const formatPrice = (value) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value ?? 0)

async function addToCart() {
  if (!authStore.isLoggedIn) {
    toast.warning('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng.')
    router.push('/login')
    return
  }

  if (props.product.stock === 0) {
    toast.error('Sản phẩm đã hết hàng.')
    return
  }

  adding.value = true
  try {
    await cartStore.addToCart(props.product.id)
    toast.success('Đã thêm sản phẩm vào giỏ hàng.')
  } catch (e) {
    toast.error(e.response?.data?.message || 'Không thể thêm sản phẩm vào giỏ hàng.')
  } finally {
    setTimeout(() => {
      adding.value = false
    }, 1500)
  }
}

function addCompare() {
  const result = compareStore.add(props.product)
  if (result.reason === 'limit') {
    toast.warning('Chỉ so sánh tối đa 4 sản phẩm.')
  }
  if (result.reason === 'exists') {
    toast.info('Sản phẩm đã nằm trong danh sách so sánh.')
  }
}
</script>
