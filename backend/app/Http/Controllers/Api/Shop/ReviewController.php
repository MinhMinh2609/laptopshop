<?php
// app/Http/Controllers/Api/Shop/ReviewController.php
namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Order;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // ─── DANH SÁCH REVIEW CỦA SẢN PHẨM (Public) ────────
    public function index(Request $request, $productId)
    {
        $reviews = Review::with('user:id,name,avatar')
            ->where('product_id', $productId)
            ->where('is_approved', 1)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $reviews]);
    }

    // ─── VIẾT ĐÁNH GIÁ (User) ───────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'order_id'   => 'required|exists:orders,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
        ]);

        // Kiểm tra đã mua hàng chưa (nếu có order_id)
            $purchased = Order::where('id', $request->order_id)
                ->where('user_id', auth()->id())
                ->where('status', 'delivered')
                ->whereHas('items', fn($q) => $q->where('product_id', $request->product_id))
                ->exists();

            if (!$purchased) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn chỉ có thể đánh giá sản phẩm đã mua và đã giao.',
                ], 422);
            }

        $review = Review::create([
            'product_id'  => $request->product_id,
            'user_id'     => auth()->id(),
            'order_id'    => $request->order_id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
            'is_approved' => 0, // Chờ admin duyệt
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gửi đánh giá thành công! Đánh giá sẽ hiển thị sau khi được duyệt.',
            'data'    => $review,
        ], 201);
    }

    // ─── SỬA ĐÁNH GIÁ ───────────────────────────────────
    public function update(Request $request, $id)
    {
        $review = Review::where('id', $id)->where('user_id', auth()->id())->firstOrFail();
        $request->validate(['rating' => 'required|integer|min:1|max:5', 'comment' => 'nullable|string|max:1000']);
        $review->update(['rating' => $request->rating, 'comment' => $request->comment, 'is_approved' => 0]);

        return response()->json(['success' => true, 'message' => 'Cập nhật đánh giá thành công!']);
    }

    // ─── XÓA ĐÁNH GIÁ (User xóa của mình) ──────────────
    public function destroy($id)
    {
        Review::where('id', $id)->where('user_id', auth()->id())->firstOrFail()->delete();
        return response()->json(['success' => true, 'message' => 'Xóa đánh giá thành công!']);
    }

    // ─── ADMIN: Danh sách tất cả review ─────────────────
    public function adminIndex(Request $request)
    {
        $reviews = Review::with(['user:id,name,email', 'product:id,name'])
            ->when($request->is_approved !== null, fn($q) => $q->where('is_approved', $request->is_approved))
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $reviews]);
    }

    // ─── ADMIN: Duyệt đánh giá ──────────────────────────
    public function approve($id)
    {
        $review = Review::findOrFail($id);
        $review->update(['is_approved' => !$review->is_approved]);

        return response()->json([
            'success' => true,
            'message' => $review->is_approved ? 'Đã duyệt đánh giá.' : 'Đã ẩn đánh giá.',
        ]);
    }

    // ─── ADMIN: Xóa đánh giá ────────────────────────────
    public function adminDestroy($id)
    {
        Review::findOrFail($id)->delete();
        return response()->json(['success' => true, 'message' => 'Xóa đánh giá thành công!']);
    }
}
