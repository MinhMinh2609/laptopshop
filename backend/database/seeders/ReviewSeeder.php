<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Chỉ tạo review cho đơn hàng đã delivered
        $deliveredOrders = DB::table('orders')
            ->where('status', 'delivered')
            ->get();

        if ($deliveredOrders->isEmpty()) return;

        $comments = [
            5 => [
                'Laptop rất tốt, giao hàng nhanh, đóng gói chắc chắn. Hiệu năng vượt mong đợi!',
                'Sản phẩm chính hãng, đúng mô tả. Màn hình đẹp, bàn phím gõ sướng. Rất hài lòng!',
                'Mua lần 2 rồi, shop uy tín, máy ngon. Pin trâu hơn tưởng tượng, dùng cả ngày không cần sạc.',
                'Đóng gói kỹ lưỡng, máy không trầy xước. Cài đặt sẵn Windows bản quyền. Rất ổn!',
            ],
            4 => [
                'Máy dùng tốt, hiệu năng ổn định. Chỉ tiếc màn hình hơi phản quang khi ra ngoài.',
                'Chất lượng tốt so với giá. Giao hàng đúng hẹn, nhân viên hỗ trợ nhiệt tình.',
                'Pin 6-7 tiếng dùng thực tế, ổn với mình. Máy chạy mát, ít ồn khi làm việc văn phòng.',
                'Hàng đúng mô tả, giá hợp lý. Sẽ giới thiệu bạn bè mua ở đây.',
            ],
            3 => [
                'Máy dùng tạm ổn nhưng pin hơi yếu hơn quảng cáo, chỉ được khoảng 4-5 tiếng.',
                'Cấu hình đúng nhưng giao hàng chậm hơn dự kiến 1 ngày. Máy hoạt động bình thường.',
            ],
        ];

        foreach ($deliveredOrders as $order) {
            $items = DB::table('order_items')->where('order_id', $order->id)->get();

            foreach ($items as $item) {
                // Tránh trùng lặp (unique: product_id + user_id + order_id)
                $exists = DB::table('reviews')
                    ->where('product_id', $item->product_id)
                    ->where('user_id', $order->user_id)
                    ->where('order_id', $order->id)
                    ->exists();

                if ($exists) continue;

                // 80% xác suất có review
                if (rand(1, 100) > 80) continue;

                $rating  = rand(0, 10) < 7 ? 5 : (rand(0, 1) ? 4 : 3); // 70% 5 sao, 20% 4 sao, 10% 3 sao
                $pool    = $comments[$rating];
                $comment = $pool[array_rand($pool)];

                DB::table('reviews')->insert([
                    'product_id'  => $item->product_id,
                    'user_id'     => $order->user_id,
                    'order_id'    => $order->id,
                    'rating'      => $rating,
                    'comment'     => $comment,
                    'is_approved' => true,
                    'created_at'  => now()->subDays(rand(0, 20)),
                    'updated_at'  => now(),
                ]);
            }
        }
    }
}
