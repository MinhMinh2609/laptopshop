<template>
  <div class="space-y-5">
    <h1 class="text-2xl font-bold text-gray-800">📦 Quản Lý Đơn Hàng</h1>

    <!-- Filter -->
    <div class="bg-white rounded-2xl border p-4 flex flex-wrap gap-3">
      <input v-model="search" @input="debouncedFetch" type="text"
        placeholder="Tìm mã đơn, tên khách..." class="form-input max-w-xs" />
      <select v-model="filterStatus" @change="fetchOrders" class="form-input max-w-44">
        <option value="">Tất cả trạng thái</option>
        <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      </div>
      <table v-else class="table-admin">
        <thead>
          <tr>
            <th>Mã đơn hàng</th>
            <th>Khách hàng</th>
            <th>Tổng tiền</th>
            <th>Thanh toán</th>
            <th>Trạng thái</th>
            <th>Ngày đặt</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!orders.length">
            <td colspan="7" class="text-center py-12 text-gray-400">Không có đơn hàng nào</td>
          </tr>
          <tr v-for="o in orders" :key="o.id">
            <td class="font-mono text-sm font-semibold text-primary-600">{{ o.order_code }}</td>
            <td>
              <p class="text-sm font-medium text-gray-800">{{ o.user?.name }}</p>
              <p class="text-xs text-gray-400">{{ o.user?.email }}</p>
            </td>
            <td class="font-bold text-sm">{{ formatPrice(o.final_amount) }}</td>
            <td>
              <span :class="o.payment_status === 'paid' ? 'badge-success' : 'badge-warning'">
                {{ o.payment_status === 'paid' ? 'Đã TT' : 'Chưa TT' }}
              </span>
            </td>
            <td>
              <select :value="o.status" @change="updateStatus(o, $event.target.value)"
                class="text-xs border border-gray-200 rounded-lg px-2 py-1 focus:outline-none focus:ring-1 focus:ring-primary-400">
                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
              </select>
            </td>
            <td class="text-xs text-gray-500">{{ formatDate(o.created_at) }}</td>
            <td>
              <RouterLink :to="`/admin/orders/${o.id}`"
                class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">Chi tiết</RouterLink>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="lastPage > 1" class="flex justify-center gap-2 p-4 border-t">
        <button v-for="p in lastPage" :key="p" @click="currentPage = p; fetchOrders()"
          class="pagination-btn" :class="{ active: currentPage === p }">{{ p }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useToast } from 'vue-toastification'
import api from '@/services/api'

const toast        = useToast()
const loading      = ref(true)
const orders       = ref([])
const search       = ref('')
const filterStatus = ref('')
const currentPage  = ref(1)
const lastPage     = ref(1)

const statuses = [
  {value:'pending',label:'Chờ xác nhận'},{value:'confirmed',label:'Đã xác nhận'},
  {value:'processing',label:'Đang xử lý'},{value:'shipped',label:'Đang giao'},
  {value:'delivered',label:'Đã giao'},{value:'cancelled',label:'Đã hủy'}
]

const formatPrice = (v) => new Intl.NumberFormat('vi-VN',{style:'currency',currency:'VND'}).format(v)
const formatDate  = (d) => new Date(d).toLocaleDateString('vi-VN')

async function fetchOrders() {
  loading.value = true
  try {
    const res = await api.get('/admin/orders', { params: { search: search.value, status: filterStatus.value, page: currentPage.value } })
    orders.value = res.data.data.data
    lastPage.value = res.data.data.last_page
  } catch { orders.value = [] }
  finally { loading.value = false }
}

async function updateStatus(order, status) {
  try {
    const res = await api.patch(`/admin/orders/${order.id}/status`, { status })
    Object.assign(order, res.data.data)
    toast.success(res.data.mail_sent ? 'Đã cập nhật trạng thái và gửi email!' : 'Đã cập nhật trạng thái!')
  } catch (e) { toast.error(e.response?.data?.message || 'Thao tác thất bại.') }
}

let timer
function debouncedFetch() { clearTimeout(timer); timer = setTimeout(fetchOrders, 400) }
onMounted(fetchOrders)
</script>
