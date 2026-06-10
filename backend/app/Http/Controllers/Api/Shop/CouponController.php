<?php
// app/Http/Controllers/Api/Shop/CouponController.php
namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // ─── ÁP DỤNG MÃ GIẢM GIÁ CHO GIỎ HÀNG ───────────────
    public function apply(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $coupon = Coupon::where('code', strtoupper($request->code))->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Mã giảm giá không tồn tại.',
            ], 404);
        }

        if ($error = $coupon->validationError()) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ], 422);
        }

        $cartItems = Cart::with('product')->where('user_id', $request->user()->id)->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Giỏ hàng của bạn đang trống.',
            ], 422);
        }

        $totalAmount = $cartItems->sum(fn($i) =>
            ($i->product->sale_price ?? $i->product->price) * $i->quantity
        );

        if ($totalAmount < $coupon->min_order) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng tối thiểu ' . number_format($coupon->min_order, 0, ',', '.') . 'đ để áp dụng mã này.',
            ], 422);
        }

        $discountAmount = $coupon->calculateDiscount($totalAmount);

        return response()->json([
            'success' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'data'    => [
                'code'            => $coupon->code,
                'type'            => $coupon->type,
                'value'           => $coupon->value,
                'discount_amount' => $discountAmount,
                'total_amount'    => $totalAmount,
                'final_amount'    => $totalAmount - $discountAmount,
            ],
        ]);
    }
}
