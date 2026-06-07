<template>
  <div class="space-y-5">
    <h1 class="text-2xl font-bold text-gray-800">👥 Quản Lý Người Dùng</h1>

    <div class="bg-white rounded-2xl border p-4 flex gap-3">
      <input v-model="search" @input="debouncedFetch" type="text"
        placeholder="Tìm tên, email..." class="form-input max-w-xs" />
      <select v-model="filterRole" @change="fetchUsers" class="form-input max-w-36">
        <option value="">Tất cả</option>
        <option value="admin">Admin</option>
        <option value="user">User</option>
      </select>
    </div>

    <div class="bg-white rounded-2xl border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      </div>
      <table v-else class="table-admin">
        <thead>
          <tr>
            <th>Người dùng</th><th>Quyền</th><th>Đơn hàng</th>
            <th>Ngày tạo</th><th>Trạng thái</th><th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!users.length">
            <td colspan="6" class="text-center py-12 text-gray-400">Không có người dùng nào</td>
          </tr>
          <tr v-for="u in users" :key="u.id">
            <td>
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center font-semibold text-sm"
                  :class="u.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'">
                  {{ u.name[0]?.toUpperCase() }}
                </div>
                <div>
                  <p class="font-medium text-gray-800 text-sm">{{ u.name }}</p>
                  <p class="text-xs text-gray-400">{{ u.email }}</p>
                </div>
              </div>
            </td>
            <td><span :class="u.role === 'admin' ? 'badge-purple' : 'badge-info'">{{ u.role }}</span></td>
            <td class="text-sm text-gray-600">{{ u.orders_count }} đơn</td>
            <td class="text-xs text-gray-500">{{ formatDate(u.created_at) }}</td>
            <td>
              <span :class="u.is_active ? 'badge-success' : 'badge-danger'">
                {{ u.is_active ? 'Hoạt động' : 'Đã khóa' }}
              </span>
            </td>
            <td>
              <button @click="toggleActive(u)"
                class="text-xs px-3 py-1.5 rounded-lg transition"
                :class="u.is_active ? 'bg-red-50 text-red-500 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100'">
                {{ u.is_active ? 'Khóa' : 'Mở khóa' }}
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-if="lastPage > 1" class="flex justify-center gap-2 p-4 border-t">
        <button v-for="p in lastPage" :key="p" @click="currentPage = p; fetchUsers()"
          class="pagination-btn" :class="{ active: currentPage === p }">{{ p }}</button>
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
const users       = ref([])
const search      = ref('')
const filterRole  = ref('')
const currentPage = ref(1)
const lastPage    = ref(1)

const formatDate = (d) => new Date(d).toLocaleDateString('vi-VN')

async function fetchUsers() {
  loading.value = true
  try {
    const res = await api.get('/admin/users', { params: { search: search.value, role: filterRole.value, page: currentPage.value } })
    users.value    = res.data.data.data
    lastPage.value = res.data.data.last_page
  } catch { users.value = [] }
  finally { loading.value = false }
}

async function toggleActive(u) {
  try {
    await api.patch(`/admin/users/${u.id}/toggle-active`)
    u.is_active = !u.is_active
    toast.success(u.is_active ? 'Đã mở khóa tài khoản!' : 'Đã khóa tài khoản!')
  } catch (e) { toast.error(e.response?.data?.message || 'Thao tác thất bại.') }
}

let timer
function debouncedFetch() { clearTimeout(timer); timer = setTimeout(fetchUsers, 400) }
onMounted(fetchUsers)
</script>