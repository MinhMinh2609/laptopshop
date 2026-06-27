<?php

namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.product:id,name,thumbnail'])
            ->where('user_id', $request->user()->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function show(Request $request, $orderCode)
    {
        $order = Order::with(['items.product:id,name,thumbnail,sku'])
            ->where('order_code', $orderCode)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $myReviews = Review::where('order_id', $order->id)
            ->where('user_id', $request->user()->id)
            ->get(['id', 'product_id', 'rating', 'comment'])
            ->keyBy('product_id');

        return response()->json([
            'success' => true,
            'data' => array_merge($order->toArray(), ['my_reviews' => $myReviews->toArray()]),
        ]);
    }

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

        $userId = $request->user()->id;

        if (!Cart::where('user_id', $userId)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng của bạn đang trống.',
            ], 422);
        }

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
            $cartItems = Cart::where('user_id', $userId)->get();

            if ($cartItems->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Giỏ hàng của bạn đang trống.',
                ], 422);
            }

            $productIds = $cartItems->pluck('product_id')->sort()->values();
            $products = Product::whereIn('id', $productIds)
                ->where('is_active', 1)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($cartItems as $item) {
                $product = $products->get($item->product_id);

                if (!$product) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Sản phẩm trong giỏ hàng không tồn tại hoặc đã ngừng bán.',
                    ], 422);
                }

                if ($product->stock < $item->quantity) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "Sản phẩm '{$product->name}' chỉ còn {$product->stock} trong kho.",
                    ], 422);
                }
            }

            $totalAmount = $cartItems->sum(fn($item) =>
                ($products[$item->product_id]->sale_price ?? $products[$item->product_id]->price) * $item->quantity
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

            foreach ($cartItems as $item) {
                $product = $products[$item->product_id];
                $unitPrice = $product->sale_price ?? $product->price;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $item->product_id,
                    'product_name' => $product->name,
                    'product_sku'  => $product->sku,
                    'quantity'     => $item->quantity,
                    'unit_price'   => $unitPrice,
                    'total_price'  => $unitPrice * $item->quantity,
                ]);

                $product->decrement('stock', $item->quantity);
            }

            Cart::where('user_id', $userId)->delete();

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

    public function cancel(Request $request, $id)
    {
        $order = Order::with('items')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!in_array($order->status, ['pending'])) {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hủy đơn hàng ở trạng thái chờ xác nhận.',
            ], 422);
        }

        DB::transaction(function () use ($order) {
            $order->restoreStock();
            $order->update(['status' => 'cancelled']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Hủy đơn hàng thành công!',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $order = Order::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($order->status !== 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể xóa đơn hàng đã hủy.',
            ], 422);
        }

        $order->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa đơn hàng thành công!',
        ]);
    }
}
