<?php
// routes/api.php - API Routes cho Laptop Shop DATN
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ProfileController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\DashboardController;
use App\Http\Controllers\Api\Shop\ProductController;
use App\Http\Controllers\Api\Shop\CartController;
use App\Http\Controllers\Api\Shop\OrderController;
use App\Http\Controllers\Api\Shop\ReviewController;
use App\Http\Controllers\Api\Shop\WishlistController;
use App\Http\Controllers\Api\Shop\CompareController;
use App\Http\Controllers\Api\Shop\CouponController;
use App\Http\Controllers\Api\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Api\Payment\VNPayController;
use App\Http\Controllers\Api\ChatbotController;

// =============================================================
//  PUBLIC ROUTES (Không cần đăng nhập)
// =============================================================

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/register',       [AuthController::class, 'register']);
    Route::post('/login',          [AuthController::class, 'login']);
    Route::post('/forgot-password',[AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Shop - Public
Route::prefix('shop')->group(function () {
    Route::get('/products',              [ProductController::class, 'index']);
    Route::get('/products/search',       [ProductController::class, 'search']);
    Route::get('/products/featured',     [ProductController::class, 'featured']);
    Route::get('/products/{slug}',       [ProductController::class, 'show']);
    Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
    Route::get('/categories',            [AdminCategoryController::class, 'index']);
    Route::get('/brands',                [AdminBrandController::class, 'index']);
    Route::get('/compare',               [CompareController::class, 'compare']); // ?ids[]=1&ids[]=2
});

// Payment Callback (VNPay gọi về - hỗ trợ cả GET và POST)
Route::match(['get', 'post'], '/payment/vnpay/callback', [VNPayController::class, 'callback']);
Route::match(['get', 'post'], '/payment/vnpay/ipn',      [VNPayController::class, 'callback']);
Route::match(['get', 'post'], '/payment/vnpay/return',   [VNPayController::class, 'return']);

// Chatbot Public
Route::post('/chatbot/message', [ChatbotController::class, 'message']);

// =============================================================
//  AUTHENTICATED ROUTES (Cần đăng nhập - Laravel Sanctum)
// =============================================================
Route::middleware(['auth:sanctum'])->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout',  [AuthController::class, 'logout']);
        Route::get('/me',       [AuthController::class, 'me']);
    });

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/',                [ProfileController::class, 'show']);
        Route::put('/',                [ProfileController::class, 'update']);
        Route::post('/change-password',[ProfileController::class, 'changePassword']);
        Route::post('/avatar',         [ProfileController::class, 'uploadAvatar']);
    });

    // Cart
    Route::prefix('cart')->group(function () {
        Route::get('/',           [CartController::class, 'index']);
        Route::post('/',          [CartController::class, 'add']);
        Route::put('/{id}',       [CartController::class, 'update']);
        Route::delete('/{id}',    [CartController::class, 'remove']);
        Route::delete('/',        [CartController::class, 'clear']);
    });

    // Coupon - Áp dụng mã giảm giá cho giỏ hàng
    Route::post('/coupons/apply', [CouponController::class, 'apply']);

    // Orders (User)
    Route::prefix('orders')->group(function () {
        Route::get('/',            [OrderController::class, 'index']);
        Route::post('/',           [OrderController::class, 'store']);
        Route::get('/{orderCode}', [OrderController::class, 'show']);
        Route::post('/{id}/cancel',[OrderController::class, 'cancel']);
        Route::delete('/{id}',     [OrderController::class, 'destroy']);
    });

    // Payment
    Route::post('/payment/vnpay/create', [VNPayController::class, 'create']);

    // Reviews (User - chỉ sau khi mua hàng)
    Route::post('/reviews',       [ReviewController::class, 'store']);
    Route::put('/reviews/{id}',   [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}',[ReviewController::class, 'destroy']);

    // Wishlist
    Route::get('/wishlist',             [WishlistController::class, 'index']);
    Route::post('/wishlist/{productId}',[WishlistController::class, 'toggle']);

    // =============================================================
    //  ADMIN ROUTES (Cần role = admin)
    // =============================================================
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        // Dashboard
        Route::get('/dashboard',          [DashboardController::class, 'index']);
        Route::get('/dashboard/stats',    [DashboardController::class, 'stats']);
        Route::get('/dashboard/revenue',  [DashboardController::class, 'revenue']);
        Route::get('/dashboard/top-products', [DashboardController::class, 'topProducts']);

        // Users Management
        Route::apiResource('users', UserController::class);
        Route::patch('/users/{id}/toggle-active', [UserController::class, 'toggleActive']);

        // Products Management
        Route::apiResource('products', AdminProductController::class);
        Route::post('/products/{id}/images',         [AdminProductController::class, 'uploadImages']);
        Route::delete('/products/{id}/images/{imgId}',[AdminProductController::class, 'deleteImage']);
        Route::patch('/products/{id}/toggle-active', [AdminProductController::class, 'toggleActive']);
        Route::patch('/products/{id}/toggle-featured',[AdminProductController::class, 'toggleFeatured']);

        // Categories Management
        Route::apiResource('categories', AdminCategoryController::class);

        // Brands Management
        Route::apiResource('brands', AdminBrandController::class);

        // Coupons Management
        Route::apiResource('coupons', AdminCouponController::class);
        Route::patch('/coupons/{id}/toggle-active', [AdminCouponController::class, 'toggleActive']);
        Route::post('/coupons/{id}/send', [AdminCouponController::class, 'sendToCustomers']);

        // Orders Management
        Route::get('/orders',             [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}',        [AdminOrderController::class, 'show']);
        Route::patch('/orders/{id}/status',[AdminOrderController::class, 'updateStatus']);

        // Reviews Management
        Route::get('/reviews',            [ReviewController::class, 'adminIndex']);
        Route::patch('/reviews/{id}/approve', [ReviewController::class, 'approve']);
        Route::delete('/reviews/{id}',    [ReviewController::class, 'adminDestroy']);
    });
});
