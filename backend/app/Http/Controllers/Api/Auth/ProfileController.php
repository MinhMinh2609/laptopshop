<?php
// app/Http/Controllers/Api/Auth/ProfileController.php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    // ─── XEM HỒ SƠ ──────────────────────────────────────
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'data'    => $request->user(),
        ]);
    }

    // ─── CẬP NHẬT HỒ SƠ ─────────────────────────────────
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'    => 'sometimes|string|max:255',
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ thành công!',
            'data'    => $user->fresh(),
        ]);
    }

    // ─── ĐỔI MẬT KHẨU ───────────────────────────────────
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng.',
                'errors'  => ['current_password' => ['Mật khẩu hiện tại không đúng.']],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);

        // Xóa tất cả token cũ, bắt buộc đăng nhập lại
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công! Vui lòng đăng nhập lại.',
        ]);
    }

    // ─── UPLOAD AVATAR ───────────────────────────────────
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $user = $request->user();

        // Xóa avatar cũ nếu có
        if ($user->avatar) {
            $oldPath = str_replace('/storage/', '', $user->avatar);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
        }

        $path = $request->file('avatar')->store("avatars/{$user->id}", 'public');
        $user->update(['avatar' => \Illuminate\Support\Facades\Storage::url($path)]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật ảnh đại diện thành công!',
            'data'    => ['avatar' => $user->avatar],
        ]);
    }
}