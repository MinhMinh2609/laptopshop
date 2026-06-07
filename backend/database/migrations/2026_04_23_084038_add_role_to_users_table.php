<?php
// database/migrations/xxxx_add_role_to_users_table.php
// Đặt tên file theo tên artisan đã tạo ra (giữ nguyên timestamp)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Thêm cột role sau email
            $table->enum('role', ['admin', 'user'])->default('user')->after('email');
            // Thêm cột is_active
            $table->boolean('is_active')->default(true)->after('role');
            // Thêm cột phone và address
            $table->string('phone', 20)->nullable()->after('is_active');
            $table->text('address')->nullable()->after('phone');
            // Thêm cột avatar
            $table->string('avatar', 500)->nullable()->after('address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'is_active', 'phone', 'address', 'avatar']);
        });
    }
};