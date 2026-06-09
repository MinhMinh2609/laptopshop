<template>
  <div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
      <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-orange-600">Tổng quan cửa hàng</p>
        <h1 class="mt-1 text-2xl font-black text-slate-950">Dashboard quản trị</h1>
        <p class="mt-1 text-sm text-slate-500">Theo dõi doanh thu, đơn hàng, sản phẩm và tồn kho.</p>
      </div>

      <div class="flex gap-2">
        <RouterLink to="/admin/products/create" class="btn-primary text-sm">Thêm sản phẩm</RouterLink>
        <RouterLink to="/admin/orders" class="btn-outline text-sm">Xem đơn hàng</RouterLink>
      </div>
    </div>

    <div v-if="loading" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
      <div v-for="i in 4" :key="i" class="h-32 animate-pulse rounded-lg bg-white"></div>
    </div>

    <template v-else>
      <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="card in statCards" :key="card.label" class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-start justify-between gap-4">
            <div>
              <p class="text-sm font-semibold text-slate-500">{{ card.label }}</p>
              <p class="mt-2 text-2xl font-black text-slate-950">{{ card.value }}</p>
            </div>
            <div :class="['flex h-11 w-11 items-center justify-center rounded-lg text-lg font-black', card.color]">
              {{ card.icon }}
            </div>
          </div>
          <p class="mt-3 text-xs text-slate-500">{{ card.note }}</p>
        </div>
      </section>

      <section class="grid gap-4 xl:grid-cols-[1.35fr_0.65fr]">
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <div class="mb-4 flex items-center justify-between gap-3">
            <div>
              <h2 class="text-lg font-black text-slate-950">Biểu đồ doanh thu 30 ngày</h2>
              <p class="text-sm text-slate-500">Chỉ tính đơn đã thanh toán.</p>
            </div>
            <button @click="fetchRevenue" class="rounded-md px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100">
              Làm mới
            </button>
          </div>

          <div v-if="!revenueRows.length" class="rounded-lg bg-slate-50 py-16 text-center text-sm text-slate-400">
            Chưa có dữ liệu doanh thu.
          </div>
          <div v-else>
            <div class="mb-4 grid grid-cols-3 gap-3">
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-semibold text-slate-500">Cao nhất</p>
                <p class="mt-1 text-sm font-black text-slate-950">{{ compactCurrency(maxRevenue) }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-semibold text-slate-500">Tổng kỳ</p>
                <p class="mt-1 text-sm font-black text-slate-950">{{ compactCurrency(totalChartRevenue) }}</p>
              </div>
              <div class="rounded-lg bg-slate-50 p-3">
                <p class="text-xs font-semibold text-slate-500">Số điểm</p>
                <p class="mt-1 text-sm font-black text-slate-950">{{ revenueRows.length }} ngày</p>
              </div>
            </div>

            <svg viewBox="0 0 640 260" class="h-72 w-full overflow-visible">
              <line v-for="y in chartGridY" :key="y" x1="36" x2="620" :y1="y" :y2="y" stroke="#e2e8f0" stroke-width="1" />
              <polyline :points="revenueAreaPoints" fill="rgba(249, 115, 22, 0.12)" stroke="none" />
              <polyline :points="revenueLinePoints" fill="none" stroke="#f97316" stroke-linecap="round" stroke-linejoin="round" stroke-width="4" />

              <g v-for="point in revenueChartPoints" :key="point.key">
                <circle :cx="point.x" :cy="point.y" r="4" fill="#ffffff" stroke="#f97316" stroke-width="3" />
                <title>{{ point.label }}: {{ formatPrice(point.value) }}</title>
              </g>

              <g v-for="label in chartLabels" :key="label.key">
                <text :x="label.x" y="248" text-anchor="middle" class="fill-slate-400 text-[11px]">{{ label.text }}</text>
              </g>
            </svg>
          </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
          <h2 class="text-lg font-black text-slate-950">Biểu đồ trạng thái đơn</h2>
          <p class="mt-1 text-sm text-slate-500">Tổng hợp theo trạng thái hiện tại.</p>

          <div v-if="!statusRows.length" class="mt-8 rounded-lg bg-slate-50 py-12 text-center text-sm text-slate-400">
            Chưa có đơn hàng.
          </div>
          <div v-else class="mt-5 space-y-4">
            <div v-for="item in statusRows" :key="item.status">
              <div class="mb-1.5 flex items-center justify-between gap-3">
                <span :class="statusClass(item.status)">{{ statusLabel(item.status) }}</span>
                <span class="text-sm font-black text-slate-950">{{ item.count }}</span>
              </div>
              <div class="h-3 overflow-hidden rounded-full bg-slate-100">
                <div
                  class="h-full rounded-full bg-slate-950"
                  :style="{ width: statusPercent(item.count), backgroundColor: statusColor(item.status) }"
                ></div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
              <h2 class="text-lg font-black text-slate-950">Sản phẩm sắp hết hàng</h2>
              <p class="text-sm text-slate-500">Tồn kho dưới 5 sản phẩm.</p>
            </div>
            <RouterLink to="/admin/products" class="text-sm font-bold text-orange-600 hover:text-orange-700">Quản lý</RouterLink>
          </div>

          <div v-if="!lowStockProducts.length" class="px-5 py-10 text-center text-sm text-slate-400">
            Không có sản phẩm sắp hết hàng.
          </div>
          <div v-else class="divide-y divide-slate-100">
            <div v-for="product in lowStockProducts" :key="product.id" class="flex items-center justify-between gap-4 px-5 py-4">
              <div class="min-w-0">
                <p class="line-clamp-1 text-sm font-bold text-slate-900">{{ product.name }}</p>
                <p class="mt-1 text-xs text-slate-500">SKU: {{ product.sku || 'Chưa cập nhật' }}</p>
              </div>
              <span class="rounded-md bg-red-50 px-3 py-1 text-sm font-black text-red-600">{{ product.stock }}</span>
            </div>
          </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
          <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
            <div>
              <h2 class="text-lg font-black text-slate-950">Top sản phẩm bán chạy</h2>
              <p class="text-sm text-slate-500">Dựa trên đơn hàng đã giao.</p>
            </div>
            <RouterLink to="/admin/products" class="text-sm font-bold text-orange-600 hover:text-orange-700">Xem sản phẩm</RouterLink>
          </div>

          <div v-if="!topProducts.length" class="px-5 py-10 text-center text-sm text-slate-400">
            Chưa có dữ liệu bán chạy.
          </div>
          <div v-else class="divide-y divide-slate-100">
            <div v-for="product in topProducts" :key="product.id" class="flex items-center gap-4 px-5 py-4">
              <img
                :src="productImage(product)"
                :alt="product.name"
                class="h-12 w-12 shrink-0 rounded-md bg-slate-50 object-contain p-1"
                @error="$event.target.src = '/placeholder.svg'"
              />
              <div class="min-w-0 flex-1">
                <p class="line-clamp-1 text-sm font-bold text-slate-900">{{ product.name }}</p>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                  <div class="h-full rounded-full bg-orange-500" :style="{ width: topProductPercent(product.total_sold) }"></div>
                </div>
                <p class="mt-1 text-xs text-slate-500">Đã bán {{ product.total_sold || 0 }} sản phẩm</p>
              </div>
              <span class="text-sm font-black text-slate-900">{{ compactCurrency(product.total_revenue) }}</span>
            </div>
          </div>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import { imageUrl } from '@/utils/image'

const loading = ref(true)
const dashboard = ref({})
const revenueRows = ref([])
const topProducts = ref([])

const lowStockProducts = computed(() => dashboard.value.low_stock_products || [])
const statusRows = computed(() => dashboard.value.orders_by_status || [])
const maxRevenue = computed(() => Math.max(...revenueRows.value.map((row) => Number(row.revenue || 0)), 1))
const totalChartRevenue = computed(() => revenueRows.value.reduce((sum, row) => sum + Number(row.revenue || 0), 0))
const statusTotal = computed(() => Math.max(statusRows.value.reduce((sum, row) => sum + Number(row.count || 0), 0), 1))
const topSoldMax = computed(() => Math.max(...topProducts.value.map((product) => Number(product.total_sold || 0)), 1))
const chartGridY = [34, 82, 130, 178, 226]

const revenueChartPoints = computed(() => {
  const rows = revenueRows.value
  if (!rows.length) return []

  const width = 584
  const height = 192
  const left = 36
  const top = 34
  const step = rows.length > 1 ? width / (rows.length - 1) : 0

  return rows.map((row, index) => {
    const value = Number(row.revenue || 0)
    return {
      key: row.date || row.month || index,
      label: formatShortDate(row.date || row.month),
      value,
      x: left + step * index,
      y: top + height - (value / maxRevenue.value) * height,
    }
  })
})

const revenueLinePoints = computed(() =>
  revenueChartPoints.value.map((point) => `${point.x},${point.y}`).join(' ')
)

const revenueAreaPoints = computed(() => {
  const points = revenueChartPoints.value
  if (!points.length) return ''
  return `36,226 ${points.map((point) => `${point.x},${point.y}`).join(' ')} 620,226`
})

const chartLabels = computed(() => {
  const points = revenueChartPoints.value
  if (!points.length) return []
  const indexes = [...new Set([0, Math.floor((points.length - 1) / 2), points.length - 1])]
  return indexes.map((index) => ({
    key: points[index].key,
    x: points[index].x,
    text: points[index].label,
  }))
})

const statCards = computed(() => [
  {
    label: 'Doanh thu',
    value: formatPrice(dashboard.value.total_revenue),
    note: `Tháng này: ${formatPrice(dashboard.value.revenue_this_month)}`,
    icon: 'đ',
    color: 'bg-orange-50 text-orange-700',
  },
  {
    label: 'Đơn hàng',
    value: formatNumber(dashboard.value.total_orders),
    note: `Hôm nay: ${formatNumber(dashboard.value.orders_today)} đơn`,
    icon: '#',
    color: 'bg-blue-50 text-blue-700',
  },
  {
    label: 'Sản phẩm đang bán',
    value: formatNumber(dashboard.value.total_products),
    note: `${lowStockProducts.value.length} sản phẩm sắp hết hàng`,
    icon: 'SP',
    color: 'bg-emerald-50 text-emerald-700',
  },
  {
    label: 'Khách hàng',
    value: formatNumber(dashboard.value.total_users),
    note: 'Tài khoản role user',
    icon: 'KH',
    color: 'bg-purple-50 text-purple-700',
  },
])

const statusMap = {
  pending: 'Chờ xác nhận',
  confirmed: 'Đã xác nhận',
  processing: 'Đang xử lý',
  shipped: 'Đang giao',
  delivered: 'Đã giao',
  cancelled: 'Đã hủy',
  refunded: 'Đã hoàn tiền',
}

const statusColors = {
  pending: '#f59e0b',
  confirmed: '#2563eb',
  processing: '#4f46e5',
  shipped: '#0891b2',
  delivered: '#059669',
  cancelled: '#dc2626',
  refunded: '#64748b',
}

function formatNumber(value) {
  return new Intl.NumberFormat('vi-VN').format(value ?? 0)
}

function formatPrice(value) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value ?? 0)
}

