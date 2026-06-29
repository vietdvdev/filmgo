<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ManagerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Chưa đăng nhập → về trang login Manager
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
            }
            return redirect()->route('manager.login');
        }

        $user = Auth::user();

        // Chỉ role 'manager' được phép vào khu vực Manager
        if ($user->roles()->where('name', 'manager')->exists()) {
            // Kiểm tra manager đã được phân công rạp chưa
            if (!$user->cinemas()->exists()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('manager.login')->withErrors([
                    'email' => 'Tài khoản của bạn chưa được phân công quản lý rạp chiếu nào. Vui lòng liên hệ Quản trị viên.',
                ]);
            }

            return $next($request);
        }

        // Đăng xuất nếu không phải manager
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Không có quyền truy cập khu vực Quản lý Rạp.'], 403);
        }

        // Admin cố vào trang manager → về admin dashboard
        if ($user->roles()->where('name', 'admin')->exists()) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Tài khoản Admin không có quyền vào khu vực Quản lý Rạp. Vui lòng đăng nhập tại trang Quản trị.',
            ]);
        }

        // Các role khác (customer, staff) → về manager login
        return redirect()->route('manager.login')->withErrors([
            'email' => 'Tài khoản của bạn không có quyền truy cập khu vực Quản lý Rạp.',
        ]);
    }
}
