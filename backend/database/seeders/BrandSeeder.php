<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'ASUS',   'slug' => 'asus',   'description' => 'ASUS là hãng máy tính nổi tiếng của Đài Loan, chuyên laptop gaming ROG/TUF và laptop văn phòng VivoBook/ZenBook.'],
            ['name' => 'Dell',   'slug' => 'dell',   'description' => 'Dell là thương hiệu Mỹ nổi tiếng với dòng Inspiron phổ thông, XPS cao cấp và Alienware gaming.'],
            ['name' => 'HP',     'slug' => 'hp',     'description' => 'HP (Hewlett-Packard) cung cấp đa dạng sản phẩm từ laptop phổ thông Pavilion đến cao cấp Spectre và gaming Omen.'],
            ['name' => 'Lenovo', 'slug' => 'lenovo', 'description' => 'Lenovo là hãng máy tính lớn nhất thế giới, nổi tiếng với ThinkPad doanh nhân và IdeaPad sinh viên.'],
            ['name' => 'Acer',   'slug' => 'acer',   'description' => 'Acer là hãng Đài Loan cung cấp laptop giá tốt dòng Aspire và gaming Nitro/Predator.'],
            ['name' => 'Apple',  'slug' => 'apple',  'description' => 'Apple MacBook nổi bật với chip M-series, hiệu năng mạnh, pin trâu và hệ sinh thái macOS.'],
            ['name' => 'MSI',    'slug' => 'msi',    'description' => 'MSI chuyên laptop gaming cao cấp với dòng Raider, Katana, Titan và Stealth.'],
            ['name' => 'LG',     'slug' => 'lg',     'description' => 'LG Gram nổi tiếng với trọng lượng siêu nhẹ, pin cực trâu, phù hợp dân văn phòng di động nhiều.'],
        ];

        foreach ($brands as $b) {
            DB::table('brands')->updateOrInsert(
                ['slug' => $b['slug']],
                array_merge($b, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
