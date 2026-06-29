<template>
  <div class="max-w-3xl space-y-5">
    <div class="flex items-center gap-3">
      <RouterLink to="/admin/orders" class="text-gray-400 hover:text-primary-600">← Đơn hàng</RouterLink>
      <span class="text-gray-300">/</span>
      <h1 class="text-xl font-bold text-gray-800">Chi Tiết Đơn Hàng</h1>
    </div>

    <div v-if="loading" class="flex justify-center py-16">
      <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else-if="order" class="space-y-4">
      <!-- Header -->
      <div class="bg-white rounded-2xl border p-5 flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-sm text-gray-500">Mã đơn hàng</p>
          <p class="font-mono font-bold text-xl text-gray-800">{{ order.order_code }}</p>
          <p class="text-xs text-gray-400 mt-1">{{ formatDate(order.created_at) }}</p>
        </div>
        <div class="flex items-center gap-3">
          <!-- Cập nhật trạng thái -->
          <select v-model="newStatus" @change="updateStatus"
            class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-400">
            <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
          </select>
          <span :class="order.payment_status === 'paid' ? 'badge-success' : 'badge-warning'">
            {{ order.payment_status === 'paid' ? '✅ Đã TT' : '⏳ Chưa TT' }}
          </span>
        </div>
      </div>

      <!-- Thông tin khách + giao hàng -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border p-5">
          <h3 class="font-bold text-gray-800 mb-3">👤 Thông Tin Khách Hàng</h3>
          <p class="font-semibold text-gray-700">{{ order.user?.name }}</p>
          <p class="text-sm text-gray-500 mt-1">📧 {{ order.user?.email }}</p>
          <p class="text-sm text-gray-500 mt-1">📞 {{ order.user?.phone || 'Chưa cập nhật' }}</p>
        </div>
        <div class="bg-white rounded-2xl border p-5">
          <h3 class="font-bold text-gray-800 mb-3">📦 Địa Chỉ Giao Hàng</h3>
          <p class="font-semibold text-gray-700">{{ order.shipping_name }}</p>
          <p class="text-sm text-gray-500 mt-1">📞 {{ order.shipping_phone }}</p>
          <p class="text-sm text-gray-500 mt-1">📍 {{ order.shipping_address }}, {{ order.shipping_city }}</p>
          <p v-if="order.note" class="text-sm text-gray-400 mt-2 italic">📝 {{ order.note }}</p>
        </div>
      </div>

      <!-- Sản phẩm -->
      <div class="bg-white rounded-2xl border p-5">
        <h3 class="font-bold text-gray-800 mb-4">🛒 Sản Phẩm</h3>
        <div class="space-y-3">
          <div v-for="item in order.items" :key="item.id"
            class="flex gap-4 items-center py-3 border-b border-gray-50 last:border-0">
            <img :src="item.product?.thumbnail" class="w-14 h-14 rounded-xl object-contain bg-gray-50 p-1 flex-shrink-0"
              @error="$event.target.src='/placeholder.jpg'" />
            <div class="flex-1">
              <p class="font-medium text-gray-800 text-sm">{{ item.product_name }}</p>
              <p class="text-xs text-gray-400">SKU: {{ item.product_sku }}</p>
            </div>
            <div class="text-right text-sm flex-shrink-0">
              <p class="text-gray-600">{{ formatPrice(item.unit_price) }} × {{ item.quantity }}</p>
              <p class="font-bold text-gray-800">{{ formatPrice(item.total_price) }}</p>
            </div>
          </div>
        </div>

        <!-- Tổng -->
        <div class="border-t pt-4 mt-2 space-y-2 text-sm">
          <div class="flex justify-between text-gray-600"><span>Tạm tính</span><span>{{ formatPrice(order.total_amount) }}</span></div>
          <div v-if="order.discount_amount > 0" class="flex justify-between text-green-600">
            <span>Giảm giá</span><span>-{{ formatPrice(order.discount_amount) }}</span>
          </div>
          <div class="flex justify-between text-gray-600"><span>Vận chuyển</span><span class="text-green-600">Miễn phí</span></div>
          <div class="flex justify-between font-bold text-base border-t pt-2">
            <span>Tổng cộng</span>
            <span class="text-primary-600">{{ formatPrice(order.final_amount) }}</span>
          </div>
        </div>
      </div>

      <!-- Thanh toán -->
      <div class="bg-white rounded-2xl border p-5">
        <h3 class="font-bold text-gray-800 mb-3">💳 Thanh Toán</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
          <div>
            <p class="text-gray-500">Phương thức</p>
            <p class="font-semibold">{{ paymentLabel(order.payment_method) }}</p>
          </div>
          <div>
            <p class="text-gray-500">Trạng thái</p>
            <p :class="order.payment_status === 'paid' ? 'text-green-600 font-semibold' : 'text-yellow-600 font-semibold'">
              {{ order.payment_status === 'paid' ? '✅ Đã thanh toán' : '⏳ Chưa thanh toán' }}
            </p>
          </div>
          <div v-if="order.vnpay_txn_ref">
            <p class="text-gray-500">Mã giao dịch VNPay</p>
            <p class="font-mono text-xs">{{ order.vnpay_txn_ref }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { useToast } from 'vue-toastification'
import api from '@/services/api'

const route   = useRoute()
const toast   = useToast()
const loading = ref(true)
const order   = ref(null)
const newStatus = ref('')

const statuses = [
  {value:'pending',label:'Chờ xác nhận'},{value:'confirmed',label:'Đã xác nhận'},
  {value:'processing',label:'Đang xử lý'},{value:'shipped',label:'Đang giao'},
  {value:'delivered',label:'Đã giao'},{value:'cancelled',label:'Đã hủy'}
]

const formatPrice  = (v) => new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND'}).format(v)
const formatDate   = (d) => new Date(d).toLocaleString('vi-VN')
const paymentLabel = (m) => ({cod:'COD',vnpay:'VNPay',bank_transfer:'Chuyển khoản'})[m] || m

async function updateStatus() {
  try {
    const res = await api.patch(`/admin/orders/${order.value.id}/status`, { status: newStatus.value })
    order.value = res.data.data
    newStatus.value = order.value.status
    toast.success(res.data.mail_sent ? 'Cập nhật trạng thái và gửi email thành công!' : 'Cập nhật trạng thái thành công!')
  } catch (e) { toast.error(e.response?.data?.message || 'Thao tác thất bại.') }
}

onMounted(async () => {
  try {
    const res  = await api.get(`/admin/orders/${route.params.id}`)
    order.value    = res.data.data
    newStatus.value = order.value.status
  } catch { order.value = null }
  finally { loading.value = false }
})
</script>
