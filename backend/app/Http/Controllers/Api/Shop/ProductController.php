<?php
// app/Http/Controllers/Api/Shop/ProductController.php
namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ─── DANH SÁCH SẢN PHẨM + FILTER ────────────────────
    public function index(Request $request)
    {
        $query = Product::with(['brand', 'category', 'images'])
            ->where('is_active', 1);

        // Filter theo Brand
        if ($request->brand_id) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->brand_slug) {
            $query->whereHas('brand', fn($q) => $q->where('slug', $request->brand_slug));
        }

        // Filter theo Category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->category_slug) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category_slug));
        }

        // Filter theo Giá
        if ($request->price_min) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->price_max) {
            $query->where('price', '<=', $request->price_max);
        }

        // Filter theo Cấu hình (tìm kiếm nâng cao)
        if ($request->cpu) {
            $query->where('cpu', 'like', "%{$request->cpu}%");
        }
        if ($request->ram) {
            $query->where('ram', 'like', "%{$request->ram}%");
        }
        if ($request->storage) {
            $query->where('storage', 'like', "%{$request->storage}%");
        }
        if ($request->gpu) {
            $query->where('gpu', 'like', "%{$request->gpu}%");
        }

        // Sắp xếp
        $sortBy = match($request->sort) {
            'price_asc'   => ['price', 'asc'],
            'price_desc'  => ['price', 'desc'],
            'newest'      => ['created_at', 'desc'],
            'popular'     => ['views', 'desc'],
            default       => ['created_at', 'desc'],
        };
        $query->orderBy(...$sortBy);

        $products = $query->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'data'    => $products,
        ]);
    }

    // ─── TÌM KIẾM NÂNG CAO ───────────────────────────────
    public function search(Request $request)
    {
        $keyword = $request->q;

        if (!$keyword || strlen($keyword) < 2) {
            return response()->json(['success' => false, 'message' => 'Từ khóa tìm kiếm quá ngắn.'], 422);
        }

        $products = Product::with(['brand', 'category'])
            ->where('is_active', 1)
            ->where(function($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('cpu',     'like', "%{$keyword}%")
                  ->orWhere('ram',     'like', "%{$keyword}%")
                  ->orWhere('storage', 'like', "%{$keyword}%")
                  ->orWhere('sku',     'like', "%{$keyword}%")
                  ->orWhereHas('brand',    fn($b) => $b->where('name', 'like', "%{$keyword}%"))
                  ->orWhereHas('category', fn($c) => $c->where('name', 'like', "%{$keyword}%"));
            })
            ->orderBy('views', 'desc')
            ->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'keyword' => $keyword,
            'data'    => $products,
        ]);
    }

    // ─── CHI TIẾT SẢN PHẨM ──────────────────────────────
    public function show($slug)
    {
        $product = Product::with(['brand', 'category', 'images',
            'reviews' => fn($q) => $q->where('is_approved', 1)
                ->with('user:id,name,avatar')
                ->latest()
                ->take(10)
        ])
        ->where('slug', $slug)
        ->where('is_active', 1)
        ->firstOrFail();

        // Tăng lượt xem
        $product->increment('views');

        // Sản phẩm liên quan (cùng brand hoặc category)
        $related = Product::with(['brand', 'images'])
            ->where('is_active', 1)
            ->where('id', '!=', $product->id)
            ->where(fn($q) => $q->where('brand_id', $product->brand_id)
                                ->orWhere('category_id', $product->category_id))
            ->inRandomOrder()
            ->take(8)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => array_merge($product->toArray(), [
                'rating_avg'   => round($product->reviews->avg('rating'), 1),
                'review_count' => $product->reviews->count(),
                'related'      => $related,
            ]),
        ]);
    }

    // ─── SẢN PHẨM NỔI BẬT ───────────────────────────────
    public function featured()
    {
        $products = Product::with(['brand', 'images'])
            ->where('is_active', 1)
            ->where('is_featured', 1)
            ->orderBy('views', 'desc')
            ->take(8)
            ->get();

        return response()->json(['success' => true, 'data' => $products]);
    }
}
