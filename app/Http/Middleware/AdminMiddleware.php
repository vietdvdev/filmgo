<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Chưa đăng nhập → về trang login Admin
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Vui lòng đăng nhập để tiếp tục.'], 401);
            }
            return redirect()->route('admin.login');
        }

        $user = Auth::user();

        // Chỉ role 'admin' được phép vào khu vực Admin
        if ($user->roles()->where('name', 'admin')->exists()) {
            return $next($request);
        }

        // Đăng xuất ngay nếu không phải admin
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Không có quyền truy cập khu vực quản trị.'], 403);
        }

        // Manager → về trang login Manager
        if ($user->roles()->where('name', 'manager')->exists()) {
            return redirect()->route('manager.login')->withErrors([
                'email' => 'Tài khoản Manager không có quyền vào khu vực Quản trị hệ thống. Vui lòng đăng nhập tại trang Manager.',
            ]);
        }

        // Các role khác (customer, staff) → về admin login với thông báo rõ
        return redirect()->route('admin.login')->withErrors([
            'email' => 'Tài khoản của bạn không có quyền truy cập khu vực Quản trị hệ thống.',
        ]);
    }
}
