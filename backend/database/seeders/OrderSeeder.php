<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users    = DB::table('users')->where('role', 'user')->pluck('id')->toArray();
        $products = DB::table('products')->where('is_active', 1)->get()->toArray();

        if (empty($users) || empty($products)) return;

        $orders = [
            // Đơn delivered - COD
            ['user_idx' => 0, 'status' => 'delivered', 'payment_method' => 'cod',          'payment_status' => 'paid',   'days_ago' => 30, 'product_indices' => [0, 4]],
            // Đơn delivered - VNPay
            ['user_idx' => 1, 'status' => 'delivered', 'payment_method' => 'vnpay',        'payment_status' => 'paid',   'days_ago' => 25, 'product_indices' => [14]],
            // Đơn delivered - bank
            ['user_idx' => 2, 'status' => 'delivered', 'payment_method' => 'bank_transfer', 'payment_status' => 'paid',  'days_ago' => 20, 'product_indices' => [8, 9]],
            // Đơn shipped
            ['user_idx' => 3, 'status' => 'shipped',   'payment_method' => 'vnpay',        'payment_status' => 'paid',   'days_ago' => 5,  'product_indices' => [15]],
            // Đơn processing
            ['user_idx' => 4, 'status' => 'processing','payment_method' => 'cod',          'payment_status' => 'unpaid', 'days_ago' => 3,  'product_indices' => [4]],
            // Đơn confirmed
            ['user_idx' => 5, 'status' => 'confirmed', 'payment_method' => 'vnpay',        'payment_status' => 'paid',   'days_ago' => 2,  'product_indices' => [12]],
            // Đơn pending
            ['user_idx' => 6, 'status' => 'pending',   'payment_method' => 'cod',          'payment_status' => 'unpaid', 'days_ago' => 1,  'product_indices' => [2]],
            // Đơn cancelled
            ['user_idx' => 0, 'status' => 'cancelled', 'payment_method' => 'cod',          'payment_status' => 'unpaid', 'days_ago' => 15, 'product_indices' => [6]],
            // Đơn delivered tháng này
            ['user_idx' => 1, 'status' => 'delivered', 'payment_method' => 'vnpay',        'payment_status' => 'paid',   'days_ago' => 7,  'product_indices' => [13]],
            // Đơn delivered hôm nay
            ['user_idx' => 2, 'status' => 'delivered', 'payment_method' => 'cod',          'payment_status' => 'paid',   'days_ago' => 0,  'product_indices' => [1]],
        ];

        $cities = ['TP.HCM', 'Hà Nội', 'Đà Nẵng', 'Cần Thơ', 'Hải Phòng'];
        $addresses = [
            '123 Nguyễn Huệ, Quận 1',
            '45 Hoàng Diệu, Ba Đình',
            '78 Lê Lợi, Hải Châu',
            '12 Trần Phú, Ninh Kiều',
            '56 Lạch Tray, Ngô Quyền',
            '89 Phan Đình Phùng, Đống Đa',
            '34 Đinh Tiên Hoàng, Bình Thạnh',
        ];
        $names  = ['Nguyễn Văn An', 'Trần Thị Bình', 'Lê Minh Khoa', 'Phạm Thị Lan', 'Hoàng Đức Mạnh', 'Vũ Thị Ngọc', 'Đặng Văn Phúc'];
        $phones = ['0912345678', '0923456789', '0934567890', '0945678901', '0956789012', '0967890123', '0978901234'];

        foreach ($orders as $i => $orderDef) {
            $userId = $users[$orderDef['user_idx'] % count($users)];
            $code   = 'LS' . now()->subDays($orderDef['days_ago'])->format('ymd') . str_pad($i + 1, 4, '0', STR_PAD_LEFT);

            // Bỏ qua nếu order code đã tồn tại
            if (DB::table('orders')->where('order_code', $code)->exists()) continue;

            // Build order items từ product indices
            $items = [];
            $totalAmount = 0;
            foreach ($orderDef['product_indices'] as $pIdx) {
                $p    = $products[$pIdx % count($products)];
                $qty  = rand(1, 2);
                $price = $p->sale_price ?? $p->price;
                $items[] = [
                    'product_id'   => $p->id,
                    'product_name' => $p->name,
                    'product_sku'  => $p->sku,
                    'quantity'     => $qty,
                    'unit_price'   => $price,
                    'total_price'  => $price * $qty,
                ];
                $totalAmount += $price * $qty;
            }

            $idx = $orderDef['user_idx'] % count($names);
            $orderId = DB::table('orders')->insertGetId([
                'user_id'          => $userId,
                'order_code'       => $code,
                'total_amount'     => $totalAmount,
                'discount_amount'  => 0,
                'final_amount'     => $totalAmount,
                'status'           => $orderDef['status'],
                'payment_method'   => $orderDef['payment_method'],
                'payment_status'   => $orderDef['payment_status'],
                'shipping_name'    => $names[$idx],
                'shipping_phone'   => $phones[$idx],
                'shipping_address' => $addresses[$idx],
                'shipping_city'    => $cities[$idx % count($cities)],
                'note'             => null,
                'created_at'       => now()->subDays($orderDef['days_ago'])->subHours(rand(0, 8)),
                'updated_at'       => now()->subDays($orderDef['days_ago']),
            ]);

            foreach ($items as $item) {
                DB::table('order_items')->insert(array_merge($item, [
                    'order_id'   => $orderId,
                    'created_at' => now()->subDays($orderDef['days_ago']),
                ]));
            }
        }
    }
}
