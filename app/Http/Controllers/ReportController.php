<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\Leave;
use App\Models\Permission;
use App\Models\Penalty;
use App\Models\CheckInOut;
use App\Models\AdminNote;
use App\Models\Incentive;
use App\Models\Settlement;
use App\Support\PayrollPeriod;
use App\Exports\FullReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart         = $period['start'];
        $periodEnd           = $period['end'];
        $previousPeriodStart = $period['previous_start']->format('Y-m-d');
        $nextPeriodStart     = $period['next_start']->format('Y-m-d');
        $isCurrentPeriod     = $period['is_current'];
        $periodLabel         = $period['label'];
        $selectedPeriodStart = $periodStart->format('Y-m-d');

        return view('full_report', compact(
            'periodStart', 'periodEnd', 'periodLabel', 'isCurrentPeriod',
            'previousPeriodStart', 'nextPeriodStart', 'selectedPeriodStart'
        ));
    }

    public function export(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];
        $startStr = $periodStart->toDateString();
        $endStr = $periodEnd->toDateString();

        // 1. Overtime
        $overtime = Overtime::with('user')->whereBetween('date', [$startStr, $endStr])->latest()->get();
        $overtimeTotals = Overtime::with('user')->whereBetween('date', [$startStr, $endStr])
            ->where('status', 'accepted')
            ->selectRaw('user_id, name, SUM(total_hours) as total')
            ->groupBy('user_id', 'name')
            ->get();

        // 2. Leaves
        $leaves = Leave::with('user')->whereBetween('date', [$startStr, $endStr])->latest()->get();

        // 3. Permissions
        $permissions = Permission::with('user')->whereBetween('date', [$startStr, $endStr])->latest()->get();
        $permissionTotals = Permission::whereBetween('date', [$startStr, $endStr])
            ->where('status', 'accepted')
            ->selectRaw('name, COUNT(*) as total_count')
            ->groupBy('name')
            ->get();

        // 4. Penalties
        $penalties = Penalty::with('user')->whereBetween('created_at', [$periodStart, $periodEnd])->latest()->get();
        $penaltyTotals = $penalties->groupBy('name')
            ->map(fn($g) => ['name' => $g->first()->name, 'total' => $g->count()]);

        // 5. Check In/Out (Attendance)
        $checkInOut = CheckInOut::with('user')->whereBetween('date', [$startStr, $endStr])
            ->orderByDesc('date')->orderByDesc('created_at')->get();
        
        $checkInOutAccepted = CheckInOut::whereBetween('date', [$startStr, $endStr])
            ->where('status', 'accepted')
            ->get();
        $checkInOutSummary = CheckInOutController::computeCycleSummary($checkInOutAccepted, $periodStart, $periodEnd);

        // 6. Admin Notes
        $adminNotes = AdminNote::whereBetween('date', [$startStr, $endStr])
            ->orderByDesc('date')->orderByDesc('created_at')->get();

        // 7. Incentives
        $incentives = Incentive::with('user')->whereBetween('date', [$startStr, $endStr])
            ->orderByDesc('date')->orderByDesc('created_at')->get();

        // 8. Settlements
        $settlements = Settlement::with('user')->whereBetween('date', [$startStr, $endStr])
            ->orderByDesc('date')->orderByDesc('created_at')->get();

        // $filename = 'Full_HR_Report_' . $periodStart->format('d_M_Y') . '_to_' . $periodEnd->format('d_M_Y') . '.xlsx';
        $filename = 'HR_Report' . $periodEnd->locale('ar')->isoFormat('MMMM YYYY') . '.xlsx';

        return Excel::download(
            new FullReportExport(
                $overtime, $overtimeTotals,
                $leaves,
                $permissions, $permissionTotals,
                $penalties, $penaltyTotals,
                $checkInOut, $checkInOutSummary,
                $adminNotes,
                $incentives,
                $settlements,
                $periodStart, $periodEnd
            ),
            $filename
        );
    }
}
