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
        $keyword = trim((string) $request->q);

        if (!$keyword || mb_strlen($keyword) < 2) {
            return response()->json(['success' => false, 'message' => 'Từ khóa tìm kiếm quá ngắn.'], 422);
        }

        $priceFilter = $this->extractPriceFilter($keyword);
        $terms = $this->extractSearchTerms($keyword);

        $query = Product::with(['brand', 'category', 'images'])
            ->where('is_active', 1)
            ->when($priceFilter['min'], fn($q, $min) => $q->whereRaw('COALESCE(sale_price, price) >= ?', [$min]))
            ->when($priceFilter['max'], fn($q, $max) => $q->whereRaw('COALESCE(sale_price, price) <= ?', [$max]));

        if (count($terms)) {
            $query->where(function($q) use ($terms) {
                foreach ($terms as $termGroup) {
                    foreach ($this->expandSearchTerm($termGroup) as $term) {
                        $like = "%{$term}%";
                        $compactLike = '%' . $this->compactSearchTerm($term) . '%';

                        $q->orWhere('name',        'like', $like)
                          ->orWhere('description', 'like', $like)
                          ->orWhere('cpu',         'like', $like)
                          ->orWhere('ram',         'like', $like)
                          ->orWhere('storage',     'like', $like)
                          ->orWhere('display',     'like', $like)
                          ->orWhere('gpu',         'like', $like)
                          ->orWhere('os',          'like', $like)
                          ->orWhere('sku',         'like', $like)
                          ->orWhereHas('brand',    fn($b) => $b->where('name', 'like', $like))
                          ->orWhereHas('category', fn($c) => $c->where('name', 'like', $like));

                        if ($compactLike !== '%%') {
                            foreach (['name', 'cpu', 'ram', 'storage', 'gpu', 'display', 'sku'] as $column) {
                                $q->orWhereRaw($this->compactColumnSql($column) . ' like ?', [$compactLike]);
                            }
                        }
                    }
                }
            });
        }

        $products = $query
            ->when(
                $priceFilter['min'] || $priceFilter['max'],
                fn($q) => $q->orderByRaw('COALESCE(sale_price, price) asc'),
                fn($q) => $q->orderBy('views', 'desc')
            )
            ->paginate($request->per_page ?? 12);

        return response()->json([
            'success' => true,
            'keyword' => $keyword,
            'data'    => $products,
        ]);
    }

    // Search helpers
    private function extractSearchTerms(string $keyword): array
    {
        $normalized = $this->normalizeSearchText($keyword);
        $normalized = preg_replace('/\d+(?:[\.,]\d+)?\s*(?:tr|trieu|m)\b/u', ' ', $normalized);
        $normalized = preg_replace('/[^\pL\pN]+/u', ' ', $normalized);

        $stopWords = [
            'laptop', 'may', 'tinh', 'maytinh', 'tim', 'kiem', 'mua', 'ban',
            'gia', 'ngan', 'sach', 'tam', 'khoang', 'duoi', 'tren', 'tu',
            'den', 'toi', 'nho', 'hon', 'lon', 'da', 'thieu', 'tr', 'trieu',
            'cho', 'can', 'co', 'khong', 'va', 'hoac', 'theo',
        ];

        $terms = array_filter(
            explode(' ', $normalized),
            fn($term) => mb_strlen($term) >= 2 && !in_array($term, $stopWords, true)
        );

        return array_values(array_unique($terms));
    }

    private function expandSearchTerm(string $term): array
    {
        $terms = [$term];
        $compact = $this->compactSearchTerm($term);

        if ($term === 'ssd') {
            $terms = array_merge($terms, ['nvme', 'pcie']);
        }

        if (preg_match('/^i([3579])$/', $compact, $matches)) {
            $terms[] = "core i{$matches[1]}";
            $terms[] = "intel core i{$matches[1]}";
        }

        if (preg_match('/^r([3579])$/', $compact, $matches)) {
            $terms[] = "ryzen {$matches[1]}";
            $terms[] = "ryzen{$matches[1]}";
        }

        if (preg_match('/^(\d+)(gb|g)$/', $compact, $matches)) {
            $terms[] = "{$matches[1]} gb";
            $terms[] = $matches[1];
        }

        if (preg_match('/^(\d+)tb$/', $compact, $matches)) {
            $terms[] = "{$matches[1]} tb";
        }

        return array_values(array_unique($terms));
    }

    private function compactSearchTerm(string $term): string
    {
        return preg_replace('/[^a-z0-9]+/u', '', $this->normalizeSearchText($term));
    }

    private function compactColumnSql(string $column): string
    {
        return "lower(replace(replace(replace(coalesce({$column}, ''), ' ', ''), '-', ''), '.', ''))";
    }

    private function extractPriceFilter(string $keyword): array
    {
        $text = $this->normalizeSearchText($keyword);
        $number = '(\d+(?:[\.,]\d+)?)';
        $unit = '(?:tr|trieu|m)';

        if (preg_match("/{$number}\s*(?:-|den|toi)\s*{$number}\s*{$unit}/u", $text, $matches)) {
            return [
                'min' => $this->millionToVnd($matches[1]),
                'max' => $this->millionToVnd($matches[2]),
            ];
        }

        if (preg_match("/(?:duoi|nho hon|toi da|<=|<)\s*{$number}\s*{$unit}/u", $text, $matches)) {
            return [
                'min' => null,
                'max' => $this->millionToVnd($matches[1]),
            ];
        }

        if (preg_match("/(?:tren|lon hon|toi thieu|>=|>)\s*{$number}\s*{$unit}/u", $text, $matches)) {
            return [
                'min' => $this->millionToVnd($matches[1]),
                'max' => null,
            ];
        }

        if ($this->hasBudgetSignal($text) && preg_match("/{$number}\s*{$unit}/u", $text, $matches)) {
            return [
                'min' => null,
                'max' => $this->millionToVnd($matches[1]),
            ];
        }

        return ['min' => null, 'max' => null];
    }

    private function normalizeSearchText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace(['đ', 'Đ'], ['d', 'd'], $text);
        $converted = function_exists('iconv')
            ? @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text)
            : false;

        return $converted ?: $text;
    }

    private function millionToVnd(string $value): int
    {
        return (int) round(((float) str_replace(',', '.', $value)) * 1000000);
    }

    private function hasBudgetSignal(string $text): bool
    {
        return str_contains($text, 'ngan sach')
            || str_contains($text, 'gia')
            || str_contains($text, 'trieu')
            || preg_match('/\d+\s*(m|tr|trieu)/u', $text);
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
