<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <p class="text-sm text-gray-500 mt-0.5">Tổng quan hoạt động kinh doanh</p>
      </div>
      <div class="text-sm text-gray-500">{{ todayLabel }}</div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
      <div v-for="i in 4" :key="i" class="bg-white rounded-2xl p-5 animate-pulse h-28"></div>
    </div>

    <template v-else>

      <!-- ─── STAT CARDS ─────────────────────────────────────── -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-2xl shrink-0">💰</div>
          <div class="min-w-0">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Tổng doanh thu</p>
            <p class="text-xl font-bold text-gray-800 truncate">{{ fmt(stats.total_revenue) }}</p>
            <p class="text-xs text-blue-600 mt-0.5">Tháng này: {{ fmt(stats.revenue_this_month) }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center text-2xl shrink-0">📦</div>
          <div class="min-w-0">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Tổng đơn hàng</p>
            <p class="text-xl font-bold text-gray-800">{{ stats.total_orders?.toLocaleString() }}</p>
            <p class="text-xs text-green-600 mt-0.5">Hôm nay: +{{ stats.orders_today }}</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-2xl shrink-0">👥</div>
          <div class="min-w-0">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Khách hàng</p>
            <p class="text-xl font-bold text-gray-800">{{ stats.total_users?.toLocaleString() }}</p>
            <p class="text-xs text-purple-600 mt-0.5">Tài khoản đã đăng ký</p>
          </div>
        </div>

        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex items-center gap-4">
          <div class="w-12 h-12 rounded-xl bg-orange-100 flex items-center justify-center text-2xl shrink-0">💻</div>
          <div class="min-w-0">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Sản phẩm</p>
            <p class="text-xl font-bold text-gray-800">{{ stats.total_products?.toLocaleString() }}</p>
            <p class="text-xs text-orange-600 mt-0.5">Đang kinh doanh</p>
          </div>
        </div>

      </div>

      <!-- ─── ROW 2: CHART + STATUS ─────────────────────────── -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Revenue Chart -->
        <div class="xl:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800">Doanh thu</h2>
            <div class="flex gap-1">
              <button v-for="p in periods" :key="p.value"
                @click="changePeriod(p.value)"
                :class="['px-3 py-1 rounded-lg text-xs font-medium transition',
                         period === p.value ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200']">
                {{ p.label }}
              </button>
            </div>
          </div>

          <!-- Bar Chart (SVG) -->
          <div v-if="revenueData.length" class="overflow-x-auto">
            <svg :width="chartWidth" height="180" class="block" style="min-width:100%">
              <!-- Y grid lines -->
              <line v-for="i in 4" :key="i"
                x1="40" :x2="chartWidth - 10"
                :y1="10 + (i-1) * 42" :y2="10 + (i-1) * 42"
                stroke="#f3f4f6" stroke-width="1"/>
              <!-- Y labels -->
              <text v-for="i in 4" :key="'y'+i"
                x="35" :y="14 + (i-1) * 42"
                font-size="9" fill="#9ca3af" text-anchor="end">
                {{ fmtShort(chartMaxRevenue - (i-1) * (chartMaxRevenue / 3)) }}
              </text>
              <!-- Bars -->
              <g v-for="(item, idx) in revenueData" :key="idx">
                <rect
                  :x="40 + idx * barSlotWidth + barPadding"
                  :y="10 + (1 - item.revenue / (chartMaxRevenue || 1)) * 126 + 0"
                  :width="barWidth"
                  :height="item.revenue / (chartMaxRevenue || 1) * 126"
                  rx="3" fill="#3b82f6" opacity="0.85"
                  class="hover:opacity-100 transition-opacity cursor-pointer">
                  <title>{{ item.date ?? item.month }}: {{ fmt(item.revenue) }}</title>
                </rect>
                <!-- X label -->
                <text
                  :x="40 + idx * barSlotWidth + barSlotWidth / 2"
                  y="178" font-size="8" fill="#9ca3af" text-anchor="middle">
                  {{ fmtLabel(item.date ?? item.month) }}
                </text>
              </g>
            </svg>
          </div>
          <div v-else class="h-40 flex items-center justify-center text-gray-400 text-sm">
            Chưa có dữ liệu doanh thu
          </div>
        </div>

        <!-- Orders by Status -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <h2 class="font-semibold text-gray-800 mb-4">Trạng thái đơn hàng</h2>
          <div class="space-y-3">
            <div v-for="item in ordersByStatus" :key="item.status" class="flex items-center gap-3">
              <span :class="['w-2 h-2 rounded-full shrink-0', statusColor(item.status)]"></span>
              <div class="flex-1 min-w-0">
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-700 capitalize">{{ statusLabel(item.status) }}</span>
                  <span class="font-semibold text-gray-800">{{ item.count }}</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                  <div :class="['h-1.5 rounded-full transition-all', statusBarColor(item.status)]"
                    :style="{ width: (item.count / totalOrders * 100).toFixed(1) + '%' }">
                  </div>
                </div>
              </div>
            </div>
            <p v-if="!ordersByStatus.length" class="text-sm text-gray-400">Chưa có đơn hàng</p>
          </div>
        </div>

      </div>

      <!-- ─── ROW 3: TOP PRODUCTS + LOW STOCK ──────────────── -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Top Products -->
        <div class="xl:col-span-2 bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800">Top sản phẩm bán chạy</h2>
            <RouterLink to="/admin/products" class="text-xs text-blue-600 hover:underline">Xem tất cả</RouterLink>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="text-left text-xs text-gray-500 font-medium pb-2">#</th>
                  <th class="text-left text-xs text-gray-500 font-medium pb-2">Sản phẩm</th>
                  <th class="text-right text-xs text-gray-500 font-medium pb-2">Đã bán</th>
                  <th class="text-right text-xs text-gray-500 font-medium pb-2">Doanh thu</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                <tr v-for="(p, i) in topProducts" :key="p.id" class="hover:bg-gray-50 transition">
                  <td class="py-2.5 pr-3">
                    <span :class="['w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold',
                      i === 0 ? 'bg-yellow-100 text-yellow-700' :
                      i === 1 ? 'bg-gray-200 text-gray-600' :
                      i === 2 ? 'bg-orange-100 text-orange-600' : 'bg-gray-100 text-gray-500']">
                      {{ i + 1 }}
                    </span>
                  </td>
                  <td class="py-2.5 pr-4 max-w-[200px]">
                    <p class="font-medium text-gray-800 truncate">{{ p.name }}</p>
                    <p class="text-xs text-gray-400">{{ p.sku }}</p>
                  </td>
                  <td class="py-2.5 text-right font-semibold text-gray-700">{{ p.total_sold }}</td>
                  <td class="py-2.5 text-right text-blue-600 font-medium whitespace-nowrap">{{ fmt(p.total_revenue) }}</td>
                </tr>
                <tr v-if="!topProducts.length">
                  <td colspan="4" class="py-8 text-center text-gray-400 text-sm">Chưa có dữ liệu</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100">
          <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-gray-800">Sắp hết hàng</h2>
            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full font-medium">
              {{ lowStockProducts.length }} sản phẩm
            </span>
          </div>
          <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
            <div v-for="p in lowStockProducts" :key="p.id"
              class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-red-50 border border-red-100">
              <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ p.name }}</p>
                <p class="text-xs text-gray-400">{{ p.sku }}</p>
              </div>
              <span :class="['text-xs font-bold px-2 py-1 rounded-lg shrink-0',
                p.stock === 0 ? 'bg-red-600 text-white' : 'bg-orange-100 text-orange-700']">
                {{ p.stock === 0 ? 'Hết hàng' : `Còn ${p.stock}` }}
              </span>
            </div>
            <p v-if="!lowStockProducts.length" class="text-sm text-gray-400 text-center py-6">
              Tất cả sản phẩm còn hàng
            </p>
          </div>
        </div>

      </div>

    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import dayjs from 'dayjs'
