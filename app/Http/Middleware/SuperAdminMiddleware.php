<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    /**
     * السماح فقط لـ super_admin
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && (
            in_array(Auth::user()->role, ['super_admin', 'developer']) ||
            Auth::user()->hasAnyPermission([
                'reports.full_report', 'reports.audit_log', 'settlements.approve', 
                'penalties.approve', 'employees.edit_profiles', 'employees.manual_entry'
            ])
        )) {
            return $next($request);
        }

        return redirect()->route('Overtime')->withErrors(['error' => 'Access denied. Super Admin only.']);
    }
}
