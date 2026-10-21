<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditController extends Controller
{
    public function index()
    {
        // Strictly restrict access to z@z.z
        //if (Auth::user()->email !== 'z@z.z' || Auth::user()->email !== 'mtaha@gama.com') {
        if (!Auth::user()->can('reports.audit_log') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) {
            abort(403, 'Unauthorized access.');
        }

        $logs = AuditLog::orderBy('created_at', 'desc')->paginate(50);

        return view('audit_log', compact('logs'));
    }
}
