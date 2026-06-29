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
            return response()->json([
                'message' => 'Khu vực này chỉ dành cho nhân sự nội bộ. Tài khoản của bạn không có quyền truy cập vào khu vực quản trị này.'
            ], 403);
        }

        // Manager → hướng dẫn về đúng cổng
        if ($user->roles()->where('name', 'manager')->exists()) {
            return redirect()->route('manager.login')->withErrors([
                'email' => 'Tài khoản Manager không có quyền vào khu vực Quản trị hệ thống. Vui lòng đăng nhập tại trang Manager.',
            ]);
        }

        // Khách hàng/role khác → 403, redirect về trang chủ với thông báo
        return redirect()->route('home')->with('forbidden_error',
            'Khu vực này chỉ dành cho nhân sự nội bộ. Vui lòng quay lại trang chủ.'
        );
    }
}
