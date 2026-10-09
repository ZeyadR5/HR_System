<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'login',
            'logout',
            'api/*',
        ]);
        $middleware->alias([
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'permission'  => \App\Http\Middleware\PermissionMiddleware::class,
            'developer'   => \App\Http\Middleware\DeveloperMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->back()
                ->withInput($request->except('_token'))
                ->withErrors(['error' => 'انتهت صلاحية الجلسة (Session Expired). يرجى المحاولة مرة أخرى، أو تحديث الصفحة.']);
        });
    })->create();
