<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LockEmployeeProfilesOnExit
{
    /**
     * Handle an incoming request.
     * Automatically lock employee profiles whenever the user navigates away to any other page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasSession() && $request->session()->has('unlocked_employee_profiles')) {
            // Check if this is a standard GET page navigation (not AJAX, not JSON, not an API/asset)
            if ($request->isMethod('GET') && !$request->expectsJson() && !$request->ajax() && !$request->is('api/*')) {
                // If the user navigated outside the employee profiles area
                if (!$request->is('employee-profiles*')) {
                    $request->session()->forget('unlocked_employee_profiles');
                }
            }
        }

        $response = $next($request);

        // Prevent browser caching for employee profiles pages so Back/Forward buttons don't restore unlocked state
        if ($request->is('employee-profiles*')) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        }

        return $response;
    }
}
