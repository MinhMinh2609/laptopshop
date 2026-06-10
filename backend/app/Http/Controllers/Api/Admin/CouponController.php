<?php
// app/Http/Controllers/Api/Admin/CouponController.php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CouponController extends Controller
{
    // ─── DANH SÁCH MÃ GIẢM GIÁ ──────────────────────────
    public function index(Request $request)
    {
        $query = Coupon::query()
            ->when($request->search, fn($q) => $q->where('code', 'like', '%' . strtoupper($request->search) . '%'))
            ->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'data'    => $query->paginate($request->per_page ?? 15),
        ]);
    }

    // ─── TẠO MÃ GIẢM GIÁ ─────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'         => 'required|string|max:50|unique:coupons,code',
            'type'         => 'required|in:percent,fixed',
            'value'        => 'required|numeric|min:0',
            'min_order'    => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit'  => 'nullable|integer|min:1',
            'is_active'    => 'boolean',
            'starts_at'    => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        if ($validated['type'] === 'percent' && $validated['value'] > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Giá trị giảm theo % không được vượt quá 100.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tạo mã giảm giá thành công!',
            'data'    => Coupon::create($validated),
        ], 201);
    }

    // ─── CHI TIẾT MÃ GIẢM GIÁ ────────────────────────────
    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data'    => Coupon::findOrFail($id),
        ]);
    }

    // ─── CẬP NHẬT MÃ GIẢM GIÁ ────────────────────────────
    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code'         => "sometimes|string|max:50|unique:coupons,code,{$id}",
            'type'         => 'sometimes|in:percent,fixed',
            'value'        => 'sometimes|numeric|min:0',
            'min_order'    => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit'  => 'nullable|integer|min:1',
            'is_active'    => 'boolean',
            'starts_at'    => 'nullable|date',
            'expires_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        $type  = $validated['type']  ?? $coupon->type;
        $value = $validated['value'] ?? $coupon->value;

        if ($type === 'percent' && $value > 100) {
            return response()->json([
                'success' => false,
                'message' => 'Giá trị giảm theo % không được vượt quá 100.',
            ], 422);
        }

        $coupon->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật mã giảm giá thành công!',
            'data'    => $coupon->fresh(),
        ]);
    }

    // ─── XÓA MÃ GIẢM GIÁ ──────────────────────────────────
    public function destroy($id)
    {
        Coupon::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa mã giảm giá thành công!',
        ]);
    }

    // ─── BẬT/TẮT MÃ GIẢM GIÁ ─────────────────────────────
    public function toggleActive($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        return response()->json([
            'success' => true,
            'message' => $coupon->is_active ? 'Đã kích hoạt mã giảm giá.' : 'Đã tắt mã giảm giá.',
            'data'    => $coupon->fresh(),
        ]);
    }

    // ─── GỬI MÃ GIẢM GIÁ CHO KHÁCH HÀNG QUA EMAIL ────────
    public function sendToCustomers($id)
    {
        $coupon = Coupon::findOrFail($id);

        if ($error = $coupon->validationError()) {
            return response()->json([
                'success' => false,
                'message' => "Không thể gửi mã này: {$error}",
            ], 422);
        }

        $users = User::where('role', 'user')
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get(['id', 'name', 'email']);

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không có khách hàng nào để gửi.',
            ], 422);
        }

        $discountText = $coupon->type === 'percent'
            ? 'giảm ' . rtrim(rtrim((string) $coupon->value, '0'), '.') . '%'
            : 'giảm ' . number_format((float) $coupon->value, 0, ',', '.') . 'đ';

        foreach ($users as $user) {
            Mail::send([], [], function ($message) use ($user, $coupon, $discountText) {
                $message->to($user->email, $user->name)
                    ->subject('🎁 Mã giảm giá dành riêng cho bạn - Laptop Shop')
                    ->html("
                        <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:30px;'>
                            <h2 style='color:#2563eb;'>🎁 Ưu Đãi Dành Cho Bạn</h2>
                            <p>Xin chào <strong>{$user->name}</strong>,</p>
                            <p>Laptop Shop gửi tặng bạn mã giảm giá <strong>{$discountText}</strong> cho đơn hàng tiếp theo:</p>
                            <div style='text-align:center;margin:30px 0;'>
                                <span style='display:inline-block;background:#2563eb;color:#fff;padding:14px 32px;border-radius:8px;font-weight:bold;font-size:20px;letter-spacing:2px;'>
                                    {$coupon->code}
                                </span>
                            </div>
                            <p style='color:#6b7280;font-size:13px;'>Nhập mã này tại trang Giỏ hàng hoặc Thanh toán để được áp dụng. Ưu đãi có thể giới hạn số lượng và thời gian sử dụng.</p>
                        </div>
                    ");
            });
        }

        return response()->json([
            'success' => true,
            'message' => "Đã gửi mã '{$coupon->code}' đến {$users->count()} khách hàng.",
        ]);
    }
}
