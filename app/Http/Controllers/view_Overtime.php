<?php

namespace App\Http\Controllers;

use App\Models\Overtime;
use App\Models\User;
use App\Support\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class view_Overtime extends Controller
{
    public function view_Overtime(Request $request)
    {
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $overtimeQuery = Overtime::with('user')
            ->whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $users = [];
        if (in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) {
            $users = User::orderBy('name')->get();
            if ($request->has('user_id') && $request->user_id) {
                $overtimeQuery->where('user_id', $request->user_id);
            }
        } else {
            $overtimeQuery->where('user_id', Auth::id());
        }

        $Overtime = $overtimeQuery->get();

        $monthlyTotalsQuery = Overtime::with('user')
            ->whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->where('status', 'accepted');

        if (in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) {
            if ($request->has('user_id') && $request->user_id) {
                $monthlyTotalsQuery->where('user_id', $request->user_id);
            }
        } else {
            $monthlyTotalsQuery->where('user_id', Auth::id());
        }

        $monthlyTotals = $monthlyTotalsQuery
            ->selectRaw('user_id, name, SUM(total_hours) as total')
            ->groupBy('user_id', 'name')
            ->orderByDesc('total')
            ->get();

        $olderPendingCount = in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])
            ? Overtime::where('status', 'pending')
                ->whereDate('date', '<', $periodStart->toDateString())
                ->count()
            : 0;

        return view('view_Overtime', [
            'Overtime' => $Overtime,
            'users' => $users,
            'monthlyTotals' => $monthlyTotals,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'periodLabel' => $period['label'],
            'selectedPeriodStart' => $period['slug'],
            'previousPeriodStart' => $period['previous_start']->format('Y-m-d'),
            'nextPeriodStart' => $period['next_start']->format('Y-m-d'),
            'isCurrentPeriod' => $period['is_current'],
            'olderPendingCount' => $olderPendingCount,
        ]);
    }

    public function refuse(Request $request, $id)
    {
        $request->validate([
            'refuse_reason' => 'required|string|min:5|max:500',
        ]);

        Overtime::findOrFail($id)->update([
            'status' => 'refused',
            'refuse_reason' => $request->refuse_reason,
        ]);

        return redirect()->route('view_Overtime')->with('success', 'Overtime refused.');
    }
}
