// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

// ─── Lazy-load Components ──────────────────────────────────────
// SHOP (User)
const HomeView         = () => import('@/views/shop/HomeView.vue')
const ProductListView  = () => import('@/views/shop/ProductListView.vue')
const ProductDetail    = () => import('@/views/shop/ProductDetailView.vue')
const CartView         = () => import('@/views/shop/CartView.vue')
const CheckoutView     = () => import('@/views/shop/CheckoutView.vue')
const OrdersView       = () => import('@/views/shop/OrdersView.vue')
const OrderDetailView  = () => import('@/views/shop/OrderDetailView.vue')
const WishlistView     = () => import('@/views/shop/WishlistView.vue')
const CompareView      = () => import('@/views/shop/CompareView.vue')
const PaymentSuccess   = () => import('@/views/shop/PaymentSuccessView.vue')
const PaymentFailed    = () => import('@/views/shop/PaymentFailedView.vue')
const ProfileView      = () => import('@/views/shop/ProfileView.vue')
const SearchView       = () => import('@/views/shop/SearchView.vue')

// AUTH
const LoginView        = () => import('@/views/auth/LoginView.vue')
const RegisterView     = () => import('@/views/auth/RegisterView.vue')

// ADMIN
const AdminLayout      = () => import('@/layouts/AdminLayout.vue')
const AdminDashboard   = () => import('@/views/admin/DashboardView.vue')
const AdminProducts    = () => import('@/views/admin/ProductsView.vue')
const AdminProductForm = () => import('@/views/admin/ProductFormView.vue')
const AdminCategories  = () => import('@/views/admin/CategoriesView.vue')
const AdminBrands      = () => import('@/views/admin/BrandsView.vue')
const AdminCoupons     = () => import('@/views/admin/CouponsView.vue')
const AdminOrders      = () => import('@/views/admin/OrdersView.vue')
const AdminOrderDetail = () => import('@/views/admin/OrderDetailView.vue')
const AdminUsers       = () => import('@/views/admin/UsersView.vue')
const AdminReviews     = () => import('@/views/admin/ReviewsView.vue')

// LAYOUTS
const ShopLayout       = () => import('@/layouts/ShopLayout.vue')

// ERROR PAGES
const NotFound         = () => import('@/views/NotFoundView.vue')
const Forbidden        = () => import('@/views/ForbiddenView.vue')

