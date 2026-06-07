<?php
// app/Http/Controllers/Api/Admin/BrandController.php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => Brand::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100|unique:brands,name',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        return response()->json([
            'success' => true,
            'message' => 'Tạo hãng thành công!',
            'data'    => Brand::create($validated),
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data'    => Brand::withCount('products')->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $brand     = Brand::findOrFail($id);
        $validated = $request->validate([
            'name'        => "sometimes|string|max:100|unique:brands,name,{$id}",
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $brand->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hãng thành công!',
            'data'    => $brand->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $brand = Brand::withCount('products')->findOrFail($id);

        if ($brand->products_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa! Hãng đang có {$brand->products_count} sản phẩm.",
            ], 422);
        }

        $brand->delete();

        return response()->json(['success' => true, 'message' => 'Xóa hãng thành công!']);
    }
}