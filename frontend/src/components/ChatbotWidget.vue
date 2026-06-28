<template>
  <!-- ─── Chatbot Toggle Button ─────────────────────────── -->
  <div class="fixed bottom-6 right-6 z-50">

    <!-- Chat Window -->
    <Transition name="slide-up">
      <div v-if="isOpen"
        class="absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden"
        style="height: 480px;">

        <!-- Header -->
        <div class="bg-primary-600 px-4 py-3 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center">
              🤖
            </div>
            <div>
              <p class="text-white font-semibold text-sm">AI Tư Vấn Laptop</p>
              <p class="text-blue-100 text-xs">Hỗ trợ 24/7</p>
            </div>
          </div>
          <button @click="isOpen = false" class="text-white/80 hover:text-white text-lg">✕</button>
        </div>

        <!-- Messages -->
        <div ref="messagesContainer" class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50">
          <div v-for="(msg, i) in messages" :key="i"
            :class="['flex gap-2', msg.role === 'user' ? 'flex-row-reverse' : 'flex-row']">

            <!-- Avatar -->
            <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-xs"
              :class="msg.role === 'user' ? 'bg-primary-600 text-white' : 'bg-gray-200'">
              {{ msg.role === 'user' ? '👤' : '🤖' }}
            </div>

            <!-- Bubble -->
            <div class="px-3 py-2 rounded-2xl text-sm leading-relaxed whitespace-pre-line"
              :class="msg.role === 'user'
                ? 'max-w-[75%] bg-primary-600 text-white rounded-tr-sm'
                : [msg.products?.length ? 'max-w-[92%] w-full' : 'max-w-[75%]', 'bg-white text-gray-800 shadow-sm border rounded-tl-sm']">
              <p>{{ msg.content }}</p>

              <div v-if="msg.products?.length" class="mt-3 space-y-2 whitespace-normal">
                <RouterLink
                  v-for="product in msg.products"
                  :key="product.id"
                  :to="`/products/${product.slug}`"
                  class="flex gap-3 rounded-lg border border-slate-100 bg-slate-50 p-2 transition hover:border-orange-200 hover:bg-orange-50"
                >
                  <img
                    :src="productImage(product)"
                    :alt="product.name"
                    class="h-20 w-20 flex-shrink-0 rounded-md bg-white object-contain p-1"
                  />

                  <div class="min-w-0 flex-1">
                    <p class="line-clamp-2 text-xs font-bold leading-4 text-slate-900">
                      {{ product.name }}
                    </p>
                    <p class="mt-1 text-sm font-bold text-orange-600">
                      {{ formatPrice(product.sale_price || product.price) }}
                    </p>
                    <div class="mt-1 flex flex-wrap gap-1">
                      <span v-if="product.cpu" class="chat-spec">{{ shortCpu(product.cpu) }}</span>
                      <span v-if="product.ram" class="chat-spec">{{ product.ram }}</span>
                      <span v-if="product.storage" class="chat-spec">{{ product.storage }}</span>
                      <span v-if="product.gpu" class="chat-spec">{{ shortGpu(product.gpu) }}</span>
                    </div>
                    <p :class="['mt-1 text-xs font-medium', product.stock > 0 ? 'text-emerald-700' : 'text-red-600']">
                      {{ product.stock > 0 ? `Còn ${product.stock} sản phẩm` : 'Hết hàng' }}
                    </p>
                  </div>
                </RouterLink>
              </div>
            </div>
          </div>

          <!-- Typing indicator -->
          <div v-if="loading" class="flex gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-xs">🤖</div>
            <div class="bg-white border shadow-sm rounded-2xl rounded-tl-sm px-3 py-2">
              <div class="flex gap-1">
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></div>
                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Suggestions -->
        <div v-if="messages.length <= 1" class="px-3 py-2 flex gap-2 flex-wrap border-t bg-white">
          <button v-for="s in suggestions" :key="s" @click="sendSuggestion(s)"
            class="text-xs px-3 py-1.5 bg-blue-50 text-primary-600 rounded-full border border-blue-100 hover:bg-blue-100 transition">
            {{ s }}
          </button>
        </div>

        <!-- Input -->
        <div class="p-3 border-t bg-white flex gap-2">
          <input
            ref="inputEl"
            v-model="inputMessage"
            @keyup.enter="sendMessage"
            type="text"
            placeholder="Nhập câu hỏi..."
            :disabled="loading"
            class="flex-1 text-sm px-3 py-2 border border-gray-200 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-400 disabled:opacity-50"
          />
          <button @click="sendMessage" :disabled="loading || !inputMessage.trim()"
            class="w-9 h-9 bg-primary-600 text-white rounded-full flex items-center justify-center hover:bg-primary-700 disabled:opacity-50 transition">
            ➤
          </button>
        </div>
      </div>
    </Transition>

    <!-- Toggle Button -->
    <button @click="isOpen = !isOpen"
      class="w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full shadow-lg flex items-center justify-center text-2xl transition-transform hover:scale-110">
      {{ isOpen ? '✕' : '🤖' }}
    </button>
  </div>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue'
