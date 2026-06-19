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
        if (Auth::check() && Auth::user()->roles()->whereIn('name', ['admin', 'manager', 'staff'])->exists()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Không có quyền truy cập.'], 403);
        }

        // Đăng xuất nếu tài khoản không đủ quyền (ví dụ khách hàng cố tình vào admin)
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->withErrors([
            'email' => 'Tài khoản của bạn không có quyền truy cập hệ thống quản trị.',
        ]);
    }
}
