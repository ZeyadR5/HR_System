<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * منطقة المطور فقط — أي حد غيره بيشوف 404 كأن الصفحة مش موجودة أصلاً.
 */
class DeveloperMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isDeveloper()) {
            return $next($request);
        }

        abort(404);
    }
}
