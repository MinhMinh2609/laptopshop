<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">🛒 Giỏ Hàng</h1>

    <!-- Loading -->
    <div v-if="cartStore.loading" class="flex justify-center py-20">
      <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Rỗng -->
    <div v-else-if="!cartStore.items.length" class="text-center py-20">
      <p class="text-6xl mb-4">🛒</p>
      <p class="text-xl font-semibold text-gray-600 mb-2">Giỏ hàng trống</p>
      <p class="text-gray-400 mb-6">Hãy thêm sản phẩm vào giỏ hàng để tiếp tục</p>
      <RouterLink to="/products" class="btn-primary">Xem Sản Phẩm</RouterLink>
    </div>

    <!-- Có items -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Cart Items -->
      <div class="lg:col-span-2 space-y-3">
        <div v-for="item in cartStore.items" :key="item.id"
          class="bg-white rounded-2xl border border-gray-100 p-4 flex gap-4 items-center">

          <!-- Ảnh -->
          <RouterLink :to="`/products/${item.product?.slug}`" class="flex-shrink-0">
            <img
              :src="imageUrl(item.product?.thumbnail)"
              :alt="item.product?.name"
              class="w-20 h-20 object-contain rounded-xl bg-gray-50 p-1"
              @error="$event.target.src='/placeholder.svg'"
            />
          </RouterLink>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <RouterLink :to="`/products/${item.product?.slug}`"
              class="font-semibold text-gray-800 text-sm line-clamp-2 hover:text-primary-600">
              {{ item.product?.name }}
            </RouterLink>
            <p class="text-xs text-gray-400 mt-0.5">{{ item.product?.brand?.name }}</p>
            <p class="text-primary-600 font-bold mt-1">{{ formatPrice(item.unit_price) }}</p>
          </div>

          <!-- Quantity -->
          <div class="flex items-center gap-2 flex-shrink-0">
            <button @click="changeQty(item, item.quantity - 1)"
              class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-600 font-bold">−</button>
            <span class="w-8 text-center font-semibold text-sm">{{ item.quantity }}</span>
            <button @click="changeQty(item, item.quantity + 1)"
              class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center hover:bg-gray-50 text-gray-600 font-bold">+</button>
          </div>

          <!-- Total + Remove -->
          <div class="text-right flex-shrink-0">
            <p class="font-bold text-gray-800">{{ formatPrice(item.total_price) }}</p>
            <button @click="removeItem(item.id)" class="text-xs text-red-400 hover:text-red-600 mt-1">Xóa</button>
          </div>
        </div>

        <button @click="clearCart" class="text-sm text-gray-400 hover:text-red-500 transition">
          🗑️ Xóa toàn bộ giỏ hàng
        </button>
      </div>

      <!-- Order Summary -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-24">
          <h3 class="font-bold text-gray-800 mb-4">Tóm Tắt Đơn Hàng</h3>

          <!-- Mã giảm giá -->
          <div class="mb-4">
            <label class="form-label">Mã giảm giá</label>
            <div v-if="!cartStore.coupon" class="flex gap-2">
              <input v-model="couponCode" @keyup.enter="applyCoupon" placeholder="Nhập mã giảm giá"
                class="form-input flex-1" :class="{'border-red-400': couponError}" />
              <button @click="applyCoupon" :disabled="couponLoading || !couponCode.trim()"
                class="btn-outline px-4 whitespace-nowrap">
                {{ couponLoading ? 'Đang...' : 'Áp dụng' }}
              </button>
            </div>
            <div v-else class="flex items-center justify-between bg-green-50 border border-green-200 rounded-xl px-3 py-2">
              <span class="text-sm font-semibold text-green-700">🎟️ {{ cartStore.coupon.code }}</span>
              <button @click="removeCoupon" class="text-xs text-red-500 hover:text-red-700">Bỏ mã</button>
            </div>
            <p v-if="couponError" class="form-error">{{ couponError }}</p>
          </div>

          <div class="space-y-2 text-sm text-gray-600 mb-4">
            <div class="flex justify-between">
              <span>Tạm tính ({{ cartStore.totalItems }} sản phẩm)</span>
              <span>{{ formatPrice(cartStore.totalAmount) }}</span>
            </div>
            <div v-if="cartStore.coupon" class="flex justify-between text-green-600">
              <span>Giảm giá ({{ cartStore.coupon.code }})</span>
              <span>−{{ formatPrice(cartStore.discountAmount) }}</span>
            </div>
            <div class="flex justify-between text-green-600">
              <span>Phí vận chuyển</span>
              <span>Miễn phí</span>
            </div>
          </div>
          <div class="border-t pt-4 mb-4">
            <div class="flex justify-between font-bold text-gray-800">
              <span>Tổng cộng</span>
              <span class="text-primary-600 text-lg">{{ formatPrice(cartStore.finalAmount) }}</span>
            </div>
          </div>
          <RouterLink to="/checkout" class="btn-primary w-full text-center block">
            Tiến Hành Thanh Toán →
          </RouterLink>
          <RouterLink to="/products" class="btn-outline w-full text-center block mt-2">
            Tiếp Tục Mua Sắm
          </RouterLink>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import { imageUrl } from '@/utils/image'

const cartStore = useCartStore()
const toast     = useToast()

const couponCode    = ref('')
const couponLoading = ref(false)
const couponError   = ref('')

const formatPrice = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v ?? 0)

async function changeQty(item, newQty) {
  if (newQty < 1) return removeItem(item.id)
  try {
    await cartStore.updateQuantity(item.id, newQty)
  } catch (e) {
    toast.error(e.response?.data?.message || 'Không thể cập nhật.')
  }
}

async function removeItem(id) {
  try {
    await cartStore.removeFromCart(id)
    toast.success('Đã xóa khỏi giỏ hàng!')
  } catch {
    toast.error('Không thể xóa.')
  }
}

async function clearCart() {
  if (!confirm('Xóa toàn bộ giỏ hàng?')) return
  try {
    await cartStore.clearCart()
    toast.success('Đã xóa giỏ hàng!')
  } catch {
    toast.error('Không thể xóa.')
  }
}

async function applyCoupon() {
  couponError.value = ''
  couponLoading.value = true
  try {
    await cartStore.applyCoupon(couponCode.value.trim())
    toast.success('Áp dụng mã giảm giá thành công!')
  } catch (e) {
    couponError.value = e.response?.data?.message || 'Mã giảm giá không hợp lệ.'
  } finally {
    couponLoading.value = false
  }
}

function removeCoupon() {
  cartStore.removeCoupon()
  couponCode.value  = ''
  couponError.value = ''
}

onMounted(() => cartStore.fetchCart())
</script>