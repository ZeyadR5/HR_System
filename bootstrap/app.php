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
        $middleware->appendToGroup('web', \App\Http\Middleware\PreventDuplicateSubmission::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\LockEmployeeProfilesOnExit::class);
        $middleware->append(\App\Http\Middleware\SanitizeInput::class);
        $middleware->append(\App\Http\Middleware\SecurityHeadersMiddleware::class);
        $middleware->alias([
            'admin'       => \App\Http\Middleware\AdminMiddleware::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'permission'  => \App\Http\Middleware\PermissionMiddleware::class,
            'developer'   => \App\Http\Middleware\DeveloperMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Record all unhandled exceptions into AuditLog table for admin visibility
        $exceptions->report(function (\Throwable $e) {
            try {
                if (class_exists(\App\Models\AuditLog::class) && \Illuminate\Support\Facades\Schema::hasTable('audit_logs')) {
                    $user = \Illuminate\Support\Facades\Auth::user();
                    $userName = $user ? $user->name : 'System/Guest';
                    $url = request() ? request()->path() : 'CLI';
                    $details = sprintf("[%s] %s (File: %s, Line: %d)", get_class($e), $e->getMessage(), basename($e->getFile()), $e->getLine());
                    \App\Models\AuditLog::log(
                        $userName,
                        'System Error',
                        'System',
                        mb_substr($url, 0, 255),
                        mb_substr($details, 0, 500)
                    );
                }
            } catch (\Throwable $auditEx) {
                // Silently bypass to avoid infinite error loops
            }
        });

        // Gracefully handle session expiration
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->back()
                ->withInput($request->except('_token'))
                ->withErrors(['error' => 'انتهت صلاحية الجلسة (Session Expired). يرجى المحاولة مرة أخرى، أو تحديث الصفحة.']);
        });

        // Hide technical errors from users and show friendly messages
        $exceptions->render(function (\Throwable $e, $request) {
            // For API or JSON requests, return clean JSON without exposing stack traces
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'حدث خطأ غير متوقع أثناء تنفيذ العملية، تم تسجيل الخطأ للمتابعة.',
                ], 500);
            }

            // Let ValidationException flash validation errors normally
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            // Let standard HTTP exceptions (404, 403, 401) render their designated error views
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                return null;
            }

            // For form submissions (POST/PUT/DELETE), redirect back with old input and friendly alert
            if (!$request->isMethod('GET') && $request->hasSession()) {
                return redirect()->back()
                    ->withInput($request->except(['password', 'password_confirmation', '_token']))
                    ->withErrors(['error' => 'عذراً، حدث خطأ أثناء تنفيذ طلبك وتم تسجيل تفاصيل المشكلة للمراجعة والتصحيح.']);
            }

            // For GET page requests, render clean, user-friendly 500 error page
            return response()->view('errors.500', [], 500);
        });
    })->create();
