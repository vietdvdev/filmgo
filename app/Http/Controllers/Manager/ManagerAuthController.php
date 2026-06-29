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
        if (Auth::check() && Auth::user()->roles()->where('name', 'manager')->exists()) {
            return redirect()->route('manager.dashboard');
        }

        return view('manager.auth.login');
    }

    /**
     * Xử lý đăng nhập của Manager.
     * Chỉ role 'manager'. Thông báo lỗi theo từng trường hợp cụ thể.
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

            // === TRƯỜNG HỢP 1: Sai vai trò ===
            if (!$user->roles()->where('name', 'manager')->exists()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Admin cố dùng cổng Manager
                if ($user->roles()->where('name', 'admin')->exists()) {
                    throw ValidationException::withMessages([
                        'email' => 'Khu vực này chỉ dành cho Quản lý Rạp. Tài khoản Admin cần đăng nhập tại: /admin/login',
                    ]);
                }

                // Khách hàng hoặc role khác
                throw ValidationException::withMessages([
                    'email' => 'Khu vực này chỉ dành cho nhân sự nội bộ. Tài khoản của bạn không có quyền truy cập vào khu vực Quản lý Rạp.',
                ]);
            }

            // === TRƯỜNG HỢP 3: Manager chưa được phân công rạp ===
            // Vẫn cho phép đăng nhập → chuyển đến trang thông báo "chưa phân công"
            $request->session()->regenerate();

            if (!$user->cinemas()->exists()) {
                return redirect()->route('manager.no-cinema');
            }

            return redirect()->intended(route('manager.dashboard'));
        }

        throw ValidationException::withMessages([
            'email' => 'Email hoặc mật khẩu không chính xác.',
        ]);
    }

    /**
     * Trang thông báo "Chưa được phân công rạp" (Case 3 — Blank Slate).
     * Manager đã đăng nhập thành công nhưng chưa được Admin gán rạp.
     */
    public function noCinema()
    {
        $user = Auth::user();

        // Nếu không phải manager → redirect về home
        if (!$user->roles()->where('name', 'manager')->exists()) {
            return redirect()->route('home');
        }

        // Nếu đã có rạp rồi → vào dashboard bình thường
        if ($user->cinemas()->exists()) {
            return redirect()->route('manager.dashboard');
        }

        return view('manager.no-cinema');
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
