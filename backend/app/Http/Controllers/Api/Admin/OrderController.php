<?php
// app/Http/Controllers/Api/Admin/OrderController.php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ─── DANH SÁCH ĐƠN HÀNG ─────────────────────────────
    public function index(Request $request)
    {
        $query = Order::with(['user:id,name,email,phone', 'items.product:id,name,thumbnail'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('order_code', 'like', "%{$request->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$request->search}%")
                      ->orWhere('email', 'like', "%{$request->search}%"));
            }))
            ->when($request->date_from, fn($q) => $q->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to,   fn($q) => $q->whereDate('created_at', '<=', $request->date_to))
            ->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'data'    => $query->paginate($request->per_page ?? 15),
        ]);
    }

    // ─── CHI TIẾT ĐƠN HÀNG ──────────────────────────────
    public function show($id)
    {
        $order = Order::with([
            'user:id,name,email,phone,address',
            'items.product:id,name,thumbnail,sku',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $order,
        ]);
    }

    // ─── CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG ──────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        $order = Order::with('items')->findOrFail($id);

        // Không cho phép quay lại trạng thái đã hủy/hoàn tiền
        if (in_array($order->status, ['cancelled', 'refunded'])) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể thay đổi trạng thái đơn hàng đã hủy hoặc đã hoàn tiền.',
            ], 422);
        }

        DB::transaction(function () use ($order, $request) {
            if (in_array($request->status, ['cancelled', 'refunded'])) {
                $order->restoreStock();
            }

            $updates = ['status' => $request->status];

            if ($request->status === 'delivered' && $order->payment_method === 'cod') {
                $updates['payment_status'] = 'paid';
            }

            if ($request->status === 'refunded') {
                $updates['payment_status'] = 'refunded';
            }

            $order->update($updates);
        });

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật trạng thái đơn hàng thành công!',
            'data'    => $order->fresh(['items.product:id,name,thumbnail,sku', 'user:id,name,email,phone,address']),
        ]);
    }
}