const routes = [

  // ═══════════════════════════════════════════════════════════
  //  SHOP ROUTES (Tất cả mọi người)
  // ═══════════════════════════════════════════════════════════
  {
    path: '/',
    component: ShopLayout,
    children: [
      {
        path: '',
        name: 'home',
        component: HomeView,
        meta: { title: 'Trang Chủ - Laptop Shop' },
      },
      {
        path: 'products',
        name: 'products',
        component: ProductListView,
        meta: { title: 'Danh Sách Sản Phẩm' },
      },
      {
        path: 'products/:slug',
        name: 'product-detail',
        component: ProductDetail,
        meta: { title: 'Chi Tiết Sản Phẩm' },
      },
      {
        path: 'search',
        name: 'search',
        component: SearchView,
        meta: { title: 'Tìm Kiếm' },
      },
      {
        path: 'compare',
        name: 'compare',
        component: CompareView,
        meta: { title: 'So Sánh Laptop' },
      },
      {
        path: 'cart',
        name: 'cart',
        component: CartView,
        meta: { title: 'Giỏ Hàng', requiresAuth: true },
      },
      {
        path: 'checkout',
        name: 'checkout',
        component: CheckoutView,
        meta: { title: 'Thanh Toán', requiresAuth: true },
      },
      {
        path: 'orders',
        name: 'orders',
        component: OrdersView,
        meta: { title: 'Đơn Hàng Của Tôi', requiresAuth: true },
      },
      {
        path: 'orders/:orderCode',
        name: 'order-detail',
        component: OrderDetailView,
        meta: { title: 'Chi Tiết Đơn Hàng', requiresAuth: true },
      },
      {
        path: 'wishlist',
        name: 'wishlist',
        component: WishlistView,
        meta: { title: 'Yêu Thích', requiresAuth: true },
      },
      {
        path: 'profile',
        name: 'profile',
        component: ProfileView,
        meta: { title: 'Hồ Sơ Cá Nhân', requiresAuth: true },
      },
      {
        path: 'payment/success',
        name: 'payment-success',
        component: PaymentSuccess,
        meta: { title: 'Thanh Toán Thành Công' },
      },
      {
        path: 'payment/failed',
        name: 'payment-failed',
        component: PaymentFailed,
        meta: { title: 'Thanh Toán Thất Bại' },
      },
    ],
  },

  // ═══════════════════════════════════════════════════════════
  //  AUTH ROUTES
  // ═══════════════════════════════════════════════════════════
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { title: 'Đăng Nhập', guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { title: 'Đăng Ký', guestOnly: true },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { title: 'Quên Mật Khẩu', guestOnly: true },
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('@/views/auth/ResetPasswordView.vue'),
    meta: { title: 'Đặt Lại Mật Khẩu', guestOnly: true },
  },
  // ═══════════════════════════════════════════════════════════
  //  ADMIN ROUTES (Chỉ role = admin)
  // ═══════════════════════════════════════════════════════════
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: '',
        redirect: { name: 'admin-dashboard' },
      },
      {
        path: 'dashboard',
        name: 'admin-dashboard',
        component: AdminDashboard,
        meta: { title: 'Dashboard - Admin' },
      },
      {
        path: 'products',
        name: 'admin-products',
        component: AdminProducts,
        meta: { title: 'Quản Lý Sản Phẩm' },
      },
      {
        path: 'products/create',
        name: 'admin-product-create',
        component: AdminProductForm,
        meta: { title: 'Thêm Sản Phẩm Mới' },
      },
      {
        path: 'products/:id/edit',
        name: 'admin-product-edit',
        component: AdminProductForm,
        meta: { title: 'Chỉnh Sửa Sản Phẩm' },
      },
      {
        path: 'categories',
        name: 'admin-categories',
        component: AdminCategories,
        meta: { title: 'Quản Lý Danh Mục' },
      },
      {
        path: 'brands',
        name: 'admin-brands',
        component: AdminBrands,
        meta: { title: 'Quản Lý Hãng' },
      },
      {
        path: 'coupons',
        name: 'admin-coupons',
        component: AdminCoupons,
        meta: { title: 'Quản Lý Mã Giảm Giá' },
      },
      {
        path: 'orders',
        name: 'admin-orders',
        component: AdminOrders,
        meta: { title: 'Quản Lý Đơn Hàng' },
      },
      {
        path: 'orders/:id',
        name: 'admin-order-detail',
        component: AdminOrderDetail,
        meta: { title: 'Chi Tiết Đơn Hàng' },
      },
      {
        path: 'users',
        name: 'admin-users',
        component: AdminUsers,
        meta: { title: 'Quản Lý Người Dùng' },
      },
      {
        path: 'reviews',
        name: 'admin-reviews',
        component: AdminReviews,
        meta: { title: 'Quản Lý Đánh Giá' },
      },
    ],
  },

  // ═══════════════════════════════════════════════════════════
  //  ERROR PAGES
  // ═══════════════════════════════════════════════════════════
  { path: '/403', name: 'forbidden', component: Forbidden },
  { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) return savedPosition
    return { top: 0, behavior: 'smooth' }
  },
})

// ─── Navigation Guards ──────────────────────────────────────
router.beforeEach(async (to, from, next) => {
  // Set page title
  document.title = to.meta.title ?? 'Laptop Shop DATN'

  const authStore = useAuthStore()

  // Khởi tạo auth state từ localStorage nếu chưa có
  if (!authStore.initialized) {
    await authStore.initialize()
  }

  const isLoggedIn = authStore.isLoggedIn
  const isAdmin    = authStore.isAdmin

  // Route chỉ cho guest (login/register)
  if (to.meta.guestOnly && isLoggedIn) {
    return next({ name: 'home' })
  }

  // Route cần đăng nhập
  if (to.meta.requiresAuth && !isLoggedIn) {
    return next({ name: 'login', query: { redirect: to.fullPath } })
  }

  // Route chỉ cho admin
  if (to.meta.requiresAdmin && !isAdmin) {
    return next({ name: 'forbidden' })
  }

  next()
})

export default router