function compactCurrency(value) {
  const amount = Number(value || 0)
  if (amount >= 1000000000) return `${Math.round(amount / 100000000) / 10} tỷ`
  if (amount >= 1000000) return `${Math.round(amount / 100000) / 10} tr`
  return formatPrice(amount)
}

function formatShortDate(value) {
  if (!value) return ''
  if (String(value).length === 7) return value
  return new Date(value).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' })
}

function statusPercent(value) {
  return `${Math.max(4, Math.round((Number(value || 0) / statusTotal.value) * 100))}%`
}

function topProductPercent(value) {
  return `${Math.max(4, Math.round((Number(value || 0) / topSoldMax.value) * 100))}%`
}

function statusLabel(status) {
  return statusMap[status] || status
}

function statusColor(status) {
  return statusColors[status] || '#0f172a'
}

function statusClass(status) {
  const base = 'rounded-md px-2.5 py-1 text-xs font-bold'
  const classes = {
    pending: 'bg-yellow-50 text-yellow-700',
    confirmed: 'bg-blue-50 text-blue-700',
    processing: 'bg-indigo-50 text-indigo-700',
    shipped: 'bg-cyan-50 text-cyan-700',
    delivered: 'bg-emerald-50 text-emerald-700',
    cancelled: 'bg-red-50 text-red-700',
    refunded: 'bg-slate-100 text-slate-700',
  }
  return `${base} ${classes[status] || 'bg-slate-100 text-slate-700'}`
}

function productImage(product) {
  return imageUrl(product.thumbnail || null)
}

async function fetchDashboard() {
  const res = await api.get('/admin/dashboard')
  dashboard.value = res.data.data || {}
}

async function fetchRevenue() {
  try {
    const res = await api.get('/admin/dashboard/revenue', { params: { period: 'month' } })
    revenueRows.value = res.data.data || []
  } catch {
    revenueRows.value = []
  }
}

async function fetchTopProducts() {
  try {
    const res = await api.get('/admin/dashboard/top-products', { params: { limit: 5 } })
    topProducts.value = res.data.data || []
  } catch {
    topProducts.value = []
  }
}

onMounted(async () => {
  loading.value = true
  try {
    await Promise.all([fetchDashboard(), fetchRevenue(), fetchTopProducts()])
  } catch {
    dashboard.value = {}
  } finally {
    loading.value = false
  }
})
</script>