import api from '@/services/api'

// ─── State ────────────────────────────────────────────────
const loading         = ref(true)
const stats           = ref({})
const revenueData     = ref([])
const topProducts     = ref([])
const period          = ref('month')

const periods = [
  { label: '7N', value: 'week' },
  { label: '30N', value: 'month' },
  { label: '12T', value: 'year' },
]

const todayLabel = dayjs().format('dddd, DD/MM/YYYY').replace(/^\w/, c => c.toUpperCase())

// ─── Computed ─────────────────────────────────────────────
const ordersByStatus = computed(() => stats.value.orders_by_status ?? [])
const lowStockProducts = computed(() => stats.value.low_stock_products ?? [])
const totalOrders = computed(() => ordersByStatus.value.reduce((s, i) => s + Number(i.count), 0) || 1)

// Chart dimensions
const barSlotWidth = computed(() => {
  const n = revenueData.value.length || 1
  return Math.max(20, Math.min(40, 560 / n))
})
const barWidth = computed(() => Math.max(6, barSlotWidth.value - 6))
const barPadding = computed(() => (barSlotWidth.value - barWidth.value) / 2)
const chartWidth = computed(() => 40 + barSlotWidth.value * (revenueData.value.length || 1) + 10)
const chartMaxRevenue = computed(() => {
  const max = Math.max(...revenueData.value.map(d => Number(d.revenue) || 0), 1)
  return Math.ceil(max / 1000000) * 1000000
})

