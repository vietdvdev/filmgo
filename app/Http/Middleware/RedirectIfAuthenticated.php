<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->roles()->where('name', 'admin')->exists()) {
                return redirect()->route('admin.dashboard');
            }

            if ($user->roles()->where('name', 'manager')->exists()) {
                return redirect()->route('manager.dashboard');
            }

            return redirect('/');
        }

        return $next($request);
    }
}
