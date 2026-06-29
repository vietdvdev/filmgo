<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ManagementAuthController extends Controller
{
    /**
     * Hiển thị trang đăng nhập Admin.
     * Chỉ dành riêng cho tài khoản có vai trò 'admin'.
     */
    public function showLoginForm()
    {
        // Nếu đã đăng nhập và đúng là Admin thì chuyển thẳng vào dashboard
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->roles()->where('name', 'admin')->exists()) {
                return redirect()->route('admin.dashboard');
            }
            // Đăng nhập sai cổng (manager/customer vào admin login) → logout và hiển thị form
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return view('admin.auth.login');
    }

    /**
     * Xử lý đăng nhập Admin.
     * Chỉ tài khoản có vai trò 'admin' mới được phép đăng nhập.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email không được để trống.',
            'email.email'       => 'Email không đúng định dạng.',
            'password.required' => 'Mật khẩu không được để trống.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Kiểm tra trạng thái tài khoản
            if ($user->status === 'locked') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên hệ thống.',
                ]);
            }

            // Kiểm tra vai trò: CHỈ 'admin' được phép đăng nhập vào cổng này
            if (!$user->roles()->where('name', 'admin')->exists()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Đưa ra thông báo phù hợp theo từng loại tài khoản
                if ($user->roles()->where('name', 'manager')->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'Tài khoản Manager không thể đăng nhập tại đây. Vui lòng truy cập: /manager/login',
                    ]);
                }

                throw ValidationException::withMessages([
                    'email' => 'Tài khoản của bạn không có quyền truy cập hệ thống Quản trị.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    /**
     * Đăng xuất Admin.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Đã đăng xuất thành công.');
    }
}
