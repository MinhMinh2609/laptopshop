<?php
// database/migrations/xxxx_create_laptop_shop_tables.php
// Ghi đè nội dung vào file artisan vừa tạo (giữ nguyên tên file)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── BRANDS ───────────────────────────────────────
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('logo', 500)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ─── CATEGORIES ───────────────────────────────────
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ─── PRODUCTS ─────────────────────────────────────
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->constrained()->restrictOnDelete();
            $table->string('name', 500);
            $table->string('slug', 500)->unique();
            $table->string('sku', 100)->unique();
            $table->longText('description')->nullable();
            $table->decimal('price', 15, 0);
            $table->decimal('sale_price', 15, 0)->nullable();
            $table->integer('stock')->default(0);
            $table->string('thumbnail', 500)->nullable();
            // Thông số kỹ thuật
            $table->string('cpu')->nullable();
            $table->string('ram', 100)->nullable();
            $table->string('storage', 100)->nullable();
            $table->string('display')->nullable();
            $table->string('gpu')->nullable();
            $table->string('os', 100)->nullable();
            $table->string('battery', 100)->nullable();
            $table->string('weight', 50)->nullable();
            // Trạng thái
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('views')->default(0);
            $table->timestamps();

            $table->index('price');
            $table->index('brand_id');
            $table->index('category_id');
            $table->index('is_active');
        });

        // ─── PRODUCT IMAGES ───────────────────────────────
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('image_path', 500);
            $table->string('alt_text')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('product_id');
        });

        // ─── CARTS ────────────────────────────────────────
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['user_id', 'product_id']);
        });

        // ─── ORDERS ───────────────────────────────────────
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('order_code', 50)->unique();
            $table->decimal('total_amount', 15, 0);
            $table->decimal('discount_amount', 15, 0)->default(0);
            $table->decimal('final_amount', 15, 0);
            $table->enum('status', ['pending','confirmed','processing','shipped','delivered','cancelled','refunded'])
                  ->default('pending');
            $table->enum('payment_method', ['cod','vnpay','bank_transfer'])->default('cod');
            $table->enum('payment_status', ['unpaid','paid','refunded'])->default('unpaid');
            $table->string('vnpay_txn_ref', 100)->nullable();
            // Thông tin giao hàng
            $table->string('shipping_name');
            $table->string('shipping_phone', 20);
            $table->text('shipping_address');
            $table->string('shipping_city', 100);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('user_id');
            $table->index('payment_status');
        });

        // ─── ORDER ITEMS ──────────────────────────────────
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('product_name', 500);
            $table->string('product_sku', 100);
            $table->integer('quantity');
            $table->decimal('unit_price', 15, 0);
            $table->decimal('total_price', 15, 0);
            $table->timestamp('created_at')->useCurrent();

            $table->index('order_id');
        });

        // ─── REVIEWS ──────────────────────────────────────
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->tinyInteger('rating');
            $table->text('comment')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'user_id', 'order_id']);
        });

        // ─── COUPONS ──────────────────────────────────────
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 10, 2);
            $table->decimal('min_order', 15, 0)->default(0);
            $table->decimal('max_discount', 15, 0)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // ─── WISHLISTS ────────────────────────────────────
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};