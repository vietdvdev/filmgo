<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->status !== 'active') {
                $userEmail = $user->email;
                $hasAdminRole = $user->roles()->where('name', 'admin')->exists();
                $hasManagerRole = $user->roles()->where('name', 'manager')->exists();
                $hasStaffRole = $user->roles()->where('name', 'staff')->exists();

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $errorMessage = 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';

                if ($request->expectsJson() || $request->is('api/*') || $request->is('*/api/*')) {
                    return response()->json([
                        'message' => $errorMessage,
                    ], 403);
                }

                // 1. Phân luồng về trang đăng nhập Admin nếu có role Admin hoặc đang ở portal Admin
                if ($hasAdminRole || $request->is('admin*') || $request->routeIs('admin.*')) {
                    return redirect()->route('admin.login')
                        ->withInput(['email' => $userEmail])
                        ->withErrors(['email' => $errorMessage]);
                }

                // 2. Phân luồng về trang đăng nhập Manager nếu có role Manager hoặc đang ở portal Manager
                if ($hasManagerRole || $request->is('manager*') || $request->routeIs('manager.*')) {
                    return redirect()->route('manager.login')
                        ->withInput(['email' => $userEmail])
                        ->withErrors(['email' => $errorMessage]);
                }

                // 3. Phân luồng về trang đăng nhập Staff nếu có role Staff hoặc đang ở portal Staff
                if ($hasStaffRole || $request->is('staff*') || $request->routeIs('staff.*')) {
                    return redirect()->route('staff.login')
                        ->withInput(['email' => $userEmail])
                        ->withErrors(['email' => $errorMessage]);
                }

                // 4. Mặc định phân luồng về trang đăng nhập Khách hàng (Customer)
                return redirect()->route('login')
                    ->withInput(['email' => $userEmail])
                    ->withErrors(['email' => $errorMessage])
                    ->with('error', $errorMessage);
            }
        }

        return $next($request);
    }
}
