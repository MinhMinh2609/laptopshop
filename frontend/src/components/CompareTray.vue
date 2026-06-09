<template>
  <teleport to="body">
    <button
      v-if="compareStore.count && !compareStore.popupOpen"
      @click="compareStore.openPopup()"
      class="fixed bottom-24 right-5 z-40 rounded-lg bg-slate-950 px-4 py-3 text-sm font-bold text-white shadow-2xl hover:bg-orange-600"
    >
      So sánh {{ compareStore.count }} sản phẩm
    </button>

    <div v-if="compareStore.popupOpen" class="fixed inset-0 z-[70]">
      <div class="absolute inset-0 bg-slate-950/50" @click="compareStore.closePopup()"></div>

      <section class="absolute inset-x-3 bottom-3 mx-auto max-h-[88vh] max-w-6xl overflow-hidden rounded-lg bg-white shadow-2xl md:inset-x-6">
        <header class="flex items-center justify-between gap-4 border-b border-slate-200 px-4 py-3">
          <div>
            <h2 class="text-base font-black text-slate-950">So sánh laptop</h2>
            <p class="text-xs text-slate-500">Chọn tối đa 4 sản phẩm từ danh sách sản phẩm.</p>
          </div>

          <div class="flex items-center gap-2">
            <button
              v-if="compareStore.count"
              @click="compareStore.clear()"
              class="rounded-md px-3 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100"
            >
              Xóa tất cả
            </button>
            <button
              @click="compareStore.closePopup()"
              class="flex h-9 w-9 items-center justify-center rounded-md bg-slate-100 text-xl leading-none text-slate-700 hover:bg-slate-200"
              aria-label="Đóng so sánh"
            >
              ×
            </button>
          </div>
        </header>

        <div v-if="!compareStore.count" class="px-4 py-10 text-center text-slate-500">
          Chưa có sản phẩm nào để so sánh.
        </div>

        <div v-else class="max-h-[calc(88vh-64px)] overflow-auto">
          <table class="w-full min-w-[760px] text-sm">
            <thead>
              <tr>
                <th class="sticky left-0 top-0 z-20 w-40 bg-slate-50 p-3 text-left font-bold text-slate-600">Thông số</th>
                <th
                  v-for="product in compareStore.items"
                  :key="product.id"
                  class="sticky top-0 z-10 min-w-52 border-l border-slate-100 bg-white p-3 align-top"
                >
                  <div class="relative text-center">
                    <button
                      @click="compareStore.remove(product.id)"
                      class="absolute right-0 top-0 flex h-7 w-7 items-center justify-center rounded-md bg-red-50 text-red-600 hover:bg-red-100"
                      aria-label="Xóa sản phẩm khỏi so sánh"
                    >
                      ×
                    </button>
                    <RouterLink :to="`/products/${product.slug}`" @click="compareStore.closePopup()">
                      <img
                        :src="productImage(product)"
                        :alt="product.name"
                        class="mx-auto h-24 w-32 object-contain p-2"
                        @error="$event.target.src = '/placeholder.svg'"
                      />
                      <p class="mt-2 line-clamp-2 pr-7 text-sm font-bold text-slate-900 hover:text-orange-600">
                        {{ product.name }}
                      </p>
                    </RouterLink>
                    <p class="mt-1 text-sm font-black text-red-600">{{ formatPrice(product.sale_price || product.price) }}</p>
                  </div>
                </th>
                <th v-if="compareStore.count < 4" class="sticky top-0 z-10 min-w-40 border-l border-dashed border-slate-200 bg-slate-50 p-3 text-center text-slate-400">
                  Thêm từ card sản phẩm
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in specRows" :key="row.key" class="border-t border-slate-100">
                <td class="sticky left-0 z-10 bg-slate-50 p-3 font-bold text-slate-600">{{ row.label }}</td>
                <td
                  v-for="product in compareStore.items"
                  :key="product.id"
                  class="border-l border-slate-100 p-3 text-center text-slate-700"
                >
                  {{ product[row.key] || 'Chưa cập nhật' }}
                </td>
                <td v-if="compareStore.count < 4" class="border-l border-dashed border-slate-200 bg-slate-50"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </teleport>
</template>

<script setup>
import { onMounted } from 'vue'
import { RouterLink } from 'vue-router'
import { useCompareStore } from '@/stores/compare'
import { imageUrl } from '@/utils/image'

const compareStore = useCompareStore()

const specRows = [
  { key: 'cpu', label: 'Bộ vi xử lý' },
  { key: 'ram', label: 'RAM' },
  { key: 'storage', label: 'Ổ cứng' },
  { key: 'display', label: 'Màn hình' },
  { key: 'gpu', label: 'Card đồ họa' },
  { key: 'os', label: 'Hệ điều hành' },
  { key: 'battery', label: 'Pin' },
  { key: 'weight', label: 'Trọng lượng' },
]

const productImage = (product) =>
  imageUrl(product.thumbnail || product.images?.[0]?.image_path || null)

const formatPrice = (value) =>
  new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value ?? 0)

onMounted(() => compareStore.initialize())
</script>
