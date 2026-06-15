<?php

namespace App\Http\Controllers\Admin;

// Kế thừa trực tiếp từ Class Routing gốc của Laravel để tránh lỗi thiếu file Controller.php trung gian
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    // 1. Hàm hiển thị form đăng nhập
    public function showLogin()
    {
        return view('admin.login');
    }

    // 2. Hàm xử lý logic khi bấm nút Đăng nhập
    public function login(Request $request)
    {
        // Kiểm tra dữ liệu đầu vào
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Kiểm tra tài khoản trong Database
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }

        // Nếu sai tài khoản thì báo lỗi
        return back()->withErrors([
            'login_error' => 'Email hoặc mật khẩu không chính xác!',
        ])->onlyInput('email');
    }

    // 3. Hàm xử lý logic Đăng xuất
    public function logout(Request $request)
    {
        Auth::logout(); // Xóa phiên đăng nhập của Admin

        $request->session()->invalidate(); // Hủy Session
        $request->session()->regenerateToken(); // Làm mới mã bảo mật CSRF

        return redirect()->route('admin.login'); // Đá admin quay lại trang đăng nhập
    }
}
