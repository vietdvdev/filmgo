<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ManagerAuthController extends Controller
{
    /**
     * Hiển thị trang đăng nhập của Manager.
     */
    public function showLoginForm()
    {
        // Nếu đã đăng nhập và là manager thì chuyển thẳng vào dashboard
        if (Auth::check() && Auth::user()->roles()->where('name', 'manager')->exists()) {
            return redirect()->route('manager.dashboard');
        }

        return view('manager.auth.login');
    }

    /**
     * Xử lý đăng nhập của Manager.
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
                    'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                ]);
            }

            // Chỉ tài khoản có vai trò "manager" mới được phép đăng nhập
            if (! $user->roles()->where('name', 'manager')->exists()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'Tài khoản này không có quyền truy cập khu vực Quản lý Rạp.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('manager.dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ]);
    }

    /**
     * Đăng xuất Manager.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('manager.login');
    }
}
