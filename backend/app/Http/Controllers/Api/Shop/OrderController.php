<?php
// app/Http/Controllers/Api/Shop/OrderController.php
namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // ─── DANH SÁCH ĐƠN HÀNG CỦA USER ───────────────────
    public function index(Request $request)
    {
        $orders = Order::with(['items.product:id,name,thumbnail'])
            ->where('user_id', $request->user()->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json(['success' => true, 'data' => $orders]);
    }

    // ─── CHI TIẾT ĐƠN HÀNG ──────────────────────────────
    public function show(Request $request, $orderCode)
    {
        $order = Order::with(['items.product:id,name,thumbnail,sku'])
            ->where('order_code', $orderCode)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $order]);
    }

    // ─── ĐẶT HÀNG ───────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'shipping_name'    => 'required|string|max:255',
            'shipping_phone'   => 'required|string|max:20',
            'shipping_address' => 'required|string',
            'shipping_city'    => 'required|string|max:100',
            'payment_method'   => 'required|in:cod,vnpay,bank_transfer',
            'note'             => 'nullable|string|max:500',
            'coupon_code'      => 'nullable|string',
        ]);

        $userId    = $request->user()->id;
        $cartItems = Cart::with('product')->where('user_id', $userId)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng của bạn đang trống.',
            ], 422);
        }

        // Kiểm tra tồn kho từng sản phẩm
        foreach ($cartItems as $item) {
            if ($item->product->stock < $item->quantity) {
                return response()->json([
                    'success' => false,
                    'message' => "Sản phẩm '{$item->product->name}' chỉ còn {$item->product->stock} trong kho.",
                ], 422);
            }
        }

        // Kiểm tra mã giảm giá (nếu có)
        $coupon = null;
        if ($request->coupon_code) {
            $coupon = Coupon::where('code', strtoupper($request->coupon_code))->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không tồn tại.',
                ], 422);
            }

            if ($error = $coupon->validationError()) {
                return response()->json([
                    'success' => false,
                    'message' => $error,
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Tính tổng tiền
            $totalAmount = $cartItems->sum(fn($i) =>
                ($i->product->sale_price ?? $i->product->price) * $i->quantity
            );

            $discountAmount = 0;
            if ($coupon) {
                if ($totalAmount < $coupon->min_order) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Đơn hàng tối thiểu ' . number_format($coupon->min_order, 0, ',', '.') . 'đ để áp dụng mã này.',
                    ], 422);
                }
                $discountAmount = $coupon->calculateDiscount((float) $totalAmount);
            }

            $finalAmount = $totalAmount - $discountAmount;

            // Tạo đơn hàng
            $order = Order::create([
                'user_id'          => $userId,
                'order_code'       => 'ORD-' . strtoupper(Str::random(10)),
                'total_amount'     => $totalAmount,
                'discount_amount'  => $discountAmount,
                'final_amount'     => $finalAmount,
                'status'           => 'pending',
                'payment_method'   => $request->payment_method,
                'payment_status'   => 'unpaid',
                'shipping_name'    => $request->shipping_name,
                'shipping_phone'   => $request->shipping_phone,
                'shipping_address' => $request->shipping_address,
                'shipping_city'    => $request->shipping_city,
                'note'             => $request->note,
            ]);

            // Tạo order items + trừ tồn kho
            foreach ($cartItems as $item) {
                $unitPrice = $item->product->sale_price ?? $item->product->price;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $item->product->name,
                    'product_sku'  => $item->product->sku,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $unitPrice * $item->quantity,
                ]);

                // Trừ tồn kho
                Product::where('id', $item->product_id)
                    ->decrement('stock', $item->quantity);
            }

            // Xóa giỏ hàng
            Cart::where('user_id', $userId)->delete();

            // Tăng lượt sử dụng mã giảm giá
            if ($coupon) {
                $coupon->increment('usage_count');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'data'    => $order->load('items'),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Đặt hàng thất bại. Vui lòng thử lại.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── HỦY ĐƠN HÀNG ───────────────────────────────────
    public function cancel(Request $request, $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Chỉ hủy được khi đang pending
        if (!in_array($order->status, ['pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hủy đơn hàng ở trạng thái chờ xác nhận.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            // Hoàn lại tồn kho
            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)
                    ->increment('stock', $item->quantity);
            }
            $order->update(['status' => 'cancelled']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Hủy đơn hàng thành công!',
        ]);
    }
}