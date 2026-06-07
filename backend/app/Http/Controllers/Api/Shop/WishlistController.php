<?php
// app/Http/Controllers/Api/Shop/WishlistController.php
namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    // ─── DANH SÁCH YÊU THÍCH ────────────────────────────
    public function index(Request $request)
    {
        $items = Wishlist::with(['product' => fn($q) => $q->with(['brand', 'images'])])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $items]);
    }

    // ─── THÊM/XÓA YÊU THÍCH (Toggle) ────────────────────
    public function toggle(Request $request, $productId)
    {
        $userId = $request->user()->id;

        $existing = Wishlist::where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'success'      => true,
                'message'      => 'Đã xóa khỏi danh sách yêu thích.',
                'in_wishlist'  => false,
            ]);
        }

        Wishlist::create(['user_id' => $userId, 'product_id' => $productId]);

        return response()->json([
            'success'     => true,
            'message'     => 'Đã thêm vào danh sách yêu thích!',
            'in_wishlist' => true,
        ]);
    }
}