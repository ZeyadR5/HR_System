<?php
namespace App\Http\Controllers;

use App\Models\Leave;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\LeavesExport;
use App\Exports\PermissionsExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\SubmissionWindow;
use App\Models\AuditLog;
use App\Models\User;


class LeavePermissionController extends Controller
{


public function permission_index()
{
    $userId = Auth::id();
    $period = \App\Support\PayrollPeriod::current();
    
    // Monthly usage in minutes (accepted + pending)
    $permissions = Permission::where('user_id', $userId)
        ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
        ->where('status', '!=', 'refused')
        ->get();

    $totalMinutes = 0;
    foreach ($permissions as $p) {
        if ($p->from && $p->to) {
            $from = \Carbon\Carbon::parse($p->from);
            $to   = \Carbon\Carbon::parse($p->to);
            $totalMinutes += $from->diffInMinutes($to);
        }
    }

    return view('ezn', [
        'dateBounds' => \App\Support\SubmissionWindow::bounds(),
        'totalMinutes' => $totalMinutes,
        'limitMinutes' => \App\Support\Settings::get('monthly_permissions_hours', 3) * 60,
    ]);
}
    // ---- LEAVE ----
    public function leave_index()
    {
        $userId = Auth::id();
        $period = \App\Support\PayrollPeriod::current();
        
        // Monthly usage (accepted + pending)
        $monthUsage = Leave::where('user_id', $userId)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        // Yearly usage (accepted + pending)
        $yearUsage = Leave::where('user_id', $userId)
            ->whereYear('date', now()->year)
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        $takenLeaves = Leave::where('user_id', $userId)
            ->where('status', '!=', 'refused')
            ->pluck('date')
            ->toArray();

        return view('agaza', [
            'dateBounds' => SubmissionWindow::bounds(),
            'monthLimit' => \App\Support\Settings::get('monthly_leaves_limit', 2),
            'monthUsage' => $monthUsage,
            'yearLimit' => \App\Support\Settings::get('annual_leaves_limit', 14),
            'yearUsage' => $yearUsage,
            'takenLeaves' => $takenLeaves,
        ]);
    }

