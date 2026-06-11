<template>
  <div class="bg-slate-50">
    <section class="relative overflow-hidden bg-slate-950 text-white">
      <div class="absolute inset-0 opacity-25">
        <img :src="heroProductImage" alt="" class="h-full w-full object-cover" />
      </div>
      <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-slate-950/95 to-slate-950/45"></div>

      <div class="relative mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 lg:grid-cols-[1.05fr_0.95fr] lg:px-8 lg:py-16">
        <div class="flex flex-col justify-center">
          <p class="mb-4 inline-flex w-fit rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-orange-100">
            Gợi ý laptop từ sản phẩm đang bán
          </p>
          <h1 class="max-w-2xl text-4xl font-extrabold leading-tight sm:text-5xl lg:text-6xl">
            Chọn laptop đúng nhu cầu, giá minh bạch.
          </h1>
          <p class="mt-5 max-w-xl text-base leading-7 text-slate-200 sm:text-lg">
            Từ học tập, văn phòng đến gaming và đồ họa, shop giúp bạn lọc nhanh cấu hình, so sánh sản phẩm và mua máy phù hợp ngân sách.
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <RouterLink to="/products" class="inline-flex items-center justify-center rounded-lg bg-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-950/30 transition hover:bg-orange-400">
              Xem laptop đang bán
            </RouterLink>
            <button
              @click="compareStore.openPopup()"
              class="inline-flex items-center justify-center rounded-lg border border-white/20 bg-white/10 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/15"
            >
              Mở bảng so sánh
            </button>
          </div>

          <div class="mt-8 grid max-w-2xl grid-cols-3 gap-3 text-sm">
            <div class="rounded-lg border border-white/10 bg-white/10 p-4 text-center transition hover:border-orange-300/40 hover:bg-white/15">
              <p class="font-bold text-white">3 phương thức</p>
              <p class="mt-0.5 text-xs text-slate-300">thanh toán linh hoạt</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/10 p-4 text-center transition hover:border-orange-300/40 hover:bg-white/15">
              <p class="font-bold text-white">4 sản phẩm</p>
              <p class="mt-0.5 text-xs text-slate-300">so sánh cấu hình</p>
            </div>
            <div class="rounded-lg border border-white/10 bg-white/10 p-4 text-center transition hover:border-orange-300/40 hover:bg-white/15">
              <p class="font-bold text-white">Mã giảm giá</p>
              <p class="mt-0.5 text-xs text-slate-300">áp dụng tức thì</p>
            </div>
          </div>
        </div>

        <div class="hidden items-end justify-center lg:flex">
          <div class="relative w-full max-w-lg rounded-lg border border-white/10 bg-white/10 p-5 shadow-2xl backdrop-blur">
            <img
              :src="heroProductImage"
              :alt="heroProduct?.name || 'Laptop đang bán'"
              class="aspect-[4/3] w-full rounded-md bg-white object-contain p-6 transition"
              @error="$event.target.src = '/placeholder.svg'"
            />
            <div class="mt-4 flex items-center justify-between gap-4">
              <div class="min-w-0">
                <p class="text-sm text-slate-300">Gợi ý theo nhu cầu</p>
                <p class="line-clamp-1 font-bold">{{ heroProduct?.name || 'Đang tải sản phẩm' }}</p>
                <p v-if="heroProduct" class="mt-1 text-sm font-black text-orange-300">
                  {{ formatPrice(heroProduct.sale_price || heroProduct.price) }}
                </p>
              </div>
              <RouterLink
                :to="heroProduct ? `/products/${heroProduct.slug}` : '/products'"
                class="shrink-0 rounded-md bg-white px-4 py-2 text-sm font-bold text-slate-950 hover:bg-slate-100"
              >
                Tìm máy
              </RouterLink>
            </div>

            <div v-if="featured.length > 1" class="mt-4 flex justify-center gap-2">
              <button
                v-for="(_, index) in featured"
                :key="index"
                @click="heroIndex = index"
                class="h-2 rounded-full transition"
                :class="index === heroIndex ? 'w-7 bg-orange-400' : 'w-2 bg-white/40 hover:bg-white/70'"
                :aria-label="`Xem gợi ý ${index + 1}`"
              ></button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="border-b border-slate-200 bg-white">
      <div class="mx-auto grid max-w-7xl gap-4 px-4 py-5 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
        <div v-for="item in trustItems" :key="item.title" class="flex gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md bg-slate-100 text-sm font-bold text-slate-900">
            {{ item.short }}
          </div>
          <div>
            <p class="text-sm font-bold text-slate-900">{{ item.title }}</p>
            <p class="mt-1 text-xs leading-5 text-slate-500">{{ item.text }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
      <div class="mb-6 flex items-end justify-between gap-4">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-orange-600">Mua theo nhu cầu</p>
          <h2 class="mt-1 text-2xl font-extrabold text-slate-950">Danh mục nổi bật</h2>
        </div>
        <RouterLink to="/products" class="text-sm font-semibold text-slate-700 hover:text-orange-600">Tất cả sản phẩm</RouterLink>
      </div>

      <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
        <RouterLink
          v-for="cat in categories"
          :key="cat.slug"
          :to="`/products?category_slug=${cat.slug}`"
          class="group rounded-lg border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-orange-300 hover:shadow-lg"
        >
          <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ cat.code }}</p>
          <p class="mt-3 font-bold text-slate-950 group-hover:text-orange-600">{{ cat.name }}</p>
          <p class="mt-2 text-xs leading-5 text-slate-500">{{ cat.desc }}</p>
        </RouterLink>
      </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
      <div class="mb-6 flex items-end justify-between gap-4">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-orange-600">Đang được quan tâm</p>
          <h2 class="mt-1 text-2xl font-extrabold text-slate-950">Laptop nổi bật</h2>
        </div>
        <RouterLink to="/products" class="text-sm font-semibold text-slate-700 hover:text-orange-600">Xem thêm</RouterLink>
      </div>

      <div v-if="loading" class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <div v-for="i in 8" :key="i" class="h-72 animate-pulse rounded-lg bg-white"></div>
      </div>

      <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-4">
        <ProductCard v-for="product in featured" :key="product.id" :product="product" />
      </div>
    </section>

    <section class="bg-white">
      <div class="mx-auto grid max-w-7xl gap-6 px-4 py-10 sm:px-6 md:grid-cols-[1fr_auto] md:items-center lg:px-8">
        <div>
          <p class="text-sm font-semibold uppercase tracking-wide text-orange-600">Cần tư vấn nhanh?</p>
          <h2 class="mt-1 text-2xl font-extrabold text-slate-950">Gửi nhu cầu, shop gợi ý cấu hình phù hợp.</h2>
          <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">
            Nói rõ ngân sách, ngành học hoặc phần mềm bạn hay dùng. Chatbot sẽ giúp lọc máy nhanh trước khi đặt hàng.
          </p>
        </div>
        <RouterLink to="/products" class="inline-flex justify-center rounded-lg bg-slate-950 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800">
          Bắt đầu chọn laptop
        </RouterLink>
      </div>
    </section>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import api from '@/services/api'
