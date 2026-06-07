<?php
// app/Http/Controllers/Api/Shop/CompareController.php
namespace App\Http\Controllers\Api\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class CompareController extends Controller
{
    // ─── SO SÁNH SẢN PHẨM ───────────────────────────────
    // GET /api/shop/compare?ids[]=1&ids[]=2&ids[]=3
    public function compare(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:2|max:4',
            'ids.*' => 'integer|exists:products,id',
        ]);

        $products = Product::with(['brand', 'category', 'images'])
            ->whereIn('id', $request->ids)
            ->where('is_active', 1)
            ->get();

        // Các tiêu chí so sánh
        $specs = ['cpu', 'ram', 'storage', 'display', 'gpu', 'os', 'battery', 'weight'];

        // Tạo bảng so sánh
        $comparison = [];
        foreach ($specs as $spec) {
            $comparison[$spec] = $products->pluck($spec, 'id');
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'products'   => $products,
                'comparison' => $comparison,
                'specs'      => [
                    'cpu'     => 'Bộ vi xử lý',
                    'ram'     => 'RAM',
                    'storage' => 'Ổ cứng',
                    'display' => 'Màn hình',
                    'gpu'     => 'Card đồ họa',
                    'os'      => 'Hệ điều hành',
                    'battery' => 'Pin',
                    'weight'  => 'Trọng lượng',
                ],
            ],
        ]);
    }
}