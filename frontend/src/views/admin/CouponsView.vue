<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-800">🎟️ Quản Lý Mã Giảm Giá</h1>
      <button @click="openModal()" class="btn-primary text-sm">+ Thêm Mã Giảm Giá</button>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-2xl border p-4 flex flex-wrap gap-3">
      <input v-model="search" @input="debouncedFetch" type="text"
        placeholder="Tìm theo mã..." class="form-input max-w-xs" />
    </div>

    <div class="bg-white rounded-2xl border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      </div>
      <table v-else class="table-admin">
        <thead>
          <tr>
            <th>Mã</th>
            <th>Loại / Giá trị</th>
            <th>Điều kiện</th>
            <th>Đã dùng</th>
            <th>Hiệu lực</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!coupons.length">
            <td colspan="7" class="text-center py-12 text-gray-400">Chưa có mã giảm giá nào</td>
          </tr>
          <tr v-for="c in coupons" :key="c.id">
            <td class="font-mono text-sm font-bold text-primary-600">{{ c.code }}</td>
            <td class="text-sm text-gray-700">
              {{ c.type === 'percent' ? `${formatNumber(c.value)}%` : formatPrice(c.value) }}
            </td>
            <td class="text-xs text-gray-500">
              <p>Đơn tối thiểu: {{ formatPrice(c.min_order) }}</p>
              <p v-if="c.max_discount">Giảm tối đa: {{ formatPrice(c.max_discount) }}</p>
            </td>
            <td class="text-sm text-gray-600">
              {{ c.usage_count }}{{ c.usage_limit ? ` / ${c.usage_limit}` : '' }}
            </td>
            <td class="text-xs text-gray-500">
              <p v-if="c.starts_at">Từ: {{ formatDate(c.starts_at) }}</p>
              <p v-if="c.expires_at">Đến: {{ formatDate(c.expires_at) }}</p>
              <p v-if="!c.starts_at && !c.expires_at">Không giới hạn</p>
            </td>
            <td>
              <button @click="toggleActive(c)"
                :class="c.is_active ? 'badge-success' : 'badge-danger'">
                {{ c.is_active ? 'Đang bật' : 'Đã tắt' }}
              </button>
            </td>
            <td>
              <div class="flex gap-2">
                <button @click="openModal(c)" class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">Sửa</button>
                <button @click="deleteCoupon(c)" class="text-xs px-3 py-1.5 bg-red-50 text-red-500 rounded-lg hover:bg-red-100">Xóa</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="lastPage > 1" class="flex justify-center gap-2 p-4 border-t">
        <button v-for="p in lastPage" :key="p" @click="currentPage = p; fetchCoupons()"
          class="pagination-btn" :class="{ active: currentPage === p }">{{ p }}</button>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4 overflow-y-auto py-8">
      <div class="bg-white rounded-2xl w-full max-w-lg p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-5">{{ editItem ? 'Sửa Mã Giảm Giá' : 'Thêm Mã Giảm Giá' }}</h3>
        <form @submit.prevent="saveCoupon" class="space-y-4">
          <div>
            <label class="form-label">Mã giảm giá *</label>
            <input v-model="modalForm.code" class="form-input uppercase" :class="{'border-red-400':modalErrors.code}"
              placeholder="VD: SUMMER10" required />
            <p v-if="modalErrors.code" class="form-error">{{ modalErrors.code[0] }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="form-label">Loại giảm giá *</label>
              <select v-model="modalForm.type" class="form-input">
                <option value="percent">Theo phần trăm (%)</option>
                <option value="fixed">Số tiền cố định (đ)</option>
              </select>
            </div>
            <div>
              <label class="form-label">Giá trị *</label>
              <input v-model.number="modalForm.value" type="number" min="0"
                :max="modalForm.type === 'percent' ? 100 : undefined" step="any"
                class="form-input" :class="{'border-red-400':modalErrors.value}" required />
              <p v-if="modalErrors.value" class="form-error">{{ modalErrors.value[0] }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="form-label">Đơn tối thiểu (đ)</label>
              <input v-model.number="modalForm.min_order" type="number" min="0" step="any" class="form-input" />
            </div>
            <div>
              <label class="form-label">Giảm tối đa (đ)</label>
              <input v-model.number="modalForm.max_discount" type="number" min="0" step="any" class="form-input"
                placeholder="Không giới hạn" />
            </div>
          </div>

          <div>
            <label class="form-label">Giới hạn lượt sử dụng</label>
            <input v-model.number="modalForm.usage_limit" type="number" min="1" class="form-input"
              placeholder="Không giới hạn" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="form-label">Bắt đầu hiệu lực</label>
              <input v-model="modalForm.starts_at" type="datetime-local" class="form-input" />
            </div>
            <div>
              <label class="form-label">Hết hiệu lực</label>
              <input v-model="modalForm.expires_at" type="datetime-local" class="form-input"
                :class="{'border-red-400':modalErrors.expires_at}" />
              <p v-if="modalErrors.expires_at" class="form-error">{{ modalErrors.expires_at[0] }}</p>
            </div>
          </div>

          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="modalForm.is_active" class="w-4 h-4 accent-primary-600" />
            <span class="text-sm font-medium text-gray-700">Kích hoạt mã giảm giá</span>
          </label>

          <div class="flex gap-3 pt-2">
            <button type="submit" :disabled="modalSaving" class="btn-primary flex-1">
              {{ modalSaving ? 'Đang lưu...' : 'Lưu' }}
            </button>
            <button type="button" @click="showModal = false" class="btn-outline flex-1">Hủy</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import api from '@/services/api'

const toast       = useToast()
const loading     = ref(true)
const coupons     = ref([])
const search      = ref('')
const currentPage = ref(1)
const lastPage    = ref(1)
const showModal   = ref(false)
const editItem    = ref(null)
const modalSaving = ref(false)
const modalErrors = ref({})

const emptyForm = () => ({
  code: '', type: 'percent', value: null,
  min_order: 0, max_discount: null, usage_limit: null,
  starts_at: '', expires_at: '', is_active: true,
})
const modalForm = ref(emptyForm())

const formatPrice  = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v ?? 0)
const formatNumber = (v) => new Intl.NumberFormat('vi-VN').format(v ?? 0)
const formatDate   = (d) => new Date(d).toLocaleString('vi-VN')

// Chuyển ISO datetime từ API → định dạng cho input datetime-local
function toDatetimeLocal(value) {
  if (!value) return ''
  const d = new Date(value)
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

async function fetchCoupons() {
  loading.value = true
  try {
    const res = await api.get('/admin/coupons', { params: { search: search.value, page: currentPage.value } })
    coupons.value  = res.data.data.data
    lastPage.value = res.data.data.last_page
  } catch { coupons.value = [] }
  finally { loading.value = false }
}

function openModal(item = null) {
  editItem.value    = item
  modalErrors.value = {}
  modalForm.value   = item
    ? {
        code: item.code, type: item.type, value: Number(item.value),
        min_order: Number(item.min_order), max_discount: item.max_discount ? Number(item.max_discount) : null,
        usage_limit: item.usage_limit, is_active: item.is_active,
        starts_at: toDatetimeLocal(item.starts_at), expires_at: toDatetimeLocal(item.expires_at),
      }
    : emptyForm()
  showModal.value = true
}

async function saveCoupon() {
  modalSaving.value = true
  modalErrors.value = {}
  try {
    const payload = {
      ...modalForm.value,
      code: modalForm.value.code.trim().toUpperCase(),
      starts_at: modalForm.value.starts_at || null,
      expires_at: modalForm.value.expires_at || null,
    }

    if (editItem.value) {
      await api.put(`/admin/coupons/${editItem.value.id}`, payload)
      toast.success('Cập nhật mã giảm giá thành công!')
    } else {
      await api.post('/admin/coupons', payload)
      toast.success('Thêm mã giảm giá thành công!')
    }
    showModal.value = false
    fetchCoupons()
  } catch (e) {
    if (e.response?.status === 422) modalErrors.value = e.response.data.errors || {}
    toast.error(e.response?.data?.message || 'Lưu thất bại.')
  } finally { modalSaving.value = false }
}

async function deleteCoupon(c) {
  if (!confirm(`Xóa mã giảm giá "${c.code}"?`)) return
  try { await api.delete(`/admin/coupons/${c.id}`); toast.success('Đã xóa!'); fetchCoupons() }
  catch (e) { toast.error(e.response?.data?.message || 'Không thể xóa.') }
}

async function toggleActive(c) {
  try {
    const res = await api.patch(`/admin/coupons/${c.id}/toggle-active`)
    c.is_active = res.data.data.is_active
    toast.success(res.data.message)
  } catch (e) { toast.error(e.response?.data?.message || 'Thao tác thất bại.') }
}

let timer
function debouncedFetch() { clearTimeout(timer); currentPage.value = 1; timer = setTimeout(fetchCoupons, 400) }
onMounted(fetchCoupons)
</script>
