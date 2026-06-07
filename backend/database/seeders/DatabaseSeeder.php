<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,     // 1 admin + 7 users
            BrandSeeder::class,    // 8 hãng laptop
            CategorySeeder::class, // 5 danh mục
            ProductSeeder::class,  // 17 laptop thực tế
            OrderSeeder::class,    // 10 đơn hàng mẫu
            ReviewSeeder::class,   // Reviews cho đơn delivered
        ]);
    }
}
