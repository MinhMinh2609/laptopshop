<template>
  <div class="max-w-4xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">📦 Đơn Hàng Của Tôi</h1>

    <!-- Filter tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto pb-1">
      <button v-for="tab in tabs" :key="tab.value" @click="activeTab = tab.value"
        class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition"
        :class="activeTab === tab.value ? 'bg-primary-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
        {{ tab.label }}
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="bg-gray-100 rounded-2xl h-32 animate-pulse"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!orders.length" class="text-center py-20">
      <p class="text-5xl mb-4">📭</p>
      <p class="text-lg font-semibold text-gray-600 mb-2">Chưa có đơn hàng nào</p>
      <RouterLink to="/products" class="btn-primary mt-4 inline-block">Mua Sắm Ngay</RouterLink>
    </div>

    <!-- Orders list -->
    <div v-else class="space-y-4">
      <div v-for="order in orders" :key="order.id"
        class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-card transition">

        <!-- Order header -->
        <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b">
          <div class="flex items-center gap-3">
            <span class="font-mono text-sm font-semibold text-gray-700">{{ order.order_code }}</span>
            <span :class="'status-' + order.status" class="text-xs">{{ statusLabel(order.status) }}</span>
          </div>
          <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400">{{ formatDate(order.created_at) }}</span>
            <span :class="order.payment_status === 'paid' ? 'badge-success' : 'badge-warning'" class="text-xs">
              {{ order.payment_status === 'paid' ? '✅ Đã thanh toán' : '⏳ Chưa thanh toán' }}
            </span>
          </div>
        </div>

        <!-- Order items preview -->
        <div class="px-5 py-4">
          <div class="flex gap-3 overflow-x-auto pb-1">
            <div v-for="item in order.items?.slice(0, 3)" :key="item.id" class="flex-shrink-0 flex items-center gap-2">
              <img :src="item.product?.thumbnail" class="w-12 h-12 rounded-xl object-contain bg-gray-50 p-1"
                @error="$event.target.src='/placeholder.jpg'" />
              <div>
                <p class="text-xs font-medium text-gray-700 line-clamp-1 max-w-32">{{ item.product_name }}</p>
                <p class="text-xs text-gray-400">x{{ item.quantity }} — {{ formatPrice(item.unit_price) }}</p>
              </div>
            </div>
            <div v-if="order.items?.length > 3"
              class="flex-shrink-0 w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center text-xs text-gray-500 font-semibold">
              +{{ order.items.length - 3 }}
            </div>
          </div>
        </div>

        <!-- Order footer -->
        <div class="flex items-center justify-between px-5 py-3 border-t bg-gray-50">
          <div>
            <span class="text-xs text-gray-500">Tổng cộng: </span>
            <span class="font-bold text-primary-600">{{ formatPrice(order.final_amount) }}</span>
          </div>
          <div class="flex gap-2">
            <button v-if="order.status === 'pending'" @click="cancelOrder(order.id)"
              class="text-xs px-3 py-1.5 border border-red-300 text-red-500 rounded-lg hover:bg-red-50 transition">
              Hủy đơn
            </button>
            <RouterLink :to="`/orders/${order.order_code}`"
              class="text-xs px-3 py-1.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition">
              Xem chi tiết →
            </RouterLink>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="lastPage > 1" class="flex justify-center gap-2 mt-4">
        <button v-for="p in lastPage" :key="p" @click="currentPage = p"
          class="pagination-btn" :class="{ active: currentPage === p }">{{ p }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useToast } from 'vue-toastification'
import api from '@/services/api'

const toast       = useToast()
const loading     = ref(true)
const orders      = ref([])
const activeTab   = ref('')
const currentPage = ref(1)
const lastPage    = ref(1)

const tabs = [
  { value: '',           label: 'Tất cả' },
  { value: 'pending',    label: '⏳ Chờ xác nhận' },
  { value: 'confirmed',  label: '✅ Đã xác nhận' },
  { value: 'shipped',    label: '🚚 Đang giao' },
  { value: 'delivered',  label: '✅ Đã giao' },
  { value: 'cancelled',  label: '❌ Đã hủy' },
]

const statusLabels = {
  pending:'Chờ xác nhận', confirmed:'Đã xác nhận', processing:'Đang xử lý',
  shipped:'Đang giao', delivered:'Đã giao', cancelled:'Đã hủy', refunded:'Hoàn tiền'
}
const statusLabel = (s) => statusLabels[s] || s
const formatPrice = (v) => new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND'}).format(v)
const formatDate  = (d) => new Date(d).toLocaleDateString('vi-VN')

async function fetchOrders() {
  loading.value = true
  try {
    const res    = await api.get('/orders', { params: { status: activeTab.value || undefined, page: currentPage.value } })
    orders.value = res.data.data.data
    lastPage.value = res.data.data.last_page
  } catch { orders.value = [] }
  finally { loading.value = false }
}

async function cancelOrder(id) {
  if (!confirm('Bạn chắc chắn muốn hủy đơn hàng này?')) return
  try {
    await api.post(`/orders/${id}/cancel`)
    toast.success('Đã hủy đơn hàng!')
    fetchOrders()
  } catch (e) { toast.error(e.response?.data?.message || 'Không thể hủy đơn hàng.') }
}

watch([activeTab, currentPage], fetchOrders)
onMounted(fetchOrders)
</script>