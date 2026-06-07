// src/stores/cart.js
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'
import { useAuthStore } from './auth'

export const useCartStore = defineStore('cart', () => {
  const items   = ref([])
  const loading = ref(false)

  // ─── Getters ───────────────────────────────────────────
  const totalItems  = computed(() => items.value.reduce((sum, i) => sum + i.quantity, 0))
  const totalAmount = computed(() => items.value.reduce((sum, i) => {
    const price = i.unit_price ?? (i.product?.sale_price ?? i.product?.price ?? 0)
    return sum + price * i.quantity
  }, 0))

  // ─── Load giỏ hàng từ server ───────────────────────────
  async function fetchCart() {
    const authStore = useAuthStore()
    if (!authStore.isLoggedIn) {
      items.value   = []
      loading.value = false
      return
    }

    loading.value = true
    try {
      const res = await api.get('/cart')
      // Response: { success, data: { items: [...], total_items, total_amount } }
      const data  = res.data.data
      items.value = Array.isArray(data) ? data : (data.items ?? [])
    } catch (err) {
      console.error('fetchCart error:', err)
      items.value = []
    } finally {
      loading.value = false
    }
  }

  // ─── Thêm vào giỏ hàng ─────────────────────────────────
  async function addToCart(productId, quantity = 1) {
    const res = await api.post('/cart', { product_id: productId, quantity })
    await fetchCart()
    return res.data
  }

  // ─── Cập nhật số lượng ─────────────────────────────────
  async function updateQuantity(cartItemId, quantity) {
    if (quantity < 1) return removeFromCart(cartItemId)
    await api.put(`/cart/${cartItemId}`, { quantity })
    const item = items.value.find(i => i.id === cartItemId)
    if (item) item.quantity = quantity
  }

  // ─── Xóa khỏi giỏ hàng ────────────────────────────────
  async function removeFromCart(cartItemId) {
    await api.delete(`/cart/${cartItemId}`)
    items.value = items.value.filter(i => i.id !== cartItemId)
  }

  // ─── Xóa toàn bộ ──────────────────────────────────────
  async function clearCart() {
    await api.delete('/cart')
    items.value = []
  }

  // ─── Reset khi logout ──────────────────────────────────
  function reset() {
    items.value   = []
    loading.value = false
  }

  return {
    items, loading,
    totalItems, totalAmount,
    fetchCart, addToCart, updateQuantity, removeFromCart, clearCart, reset,
  }
})