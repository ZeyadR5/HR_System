<?php

namespace App\Http\Controllers;

use App\Models\CheckInOut;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckInOutController extends Controller
{
    public function index_front()
    {
        $todayRecords = CheckInOut::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
            ->get();

        $hasCheckedIn = $todayRecords->where('type', 'حضور')->isNotEmpty();
        $hasCheckedOut = $todayRecords->where('type', 'انصراف')->isNotEmpty();

        return view('check_in_out', compact('hasCheckedIn', 'hasCheckedOut'));
    }

    public function store(Request $request)
    {
        if (!(in_array(Auth::user()->role, ['super_admin', 'developer']) || 
              Auth::user()->email === 'hend@gama.com' || 
              Auth::user()->email === 'RawanEssam@gamma.com' || 
              Auth::user()->email === 'esraa.abdulla30@gmail.com')) {
            return redirect()->route('home')->withErrors(['error' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.']);
        }

        $request->validate([
            'type' => 'required|in:حضور,انصراف',
        ]);

        $name = Auth::user()->name;

        // Check if movement already exists for today
        $existing = CheckInOut::where('user_id', Auth::id())
            ->where('date', now()->toDateString())
            ->where('type', $request->type)
            ->first();

        if ($existing) {
            return redirect()->back()->withErrors(['error' => 'لقد قمت بتسجيل ' . $request->type . ' بالفعل اليوم.']);
        }
        
        $attendance = CheckInOut::create([
            'user_id' => Auth::id(),
            'name' => $name,
            'date' => now()->toDateString(),
            'day' => now()->locale('ar')->dayName,
            'type' => $request->type,
            'status' => 'accepted',
            'actioned_by' => 'نظام تلقائي',
        ]);

        if ($attendance) {
            AuditLog::log(Auth::user()->name, 'Created', 'CheckInOut', $name, "Type: {$request->type}, Date: " . now()->toDateString());
            return redirect()->route('home')->with('success', 'تم تسجيل ال' . $request->type . ' بنجاح!');
        }

        return redirect()->back()->withErrors(['error' => 'حدث خطأ أثناء التسجيل']);
    }

    public function index_view(Request $request)
    {
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = CheckInOut::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        $users = [];

        if (in_array(Auth::user()->role, ['super_admin', 'developer']) || Auth::user()->role === 'admin') {
            $users = User::orderBy('name')->get();
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', Auth::id());
        }

        $records = $query->get();

        // Summary per employee this cycle
        $summaryQuery = CheckInOut::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->where('status', 'accepted');

        if (in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) {
            if ($request->has('user_id') && $request->user_id) {
                $summaryQuery->where('user_id', $request->user_id);
            }
        } else {
            $summaryQuery->where('user_id', Auth::id());
        }

        $cycleSummary = self::computeCycleSummary($summaryQuery->get(), $periodStart, $periodEnd);

        $olderPendingCount = in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])
            ? CheckInOut::where('status', 'pending')
                ->whereDate('date', '<', $periodStart->toDateString())
                ->count()
            : 0;

        return view('view_check_in_out', [
            'records' => $records,
            'users' => $users,
            'cycleSummary' => $cycleSummary,
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

    public function accept($id)
    {
        if (!(in_array(Auth::user()->role, ['super_admin', 'developer']) || 
              Auth::user()->email === 'hend@gama.com' || 
              Auth::user()->email === 'RawanEssam@gamma.com' || 
              Auth::user()->email === 'esraa.abdulla30@gmail.com')) {
            return redirect()->route('home')->withErrors(['error' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.']);
        }

        $record = CheckInOut::findOrFail($id);
        $record->update([
            'status'      => 'accepted',
            'actioned_by' => Auth::user()->name,
        ]);

        AuditLog::log(Auth::user()->name, 'Accepted', 'CheckInOut', $record->name, "Type: {$record->type}, Date: {$record->date}");

        return redirect()->route('view_check_in_out')->with('success', 'تم قبول الحركة.');
    }

    public function refuse(Request $request, $id)
    {
        if (!(in_array(Auth::user()->role, ['super_admin', 'developer']) || 
              Auth::user()->email === 'hend@gama.com' || 
              Auth::user()->email === 'RawanEssam@gamma.com' || 
              Auth::user()->email === 'esraa.abdulla30@gmail.com')) {
            return redirect()->route('home')->withErrors(['error' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.']);
        }

        $request->validate(['refuse_reason' => 'required|string|max:500']);
        
        $record = CheckInOut::findOrFail($id);
        $record->update([
            'status'        => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by'   => Auth::user()->name,
        ]);

        AuditLog::log(Auth::user()->name, 'Refused', 'CheckInOut', $record->name, "Reason: {$request->refuse_reason}, Date: {$record->date}");

        return redirect()->route('view_check_in_out')->with('success', 'تم رفض الحركة.');
    }

    public static function computeCycleSummary($records, $periodStart = null, $periodEnd = null)
    {
        if (!$periodStart && $records->isNotEmpty()) {
            $periodStart = $records->min('date');
        }
        if (!$periodEnd && $records->isNotEmpty()) {
            $periodEnd = $records->max('date');
        }

        $acceptedLeavesMap = collect();
        if ($periodStart && $periodEnd) {
            $startStr = \Carbon\Carbon::parse($periodStart)->toDateString();
            $endStr   = \Carbon\Carbon::parse($periodEnd)->toDateString();

            $acceptedLeavesMap = \App\Models\Leave::whereBetween('date', [$startStr, $endStr])
                ->where('status', 'accepted')
                ->selectRaw('name, user_id, SUM(days_count) as total_leave_days')
                ->groupBy('user_id', 'name')
                ->get()
                ->keyBy(function($item) {
                    return $item->user_id ? 'uid_' . $item->user_id : 'name_' . mb_strtolower(trim($item->name));
                });
        }

        return $records->where('status', 'accepted')->groupBy('name')->map(function($userRecords, $name) use ($acceptedLeavesMap) {
            $userId = $userRecords->first()->user_id ?? null;
            $checkInCount = $userRecords->where('type', 'حضور')->count();
            $checkOutCount = $userRecords->where('type', 'انصراف')->count();
            $totalRecords = $userRecords->count();
            
            $byDate = $userRecords->groupBy('date');
            
            $totalShifts = $byDate->count();
            $regularShifts = 0;
            $fridayShifts = 0;
            $fridayActualHours = 0;
            $fridayBonusHours = 0;
            
            foreach ($byDate as $dateStr => $dayRecords) {
                $dateObj = \Carbon\Carbon::parse($dateStr);
                $dayName = $dayRecords->first()->day ?? '';
                $isFriday = ($dateObj->dayOfWeek === \Carbon\Carbon::FRIDAY) || (mb_strpos($dayName, 'جمعة') !== false);
                
                if ($isFriday) {
                    $fridayShifts++;
                    
                    $checkInRecord = $dayRecords->where('type', 'حضور')->sortBy('created_at')->first();
                    $checkOutRecord = $dayRecords->where('type', 'انصراف')->sortByDesc('created_at')->first();
                    
                    if ($checkInRecord && $checkOutRecord) {
                        $inTime = \Carbon\Carbon::parse($checkInRecord->created_at);
                        $outTime = \Carbon\Carbon::parse($checkOutRecord->created_at);
                        if ($outTime->gt($inTime)) {
                            $hours = abs($inTime->diffInMinutes($outTime)) / 60;
                            $fridayActualHours += $hours;
                            $fridayBonusHours += ($hours * 1.5);
                        }
                    }
                } else {
                    $regularShifts++;
                }
            }

            $leaveDays = 0;
            if ($userId && $acceptedLeavesMap->has('uid_' . $userId)) {
                $leaveDays = (float) $acceptedLeavesMap->get('uid_' . $userId)->total_leave_days;
            } elseif ($acceptedLeavesMap->has('name_' . mb_strtolower(trim($name)))) {
                $leaveDays = (float) $acceptedLeavesMap->get('name_' . mb_strtolower(trim($name)))->total_leave_days;
            }

            $totalShiftsAndLeaves = $regularShifts + $fridayShifts + $leaveDays;
            
            return (object) [
                'name'                    => $name,
                'user_id'                 => $userId,
                'check_in_count'          => $checkInCount,
                'check_out_count'         => $checkOutCount,
                'shifts_count'            => $totalShifts,
                'regular_shifts'          => $regularShifts,
                'friday_shifts'           => $fridayShifts,
                'accepted_leaves'         => $leaveDays,
                'total_shifts_and_leaves' => $totalShiftsAndLeaves,
                'friday_actual_hours'     => round($fridayActualHours, 2),
                'friday_bonus_hours'      => round($fridayBonusHours, 2),
                'total'                   => $totalRecords,
            ];
        })->sortByDesc('total_shifts_and_leaves')->values();
    }

    public function export(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $records = CheckInOut::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $acceptedRecords = CheckInOut::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->where('status', 'accepted')
            ->get();

        $summary = self::computeCycleSummary($acceptedRecords, $periodStart, $periodEnd);

        $filename = 'check_in_out_' . $periodStart->format('Y_m_d') . '_to_' . $periodEnd->format('Y_m_d') . '.xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\CheckInOutExport($records, $summary, $periodStart, $periodEnd),
            $filename
        );
    }
}
