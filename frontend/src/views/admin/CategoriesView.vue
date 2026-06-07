<template>
  <div class="space-y-5">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-bold text-gray-800">🗂️ Quản Lý Danh Mục</h1>
      <button @click="openModal()" class="btn-primary text-sm">+ Thêm Danh Mục</button>
    </div>

    <div class="bg-white rounded-2xl border overflow-hidden">
      <div v-if="loading" class="p-8 text-center">
        <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
      </div>
      <table v-else class="table-admin">
        <thead><tr><th>Tên danh mục</th><th>Slug</th><th>Số SP</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
        <tbody>
          <tr v-if="!categories.length">
            <td colspan="5" class="text-center py-12 text-gray-400">Chưa có danh mục nào</td>
          </tr>
          <tr v-for="c in categories" :key="c.id">
            <td class="font-semibold text-gray-800">{{ c.name }}</td>
            <td class="font-mono text-xs text-gray-500">{{ c.slug }}</td>
            <td class="text-sm text-gray-600">{{ c.products_count }} sản phẩm</td>
            <td><span :class="c.is_active ? 'badge-success' : 'badge-danger'">{{ c.is_active ? 'Hiển thị' : 'Ẩn' }}</span></td>
            <td>
              <div class="flex gap-2">
                <button @click="openModal(c)" class="text-xs px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100">Sửa</button>
                <button @click="deleteCategory(c)" class="text-xs px-3 py-1.5 bg-red-50 text-red-500 rounded-lg hover:bg-red-100">Xóa</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal thêm/sửa -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center px-4">
      <div class="bg-white rounded-2xl w-full max-w-md p-6">
        <h3 class="font-bold text-gray-800 text-lg mb-5">{{ editItem ? 'Sửa Danh Mục' : 'Thêm Danh Mục Mới' }}</h3>
        <form @submit.prevent="saveCategory" class="space-y-4">
          <div>
            <label class="form-label">Tên danh mục *</label>
            <input v-model="modalForm.name" class="form-input" :class="{'border-red-400':modalErrors.name}"
              placeholder="VD: Laptop Gaming" required />
            <p v-if="modalErrors.name" class="form-error">{{ modalErrors.name[0] }}</p>
          </div>
          <div>
            <label class="form-label">Mô tả</label>
            <textarea v-model="modalForm.description" class="form-input" rows="2"></textarea>
          </div>
          <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" v-model="modalForm.is_active" class="w-4 h-4 accent-primary-600" />
            <span class="text-sm font-medium text-gray-700">Hiển thị danh mục</span>
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
const categories  = ref([])
const showModal   = ref(false)
const editItem    = ref(null)
const modalSaving = ref(false)
const modalErrors = ref({})
const modalForm   = ref({ name:'', description:'', is_active:true })

async function fetchCategories() {
  loading.value = true
  try { const res = await api.get('/admin/categories'); categories.value = res.data.data }
  catch { categories.value = [] }
  finally { loading.value = false }
}

function openModal(item = null) {
  editItem.value    = item
  modalErrors.value = {}
  modalForm.value   = item
    ? { name: item.name, description: item.description || '', is_active: item.is_active }
    : { name:'', description:'', is_active:true }
  showModal.value = true
}

async function saveCategory() {
  modalSaving.value = true
  modalErrors.value = {}
  try {
    if (editItem.value) {
      await api.put(`/admin/categories/${editItem.value.id}`, modalForm.value)
      toast.success('Cập nhật danh mục thành công!')
    } else {
      await api.post('/admin/categories', modalForm.value)
      toast.success('Tạo danh mục thành công!')
    }
    showModal.value = false
    fetchCategories()
  } catch (e) {
    if (e.response?.status === 422) modalErrors.value = e.response.data.errors || {}
    else toast.error('Lưu thất bại.')
  } finally { modalSaving.value = false }
}

async function deleteCategory(c) {
  if (c.products_count > 0) { toast.error(`Không thể xóa! Danh mục có ${c.products_count} sản phẩm.`); return }
  if (!confirm(`Xóa danh mục "${c.name}"?`)) return
  try { await api.delete(`/admin/categories/${c.id}`); toast.success('Đã xóa!'); fetchCategories() }
  catch (e) { toast.error(e.response?.data?.message || 'Không thể xóa.') }
}

onMounted(fetchCategories)
</script>