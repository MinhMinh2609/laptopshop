<template>
  <div class="max-w-5xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Thanh Toán</h1>

    <div v-if="!cartStore.items.length" class="text-center py-16">
      <p class="text-gray-400 mb-4">Giỏ hàng trống, không thể thanh toán.</p>
      <RouterLink to="/products" class="btn-primary">Xem Sản Phẩm</RouterLink>
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- Form giao hàng + thanh toán -->
      <div class="lg:col-span-2 space-y-4">

        <!-- Thông tin giao hàng -->
        <div class="bg-white rounded-2xl border p-6">
          <h3 class="font-bold text-gray-800 mb-4">📦 Thông Tin Giao Hàng</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="form-label">Họ và tên *</label>
              <input v-model="form.shipping_name" class="form-input"
                :class="{'border-red-400': errors.shipping_name}" placeholder="Nguyễn Văn A" />
              <p v-if="errors.shipping_name" class="form-error">{{ errors.shipping_name[0] }}</p>
            </div>
            <div>
              <label class="form-label">Số điện thoại *</label>
              <input v-model="form.shipping_phone" class="form-input"
                :class="{'border-red-400': errors.shipping_phone}" placeholder="0912 345 678" />
              <p v-if="errors.shipping_phone" class="form-error">{{ errors.shipping_phone[0] }}</p>
            </div>
            <div>
              <label class="form-label">Tỉnh / Thành phố *</label>
              <select v-model="form.shipping_city" class="form-input">
                <option value="">-- Chọn tỉnh thành --</option>
                <option v-for="c in cities" :key="c" :value="c">{{ c }}</option>
              </select>
              <p v-if="errors.shipping_city" class="form-error">{{ errors.shipping_city[0] }}</p>
            </div>
            <div>
              <label class="form-label">Địa chỉ cụ thể *</label>
              <input v-model="form.shipping_address" class="form-input"
                placeholder="Số nhà, đường, phường/xã" />
              <p v-if="errors.shipping_address" class="form-error">{{ errors.shipping_address[0] }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="form-label">Ghi chú (tùy chọn)</label>
              <textarea v-model="form.note" class="form-input" rows="2"
                placeholder="Ghi chú thêm cho đơn hàng..."></textarea>
            </div>
          </div>
        </div>

        <!-- Phương thức thanh toán -->
        <div class="bg-white rounded-2xl border p-6">
          <h3 class="font-bold text-gray-800 mb-4">💳 Phương Thức Thanh Toán</h3>
          <div class="space-y-3">
            <label v-for="m in paymentMethods" :key="m.value"
              class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition"
              :class="form.payment_method === m.value ? 'border-primary-500 bg-primary-50' : 'border-gray-200 hover:border-gray-300'">
              <input type="radio" :value="m.value" v-model="form.payment_method" class="accent-primary-600" />
              <span class="text-xl">{{ m.icon }}</span>
              <div>
                <p class="font-semibold text-sm text-gray-800">{{ m.label }}</p>
                <p class="text-xs text-gray-400">{{ m.desc }}</p>
              </div>
            </label>
          </div>
        </div>
      </div>

      <!-- Order summary -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border p-5 sticky top-24">
          <h3 class="font-bold text-gray-800 mb-4">Đơn Hàng ({{ cartStore.totalItems }})</h3>

          <div class="space-y-3 max-h-52 overflow-y-auto mb-4">
            <div v-for="item in cartStore.items" :key="item.id" class="flex gap-3 text-sm">
              <img :src="imageUrl(item.product?.thumbnail)" class="w-12 h-12 object-contain rounded-lg bg-gray-50 p-1 flex-shrink-0"
                @error="$event.target.src='/placeholder.jpg'" />
              <div class="flex-1 min-w-0">
                <p class="text-gray-700 text-xs line-clamp-2">{{ item.product.name }}</p>
                <p class="text-gray-500 text-xs mt-0.5">x{{ item.quantity }}</p>
              </div>
              <p class="font-semibold text-gray-800 flex-shrink-0">{{ formatPrice(item.total_price) }}</p>
            </div>
          </div>

          <div class="border-t pt-3 space-y-2 text-sm">
            <div class="flex justify-between text-gray-600">
              <span>Tạm tính</span><span>{{ formatPrice(cartStore.totalAmount) }}</span>
            </div>
            <div class="flex justify-between text-green-600">
              <span>Phí vận chuyển</span><span>Miễn phí</span>
            </div>
            <div class="flex justify-between font-bold text-gray-800 text-base border-t pt-2 mt-2">
              <span>Tổng cộng</span>
              <span class="text-primary-600">{{ formatPrice(cartStore.totalAmount) }}</span>
            </div>
          </div>

          <!-- Error -->
          <div v-if="errorMsg" class="mt-3 p-3 bg-red-50 rounded-xl text-xs text-red-600">{{ errorMsg }}</div>

          <button @click="placeOrder" :disabled="loading"
            class="btn-primary w-full mt-4 flex items-center justify-center gap-2">
            <span v-if="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            {{ loading ? 'Đang đặt hàng...' : '✅ Đặt Hàng Ngay' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { RouterLink, useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'
import { imageUrl } from '@/utils/image'

const router    = useRouter()
const toast     = useToast()
const cartStore = useCartStore()
const authStore = useAuthStore()

const loading  = ref(false)
const errorMsg = ref('')
const errors   = ref({})

const form = ref({
  shipping_name:    authStore.user?.name || '',
  shipping_phone:   authStore.user?.phone || '',
  shipping_address: authStore.user?.address || '',
  shipping_city:    '',
  payment_method:   'cod',
  note:             '',
})

const paymentMethods = [
  { value: 'cod',          icon: '💵', label: 'Thanh toán khi nhận hàng (COD)', desc: 'Trả tiền mặt khi nhận hàng' },
  { value: 'vnpay',        icon: '💳', label: 'VNPay',  desc: 'Thanh toán qua cổng VNPay (ATM/QR)' },
  { value: 'bank_transfer',icon: '🏦', label: 'Chuyển khoản ngân hàng', desc: 'Chuyển khoản trước khi giao hàng' },
]

const cities = ['Hà Nội','TP. Hồ Chí Minh','Đà Nẵng','Hải Phòng','Cần Thơ','An Giang','Bà Rịa - Vũng Tàu','Bắc Giang','Bắc Kạn','Bạc Liêu','Bắc Ninh','Bến Tre','Bình Định','Bình Dương','Bình Phước','Bình Thuận','Cà Mau','Cao Bằng','Đắk Lắk','Đắk Nông','Điện Biên','Đồng Nai','Đồng Tháp','Gia Lai','Hà Giang','Hà Nam','Hà Tĩnh','Hải Dương','Hậu Giang','Hòa Bình','Hưng Yên','Khánh Hòa','Kiên Giang','Kon Tum','Lai Châu','Lâm Đồng','Lạng Sơn','Lào Cai','Long An','Nam Định','Nghệ An','Ninh Bình','Ninh Thuận','Phú Thọ','Phú Yên','Quảng Bình','Quảng Nam','Quảng Ngãi','Quảng Ninh','Quảng Trị','Sóc Trăng','Sơn La','Tây Ninh','Thái Bình','Thái Nguyên','Thanh Hóa','Thừa Thiên Huế','Tiền Giang','Trà Vinh','Tuyên Quang','Vĩnh Long','Vĩnh Phúc','Yên Bái']

const formatPrice = (v) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v)

async function placeOrder() {
  errorMsg.value = ''
  errors.value   = {}
  if (!form.value.shipping_city) { errorMsg.value = 'Vui lòng chọn tỉnh thành.'; return }

  loading.value = true
  try {
    const res   = await api.post('/orders', form.value)
    const order = res.data.data

    // Nếu VNPay → tạo URL thanh toán
    if (form.value.payment_method === 'vnpay') {
      const pay = await api.post('/payment/vnpay/create', { order_id: order.id })
      window.location.href = pay.data.data.payment_url
      return
    }

    await cartStore.fetchCart()
    toast.success('Đặt hàng thành công!')
    router.push(`/orders/${order.order_code}`)
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value   = err.response.data.errors || {}
      errorMsg.value = err.response.data.message || 'Thông tin không hợp lệ.'
    } else {
      errorMsg.value = 'Đặt hàng thất bại. Vui lòng thử lại.'
    }
  } finally { loading.value = false }
}

onMounted(() => cartStore.fetchCart())
</script>