    public function leave_post(Request $request)
    {
        $request->validate([
            'date'       => 'required|date',
            'day'        => 'required|string',
            'reason'     => 'required|string|max:1000',
            'substitute' => 'required|string|max:255',
            'days_count' => 'required|integer|min:1',
        ]);

        $userId = Auth::id();
        $requestDate = \Carbon\Carbon::parse($request->date);
        $period = \App\Support\PayrollPeriod::fromRequest($request->date);

        // 1. Check Monthly Limit (Max 2 days per cycle)
        $monthUsage = Leave::where('user_id', $userId)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        $monthLimit = \App\Support\Settings::get('monthly_leaves_limit', 2);
        if (($monthUsage + $request->days_count) > $monthLimit) {
            return redirect()->back()->with('alert_error', "⚠️ تخطيت الحد المسموح به خلال الشهر وهو {$monthLimit} أيام، يرجى الرجوع إلى المسؤول.")->withInput();
        }

        // 2. Check Yearly Limit (Max 14 days per year)
        $yearUsage = Leave::where('user_id', $userId)
            ->whereYear('date', $requestDate->year)
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        $yearLimit = \App\Support\Settings::get('annual_leaves_limit', 14);
        if (($yearUsage + $request->days_count) > $yearLimit) {
            return redirect()->back()->with('alert_error', "⚠️ عذراً، رصيد إجازاتك السنوي ({$yearLimit} يوم) قد انتهى.")->withInput();
        }

        SubmissionWindow::assertDateWithinAllowedWindow($request->date, 'Leave date');

        $name = (Auth::user()->email === 'guest@gamma.com' && $request->has('name')) 
            ? $request->name 
            : Auth::user()->name;

        Leave::create([
            'user_id'    => Auth::id(),
            'name'       => $name,
            'date'       => $request->date,
            'day'        => $request->day,
            'reason'     => $request->reason,
            'substitute' => $request->substitute,
            'days_count' => $request->days_count,
        ]);

        AuditLog::log(Auth::user()->name, 'Created', 'Leave', $name, "Date: {$request->date}");

        return redirect()->route('home')->with('success', 'Leave submitted successfully!');
    }

public function leave_accept($id)
{
    $leave = Leave::findOrFail($id);
    $leave->update([
        'status'      => 'accepted',
        'actioned_by' => Auth::user()->name,
    ]);
    
    AuditLog::log(Auth::user()->name, 'Accepted', 'Leave', $leave->name, "Date: {$leave->date}");

    if ($leave->user) {
        $leave->user->sendOneSignalNotification(
            'تم قبول الإجازة 🎉',
            "تم قبول طلب الإجازة الخاص بك من {$leave->start_date} إلى {$leave->end_date}.",
            Auth::user()->name
        );
    }

    return redirect()->route('view_leave')->with('success', 'Leave accepted.');
}

public function leave_refuse(Request $request, $id)
{
    $request->validate(['refuse_reason' => 'required|string|max:500']);
    $leave = Leave::findOrFail($id);
    $leave->update([
        'status'        => 'refused',
        'refuse_reason' => $request->refuse_reason,
        'actioned_by'   => Auth::user()->name,
    ]);

    AuditLog::log(Auth::user()->name, 'Refused', 'Leave', $leave->name, "Date: {$leave->date}, Reason: {$request->refuse_reason}");
    
    if ($leave->user) {
        $leave->user->sendOneSignalNotification(
            'تم رفض الإجازة ⚠️',
            "تم رفض طلب الإجازة الخاص بك. السبب: {$request->refuse_reason}",
            Auth::user()->name
        );
    }

    return redirect()->route('view_leave')->with('success', 'Leave refused.');
}

public function view_leave(Request $request)
{
    $period = \App\Support\PayrollPeriod::fromRequest($request->query('period_start'));
    $periodStart        = $period['start'];
    $periodEnd          = $period['end'];
    $previousPeriodStart = $period['previous_start']->format('Y-m-d');
    $nextPeriodStart     = $period['next_start']->format('Y-m-d');
    $isCurrentPeriod    = $period['is_current'];
    $periodLabel        = $period['label'];
    $selectedPeriodStart = $periodStart->format('Y-m-d');

    $users = [];
    if (in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) {
        $users = User::orderBy('name')->get();
        $query = Leave::whereBetween('date', [$periodStart, $periodEnd]);
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        $leaves = $query->latest()->get();
    } else {
        $leaves = Leave::where('user_id', Auth::id())
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->latest()->get();
    }

    return view('view_agaza', compact(
        'leaves', 'users', 'periodStart', 'periodEnd', 'periodLabel',
        'isCurrentPeriod', 'previousPeriodStart', 'nextPeriodStart', 'selectedPeriodStart'
    ));
}

public function view_permission(Request $request)
{
    $period = \App\Support\PayrollPeriod::fromRequest($request->query('period_start'));
    $periodStart        = $period['start'];
    $periodEnd          = $period['end'];
    $previousPeriodStart = $period['previous_start']->format('Y-m-d');
    $nextPeriodStart     = $period['next_start']->format('Y-m-d');
    $isCurrentPeriod    = $period['is_current'];
    $periodLabel        = $period['label'];
    $selectedPeriodStart = $periodStart->format('Y-m-d');

    $users = [];
    if (in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) {
        $users = User::orderBy('name')->get();
        $query = Permission::whereBetween('date', [$periodStart, $periodEnd]);
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }
        $permissions = $query->latest()->get();

        $monthlyTotalsQuery = Permission::whereBetween('date', [$periodStart, $periodEnd])
            ->where('status', 'accepted');
        if ($request->has('user_id') && $request->user_id) {
            $monthlyTotalsQuery->where('user_id', $request->user_id);
        }
        $monthlyTotals = $monthlyTotalsQuery
            ->selectRaw('name, COUNT(*) as total')
            ->groupBy('name')
            ->orderByDesc('total')
            ->get();
    } else {
        $permissions = Permission::where('user_id', Auth::id())
            ->whereBetween('date', [$periodStart, $periodEnd])
            ->latest()->get();
        $monthlyTotals = collect();
    }

