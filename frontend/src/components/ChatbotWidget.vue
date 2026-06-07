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
            <div class="max-w-[75%] px-3 py-2 rounded-2xl text-sm leading-relaxed whitespace-pre-line"
              :class="msg.role === 'user'
                ? 'bg-primary-600 text-white rounded-tr-sm'
                : 'bg-white text-gray-800 shadow-sm border rounded-tl-sm'">
              {{ msg.content }}
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
import { ref, nextTick, onMounted } from 'vue'
import api from '@/services/api'

const isOpen          = ref(false)
const loading         = ref(false)
const inputMessage    = ref('')
const messagesContainer = ref(null)
const messages        = ref([
  { role: 'assistant', content: 'Xin chào! Tôi là AI tư vấn laptop. Tôi có thể giúp bạn tìm laptop phù hợp với nhu cầu và ngân sách. Bạn cần tư vấn gì không? 😊' }
])

const suggestions = ['Laptop dưới 15 triệu', 'Laptop gaming tốt nhất', 'Laptop cho sinh viên', 'So sánh cấu hình']

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
    })
    messages.value.push({ role: 'assistant', content: res.data.data.reply })
  } catch {
    messages.value.push({ role: 'assistant', content: 'Xin lỗi, tôi gặp sự cố. Vui lòng thử lại sau.' })
  } finally {
    loading.value = false
    scrollToBottom()
  }
}

function sendSuggestion(text) {
  inputMessage.value = text
  sendMessage()
}

async function scrollToBottom() {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}
</script>

<style scoped>
.slide-up-enter-active, .slide-up-leave-active { transition: all 0.3s ease; }
.slide-up-enter-from, .slide-up-leave-to { opacity: 0; transform: translateY(20px) scale(0.95); }
</style>