import api from '@/services/api'
import { imageUrl } from '@/utils/image'
import { useCompareStore } from '@/stores/compare'

const isOpen          = ref(false)
const loading         = ref(false)
const inputMessage    = ref('')
const messagesContainer = ref(null)
const inputEl = ref(null)
const compareStore = useCompareStore()
const messages        = ref([
  { role: 'assistant', content: 'Xin chào! Tôi là AI tư vấn laptop. Tôi có thể giúp bạn tìm laptop phù hợp với nhu cầu và ngân sách. Bạn cần tư vấn gì không? 😊' }
])

const suggestions = ['Laptop dưới 15 triệu', 'Laptop gaming tốt nhất', 'Laptop cho sinh viên', 'So sánh cấu hình']

const chatSessionId = getChatSessionId()
compareStore.initialize()

async function sendMessage() {
  const text = inputMessage.value.trim()
  if (!text || loading.value) return

  messages.value.push({ role: 'user', content: text })
  inputMessage.value = ''
  loading.value = true
  scrollToBottom()

  try {
    const res = await api.post('/chatbot/message', {
      message:  text,
      history:  messages.value.slice(-6).map(m => ({ role: m.role, content: m.content })),
      session_id: chatSessionId,
      compare_product_ids: compareStore.items.map(product => product.id),
    })
    messages.value.push({
      role: 'assistant',
      content: res.data.data.reply,
      products: res.data.data.products || [],
    })
  } catch {
    messages.value.push({ role: 'assistant', content: 'Xin lỗi, tôi gặp sự cố. Vui lòng thử lại sau.' })
  } finally {
    loading.value = false
    await scrollToBottom()
    focusInput()
  }
}

function sendSuggestion(text) {
  inputMessage.value = text
  sendMessage()
}

function getChatSessionId() {
  const key = 'chatbot_session_id'
  const current = localStorage.getItem(key)
  if (current) return current

  const next = window.crypto?.randomUUID?.() || `chat-${Date.now()}-${Math.random().toString(16).slice(2)}`
  localStorage.setItem(key, next)
  return next
}

async function scrollToBottom() {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

async function focusInput() {
  await nextTick()
  if (isOpen.value && !loading.value) {
    inputEl.value?.focus()
  }
}

function productImage(product) {
  return imageUrl(product.thumbnail)
}

function formatPrice(value) {
  return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value ?? 0)
}

function shortCpu(cpu) {
  return cpu
    .replace('Intel Core ', '')
    .replace('Intel core ', '')
    .replace('AMD Ryzen ', 'R')
    .substring(0, 18)
}

function shortGpu(gpu) {
  return gpu
    .replace('NVIDIA GeForce ', '')
    .replace('NVIDIA ', '')
    .substring(0, 18)
}

watch(isOpen, (open) => {
  if (open) focusInput()
})
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(20px) scale(0.95); }
.chat-spec {
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  background: #fff;
  color: #475569;
  font-size: 10px;
  font-weight: 600;
  line-height: 1;
  padding: 4px 5px;
}
</style>
