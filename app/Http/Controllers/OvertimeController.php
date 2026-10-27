<?php

namespace App\Http\Controllers;

use App\Exports\OvertimeExport;
use App\Models\Overtime;
use App\Support\PayrollPeriod;
use App\Support\SubmissionWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AuditLog;

class OvertimeController extends Controller
{
    public function overtime()
    {
        $userId = Auth::id();
        $period = PayrollPeriod::current();
        
        // Sum of hours for today (accepted + pending)
        $dayUsage = Overtime::where('user_id', $userId)
            ->where('date', \Carbon\Carbon::today()->toDateString())
            ->where('status', '!=', 'refused')
            ->sum('total_hours');

        $user = Auth::user();

        return view('overtime', [
            'dateBounds' => SubmissionWindow::bounds(),
            'otLimit'    => \App\Support\Settings::get('daily_overtime_limit', 5),
            'otUsage'    => round($dayUsage, 2),
            'shiftGroup' => $user?->shiftGroup,
        ]);
    }

    public function overtime_post(Request $request)
    {
        $request->validate([
            'date'   => 'required|date',
            'day'    => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
            'from'   => 'required|date_format:H:i',
            'to'     => 'required|date_format:H:i|after:from',
        ]);

        $user = Auth::user();

        // منع تسجيل إضافي داخل ساعات الشيفت الأساسي للموظف
        if ($user && $user->shiftGroup) {
            $shift = $user->shiftGroup;
            if ($shift->overlapsWith($request->from, $request->to)) {
                return redirect()->back()
                    ->with('alert_error', "🛑 عذراً، لا يمكنك تسجيل وقت إضافي (من {$request->from} إلى {$request->to}) لأنه يقع داخل ساعات شيفتك المسجل في «{$shift->name}» ({$shift->formatted_hours}). الإضافي يُحتسب فقط قبل بداية الشيفت أو بعد نهايته.")
                    ->withInput();
            }
        }

        $from = \Carbon\Carbon::createFromFormat('H:i', $request->from);
        $to = \Carbon\Carbon::createFromFormat('H:i', $request->to);
        $total_hours = round($from->diffInMinutes($to) / 60, 2);

        $userId = Auth::id();
        $period = PayrollPeriod::fromRequest($request->date);

        // Check Daily Overtime Limit (Max 5 hours)
        $dayUsage = Overtime::where('user_id', $userId)
            ->where('date', $request->date)
            ->where('status', '!=', 'refused')
            ->sum('total_hours');

        $dailyLimit = \App\Support\Settings::get('daily_overtime_limit', 5);
        if (($dayUsage + $total_hours) > $dailyLimit) {
            $remaining = $dailyLimit - $dayUsage;
            if ($remaining < 0) $remaining = 0;
            return redirect()->back()->with('alert_error', "⚠️ عذراً، لقد تخطيت الحد المسموح به للإضافي لهذا اليوم ({$dailyLimit} ساعات). المتبقي لك هو {$remaining} ساعة فقط.")->withInput();
        }

        // Prevent submitting overtime before it ends
        $overtimeEndTime = \Carbon\Carbon::parse($request->date . ' ' . $request->to);
        if ($overtimeEndTime->isFuture()) {
            return redirect()->back()->with('alert_error', '⚠️ لا يمكنك تقديم طلب العمل الإضافي قبل أن ينتهي وقته الفعلي.')->withInput();
        }

        SubmissionWindow::assertDateWithinAllowedWindow($request->date, 'Overtime date');

        $from = \Carbon\Carbon::createFromFormat('H:i', $request->from);
        $to = \Carbon\Carbon::createFromFormat('H:i', $request->to);
        $total_hours = round($from->diffInMinutes($to) / 60, 2);

        $name = (Auth::user()->email === 'guest@gamma.com' && $request->has('name')) 
            ? $request->name 
            : Auth::user()->name;

        $over = Overtime::create([
            'user_id' => Auth::id(),
            'name' => $name,
            'date' => $request->date,
            'day' => $request->day,
            'total_hours' => $total_hours,
            'reason' => $request->reason,
            'from' => $request->from,
            'to' => $request->to,
        ]);

        if ($over) {
            AuditLog::log(Auth::user()->name, 'Created', 'Overtime', $name, "Hours: {$total_hours}, Date: {$request->date}");
            
            \App\Models\User::notifyManagement(
                'طلب إضافي جديد ⏱️',
                "قام الموظف {$name} بتقديم طلب عمل إضافي ({$total_hours} ساعة) ليوم {$request->date}.",
                $name,
                Auth::id()
            );

            return redirect()->route('home')->with('success', 'Overtime submitted successfully!');
        }

        return redirect()->route('Overtime')->withErrors(['error' => 'Overtime submission failed']);
    }

public function accept($id)
{
    $ov = Overtime::findOrFail($id);
    $ov->update([
        'status'      => 'accepted',
        'actioned_by' => Auth::user()->name,
    ]);

    AuditLog::log(Auth::user()->name, 'Accepted', 'Overtime', $ov->name, "Hours: {$ov->total_hours}, Date: {$ov->date}");

    if ($ov->user) {
        $ov->user->sendOneSignalNotification(
            'تم قبول الإضافي 🎉',
            "تم قبول طلب الإضافي الخاص بك ليوم {$ov->day} ({$ov->total_hours} ساعات).",
            Auth::user()->name
        );
    }

    return redirect()->route('view_Overtime')->with('success', 'Overtime accepted.');
}

public function refuse(Request $request, $id)
{
    $request->validate(['refuse_reason' => 'required|string|max:500']);
    $ov = Overtime::findOrFail($id);
    $ov->update([
        'status'        => 'refused',
        'refuse_reason' => $request->refuse_reason,
        'actioned_by'   => Auth::user()->name,
    ]);

    AuditLog::log(Auth::user()->name, 'Refused', 'Overtime', $ov->name, "Reason: {$request->refuse_reason}, Date: {$ov->date}");

    if ($ov->user) {
        $ov->user->sendOneSignalNotification(
            'تم رفض الإضافي ⚠️',
            "تم رفض طلب الإضافي الخاص بك ليوم {$ov->day}. السبب: {$request->refuse_reason}",
            Auth::user()->name
        );
    }

    return redirect()->route('view_Overtime')->with('success', 'Overtime refused.');
}

    public function export(Request $request)
    {
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $overtimeQuery = Overtime::with('user')
            ->whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ]);

        $monthlyTotalsQuery = Overtime::with('user')
            ->whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->where('status', 'accepted');

        if (!Auth::user()->can('overtime.export') && !Auth::user()->can('overtime.view_all')) {
            $overtimeQuery->where('user_id', Auth::id());
            $monthlyTotalsQuery->where('user_id', Auth::id());
        }

        $overtime = $overtimeQuery
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $monthlyTotals = $monthlyTotalsQuery
            ->selectRaw('user_id, name, SUM(total_hours) as total')
            ->groupBy('user_id', 'name')
            ->get();

        $filename = 'overtime_' . $periodStart->format('Y_m_d') . '_to_' . $periodEnd->format('Y_m_d') . '.xlsx';

        return Excel::download(
            new OvertimeExport($overtime, $monthlyTotals, $periodStart, $periodEnd),
            $filename
        );
    }
}
