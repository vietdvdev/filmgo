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
        if (Auth::check() && Auth::user()->roles()->where('name', 'manager')->exists()) {
            // Kiểm tra xem manager có được gán rạp nào không
            if (Auth::user()->cinemas()->exists()) {
                return $next($request);
            }
            
            return redirect()->route('home')->with('error', 'Tài khoản của bạn chưa được phân công quản lý rạp chiếu nào.');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Không có quyền truy cập.'], 403);
        }

        return redirect()->route('manager.login')->withErrors([
            'email' => 'Tài khoản của bạn không có quyền truy cập khu vực quản lý rạp.',
        ]);
    }
}
