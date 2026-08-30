<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('admin*') || $request->routeIs('admin.*')) {
                return route('admin.login');
            }
            if ($request->is('manager*') || $request->routeIs('manager.*')) {
                return route('manager.login');
            }
            if ($request->is('staff*') || $request->routeIs('staff.*')) {
                return route('staff.login');
            }
            return route('login');
        });

        $middleware->web(append: [
            \App\Http\Middleware\CheckUserStatus::class,
        ]);

        $middleware->alias([
            'admin'        => \App\Http\Middleware\AdminMiddleware::class,
            'manager'      => \App\Http\Middleware\ManagerMiddleware::class,
            'customer'     => \App\Http\Middleware\CustomerMiddleware::class,
            'staff'        => \App\Http\Middleware\StaffMiddleware::class,
            'guest'        => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'check.status' => \App\Http\Middleware\CheckUserStatus::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->expectsJson() || $request->is('api/*') || $request->is('*/api/*'),
        );
    })->create();