import ProductCard from '@/components/ProductCard.vue'
import { imageUrl } from '@/utils/image'
import { useCompareStore } from '@/stores/compare'
import fallbackHeroImage from '@/pictures_laptop/asus/asus1.png'

const loading = ref(true)
const featured = ref([])
const heroIndex = ref(0)
const compareStore = useCompareStore()
let heroTimer

const heroProduct = computed(() => featured.value[heroIndex.value] || null)
const heroProductImage = computed(() => {
  const product = heroProduct.value
  if (!product) return fallbackHeroImage
  return imageUrl(product.thumbnail || product.images?.[0]?.image_path || null)
})

const trustItems = [
  { short: 'TT', title: 'Thanh toán đa dạng', text: 'Hỗ trợ COD, chuyển khoản ngân hàng và VNPay, chọn phương thức phù hợp khi đặt hàng.' },
  { short: 'MG', title: 'Mã giảm giá', text: 'Nhập mã ưu đãi ngay tại giỏ hàng để được giảm trực tiếp vào tổng đơn.' },
  { short: 'SS', title: 'So sánh laptop', text: 'Chọn trực tiếp từ card sản phẩm, xem bảng so sánh cấu hình ngay trên màn hình.' },
  { short: 'DG', title: 'Đánh giá sản phẩm', text: 'Khách hàng đã mua có thể đánh giá, để lại nhận xét cho người mua sau.' },
]

const categories = [
  { name: 'Gaming', slug: 'gaming', code: 'RTX', desc: 'Hiệu năng mạnh, tản nhiệt tốt.' },
  { name: 'Văn phòng', slug: 'van-phong', code: 'Office', desc: 'Gọn nhẹ, pin tốt, giá hợp lý.' },
  { name: 'Đồ họa', slug: 'do-hoa', code: 'Design', desc: 'Màn đẹp, CPU và GPU ổn định.' },
  { name: 'Mỏng nhẹ', slug: 'mong-nhe', code: 'Lite', desc: 'Dễ mang đi học và đi làm.' },
  { name: 'Sinh viên', slug: 'sinh-vien', code: 'Study', desc: 'Cân bằng giá, pin và độ bền.' },
]

const formatPrice = (value) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value ?? 0)

function startHeroRotation() {
  clearInterval(heroTimer)
  if (featured.value.length <= 1) return

  heroTimer = setInterval(() => {
    heroIndex.value = (heroIndex.value + 1) % featured.value.length
  }, 4500)
}

onMounted(async () => {
  compareStore.initialize()

  try {
    const res = await api.get('/shop/products', { params: { per_page: 8 } })
    featured.value = res.data.data.data
    startHeroRotation()
  } catch {
    featured.value = []
  } finally {
    loading.value = false
  }
})

onUnmounted(() => clearInterval(heroTimer))
</script>
