// src/utils/image.js
const BACKEND_URL = import.meta.env.VITE_BACKEND_URL || 'http://localhost:8000'

export function imageUrl(path) {
  if (!path) return '/placeholder.svg'
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  return BACKEND_URL + (path.startsWith('/') ? path : '/' + path)
}

export default imageUrl