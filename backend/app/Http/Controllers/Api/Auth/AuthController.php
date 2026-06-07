<?php
// app/Http/Controllers/Api/Auth/AuthController.php
namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ─── ĐĂNG KÝ ───────────────────────────────────────────
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
            'phone'    => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone'    => $validated['phone'] ?? null,
            'role'     => 'user',
        ]);

        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng ký thành công!',
            'data'    => ['user' => $this->formatUser($user), 'token' => $token, 'token_type' => 'Bearer'],
        ], 201);
    }

    // ─── ĐĂNG NHẬP ─────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email hoặc mật khẩu không chính xác.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ Admin.',
            ], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('auth_token', ['*'], now()->addDays(30))->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'data'    => ['user' => $this->formatUser($user), 'token' => $token, 'token_type' => 'Bearer'],
        ]);
    }

    // ─── ĐĂNG XUẤT ─────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Đăng xuất thành công!']);
    }

    // ─── THÔNG TIN USER HIỆN TẠI ───────────────────────────
    public function me(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->formatUser($request->user())]);
    }

    // ─── QUÊN MẬT KHẨU ─────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        // Xóa token cũ + tạo token mới
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $token = Str::random(64);

        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        // Link reset trỏ về Vue.js frontend
        $resetUrl = env('FRONTEND_URL', 'http://localhost:5173')
            . '/reset-password?token=' . $token
            . '&email=' . urlencode($request->email);

        // Gửi email
        Mail::send([], [], function ($message) use ($user, $resetUrl) {
            $message->to($user->email, $user->name)
                ->subject('🔐 Đặt Lại Mật Khẩu - Laptop Shop')
                ->html("
                    <div style='font-family:Arial,sans-serif;max-width:600px;margin:auto;padding:30px;'>
                        <h2 style='color:#2563eb;'>🔐 Đặt Lại Mật Khẩu</h2>
                        <p>Xin chào <strong>{$user->name}</strong>,</p>
                        <p>Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                        <p>Nhấn vào nút bên dưới để đặt lại mật khẩu (link có hiệu lực trong <strong>60 phút</strong>):</p>
                        <div style='text-align:center;margin:30px 0;'>
                            <a href='{$resetUrl}'
                               style='background:#2563eb;color:#fff;padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:16px;'>
                                Đặt Lại Mật Khẩu
                            </a>
                        </div>
                        <p style='color:#6b7280;font-size:13px;'>Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>
                        <p style='color:#6b7280;font-size:13px;'>Hoặc copy link: <a href='{$resetUrl}'>{$resetUrl}</a></p>
                        <hr style='margin:20px 0;border:none;border-top:1px solid #e5e7eb;'>
                        <p style='color:#9ca3af;font-size:12px;text-align:center;'>© 2024 Laptop Shop DATN - Đào Duy Minh</p>
                    </div>
                ");
        });

        return response()->json([
            'success' => true,
            'message' => 'Link đặt lại mật khẩu đã được gửi đến email của bạn.',
        ]);
    }

    // ─── ĐẶT LẠI MẬT KHẨU ─────────────────────────────────
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => 'required|email|exists:users,email',
            'token'                 => 'required|string',
            'password'              => ['required', 'confirmed', Password::min(8)],
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token không hợp lệ hoặc đã hết hạn.',
            ], 422);
        }

        // Kiểm tra hết hạn 60 phút
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json([
                'success' => false,
                'message' => 'Token đã hết hạn. Vui lòng yêu cầu lại.',
            ], 422);
        }

        // Cập nhật mật khẩu + xóa token + logout all sessions
        $user = User::where('email', $request->email)->first();
        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đặt lại mật khẩu thành công! Vui lòng đăng nhập.',
        ]);
    }

    // ─── HELPER ────────────────────────────────────────────
    private function formatUser(User $user): array
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role,
            'is_admin'  => $user->role === 'admin',
            'phone'     => $user->phone,
            'address'   => $user->address,
            'avatar'    => $user->avatar,
            'is_active' => $user->is_active,
            'created_at'=> $user->created_at,
        ];
    }
}