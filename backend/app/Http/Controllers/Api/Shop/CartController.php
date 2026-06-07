<?php
// app/Http/Controllers/Api/Shop/CartController.php
namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // ─── XEM GIỎ HÀNG ───────────────────────────────────
    public function index(Request $request)
    {
        $items = Cart::with(['product' => fn($q) => $q->with(['brand', 'images'])])
            ->where('user_id', $request->user()->id)
            ->get()
            ->map(function ($item) {
                // Kiểm tra product tồn tại
                if (!$item->product) return null;

                $unitPrice = $item->product->sale_price ?? $item->product->price;
                return [
                    'id'          => $item->id,
                    'quantity'    => $item->quantity,
                    'product'     => $item->product,
                    'unit_price'  => $unitPrice,
                    'total_price' => $unitPrice * $item->quantity,
                ];
            })
            ->filter() // Xóa null items
            ->values();

        return response()->json([
            'success' => true,
            'data'    => [
                'items'        => $items,
                'total_items'  => $items->sum('quantity'),
                'total_amount' => $items->sum('total_price'),
            ],
        ]);
    }

    // ─── THÊM VÀO GIỎ HÀNG ──────────────────────────────
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'integer|min:1|max:10',
        ]);

        $product = Product::where('id', $request->product_id)
            ->where('is_active', 1)
            ->first();

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Sản phẩm không tồn tại hoặc đã ngừng bán.',
            ], 404);
        }

        $quantity = $request->quantity ?? 1;

        if ($product->stock < $quantity) {
            return response()->json([
                'success' => false,
                'message' => "Sản phẩm chỉ còn {$product->stock} trong kho.",
            ], 422);
        }

        $cartItem = Cart::where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $quantity;
            if ($newQty > $product->stock) {
                return response()->json([
                    'success' => false,
                    'message' => "Không thể thêm. Giỏ đã có {$cartItem->quantity}, kho còn {$product->stock}.",
                ], 422);
            }
            $cartItem->update(['quantity' => $newQty]);
        } else {
            $cartItem = Cart::create([
                'user_id'    => $request->user()->id,
                'product_id' => $product->id,
                'quantity'   => $quantity,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã thêm vào giỏ hàng!',
            'data'    => $cartItem->load(['product.brand', 'product.images']),
        ]);
    }

    // ─── CẬP NHẬT SỐ LƯỢNG ──────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cartItem = Cart::with('product')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if ($cartItem->product && $cartItem->product->stock < $request->quantity) {
            return response()->json([
                'success' => false,
                'message' => "Sản phẩm chỉ còn {$cartItem->product->stock} trong kho.",
            ], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật giỏ hàng thành công!',
            'data'    => $cartItem->fresh(['product.brand']),
        ]);
    }

    // ─── XÓA 1 SẢN PHẨM ─────────────────────────────────
    public function remove(Request $request, $id)
    {
        Cart::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail()
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng!',
        ]);
    }

    // ─── XÓA TOÀN BỘ GIỎ ────────────────────────────────
    public function clear(Request $request)
    {
        Cart::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa toàn bộ giỏ hàng!',
        ]);
    }
}