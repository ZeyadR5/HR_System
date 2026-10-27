<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use App\Models\User;
use App\Models\AuditLog;
use App\Support\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class SettlementController extends Controller
{
    // صفحة إضافة تسوية (للموظف)
    public function create()
    {
        $startDay = \App\Support\Settings::get('settlement_start_day', 1);
        $endDay = \App\Support\Settings::get('settlement_end_day', 10);
        $dayOfMonth = now()->day;
        if (!Auth::user()->can('settlements.bypass_window') && !in_array(Auth::user()->role, ['super_admin', 'developer']) && ($dayOfMonth < $startDay || $dayOfMonth > $endDay)) {
            return redirect()->route('home')->with('alert_error', "⚠️ عذراً، تقديم طلبات التسوية متاح فقط من يوم {$startDay} إلى يوم {$endDay} من كل شهر.");
        }
        return view('settlement');
    }

    // حفظ التسوية
    public function store(Request $request)
    {
        $startDay = \App\Support\Settings::get('settlement_start_day', 1);
        $endDay = \App\Support\Settings::get('settlement_end_day', 10);
        $dayOfMonth = now()->day;
        if (!Auth::user()->can('settlements.bypass_window') && !in_array(Auth::user()->role, ['super_admin', 'developer']) && ($dayOfMonth < $startDay || $dayOfMonth > $endDay)) {
            return redirect()->route('home')->with('alert_error', "⚠️ عذراً، التقديم مغلق حالياً. الفترة المسموح بها هي من {$startDay} إلى {$endDay} في الشهر.");
        }

        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $settlement = Settlement::create([
            'user_id' => Auth::id(),
            'name' => Auth::user()->name,
            'note' => $request->note,
            'date' => now()->toDateString(),
            'day' => now()->locale('ar')->dayName,
        ]);

        \App\Models\User::notifyManagement(
            'طلب تسوية جديد 📝',
            "قام الموظف " . Auth::user()->name . " بتقديم طلب تسوية جديد: " . \Illuminate\Support\Str::limit($request->note, 100),
            Auth::user()->name,
            Auth::id()
        );

        return redirect()->route('home')->with('success', 'تم إرسال التسوية بنجاح!');
    }

    // صفحة عرض التسويات
    public function index(Request $request)
    {
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = Settlement::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $users = [];

        if (Auth::user()->can('settlements.view_all') || in_array(Auth::user()->role, ['super_admin', 'developer'])) {
            $users = User::orderBy('name')->get();
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', Auth::id());
        }

        $settlements = $query->get();

        $olderPendingCount = (Auth::user()->can('settlements.approve') || in_array(Auth::user()->role, ['super_admin', 'developer']))
            ? Settlement::where('status', 'pending')
                ->whereDate('date', '<', $periodStart->toDateString())
                ->count()
            : 0;

        return view('view_settlements', [
            'settlements' => $settlements,
            'users' => $users,
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

    // قبول التسوية
    public function accept(Request $request, $id)
    {
        if (!Auth::user()->can('settlements.approve') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $request->validate(['accept_note' => 'nullable|string|max:500']);

        $record = Settlement::findOrFail($id);
        $record->update([
            'status' => 'accepted',
            'accept_note' => $request->accept_note,
            'actioned_by' => Auth::user()->name,
        ]);

        AuditLog::log(Auth::user()->name, 'Accepted', 'Settlement', $record->name, "Note: {$record->note}");

        return redirect()->back()->with('success', 'تم قبول التسوية.');
    }

    // رفض التسوية
    public function refuse(Request $request, $id)
    {
        if (!Auth::user()->can('settlements.approve') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $request->validate(['refuse_reason' => 'required|string|max:500']);

        $record = Settlement::findOrFail($id);
        $record->update([
            'status' => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by' => Auth::user()->name,
        ]);

        AuditLog::log(Auth::user()->name, 'Refused', 'Settlement', $record->name, "Reason: {$request->refuse_reason}");

        return redirect()->back()->with('success', 'تم رفض التسوية.');
    }

    // تصدير Excel
    public function export(Request $request)
    {
        if (!Auth::user()->can('settlements.export') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $settlements = Settlement::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $filename = 'settlements_' . $periodStart->format('Y_m_d') . '_to_' . $periodEnd->format('Y_m_d') . '.xlsx';

        return Excel::download(
            new \App\Exports\SettlementExport($settlements, $periodStart, $periodEnd),
            $filename
        );
    }
}
