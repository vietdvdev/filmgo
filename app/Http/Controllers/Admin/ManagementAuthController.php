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
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->roles()->where('name', 'admin')->exists()) {
                return redirect()->route('admin.dashboard');
            }
            // Đã đăng nhập nhưng sai role → logout, hiển thị form với thông báo
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return view('admin.auth.login');
    }

    /**
     * Xử lý đăng nhập Admin.
     * Chỉ tài khoản role 'admin' mới được phép. Các role khác bị chặn với thông báo rõ ràng.
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

            // === TRƯỜNG HỢP 2: Tài khoản bị khóa ===
            if ($user->status === 'locked') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Tài khoản của bạn hiện đang bị khóa hoặc tạm ngưng hoạt động. Vui lòng liên hệ Quản lý hoặc Quản trị viên hệ thống để được hỗ trợ.',
                ]);
            }

            // === TRƯỜNG HỢP 1: Sai vai trò — Khách hàng/Manager cố đăng nhập vào Admin ===
            if (!$user->roles()->where('name', 'admin')->exists()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Manager cố vào Admin → hướng dẫn đúng cổng
                if ($user->roles()->where('name', 'manager')->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'Khu vực này chỉ dành cho Quản trị viên hệ thống. Tài khoản Manager của bạn cần đăng nhập tại: /manager/login',
                    ]);
                }

                // Khách hàng hoặc các role khác cố đăng nhập vào Admin
                throw ValidationException::withMessages([
                    'email' => 'Khu vực này chỉ dành cho nhân sự nội bộ. Tài khoản của bạn không có quyền truy cập vào khu vực quản trị này.',
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
