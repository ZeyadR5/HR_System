<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * الاستخدام: ->middleware('permission:overtime.approve')
 * أو أكتر من صلاحية (يكفي واحدة): ->middleware('permission:overtime.approve|leave.approve')
 */
class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = Auth::user();
        $list = explode('|', $permissions);

        if ($user && $user->hasAnyPermission($list)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json(['status' => 'error', 'message' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.'], 403);
        }

        return redirect()->route('Overtime')->withErrors(['error' => 'عفواً، لا تملك صلاحية الوصول لهذه الصفحة.']);
    }
}