    return view('view_ezn', compact(
        'permissions', 'users', 'monthlyTotals', 'periodStart', 'periodEnd', 'periodLabel',
        'isCurrentPeriod', 'previousPeriodStart', 'nextPeriodStart', 'selectedPeriodStart'
    ));
}

    public function permission_post(Request $request)
    {
        $request->validate([
            'date'            => 'required|date',
            'day'             => 'required|string',
            'reason'          => 'required|string|max:1000',
            'permission_type' => 'required|string|in:إذن تأخير,إذن انصراف باكر,إذن نسيان بصمة حضور,إذن نسيان بصمة انصراف',
            'from'            => 'nullable|date_format:H:i|required_if:permission_type,إذن تأخير,إذن انصراف باكر',
            'to'              => 'nullable|date_format:H:i|after:from|required_if:permission_type,إذن تأخير,إذن انصراف باكر',
        ]);

        SubmissionWindow::assertDateWithinAllowedWindow($request->date, 'Permission date');

        // Check if the permission exceeds 3 hours limit per month (180 minutes)
        $requestedMinutes = 0;
        if ($request->filled('from') && $request->filled('to')) {
            $fromTime = \Carbon\Carbon::parse($request->from);
            $toTime   = \Carbon\Carbon::parse($request->to);
            $requestedMinutes = $fromTime->diffInMinutes($toTime);
        }

        $period = \App\Support\PayrollPeriod::fromRequest($request->date);
        $permissions = Permission::where('user_id', Auth::id())
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->get();
        
        $totalMinutes = 0;
        foreach ($permissions as $p) {
            if ($p->from && $p->to) {
                $p_from = \Carbon\Carbon::parse($p->from);
                $p_to   = \Carbon\Carbon::parse($p->to);
                $totalMinutes += $p_from->diffInMinutes($p_to);
            }
        }

        $maxMinutes = \App\Support\Settings::get('monthly_permissions_hours', 3) * 60;
        if (($totalMinutes + $requestedMinutes) > $maxMinutes) {
            $maxHours = \App\Support\Settings::get('monthly_permissions_hours', 3);
            return redirect()->back()->with('alert_error', "⚠️ عذراً، لا يمكنك إضافة هذا الإذن لأنك ستتخطى الحد الأقصى المسموح به للأذونات وهو {$maxHours} ساعات شهرياً.")->withInput();
        }

        $name = (Auth::user()->email === 'guest@gamma.com' && $request->has('name')) 
            ? $request->name 
            : Auth::user()->name;

        Permission::create([
            'user_id'         => Auth::id(),
            'name'            => $name,
            'date'            => $request->date,
            'day'             => $request->day,
            'reason'          => $request->reason,
            'from'            => $request->from,
            'to'              => $request->to,
            'permission_type' => $request->permission_type,
        ]);

        AuditLog::log(Auth::user()->name, 'Created', 'Permission', $name, "Type: {$request->permission_type}, Date: {$request->date}");

        return redirect()->route('home')->with('success', 'Permission submitted successfully!');
    }

public function permission_accept($id)
{
    $p = Permission::findOrFail($id);
    $p->update([
        'status'      => 'accepted',
        'actioned_by' => Auth::user()->name,
    ]);

    AuditLog::log(Auth::user()->name, 'Accepted', 'Permission', $p->name, "Type: {$p->permission_type}, Date: {$p->date}");

    if ($p->user) {
        $p->user->sendOneSignalNotification(
            'تم قبول الإذن 🎉',
            "تم قبول طلب الإذن الخاص بك ليوم {$p->date}.",
            Auth::user()->name
        );
    }

    return redirect()->route('view_permission')->with('success', 'Permission accepted.');
}

public function permission_refuse(Request $request, $id)
{
    $request->validate(['refuse_reason' => 'required|string|max:500']);
    $p = Permission::findOrFail($id);
    $p->update([
        'status'        => 'refused',
        'refuse_reason' => $request->refuse_reason,
        'actioned_by'   => Auth::user()->name,
    ]);

    AuditLog::log(Auth::user()->name, 'Refused', 'Permission', $p->name, "Type: {$p->permission_type}, Date: {$p->date}, Reason: {$request->refuse_reason}");

    if ($p->user) {
        $p->user->sendOneSignalNotification(
            'تم رفض الإذن ⚠️',
            "تم رفض طلب {$p->permission_type} الخاص بك ليوم {$p->day}. السبب: {$request->refuse_reason}"
        );
    }

    return redirect()->route('view_permission')->with('success', 'Permission refused.');
}





public function export_lev(Request $request)
{
    if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);
    
    $period = \App\Support\PayrollPeriod::fromRequest($request->query('period_start'));
    $periodStart = $period['start'];
    $periodEnd   = $period['end'];

    $leaves = Leave::whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
        ->orderBy('date')
        ->get();

    $filename = 'leaves_' . $periodStart->format('Y_m_d') . '_to_' . $periodEnd->format('Y_m_d') . '.xlsx';

    return Excel::download(
        new LeavesExport($leaves, $periodStart, $periodEnd),
        $filename
    );
}


public function export_per(Request $request)
{
    if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

    $period = \App\Support\PayrollPeriod::fromRequest($request->query('period_start'));
    $periodStart = $period['start'];
    $periodEnd   = $period['end'];

    $permissions = Permission::whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
        ->orderBy('date')
        ->get();

    $monthlyTotals = Permission::whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
        ->where('status', 'accepted')
        ->selectRaw('name, COUNT(*) as total')
        ->groupBy('name')
        ->get();

    $filename = 'permissions_' . $periodStart->format('Y_m_d') . '_to_' . $periodEnd->format('Y_m_d') . '.xlsx';

    return Excel::download(
        new PermissionsExport($permissions, $monthlyTotals, $periodStart, $periodEnd),
        $filename
    );
}
}