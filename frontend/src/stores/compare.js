import { defineStore } from 'pinia'

const STORAGE_KEY = 'compare_products'

export const useCompareStore = defineStore('compare', {
  state: () => ({
    items: [],
    initialized: false,
    popupOpen: false,
  }),

  getters: {
    count: (state) => state.items.length,
    has: (state) => (id) => state.items.some((item) => item.id === id),
  },

  actions: {
    initialize() {
      if (this.initialized) return

      try {
        const raw = localStorage.getItem(STORAGE_KEY)
        this.items = raw ? JSON.parse(raw) : []
      } catch {
        this.items = []
      }

      this.initialized = true
    },

    persist() {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(this.items))
    },

    add(product) {
      this.initialize()
      if (this.has(product.id)) {
        return { ok: false, reason: 'exists' }
      }

      if (this.items.length >= 4) {
        return { ok: false, reason: 'limit' }
      }

      this.items.push(product)
      this.persist()
      return { ok: true }
    },

    remove(id) {
      this.items = this.items.filter((item) => item.id !== id)
      this.persist()
    },

    clear() {
      this.items = []
      this.persist()
    },

    openPopup() {
      this.popupOpen = true
    },

    closePopup() {
      this.popupOpen = false
    },
  },
})
