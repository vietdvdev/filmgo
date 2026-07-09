<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('staff.login');
        }

        $user = Auth::user();

        if ($user->roles()->where('name', 'staff')->exists()) {
            return $next($request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('staff.login')->withErrors([
            'email' => 'Khu vực này chỉ dành cho nhân viên (Staff). Tài khoản của bạn không có quyền truy cập.',
        ]);
    }
}
