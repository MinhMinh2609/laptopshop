<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Laptop Gaming',        'slug' => 'laptop-gaming',        'description' => 'Laptop chuyên game với GPU rời mạnh, màn hình tốc độ cao, tản nhiệt hiệu quả.'],
            ['name' => 'Laptop Văn Phòng',     'slug' => 'laptop-van-phong',     'description' => 'Laptop nhỏ gọn, pin lâu, phù hợp làm việc văn phòng và học tập hàng ngày.'],
            ['name' => 'Laptop Đồ Họa',        'slug' => 'laptop-do-hoa',        'description' => 'Laptop màn hình màu sắc chuẩn, GPU mạnh cho thiết kế, dựng phim, render 3D.'],
            ['name' => 'Ultrabook / Mỏng nhẹ', 'slug' => 'ultrabook-mong-nhe',   'description' => 'Laptop siêu mỏng nhẹ, pin lâu, phù hợp di chuyển và làm việc mọi nơi.'],
            ['name' => 'MacBook',              'slug' => 'macbook',              'description' => 'Laptop Apple MacBook với chip M-series, hiệu năng cao và thời lượng pin vượt trội.'],
        ];

        foreach ($categories as $c) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $c['slug']],
                array_merge($c, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
