<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $brandId    = fn(string $slug) => DB::table('brands')->where('slug', $slug)->value('id');
        $categoryId = fn(string $slug) => DB::table('categories')->where('slug', $slug)->value('id');

        $gaming   = $categoryId('laptop-gaming');
        $office   = $categoryId('laptop-van-phong');
        $design   = $categoryId('laptop-do-hoa');
        $ultra    = $categoryId('ultrabook-mong-nhe');
        $mac      = $categoryId('macbook');

        $asus   = $brandId('asus');
        $dell   = $brandId('dell');
        $hp     = $brandId('hp');
        $lenovo = $brandId('lenovo');
        $acer   = $brandId('acer');
        $apple  = $brandId('apple');
        $msi    = $brandId('msi');
        $lg     = $brandId('lg');

        $products = [
            // ─── GAMING ───────────────────────────────────────────────
            [
                'category_id' => $gaming, 'brand_id' => $asus,
                'name'        => 'ASUS TUF Gaming F15 FX507ZC4',
                'slug'        => 'asus-tuf-gaming-f15-fx507zc4',
                'sku'         => 'TUF-F15-ZC4',
                'description' => 'ASUS TUF Gaming F15 trang bị Intel Core i5-12500H và NVIDIA RTX 3050, màn hình 144Hz IPS. Thiết kế bền bỉ chuẩn quân sự MIL-STD-810H, hệ thống tản nhiệt Arc Flow Fans, phù hợp game thủ có ngân sách vừa phải.',
                'price'       => 22990000, 'sale_price' => 21490000, 'stock' => 15,
                'cpu'         => 'Intel Core i5-12500H (12 nhân, 18 luồng, tới 4.5GHz)',
                'ram'         => '8GB DDR5 4800MHz (2 khe, max 32GB)',
                'storage'     => '512GB NVMe PCIe 4.0 SSD',
                'display'     => '15.6" FHD IPS 144Hz, 100% sRGB',
                'gpu'         => 'NVIDIA GeForce RTX 3050 4GB GDDR6',
                'os'          => 'Windows 11 Home',
                'battery'     => '90Wh, sạc 150W',
                'weight'      => '2.2 kg',
                'is_featured' => true, 'views' => 245,
            ],
            [
                'category_id' => $gaming, 'brand_id' => $asus,
                'name'        => 'ASUS ROG Strix G15 G513RC',
                'slug'        => 'asus-rog-strix-g15-g513rc',
                'sku'         => 'ROG-G15-RC',
                'description' => 'ASUS ROG Strix G15 với AMD Ryzen 7 6800H và RTX 3050, màn hình 144Hz. Thiết kế ROG Strix nổi bật với đèn RGB, hệ thống tản nhiệt Tri-Fan Technology.',
                'price'       => 26990000, 'sale_price' => 24990000, 'stock' => 10,
                'cpu'         => 'AMD Ryzen 7 6800H (8 nhân, 16 luồng, tới 4.7GHz)',
                'ram'         => '16GB DDR5 4800MHz',
                'storage'     => '512GB NVMe PCIe 4.0 SSD',
                'display'     => '15.6" FHD IPS 144Hz, 100% sRGB',
                'gpu'         => 'NVIDIA GeForce RTX 3050 4GB',
                'os'          => 'Windows 11 Home',
                'battery'     => '90Wh, sạc 200W',
                'weight'      => '2.3 kg',
                'is_featured' => true, 'views' => 312,
            ],
            [
                'category_id' => $gaming, 'brand_id' => $acer,
                'name'        => 'Acer Nitro 5 AN515-58',
                'slug'        => 'acer-nitro-5-an515-58',
                'sku'         => 'NITRO5-AN515',
                'description' => 'Acer Nitro 5 là lựa chọn gaming phổ thông với Intel Core i5-12500H và RTX 3050Ti. Màn hình 144Hz FHD, hệ thống tản nhiệt kép, giá thành hợp lý cho sinh viên.',
                'price'       => 20990000, 'sale_price' => null, 'stock' => 20,
                'cpu'         => 'Intel Core i5-12500H (12 nhân, 4.5GHz)',
                'ram'         => '8GB DDR4 3200MHz (có thể nâng lên 32GB)',
                'storage'     => '512GB NVMe SSD',
                'display'     => '15.6" FHD IPS 144Hz',
                'gpu'         => 'NVIDIA GeForce RTX 3050 Ti 4GB',
                'os'          => 'Windows 11 Home',
                'battery'     => '57.5Wh, sạc 180W',
                'weight'      => '2.5 kg',
                'is_featured' => false, 'views' => 189,
            ],
            [
                'category_id' => $gaming, 'brand_id' => $msi,
                'name'        => 'MSI Katana GF66 12UC',
                'slug'        => 'msi-katana-gf66-12uc',
                'sku'         => 'MSI-GF66-12UC',
                'description' => 'MSI Katana GF66 với Intel Core i7-12650H và RTX 3050, màn 144Hz. Thiết kế gaming mạnh mẽ với bàn phím RGB, webcam IR nhận diện khuôn mặt Windows Hello.',
                'price'       => 24990000, 'sale_price' => 22990000, 'stock' => 8,
                'cpu'         => 'Intel Core i7-12650H (10 nhân, 16 luồng, 4.7GHz)',
                'ram'         => '8GB DDR4 3200MHz',
                'storage'     => '512GB NVMe SSD',
                'display'     => '15.6" FHD IPS 144Hz',
                'gpu'         => 'NVIDIA GeForce RTX 3050 4GB GDDR6',
                'os'          => 'Windows 11 Home',
                'battery'     => '53.5Wh, sạc 120W',
                'weight'      => '2.25 kg',
                'is_featured' => false, 'views' => 98,
            ],

            // ─── VĂN PHÒNG ─────────────────────────────────────────────
            [
                'category_id' => $office, 'brand_id' => $dell,
                'name'        => 'Dell Inspiron 15 3520',
                'slug'        => 'dell-inspiron-15-3520',
                'sku'         => 'INS15-3520-I5',
                'description' => 'Dell Inspiron 15 là laptop văn phòng phổ biến nhất với Intel Core i5-1235U, màn hình FHD chống chói. Thiết kế gọn nhẹ, pin 54Wh, bàn phím thoải mái cho làm việc cả ngày.',
                'price'       => 16490000, 'sale_price' => 14990000, 'stock' => 25,
                'cpu'         => 'Intel Core i5-1235U (10 nhân, 12 luồng, 4.4GHz)',
                'ram'         => '8GB DDR4 3200MHz (1 khe trống)',
                'storage'     => '256GB NVMe SSD',
                'display'     => '15.6" FHD Anti-glare 120Hz',
                'gpu'         => 'Intel Iris Xe Graphics',
                'os'          => 'Windows 11 Home',
                'battery'     => '54Wh, sạc 65W',
                'weight'      => '1.73 kg',
                'is_featured' => true, 'views' => 401,
            ],
            [
                'category_id' => $office, 'brand_id' => $lenovo,
                'name'        => 'Lenovo IdeaPad 5 15IAL7',
                'slug'        => 'lenovo-ideapad-5-15ial7',
                'sku'         => 'IDP5-15IAL7',
                'description' => 'Lenovo IdeaPad 5 nổi bật với thiết kế mỏng nhẹ, màn hình 2.8K OLED siêu nét và Intel Core i5-1235U. Bàn phím thoải mái, bảo mật vân tay, pin 71Wh sạc nhanh 65W.',
                'price'       => 18990000, 'sale_price' => 17490000, 'stock' => 18,
                'cpu'         => 'Intel Core i5-1235U (10 nhân, 4.4GHz)',
                'ram'         => '16GB LPDDR4X 4266MHz (hàn cố định)',
                'storage'     => '512GB NVMe PCIe 4.0 SSD',
                'display'     => '15.6" 2.8K OLED 90Hz, 100% DCI-P3',
                'gpu'         => 'Intel Iris Xe Graphics',
                'os'          => 'Windows 11 Home',
                'battery'     => '71Wh, sạc nhanh 65W',
                'weight'      => '1.7 kg',
                'is_featured' => true, 'views' => 278,
            ],
            [
                'category_id' => $office, 'brand_id' => $hp,
                'name'        => 'HP Pavilion 15-eg3090TU',
                'slug'        => 'hp-pavilion-15-eg3090tu',
                'sku'         => 'PAV15-EG3090',
                'description' => 'HP Pavilion 15 với Intel Core i5-1335U thế hệ 13, màn FHD IPS. Thiết kế đẹp, có cổng USB-C, webcam 720p, phù hợp sinh viên và nhân viên văn phòng.',
                'price'       => 15990000, 'sale_price' => null, 'stock' => 30,
                'cpu'         => 'Intel Core i5-1335U (10 nhân, 12 luồng, 4.6GHz)',
                'ram'         => '8GB DDR4 3200MHz',
                'storage'     => '512GB NVMe SSD',
                'display'     => '15.6" FHD IPS Anti-glare',
                'gpu'         => 'Intel Iris Xe Graphics',
                'os'          => 'Windows 11 Home',
                'battery'     => '41Wh, sạc 65W',
                'weight'      => '1.75 kg',
                'is_featured' => false, 'views' => 156,
            ],
            [
                'category_id' => $office, 'brand_id' => $acer,
                'name'        => 'Acer Aspire 5 A515-58M',
                'slug'        => 'acer-aspire-5-a515-58m',
                'sku'         => 'ASPIRE5-A515',
                'description' => 'Acer Aspire 5 cung cấp hiệu năng tốt với Intel Core i5-13420H thế hệ 13, giá phải chăng. Bàn phím đèn nền, màn hình FHD IPS, thiết kế nhôm sang trọng hơn đời trước.',
                'price'       => 14990000, 'sale_price' => 13490000, 'stock' => 22,
                'cpu'         => 'Intel Core i5-13420H (8 nhân, 12 luồng, 4.6GHz)',
                'ram'         => '8GB DDR5 4800MHz (2 khe, max 32GB)',
                'storage'     => '512GB NVMe PCIe 4.0 SSD',
                'display'     => '15.6" FHD IPS 60Hz',
                'gpu'         => 'Intel UHD Graphics',
                'os'          => 'Windows 11 Home',
                'battery'     => '56.5Wh, sạc 65W',
                'weight'      => '1.8 kg',
                'is_featured' => false, 'views' => 134,
            ],
            [
                'category_id' => $office, 'brand_id' => $asus,
                'name'        => 'ASUS VivoBook 15 X1504ZA',
                'slug'        => 'asus-vivobook-15-x1504za',
                'sku'         => 'VIVO15-X1504',
                'description' => 'ASUS VivoBook 15 nhẹ chỉ 1.7kg với Intel Core i5-1235U, màn hình FHD IPS chống chói. Pin 42Wh, bàn phím đèn nền, cổng USB-C, phù hợp sinh viên di chuyển nhiều.',
                'price'       => 14490000, 'sale_price' => 13290000, 'stock' => 35,
                'cpu'         => 'Intel Core i5-1235U (10 nhân, 4.4GHz)',
                'ram'         => '8GB DDR4 3200MHz',
                'storage'     => '512GB NVMe SSD',
                'display'     => '15.6" FHD IPS Anti-glare 60Hz',
                'gpu'         => 'Intel Iris Xe Graphics',
                'os'          => 'Windows 11 Home',
                'battery'     => '42Wh, sạc 45W',
                'weight'      => '1.7 kg',
                'is_featured' => true, 'views' => 389,
            ],

            // ─── ĐỒ HỌA ───────────────────────────────────────────────
            [
                'category_id' => $design, 'brand_id' => $dell,
                'name'        => 'Dell XPS 15 9530',
                'slug'        => 'dell-xps-15-9530',
                'sku'         => 'XPS15-9530-I7',
                'description' => 'Dell XPS 15 là laptop đồ họa cao cấp với Intel Core i7-13700H, màn hình OLED 3.5K cảm ứng 60Hz, 100% DCI-P3. RTX 4060 mạnh mẽ cho thiết kế và dựng video chuyên nghiệp.',
                'price'       => 55990000, 'sale_price' => 52990000, 'stock' => 5,
                'cpu'         => 'Intel Core i7-13700H (14 nhân, 20 luồng, 5.0GHz)',
                'ram'         => '16GB DDR5 4800MHz (2 khe, max 64GB)',
                'storage'     => '512GB NVMe PCIe 4.0 SSD',
                'display'     => '15.6" OLED 3.5K 60Hz cảm ứng, 100% DCI-P3',
                'gpu'         => 'NVIDIA GeForce RTX 4060 8GB GDDR6',
                'os'          => 'Windows 11 Pro',
                'battery'     => '86Wh, sạc 130W',
                'weight'      => '1.86 kg',
                'is_featured' => true, 'views' => 521,
            ],
            [
                'category_id' => $design, 'brand_id' => $hp,
                'name'        => 'HP Envy 16-h1014TX',
                'slug'        => 'hp-envy-16-h1014tx',
                'sku'         => 'ENVY16-H1014',
                'description' => 'HP Envy 16 với Intel Core i7-13700H và RTX 4060, màn hình 2K IPS 120Hz 100% sRGB. Thiết kế premium nhôm, loa B&O 5 chiều, phù hợp content creator và kỹ sư đồ họa.',
                'price'       => 42990000, 'sale_price' => 39990000, 'stock' => 7,
                'cpu'         => 'Intel Core i7-13700H (14 nhân, 5.0GHz)',
                'ram'         => '16GB DDR5 (2 khe, max 64GB)',
                'storage'     => '1TB NVMe PCIe 4.0 SSD',
                'display'     => '16" 2K IPS 120Hz, 100% sRGB',
                'gpu'         => 'NVIDIA GeForce RTX 4060 8GB',
                'os'          => 'Windows 11 Home',
                'battery'     => '83Wh, sạc nhanh 200W',
                'weight'      => '2.14 kg',
                'is_featured' => false, 'views' => 187,
            ],

            // ─── ULTRABOOK ─────────────────────────────────────────────
            [
                'category_id' => $ultra, 'brand_id' => $lenovo,
                'name'        => 'Lenovo ThinkPad X1 Carbon Gen 11',
                'slug'        => 'lenovo-thinkpad-x1-carbon-gen11',
                'sku'         => 'X1C-GEN11-I7',
                'description' => 'ThinkPad X1 Carbon Gen 11 là biểu tượng laptop doanh nhân: nhẹ chỉ 1.12kg, Intel Core i7-1365U, màn 2.8K OLED. Chuẩn MIL-SPEC, bàn phím huyền thoại ThinkPad, bảo mật doanh nghiệp.',
                'price'       => 48990000, 'sale_price' => 45990000, 'stock' => 6,
                'cpu'         => 'Intel Core i7-1365U (10 nhân, 12 luồng, 5.2GHz)',
                'ram'         => '16GB LPDDR5 5200MHz (hàn)',
                'storage'     => '512GB NVMe PCIe 4.0 SSD',
                'display'     => '14" 2.8K OLED 60Hz, 100% DCI-P3, cảm ứng',
                'gpu'         => 'Intel Iris Xe Graphics',
                'os'          => 'Windows 11 Pro',
                'battery'     => '57Wh, sạc nhanh 65W',
                'weight'      => '1.12 kg',
                'is_featured' => true, 'views' => 298,
            ],
            [
                'category_id' => $ultra, 'brand_id' => $lg,
                'name'        => 'LG Gram 14 2023 14Z90R',
                'slug'        => 'lg-gram-14-2023-14z90r',
                'sku'         => 'GRAM14-Z90R',
                'description' => 'LG Gram 14 nhẹ nhất phân khúc chỉ 999g, Intel Core i5-1340P, pin 72Wh kéo dài 18 giờ. Chuẩn MIL-STD-810H 7 chuẩn khắc nghiệt, màn hình IPS 1200p sắc nét.',
                'price'       => 29990000, 'sale_price' => 27490000, 'stock' => 12,
                'cpu'         => 'Intel Core i5-1340P (12 nhân, 16 luồng, 4.6GHz)',
                'ram'         => '16GB LPDDR5 (hàn cố định)',
                'storage'     => '512GB NVMe SSD',
                'display'     => '14" WUXGA IPS (1920x1200) 60Hz, anti-glare',
                'gpu'         => 'Intel Iris Xe Graphics',
                'os'          => 'Windows 11 Home',
                'battery'     => '72Wh — lên đến 18 giờ sử dụng',
                'weight'      => '999 g',
                'is_featured' => false, 'views' => 167,
            ],
            [
                'category_id' => $ultra, 'brand_id' => $hp,
                'name'        => 'HP Envy x360 13-bf0091TU',
                'slug'        => 'hp-envy-x360-13-bf0091tu',
                'sku'         => 'ENVYX360-13-BF',
                'description' => 'HP Envy x360 13 là laptop 2-in-1 cảm ứng lật 360°, Intel Core i5-1230U, màn OLED 2.8K. Bút stylus kèm sẵn, phù hợp vẽ kỹ thuật số và ghi chép sáng tạo.',
                'price'       => 26990000, 'sale_price' => null, 'stock' => 9,
                'cpu'         => 'Intel Core i5-1230U (10 nhân, 4.4GHz)',
                'ram'         => '8GB LPDDR4X',
                'storage'     => '512GB NVMe SSD',
                'display'     => '13.3" 2.8K OLED cảm ứng lật 360°, 100% DCI-P3',
                'gpu'         => 'Intel Iris Xe Graphics',
                'os'          => 'Windows 11 Home',
                'battery'     => '66Wh, sạc nhanh 65W',
                'weight'      => '1.3 kg',
                'is_featured' => false, 'views' => 143,
            ],

            // ─── MACBOOK ───────────────────────────────────────────────
            [
                'category_id' => $mac, 'brand_id' => $apple,
                'name'        => 'MacBook Air 13 M2 2022',
                'slug'        => 'macbook-air-13-m2-2022',
                'sku'         => 'MBA13-M2-8GB',
                'description' => 'MacBook Air M2 thiết kế lại hoàn toàn: màn hình Liquid Retina 13.6", chip M2 8 nhân CPU + 8 nhân GPU, không quạt tản nhiệt. Pin 18 giờ, cổng MagSafe, Midnight và Starlight.',
                'price'       => 27990000, 'sale_price' => 26490000, 'stock' => 20,
                'cpu'         => 'Apple M2 (8 nhân CPU: 4P + 4E)',
                'ram'         => '8GB Unified Memory (tùy chọn 16GB/24GB)',
                'storage'     => '256GB SSD (tùy chọn đến 2TB)',
                'display'     => '13.6" Liquid Retina IPS 2560×1664, 500 nits',
                'gpu'         => 'Apple M2 8-core GPU',
                'os'          => 'macOS Ventura',
                'battery'     => '52.6Wh — lên đến 18 giờ',
                'weight'      => '1.24 kg',
                'is_featured' => true, 'views' => 634,
            ],
            [
                'category_id' => $mac, 'brand_id' => $apple,
                'name'        => 'MacBook Pro 14 M3 Pro 2023',
                'slug'        => 'macbook-pro-14-m3-pro-2023',
                'sku'         => 'MBP14-M3PRO',
                'description' => 'MacBook Pro 14 M3 Pro với chip M3 Pro 12 nhân CPU + 18 nhân GPU, màn hình Liquid Retina XDR 3024×1964 ProMotion 120Hz. 18GB RAM Unified, hiệu năng chuyên nghiệp tuyệt đỉnh.',
                'price'       => 52990000, 'sale_price' => null, 'stock' => 8,
                'cpu'         => 'Apple M3 Pro (12 nhân CPU: 6P + 6E, 5nm)',
                'ram'         => '18GB Unified Memory (tùy chọn 36GB)',
                'storage'     => '512GB SSD (tùy chọn đến 4TB)',
                'display'     => '14.2" Liquid Retina XDR 3024×1964, ProMotion 120Hz, 1000 nits',
                'gpu'         => 'Apple M3 Pro 18-core GPU',
                'os'          => 'macOS Sonoma',
                'battery'     => '70Wh — lên đến 18 giờ',
                'weight'      => '1.61 kg',
                'is_featured' => true, 'views' => 487,
            ],
        ];

        foreach ($products as $p) {
            $existing = DB::table('products')->where('slug', $p['slug'])->first();
            if ($existing) continue;

            DB::table('products')->insert(array_merge($p, [
                'is_active'   => true,
                'created_at'  => now()->subDays(rand(10, 120)),
                'updated_at'  => now(),
            ]));
        }
    }
}
