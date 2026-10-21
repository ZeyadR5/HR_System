<?php
namespace App\Http\Controllers;

use App\Models\Penalty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;
use App\Exports\PenaltyExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\PayrollPeriod;

class PenaltyController extends Controller
{
    // صفحة إضافة جزاء - ادمن + سوبر ادمن
    public function index()
    {
        if (!Auth::user()->can('penalties.create') && !in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) abort(403);
        $users = User::orderBy('name')->get();
        return view('admin_penalty', compact('users'));
    }

    // حفظ الجزاء - ادمن + سوبر ادمن
    public function store(Request $request)
    {
        if (!Auth::user()->can('penalties.create') && !in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) abort(403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'reason'  => 'required|string|max:500',
            'amount'  => 'required|string|min:0',
            'notes'   => 'nullable|string|max:1000',
        ]);

        $user = User::findOrFail($request->user_id);

        Penalty::create([
            'user_id' => $user->id,
            'name'    => $user->name,
            'reason'  => $request->reason,
            'amount'  => $request->amount,
            'notes'   => $request->notes,
            'status'  => 'accepted',
            'actioned_by'  => Auth::user()->name,
        ]);

        AuditLog::log(Auth::user()->name, 'Created', 'Penalty', $user->name, "Amount: {$request->amount}, Reason: {$request->reason}");

        $user->sendOneSignalNotification(
            'إشعار بخصم / جزاء ⚠️',
            "تم إضافة جزاء بقيمة {$request->amount}. السبب: {$request->reason}",
            Auth::user()->name
        );

        return redirect()->back()->with('success', 'Penalty added successfully!');
    }

    // قبول الجزاء - سوبر ادمن فقط
    public function accept($id)
    {
        if (!Auth::user()->can('penalties.approve') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        Penalty::findOrFail($id)->update([
            'status'      => 'accepted',
            'actioned_by' => Auth::user()->name,
        ]);

        $p = Penalty::find($id);
        AuditLog::log(Auth::user()->name, 'Accepted', 'Penalty', $p->name, "Amount: {$p->amount}");

        return redirect()->route('view_penalties')->with('success', 'Penalty accepted.');
    }

    // رفض الجزاء - سوبر ادمن فقط
    public function refuse(Request $request, $id)
    {
        if (!Auth::user()->can('penalties.approve') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $request->validate(['refuse_reason' => 'required|string|max:500']);

        Penalty::findOrFail($id)->update([
            'status'        => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by'   => Auth::user()->name,
        ]);

        $p = Penalty::find($id);
        AuditLog::log(Auth::user()->name, 'Refused', 'Penalty', $p->name, "Amount: {$p->amount}, Reason: {$request->refuse_reason}");

        return redirect()->route('view_penalties')->with('success', 'Penalty refused.');
    }

    // صفحة عرض الجزاءات - للموظف والأدمن والسوبر ادمن
    public function view_penalties(Request $request)
    {
        $period = \App\Support\PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart         = $period['start'];
        $periodEnd           = $period['end'];
        $previousPeriodStart = $period['previous_start']->format('Y-m-d');
        $nextPeriodStart     = $period['next_start']->format('Y-m-d');
        $isCurrentPeriod     = $period['is_current'];
        $periodLabel         = $period['label'];
        $selectedPeriodStart = $periodStart->format('Y-m-d');

        $users = [];
        if (Auth::user()->can('penalties.view_all') || in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) {
            $users = User::orderBy('name')->get();
            $query = Penalty::whereBetween('created_at', [$periodStart, $periodEnd]);
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            $penalties = $query->latest()->get();
        } else {
            $penalties = Penalty::where('user_id', Auth::id())
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->latest()->get();
        }

        return view('view_penalties', compact(
            'penalties', 'users', 'periodStart', 'periodEnd', 'periodLabel',
            'isCurrentPeriod', 'previousPeriodStart', 'nextPeriodStart', 'selectedPeriodStart'
        ));
    }

    public function export(Request $request)
    {
        if (!Auth::user()->can('penalties.export') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $penalties = Penalty::whereBetween('created_at', [$periodStart, $periodEnd])
            ->latest()
            ->get();

        $employeeTotals = $penalties->groupBy('name')
            ->map(fn($g) => ['name' => $g->first()->name, 'total' => $g->count()]);

        $filename = 'penalties_' . $periodStart->format('Y_m_d') . '_to_' . $periodEnd->format('Y_m_d') . '.xlsx';

        return Excel::download(
            new PenaltyExport($penalties, $employeeTotals, $periodStart, $periodEnd),
            $filename
        );
    }
}