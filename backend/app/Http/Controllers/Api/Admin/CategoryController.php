<?php
// app/Http/Controllers/Api/Admin/CategoryController.php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        return response()->json([
            'success' => true,
            'message' => 'Tạo danh mục thành công!',
            'data'    => Category::create($validated),
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data'    => Category::withCount('products')->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $category  = Category::findOrFail($id);
        $validated = $request->validate([
            'name'        => "sometimes|string|max:255|unique:categories,name,{$id}",
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật danh mục thành công!',
            'data'    => $category->fresh(),
        ]);
    }

    public function destroy($id)
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            return response()->json([
                'success' => false,
                'message' => "Không thể xóa! Danh mục đang có {$category->products_count} sản phẩm.",
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => true, 'message' => 'Xóa danh mục thành công!']);
    }
}