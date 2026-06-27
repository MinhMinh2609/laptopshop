<template>
  <div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center gap-3 mb-6">
      <RouterLink to="/orders" class="text-gray-400 hover:text-primary-600">← Đơn hàng của tôi</RouterLink>
      <span class="text-gray-300">/</span>
      <span class="font-semibold text-gray-700">{{ orderCode }}</span>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else-if="order" class="space-y-4">
      <!-- Status banner -->
      <div class="bg-white rounded-2xl border p-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
          <div>
            <p class="text-sm text-gray-500 mb-1">Mã đơn hàng</p>
            <p class="font-mono font-bold text-lg text-gray-800">{{ order.order_code }}</p>
          </div>
          <div class="text-right">
            <span :class="'status-' + order.status" class="text-sm px-3 py-1.5">{{ statusLabel(order.status) }}</span>
            <p class="text-xs text-gray-400 mt-1">{{ formatDate(order.created_at) }}</p>
          </div>
        </div>

        <!-- Progress bar -->
        <div class="mt-5">
          <div class="flex items-center justify-between text-xs text-gray-400 mb-2">
            <span v-for="step in progressSteps" :key="step.key"
              :class="stepReached(step.key) ? 'text-primary-600 font-semibold' : ''">
              {{ step.label }}
            </span>
          </div>
          <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full transition-all duration-500"
              :style="`width: ${progressPercent}%`"></div>
          </div>
        </div>
      </div>

      <!-- Items -->
      <div class="bg-white rounded-2xl border p-5">
        <h3 class="font-bold text-gray-800 mb-4">Sản Phẩm Đã Đặt</h3>
        <div class="space-y-3">
          <div v-for="item in order.items" :key="item.id" class="py-3 border-b border-gray-50 last:border-0">
            <div class="flex gap-4">
              <img :src="item.product?.thumbnail" class="w-16 h-16 rounded-xl object-contain bg-gray-50 p-1"
                @error="$event.target.src='/placeholder.jpg'" />
              <div class="flex-1">
                <p class="font-medium text-gray-800 text-sm">{{ item.product_name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">SKU: {{ item.product_sku }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ formatPrice(item.unit_price) }} x {{ item.quantity }}</p>
              </div>
              <p class="font-bold text-gray-800 self-center">{{ formatPrice(item.total_price) }}</p>
            </div>

            <!-- Review action -->
            <div v-if="order.status === 'delivered'" class="mt-2 pl-20">
              <button @click="openReviewModal(item)"
                class="text-xs font-semibold text-primary-600 hover:underline">
                {{ myReview(item.product_id) ? '✏️ Sửa đánh giá' : '⭐ Viết đánh giá' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Total -->
        <div class="border-t pt-4 mt-2 space-y-2 text-sm">
          <div class="flex justify-between text-gray-600">
            <span>Tạm tính</span><span>{{ formatPrice(order.total_amount) }}</span>
          </div>
          <div v-if="order.discount_amount > 0" class="flex justify-between text-green-600">
            <span>Giảm giá</span><span>-{{ formatPrice(order.discount_amount) }}</span>
          </div>
          <div class="flex justify-between text-gray-600">
            <span>Phí vận chuyển</span><span class="text-green-600">Miễn phí</span>
          </div>
          <div class="flex justify-between font-bold text-base border-t pt-2 mt-1">
            <span>Tổng cộng</span>
            <span class="text-primary-600">{{ formatPrice(order.final_amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Shipping + Payment info -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border p-5">
          <h3 class="font-bold text-gray-800 mb-3">📦 Địa Chỉ Giao Hàng</h3>
          <p class="font-semibold text-gray-700">{{ order.shipping_name }}</p>
          <p class="text-sm text-gray-600 mt-1">📞 {{ order.shipping_phone }}</p>
          <p class="text-sm text-gray-600 mt-1">📍 {{ order.shipping_address }}, {{ order.shipping_city }}</p>
          <p v-if="order.note" class="text-sm text-gray-500 mt-2 italic">📝 {{ order.note }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-5">
          <h3 class="font-bold text-gray-800 mb-3">💳 Thanh Toán</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Phương thức</span>
              <span class="font-medium">{{ paymentLabel(order.payment_method) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Trạng thái</span>
              <span :class="order.payment_status === 'paid' ? 'text-green-600 font-semibold' : 'text-yellow-600 font-semibold'">
                {{ order.payment_status === 'paid' ? '✅ Đã thanh toán' : '⏳ Chưa thanh toán' }}
              </span>
            </div>
          </div>

          <!-- Nếu chưa TT + VNPay -->
          <button v-if="order.payment_status === 'unpaid' && order.payment_method === 'vnpay'"
            @click="payVNPay" class="btn-primary w-full mt-4 text-sm">
            💳 Thanh Toán Ngay
          </button>
        </div>
      </div>

      <!-- Cancel button -->
      <div v-if="order.status === 'pending'" class="text-center">
        <button @click="cancelOrder" class="btn-danger text-sm">❌ Hủy Đơn Hàng</button>
      </div>
      <div v-if="order.status === 'cancelled'" class="text-center">
        <button @click="deleteOrder" class="btn-danger text-sm">Xóa đơn hàng</button>
      </div>
    </div>

    <div v-else class="text-center py-20">
      <p class="text-5xl mb-3">😕</p>
      <p class="text-gray-500">Không tìm thấy đơn hàng</p>
      <RouterLink to="/orders" class="btn-primary mt-4 inline-block">Xem Đơn Hàng</RouterLink>
    </div>

    <!-- Review modal -->
    <div v-if="reviewModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
      @click.self="closeReviewModal">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md">
        <h3 class="font-bold text-gray-800 mb-1">{{ reviewModal.id ? 'Sửa đánh giá' : 'Viết đánh giá' }}</h3>
        <p class="text-sm text-gray-500 mb-4 line-clamp-1">{{ reviewModal.productName }}</p>

        <div class="flex gap-1 mb-4 text-2xl">
          <button v-for="i in 5" :key="i" type="button" @click="reviewModal.rating = i"
            class="text-yellow-400 transition hover:scale-110">
            {{ i <= reviewModal.rating ? '★' : '☆' }}
          </button>
        </div>

        <textarea v-model="reviewModal.comment" rows="4" placeholder="Chia sẻ cảm nhận của bạn về sản phẩm..."
          class="form-input resize-none"></textarea>

        <div class="flex gap-3 mt-4">
          <button @click="closeReviewModal" class="flex-1 btn-outline text-sm">Hủy</button>
          <button @click="submitReview" :disabled="reviewModal.submitting || !reviewModal.rating"
            class="flex-1 btn-primary text-sm disabled:opacity-50">
            {{ reviewModal.submitting ? 'Đang gửi...' : 'Gửi đánh giá' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink, useRoute, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import api from '@/services/api'

const route  = useRoute()
const router = useRouter()
const toast  = useToast()

const loading   = ref(true)
const order     = ref(null)
const orderCode = computed(() => route.params.orderCode)

const progressSteps = [
  { key: 'pending', label: 'Đặt hàng' },
  { key: 'confirmed', label: 'Xác nhận' },
  { key: 'processing', label: 'Xử lý' },
  { key: 'shipped', label: 'Đang giao' },
  { key: 'delivered', label: 'Đã giao' },
]
const stepOrder = ['pending','confirmed','processing','shipped','delivered']
const stepReached = (key) => {
  if (!order.value) return false
  const cur = stepOrder.indexOf(order.value.status)
  const tar = stepOrder.indexOf(key)
  return tar <= cur
}
const progressPercent = computed(() => {
  if (!order.value) return 0
  const idx = stepOrder.indexOf(order.value.status)
  return idx < 0 ? 0 : (idx / (stepOrder.length - 1)) * 100
})

const statusLabels = { pending:'Chờ xác nhận', confirmed:'Đã xác nhận', processing:'Đang xử lý', shipped:'Đang giao', delivered:'Đã giao', cancelled:'Đã hủy', refunded:'Hoàn tiền' }
const statusLabel  = (s) => statusLabels[s] || s
const paymentLabel = (m) => ({ cod:'Tiền mặt (COD)', vnpay:'VNPay', bank_transfer:'Chuyển khoản' })[m] || m
const formatPrice  = (v) => new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND'}).format(v)
const formatDate   = (d) => new Date(d).toLocaleString('vi-VN')

async function cancelOrder() {
  if (!confirm('Bạn chắc chắn muốn hủy đơn hàng này?')) return
  try {
    await api.post(`/orders/${order.value.id}/cancel`)
    toast.success('Đã hủy đơn hàng!')
    order.value.status = 'cancelled'
  } catch (e) { toast.error(e.response?.data?.message || 'Không thể hủy.') }
}

async function deleteOrder() {
  if (!confirm('Bạn chắc chắn muốn xóa đơn hàng đã hủy này?')) return
  try {
    await api.delete(`/orders/${order.value.id}`)
    toast.success('Đã xóa đơn hàng!')
    router.push('/orders')
  } catch (e) { toast.error(e.response?.data?.message || 'Không thể xóa đơn hàng.') }
}

async function payVNPay() {
  try {
    const res = await api.post('/payment/vnpay/create', { order_id: order.value.id })
    window.location.href = res.data.data.payment_url
  } catch { toast.error('Không thể tạo thanh toán.') }
}

const reviewModal = ref({ open: false, id: null, productId: null, productName: '', rating: 0, comment: '', submitting: false })

function myReview(productId) {
  return order.value?.my_reviews?.[productId] || null
}

function openReviewModal(item) {
  const existing = myReview(item.product_id)
  reviewModal.value = {
    open: true,
    id: existing?.id || null,
    productId: item.product_id,
    productName: item.product_name,
    rating: existing?.rating || 0,
    comment: existing?.comment || '',
    submitting: false,
  }
}

function closeReviewModal() {
  reviewModal.value.open = false
}

async function submitReview() {
  reviewModal.value.submitting = true
  try {
    const payload = { rating: reviewModal.value.rating, comment: reviewModal.value.comment }
    if (reviewModal.value.id) {
      await api.put(`/reviews/${reviewModal.value.id}`, payload)
      order.value.my_reviews[reviewModal.value.productId] = { id: reviewModal.value.id, product_id: reviewModal.value.productId, ...payload }
    } else {
      const res = await api.post('/reviews', { ...payload, product_id: reviewModal.value.productId, order_id: order.value.id })
      order.value.my_reviews[reviewModal.value.productId] = res.data.data
    }
    toast.success('Cảm ơn bạn đã đánh giá! Đánh giá sẽ hiển thị sau khi được duyệt.')
    closeReviewModal()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Không thể gửi đánh giá.')
  } finally {
    reviewModal.value.submitting = false
  }
}

onMounted(async () => {
  try {
    const res  = await api.get(`/orders/${orderCode.value}`)
    order.value = res.data.data
  } catch { order.value = null }
  finally { loading.value = false }
})
</script>
