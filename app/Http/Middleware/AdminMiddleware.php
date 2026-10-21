<?php

namespace App\Http\Middleware;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
// app/Http/Middleware/AdminMiddleware.php
public function handle(Request $request, Closure $next)
{
    if (Auth::check() && (
        in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']) || 
        Auth::user()->hasAnyPermission([
            'overtime.view_all', 'leave.view_all', 'permission.view_all', 
            'penalties.create', 'penalties.view_all', 'employees.view_profiles',
            'checkinout.view_all', 'settlements.view_all'
        ])
    )) {
        return $next($request);
    }

    return redirect()->route('Overtime')->withErrors(['error' => 'Access denied.']);
}



}