// ─── Fetch ────────────────────────────────────────────────
async function fetchAll() {
  loading.value = true
  try {
    const [dashRes, revRes, topRes] = await Promise.all([
      api.get('/admin/dashboard'),
      api.get('/admin/dashboard/revenue', { params: { period: period.value } }),
      api.get('/admin/dashboard/top-products', { params: { limit: 5 } }),
    ])
    stats.value       = dashRes.data.data
    revenueData.value = revRes.data.data
    topProducts.value = topRes.data.data
  } catch (e) {
    // errors handled by api interceptor
  } finally {
    loading.value = false
  }
}

async function changePeriod(val) {
  period.value = val
  try {
    const res = await api.get('/admin/dashboard/revenue', { params: { period: val } })
    revenueData.value = res.data.data
  } catch {}
}

onMounted(fetchAll)

// ─── Helpers ──────────────────────────────────────────────
function fmt(val) {
  if (!val) return '0đ'
  const n = Number(val)
  if (n >= 1_000_000_000) return (n / 1_000_000_000).toFixed(1) + ' tỷ'
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + ' tr'
  return n.toLocaleString('vi-VN') + 'đ'
}

function fmtShort(val) {
  const n = Number(val)
  if (n >= 1_000_000) return (n / 1_000_000).toFixed(0) + 'M'
  if (n >= 1_000) return (n / 1_000).toFixed(0) + 'K'
  return n.toFixed(0)
}

function fmtLabel(val) {
  if (!val) return ''
  if (val.includes('-') && val.length === 10) return dayjs(val).format('DD/M')  // date
  if (val.length === 7) return dayjs(val).format('M/YY')                         // month
  return val
}

const STATUS_MAP = {
  pending:    { label: 'Chờ xác nhận', dot: 'bg-yellow-400', bar: 'bg-yellow-400' },
  confirmed:  { label: 'Đã xác nhận',  dot: 'bg-blue-400',   bar: 'bg-blue-400' },
  processing: { label: 'Đang xử lý',   dot: 'bg-indigo-400', bar: 'bg-indigo-400' },
  shipped:    { label: 'Đang giao',     dot: 'bg-cyan-400',   bar: 'bg-cyan-400' },
  delivered:  { label: 'Hoàn thành',   dot: 'bg-green-500',  bar: 'bg-green-500' },
  cancelled:  { label: 'Đã huỷ',       dot: 'bg-red-400',    bar: 'bg-red-400' },
  refunded:   { label: 'Hoàn tiền',    dot: 'bg-gray-400',   bar: 'bg-gray-400' },
}
const statusLabel    = s => STATUS_MAP[s]?.label    ?? s
const statusColor    = s => STATUS_MAP[s]?.dot      ?? 'bg-gray-400'
const statusBarColor = s => STATUS_MAP[s]?.bar      ?? 'bg-gray-400'
</script>
