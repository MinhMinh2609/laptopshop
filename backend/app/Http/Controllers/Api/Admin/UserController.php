<?php
// app/Http/Controllers/Api/Admin/UserController.php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    // ─── DANH SÁCH NGƯỜI DÙNG ───────────────────────────
    public function index(Request $request)
    {
        $query = User::query()
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name',  'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%");
            }))
            ->when($request->role,      fn($q) => $q->where('role', $request->role))
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->is_active))
            ->withCount('orders')
            ->orderBy('created_at', 'desc');

        return response()->json([
            'success' => true,
            'data'    => $query->paginate($request->per_page ?? 15),
        ]);
    }

    // ─── CHI TIẾT NGƯỜI DÙNG ────────────────────────────
    public function show($id)
    {
        $user = User::withCount('orders')
            ->with(['orders' => fn($q) => $q->latest()->take(5)])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $user,
        ]);
    }

    // ─── TẠO NGƯỜI DÙNG MỚI (Admin tạo) ────────────────
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'role'     => 'required|in:admin,user',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'is_active'=> 'boolean',
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo người dùng thành công!',
            'data'    => $user,
        ], 201);
    }

    // ─── CẬP NHẬT NGƯỜI DÙNG ────────────────────────────
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => "sometimes|email|unique:users,email,{$id}",
            'role'     => 'sometimes|in:admin,user',
            'phone'    => 'nullable|string|max:20',
            'address'  => 'nullable|string',
            'is_active'=> 'boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật người dùng thành công!',
            'data'    => $user->fresh(),
        ]);
    }

    // ─── XÓA NGƯỜI DÙNG ─────────────────────────────────
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Không cho xóa chính mình
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa tài khoản đang đăng nhập.',
            ], 422);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa người dùng thành công!',
        ]);
    }

    // ─── BẬT/TẮT KHÓA TÀI KHOẢN ────────────────────────
    public function toggleActive($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể khóa tài khoản đang đăng nhập.',
            ], 422);
        }

        $user->update(['is_active' => !$user->is_active]);

        return response()->json([
            'success' => true,
            'message' => $user->is_active ? 'Đã mở khóa tài khoản.' : 'Đã khóa tài khoản.',
            'data'    => ['is_active' => $user->is_active],
        ]);
    }
}