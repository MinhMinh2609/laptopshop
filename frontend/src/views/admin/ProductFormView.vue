<template>
  <div class="max-w-3xl space-y-5">
    <div class="flex items-center gap-3">
      <RouterLink to="/admin/products" class="text-gray-400 hover:text-primary-600">← Sản phẩm</RouterLink>
      <span class="text-gray-300">/</span>
      <h1 class="text-xl font-bold text-gray-800">{{ isEdit ? 'Chỉnh Sửa Sản Phẩm' : 'Thêm Sản Phẩm Mới' }}</h1>
    </div>

    <div v-if="pageLoading" class="flex justify-center py-16">
      <div class="w-8 h-8 border-4 border-primary-600 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <form v-else @submit.prevent="handleSubmit" class="space-y-5">

      <!-- Thông tin cơ bản -->
      <div class="bg-white rounded-2xl border p-6 space-y-4">
        <h3 class="font-bold text-gray-800 border-b pb-3">📝 Thông Tin Cơ Bản</h3>

        <div>
          <label class="form-label">Tên sản phẩm *</label>
          <input v-model="form.name" class="form-input" :class="{'border-red-400': errors.name}"
            placeholder="VD: Asus ROG Strix G15 G513IE" required />
          <p v-if="errors.name" class="form-error">{{ errors.name[0] }}</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Danh mục *</label>
            <select v-model="form.category_id" class="form-input" :class="{'border-red-400': errors.category_id}" required>
              <option value="">-- Chọn danh mục --</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <p v-if="errors.category_id" class="form-error">{{ errors.category_id[0] }}</p>
          </div>
          <div>
            <label class="form-label">Hãng *</label>
            <select v-model="form.brand_id" class="form-input" :class="{'border-red-400': errors.brand_id}" required>
              <option value="">-- Chọn hãng --</option>
              <option v-for="b in brands" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
            <p v-if="errors.brand_id" class="form-error">{{ errors.brand_id[0] }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">SKU *</label>
            <input v-model="form.sku" class="form-input" :class="{'border-red-400': errors.sku}"
              placeholder="VD: ASUS-ROG-G15-001" required />
            <p v-if="errors.sku" class="form-error">{{ errors.sku[0] }}</p>
          </div>
          <div>
            <label class="form-label">Số lượng tồn kho *</label>
            <input v-model.number="form.stock" type="number" min="0" class="form-input" required />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="form-label">Giá gốc (VNĐ) *</label>
            <input v-model.number="form.price" type="number" min="0" class="form-input"
              :class="{'border-red-400': errors.price}" placeholder="25000000" required />
            <p v-if="errors.price" class="form-error">{{ errors.price[0] }}</p>
          </div>
          <div>
            <label class="form-label">Giá KM (VNĐ) <span class="text-xs text-gray-400">— để trống nếu không KM</span></label>
            <input v-model.number="form.sale_price" type="number" min="0" class="form-input"
              placeholder="Để trống nếu không có KM" />
            <p v-if="salePriceError" class="form-error">{{ salePriceError }}</p>
          </div>
        </div>

        <div>
          <label class="form-label">Mô tả sản phẩm</label>
          <textarea v-model="form.description" class="form-input" rows="4" placeholder="Mô tả chi tiết..."></textarea>
        </div>

        <div class="flex gap-6">
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" v-model="form.is_active" class="w-4 h-4 accent-primary-600" />
            <span class="text-sm font-medium text-gray-700">✅ Hiển thị sản phẩm</span>
          </label>
          <label class="flex items-center gap-2 cursor-pointer select-none">
            <input type="checkbox" v-model="form.is_featured" class="w-4 h-4 accent-yellow-500" />
            <span class="text-sm font-medium text-gray-700">⭐ Sản phẩm nổi bật</span>
          </label>
        </div>
      </div>

      <!-- Thông số kỹ thuật -->
      <div class="bg-white rounded-2xl border p-6 space-y-4">
        <h3 class="font-bold text-gray-800 border-b pb-3">⚙️ Thông Số Kỹ Thuật</h3>
        <div class="grid grid-cols-2 gap-4">
          <div v-for="spec in specFields" :key="spec.key">
            <label class="form-label">{{ spec.label }}</label>
            <input v-model="form[spec.key]" class="form-input" :placeholder="spec.placeholder" />
          </div>
        </div>
      </div>

      <!-- Upload ảnh -->
      <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold text-gray-800 border-b pb-3 mb-4">
          🖼️ Hình Ảnh Sản Phẩm
          <span class="font-normal text-sm text-gray-400 ml-2">— tùy chọn</span>
        </h3>

        <!-- Ảnh hiện tại -->
        <div v-if="existingImages.length" class="mb-4">
          <p class="text-xs text-gray-500 mb-2">Ảnh hiện tại:</p>
          <div class="flex gap-3 flex-wrap">
            <div v-for="img in existingImages" :key="img.id" class="relative group">
              <img :src="img.image_path" class="w-20 h-20 object-contain rounded-xl border bg-gray-50 p-1" />
              <button type="button" @click="deleteImage(img.id)"
                class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs
                       items-center justify-center hover:bg-red-600 hidden group-hover:flex">✕</button>
            </div>
          </div>
        </div>

        <!-- Upload mới -->
        <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center
                    hover:border-primary-400 transition cursor-pointer"
          @click="$refs.fileInput.click()"
          @dragover.prevent
          @drop.prevent="onDrop">
          <input ref="fileInput" type="file" multiple accept="image/png,image/jpeg,image/jpg,image/webp"
            class="hidden" @change="onFileChange" />

          <div v-if="!previewImages.length">
            <p class="text-3xl mb-2">📸</p>
            <p class="text-sm font-medium text-gray-600">Kéo thả hoặc click để chọn ảnh</p>
            <p class="text-xs text-gray-400 mt-1">PNG, JPG, WebP — Tối đa 5MB/ảnh, tối đa 10 ảnh</p>
          </div>

          <div v-else class="flex gap-3 flex-wrap justify-center" @click.stop>
            <div v-for="(src, i) in previewImages" :key="i" class="relative group">
              <img :src="src" class="w-20 h-20 object-contain rounded-xl border bg-gray-50 p-1" />
              <button type="button" @click="removePreview(i)"
                class="absolute -top-1 -right-1 w-6 h-6 bg-red-500 text-white rounded-full text-xs
                       items-center justify-center hover:bg-red-600 hidden group-hover:flex">✕</button>
            </div>
            <button type="button" @click="$refs.fileInput.click()"
              class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-xl flex items-center
                     justify-center text-2xl text-gray-400 hover:border-primary-400">+</button>
          </div>
        </div>
      </div>

      <!-- Error / Success messages -->
      <div v-if="errorMsg" class="p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-600">
        ❌ {{ errorMsg }}
      </div>
      <div v-if="errors && Object.keys(errors).length" class="p-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-600">
        <p class="font-semibold mb-1">Lỗi validation:</p>
        <ul class="list-disc list-inside space-y-1">
          <li v-for="(msgs, field) in errors" :key="field">
            <strong>{{ field }}:</strong> {{ msgs[0] }}
          </li>
        </ul>
      </div>

      <!-- Buttons -->
      <div class="flex gap-3 pb-8">
        <button type="submit" :disabled="saving || !!salePriceError"
          class="btn-primary flex items-center gap-2 min-w-36">
          <span v-if="saving" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
          {{ saving ? 'Đang lưu...' : (isEdit ? '💾 Cập Nhật' : '✅ Tạo Sản Phẩm') }}
        </button>
        <RouterLink to="/admin/products" class="btn-outline">Hủy</RouterLink>
      </div>
    </form>
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

const isEdit         = computed(() => !!route.params.id)
const pageLoading    = ref(false)
const saving         = ref(false)
const errorMsg       = ref('')
const errors         = ref({})
const categories     = ref([])
const brands         = ref([])
const existingImages = ref([])
const previewImages  = ref([])
const newFiles       = ref([])

const form = ref({
  name: '', category_id: '', brand_id: '', sku: '',
  stock: 0, price: '', sale_price: '', description: '',
  is_active: true, is_featured: false,
  cpu: '', ram: '', storage: '', display: '',
  gpu: '', os: '', battery: '', weight: '',
})

const salePriceError = computed(() => {
  if (!form.value.sale_price || !form.value.price) return ''
  if (Number(form.value.sale_price) >= Number(form.value.price)) {
    return 'Giá khuyến mãi phải nhỏ hơn giá gốc'
  }
  return ''
})

const specFields = [
  { key: 'cpu',     label: 'CPU',          placeholder: 'Intel Core i5-13500H' },
  { key: 'ram',     label: 'RAM',          placeholder: '16GB DDR5' },
  { key: 'storage', label: 'Ổ cứng',       placeholder: '512GB SSD NVMe' },
  { key: 'display', label: 'Màn hình',     placeholder: '15.6 inch FHD 144Hz' },
  { key: 'gpu',     label: 'Card đồ họa',  placeholder: 'NVIDIA RTX 4060 8GB' },
  { key: 'os',      label: 'Hệ điều hành', placeholder: 'Windows 11 Home' },
  { key: 'battery', label: 'Pin',          placeholder: '90Wh' },
  { key: 'weight',  label: 'Trọng lượng',  placeholder: '2.1kg' },
]

// ─── File handling ──────────────────────────────────────
function onFileChange(e) {
  Array.from(e.target.files).forEach(readFile)
  e.target.value = '' // reset để chọn lại cùng file
}

function onDrop(e) {
  Array.from(e.dataTransfer.files)
    .filter(f => f.type.startsWith('image/'))
    .forEach(readFile)
}

function readFile(file) {
  newFiles.value.push(file)
  const reader = new FileReader()
  reader.onload = (ev) => previewImages.value.push(ev.target.result)
  reader.readAsDataURL(file)
}

function removePreview(i) {
  previewImages.value.splice(i, 1)
  newFiles.value.splice(i, 1)
}

async function deleteImage(imgId) {
  try {
    await api.delete(`/admin/products/${route.params.id}/images/${imgId}`)
    existingImages.value = existingImages.value.filter(i => i.id !== imgId)
    toast.success('Đã xóa ảnh!')
  } catch {
    toast.error('Không thể xóa ảnh.')
  }
}

// ─── Submit ─────────────────────────────────────────────
async function handleSubmit() {
  if (salePriceError.value) return

  saving.value   = true
  errorMsg.value = ''
  errors.value   = {}

  try {
    // Build payload — ép kiểu đúng
    const payload = {
      name:        form.value.name,
      category_id: Number(form.value.category_id),
      brand_id:    Number(form.value.brand_id),
      sku:         form.value.sku,
      stock:       Number(form.value.stock),
      price:       Number(form.value.price),
      sale_price:  form.value.sale_price ? Number(form.value.sale_price) : null,
      description: form.value.description || null,
      is_active:   Boolean(form.value.is_active),
      is_featured: Boolean(form.value.is_featured),
      cpu:     form.value.cpu     || null,
      ram:     form.value.ram     || null,
      storage: form.value.storage || null,
      display: form.value.display || null,
      gpu:     form.value.gpu     || null,
      os:      form.value.os      || null,
      battery: form.value.battery || null,
      weight:  form.value.weight  || null,
    }

    let productId = route.params.id

    if (isEdit.value) {
      await api.put(`/admin/products/${productId}`, payload)
    } else {
      const res = await api.post('/admin/products', payload)
      productId = res.data.data.id
    }

    // Upload ảnh mới nếu có
    if (newFiles.value.length > 0) {
      const fd = new FormData()
      newFiles.value.forEach(f => fd.append('images[]', f))
      try {
        await api.post(`/admin/products/${productId}/images`, fd, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
      } catch (imgErr) {
        toast.warning('Thông tin đã lưu nhưng upload ảnh thất bại. Thử lại sau.')
        router.push('/admin/products')
        return
      }
    }

    toast.success(isEdit.value ? '✅ Cập nhật thành công!' : '✅ Tạo sản phẩm thành công!')
    router.push('/admin/products')

  } catch (e) {
    console.error('Submit error:', e.response?.data)
    if (e.response?.status === 422) {
      errors.value   = e.response.data.errors || {}
      errorMsg.value = e.response.data.message || 'Dữ liệu không hợp lệ.'
    } else {
      errorMsg.value = e.response?.data?.message || `Lỗi: ${e.message}`
    }
  } finally {
    saving.value = false
  }
}

// ─── Load data ──────────────────────────────────────────
onMounted(async () => {
  pageLoading.value = true
  try {
    const [catRes, brandRes] = await Promise.all([
      api.get('/shop/categories'),
      api.get('/shop/brands'),
    ])
    categories.value = catRes.data.data
    brands.value     = brandRes.data.data

    if (isEdit.value) {
      const res = await api.get(`/admin/products/${route.params.id}`)
      const p   = res.data.data

      // Map từng field — ép kiểu đúng
      form.value.name        = p.name        ?? ''
      form.value.category_id = Number(p.category_id) || ''
      form.value.brand_id    = Number(p.brand_id)    || ''
      form.value.sku         = p.sku         ?? ''
      form.value.stock       = Number(p.stock)       || 0
      form.value.price       = Number(p.price)       || ''
      form.value.sale_price  = p.sale_price ? Number(p.sale_price) : ''
      form.value.description = p.description ?? ''
      form.value.is_active   = p.is_active   === true || p.is_active   === 1
      form.value.is_featured = p.is_featured === true || p.is_featured === 1
      form.value.cpu         = p.cpu     ?? ''
      form.value.ram         = p.ram     ?? ''
      form.value.storage     = p.storage ?? ''
      form.value.display     = p.display ?? ''
      form.value.gpu         = p.gpu     ?? ''
      form.value.os          = p.os      ?? ''
      form.value.battery     = p.battery ?? ''
      form.value.weight      = p.weight  ?? ''

      existingImages.value = p.images ?? []
    }
  } catch (e) {
    toast.error('Không thể tải dữ liệu. Vui lòng thử lại.')
    console.error('Load error:', e)
  } finally {
    pageLoading.value = false
  }
})
</script>