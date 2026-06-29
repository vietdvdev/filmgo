<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Chưa đăng nhập → về trang login Customer
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $user = Auth::user();

        // Cho phép nếu có role 'customer' hoặc không có role nào (mặc định customer)
        $isCustomer = $user->roles()->where('name', 'customer')->exists();
        $hasNonCustomerRole = $user->roles()->whereIn('name', ['admin', 'manager', 'staff'])->exists();

        if ($isCustomer && !$hasNonCustomerRole) {
            // Kiểm tra tài khoản có bị khóa không
            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.',
                ]);
            }

            return $next($request);
        }

        // Admin/Manager/Staff cố vào khu vực customer → logout và redirect về đúng portal
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($user->roles()->where('name', 'admin')->exists()) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Tài khoản Quản trị viên không thể truy cập khu vực Khách hàng.',
            ]);
        }

        if ($user->roles()->where('name', 'manager')->exists()) {
            return redirect()->route('manager.login')->withErrors([
                'email' => 'Tài khoản Quản lý Rạp không thể truy cập khu vực Khách hàng.',
            ]);
        }

        // Staff hoặc các role khác
        return redirect()->route('login')->withErrors([
            'email' => 'Tài khoản của bạn không có quyền truy cập khu vực này.',
        ]);
    }
}
