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

        // Kiểm tra tài khoản có bị khóa không
        if ($user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.'], 403);
            }

            return redirect()->route('manager.login')->withErrors([
                'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
            ]);
        }

        // Chỉ role 'manager' được phép vào khu vực Manager
        if ($user->roles()->where('name', 'manager')->exists()) {
            // === TRƯỜNG HỢP 3: Manager chưa được phân công rạp ===
            // Cho vào nhưng redirect về trang thông báo, trừ khi đang ở route no-cinema
            if (!$user->cinemas()->exists() && $request->routeIs('manager.no-cinema') === false) {
                return redirect()->route('manager.no-cinema');
            }

            return $next($request);
        }

        // Đăng xuất nếu không phải manager
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Khu vực này chỉ dành cho nhân sự nội bộ. Tài khoản của bạn không có quyền truy cập vào khu vực Quản lý Rạp.'
            ], 403);
        }

        // Admin cố vào trang manager → hướng về đúng cổng
        if ($user->roles()->where('name', 'admin')->exists()) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Tài khoản Admin không có quyền vào khu vực Quản lý Rạp. Vui lòng đăng nhập tại trang Quản trị.',
            ]);
        }

        // === TRƯỜNG HỢP 1: Khách hàng/role khác cố vào manager ===
        // → 403, redirect về trang chủ với thông báo rõ ràng
        return redirect()->route('home')->with('forbidden_error',
            'Khu vực này chỉ dành cho nhân sự nội bộ. Vui lòng quay lại trang chủ mua vé.'
        );
    }
}
