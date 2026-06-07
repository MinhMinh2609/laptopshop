<template>
  <div class="space-y-5">
    <h1 class="text-2xl font-bold text-gray-800">⭐ Quản Lý Đánh Giá</h1>

    <!-- Filter -->
    <div class="bg-white rounded-2xl border p-4 flex gap-3">
      <select v-model="filterApproved" @change="fetchReviews" class="form-input max-w-44">
        <option value="">Tất cả</option>
        <option value="0">Chờ duyệt</option>
        <option value="1">Đã duyệt</option>
      </select>
    </div>

    <div class="bg-white rounded-2xl border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      </div>
      <table v-else class="table-admin">
        <thead>
          <tr><th>Sản phẩm</th><th>Người dùng</th><th>Đánh giá</th><th>Nội dung</th><th>Trạng thái</th><th>Thao tác</th></tr>
        </thead>
        <tbody>
          <tr v-if="!reviews.length">
            <td colspan="6" class="text-center py-12 text-gray-400">Không có đánh giá nào</td>
          </tr>
          <tr v-for="r in reviews" :key="r.id">
            <td>
              <p class="text-sm font-medium text-gray-800 line-clamp-1 max-w-36">{{ r.product?.name }}</p>
            </td>
            <td>
              <p class="text-sm font-medium text-gray-700">{{ r.user?.name }}</p>
              <p class="text-xs text-gray-400">{{ r.user?.email }}</p>
            </td>
            <td>
              <div class="flex text-yellow-400 text-sm">
                <span v-for="i in 5" :key="i">{{ i <= r.rating ? '★' : '☆' }}</span>
              </div>
              <p class="text-xs text-gray-400">{{ r.rating }}/5</p>
            </td>
            <td class="max-w-48">
              <p class="text-sm text-gray-600 line-clamp-2">{{ r.comment || '(Không có nhận xét)' }}</p>
              <p class="text-xs text-gray-400 mt-1">{{ formatDate(r.created_at) }}</p>
            </td>
            <td>
              <span :class="r.is_approved ? 'badge-success' : 'badge-warning'">
                {{ r.is_approved ? '✅ Đã duyệt' : '⏳ Chờ duyệt' }}
              </span>
            </td>
            <td>
              <div class="flex gap-2">
                <button @click="toggleApprove(r)"
                  class="text-xs px-3 py-1.5 rounded-lg transition"
                  :class="r.is_approved ? 'bg-yellow-50 text-yellow-600 hover:bg-yellow-100' : 'bg-green-50 text-green-600 hover:bg-green-100'">
                  {{ r.is_approved ? 'Ẩn' : 'Duyệt' }}
                </button>
                <button @click="deleteReview(r.id)"
                  class="text-xs px-3 py-1.5 bg-red-50 text-red-500 rounded-lg hover:bg-red-100">Xóa</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="lastPage > 1" class="flex justify-center gap-2 p-4 border-t">
        <button v-for="p in lastPage" :key="p" @click="currentPage = p; fetchReviews()"
          class="pagination-btn" :class="{ active: currentPage === p }">{{ p }}</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import api from '@/services/api'

const toast          = useToast()
const loading        = ref(true)
const reviews        = ref([])
const filterApproved = ref('')
const currentPage    = ref(1)
const lastPage       = ref(1)

const formatDate = (d) => new Date(d).toLocaleDateString('vi-VN')

async function fetchReviews() {
  loading.value = true
  try {
    const res = await api.get('/admin/reviews', {
      params: { is_approved: filterApproved.value !== '' ? filterApproved.value : undefined, page: currentPage.value }
    })
    reviews.value  = res.data.data.data
    lastPage.value = res.data.data.last_page
  } catch { reviews.value = [] }
  finally { loading.value = false }
}

async function toggleApprove(r) {
  try {
    await api.patch(`/admin/reviews/${r.id}/approve`)
    r.is_approved = !r.is_approved
    toast.success(r.is_approved ? 'Đã duyệt đánh giá!' : 'Đã ẩn đánh giá!')
  } catch { toast.error('Thao tác thất bại.') }
}

async function deleteReview(id) {
  if (!confirm('Xóa đánh giá này?')) return
  try {
    await api.delete(`/admin/reviews/${id}`)
    toast.success('Đã xóa đánh giá!')
    fetchReviews()
  } catch { toast.error('Không thể xóa.') }
}

onMounted(fetchReviews)
</script>