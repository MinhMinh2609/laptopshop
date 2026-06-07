<?php
// app/Http/Controllers/Api/Admin/ProductController.php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // ─── DANH SÁCH ───────────────────────────────────────
    public function index(Request $request)
    {
        $query = Product::with(['category', 'brand', 'images'])
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku',  'like', "%{$request->search}%");
            }))
            ->when($request->brand_id,    fn($q) => $q->where('brand_id',    $request->brand_id))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->is_active !== null && $request->is_active !== '',
                   fn($q) => $q->where('is_active', $request->is_active))
            ->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'data'    => $query->paginate($request->per_page ?? 15),
        ]);
    }

    // ─── CHI TIẾT ────────────────────────────────────────
    public function show($id)
    {
        $product = Product::with(['category', 'brand', 'images'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $product]);
    }

    // ─── TẠO MỚI ─────────────────────────────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'required|exists:brands,id',
            'name'        => 'required|string|max:500',
            'sku'         => 'required|string|unique:products,sku|max:100',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'cpu'         => 'nullable|string|max:255',
            'ram'         => 'nullable|string|max:100',
            'storage'     => 'nullable|string|max:100',
            'display'     => 'nullable|string|max:255',
            'gpu'         => 'nullable|string|max:255',
            'os'          => 'nullable|string|max:100',
            'battery'     => 'nullable|string|max:100',
            'weight'      => 'nullable|string|max:50',
            'is_active'   => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if (!empty($validated['sale_price']) && $validated['sale_price'] >= $validated['price']) {
            return response()->json([
                'success' => false,
                'message' => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
                'errors'  => ['sale_price' => ['Giá khuyến mãi phải nhỏ hơn giá gốc.']],
            ], 422);
        }

        // Tạo slug unique
        $baseSlug = Str::slug($validated['name']);
        $slug     = $baseSlug . '-' . Str::random(6);
        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . Str::random(8);
        }

        $validated['slug']       = $slug;
        $validated['sale_price'] = $validated['sale_price'] ?: null;

        $product = Product::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tạo sản phẩm thành công!',
            'data'    => $product->load(['category', 'brand']),
        ], 201);
    }

    // ─── CẬP NHẬT ────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'brand_id'    => 'sometimes|exists:brands,id',
            'name'        => 'sometimes|string|max:500',
            'sku'         => "sometimes|string|unique:products,sku,{$id}|max:100",
            'description' => 'nullable|string',
            'price'       => 'sometimes|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'cpu'         => 'nullable|string|max:255',
            'ram'         => 'nullable|string|max:100',
            'storage'     => 'nullable|string|max:100',
            'display'     => 'nullable|string|max:255',
            'gpu'         => 'nullable|string|max:255',
            'os'          => 'nullable|string|max:100',
            'battery'     => 'nullable|string|max:100',
            'weight'      => 'nullable|string|max:50',
            'is_active'   => 'boolean',
            'is_featured' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $baseSlug = Str::slug($validated['name']);
            $slug     = $baseSlug . '-' . Str::random(6);
            while (Product::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $baseSlug . '-' . Str::random(8);
            }
            $validated['slug'] = $slug;
        }

        if (array_key_exists('sale_price', $validated)) {
            $validated['sale_price'] = $validated['sale_price'] ?: null;
        }

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật sản phẩm thành công!',
            'data'    => $product->fresh(['category', 'brand', 'images']),
        ]);
    }

    // ─── XÓA ─────────────────────────────────────────────
    public function destroy($id)
    {
        $product = Product::with('images')->findOrFail($id);

        foreach ($product->images as $image) {
            // Xóa file vật lý
            $relativePath = ltrim(parse_url($image->image_path, PHP_URL_PATH), '/');
            $relativePath = str_replace('storage/', '', $relativePath);
            Storage::disk('public')->delete($relativePath);
        }

        $product->delete();

        return response()->json(['success' => true, 'message' => 'Xóa sản phẩm thành công!']);
    }

    // ─── UPLOAD ẢNH ──────────────────────────────────────
    public function uploadImages(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'images'   => 'required|array|max:10',
            'images.*' => 'required|file|image|mimes:jpeg,jpg,png,webp|max:5120',
        ]);

        if (!$request->hasFile('images')) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy file ảnh trong request.',
            ], 422);
        }

        $uploaded = [];
        $imgCount = $product->images()->count();

        foreach ($request->file('images') as $index => $image) {
            if (!$image->isValid()) continue;

            // Lưu file vào storage/app/public/products/{id}/
            $path = $image->store("products/{$product->id}", 'public');

            // URL đầy đủ để hiển thị
            $url = Storage::url($path);

            $isPrimary = ($index === 0 && $imgCount === 0);

            $productImage = ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $url,
                'is_primary' => $isPrimary,
                'sort_order' => $imgCount + $index,
            ]);

            // Cập nhật thumbnail sản phẩm
            if ($isPrimary) {
                $product->update(['thumbnail' => $url]);
            }

            $uploaded[] = $productImage;
        }

        if (empty($uploaded)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có ảnh nào được upload thành công.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => count($uploaded) . ' ảnh đã được upload thành công!',
            'data'    => $uploaded,
        ]);
    }

    // ─── XÓA 1 ẢNH ───────────────────────────────────────
    public function deleteImage($productId, $imgId)
    {
        $image = ProductImage::where('id', $imgId)
            ->where('product_id', $productId)
            ->firstOrFail();

        // Xóa file vật lý từ storage
        $path = parse_url($image->image_path, PHP_URL_PATH);
        $path = ltrim(str_replace('/storage', '', $path), '/');
        Storage::disk('public')->delete($path);

        $image->delete();

        // Nếu xóa ảnh primary, set ảnh khác làm primary
        $product   = Product::find($productId);
        $remaining = ProductImage::where('product_id', $productId)->orderBy('sort_order')->first();

        if ($remaining) {
            $remaining->update(['is_primary' => true]);
            $product->update(['thumbnail' => $remaining->image_path]);
        } else {
            $product->update(['thumbnail' => null]);
        }

        return response()->json(['success' => true, 'message' => 'Đã xóa ảnh!']);
    }

    // ─── TOGGLE ──────────────────────────────────────────
    public function toggleActive($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_active' => !$product->is_active]);
        return response()->json([
            'success' => true,
            'message' => $product->is_active ? 'Đã hiện sản phẩm' : 'Đã ẩn sản phẩm',
            'data'    => ['is_active' => $product->is_active],
        ]);
    }

    public function toggleFeatured($id)
    {
        $product = Product::findOrFail($id);
        $product->update(['is_featured' => !$product->is_featured]);
        return response()->json([
            'success' => true,
            'message' => $product->is_featured ? 'Đã đặt nổi bật' : 'Đã bỏ nổi bật',
            'data'    => ['is_featured' => $product->is_featured],
        ]);
    }
}