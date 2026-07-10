<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Middleware 'guest': Nếu đã đăng nhập thì redirect về đúng portal theo role.
     * Ngăn user đã đăng nhập xem lại trang login/register.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Admin → Admin Dashboard
            if ($user->roles()->where('name', 'admin')->exists()) {
                return redirect()->route('admin.dashboard');
            }

            // Manager → Manager Dashboard
            if ($user->roles()->where('name', 'manager')->exists()) {
                return redirect()->route('manager.dashboard');
            }

            // Staff → Staff POS (màn hình làm việc chính)
            if ($user->roles()->where('name', 'staff')->exists()) {
                return redirect()->route('staff.pos.index');
            }

            // Customer hoặc các role khác → Trang chủ
            return redirect()->route('home');
        }

        return $next($request);
    }
}
