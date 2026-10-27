<?php

namespace App\Http\Controllers;

use App\Models\CheckInOut;
use App\Support\Geofence;
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

        $geofence = [
            'enabled'        => Geofence::enabled(),
            'radius'         => Geofence::radius(),
            'require_selfie' => Geofence::requireSelfie(),
        ];

        return view('check_in_out', compact('hasCheckedIn', 'hasCheckedOut', 'geofence'));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->can('checkinout.self_register') && 
            !in_array(Auth::user()->role, ['super_admin', 'developer']) && 
            !in_array(Auth::user()->email, ['hend@gama.com', 'RawanEssam@gamma.com', 'esraa.abdulla30@gmail.com'])) {
            return redirect()->route('home')->withErrors(['error' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.']);
        }

        $request->validate([
            'type'      => 'required|in:حضور,انصراف',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'selfie'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'source'    => 'nullable|in:web,app',
        ]);

        // --- التحقق من الموقع الجغرافي (Geofencing) ---
        $distance = null;
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $distance = Geofence::distanceFromOffice((float) $request->latitude, (float) $request->longitude);
        }

        if (Geofence::enabled()) {
            if ($distance === null) {
                return redirect()->back()->withErrors(['error' => 'لا يمكن تسجيل الحضور بدون تحديد موقعك الجغرافي. فعّل الـ GPS وحاول مرة أخرى.']);
            }
            if ($distance > Geofence::radius()) {
                return redirect()->back()->withErrors(['error' => 'أنت خارج نطاق الشركة بمسافة ' . $distance . ' متر (الحد المسموح ' . Geofence::radius() . ' متر).']);
            }
        }

        // --- الصورة الشخصية كإثبات (إلزامية عند التفعيل في حالة الحضور) ---
        if (Geofence::requireSelfie() && $request->type === 'حضور' && !$request->hasFile('selfie')) {
            return redirect()->back()->withErrors(['error' => 'يجب التقاط صورة سيلفي لإثبات الحضور.']);
        }

        $selfiePath = $request->hasFile('selfie')
            ? $request->file('selfie')->store('attendance_selfies/' . now()->format('Y-m'), 'public')
            : null;

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
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'distance_m'  => $distance,
            'selfie_path' => $selfiePath,
            'source'      => $request->input('source', 'web'),
        ]);

        if ($attendance) {
            AuditLog::log(Auth::user()->name, 'Created', 'CheckInOut', $name, "Type: {$request->type}, Date: " . now()->toDateString());

            \App\Models\User::notifyManagement(
                "تسجيل {$request->type} جديد 📍",
                "قام الموظف {$name} بتسجيل {$request->type} الساعة " . now()->format('h:i A') . ".",
                $name,
                Auth::id()
            );

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

        if (Auth::user()->can('checkinout.view_all') || in_array(Auth::user()->role, ['super_admin', 'developer']) || Auth::user()->role === 'admin') {
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

        if (Auth::user()->can('checkinout.view_all') || in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) {
            if ($request->has('user_id') && $request->user_id) {
                $summaryQuery->where('user_id', $request->user_id);
            }
        } else {
            $summaryQuery->where('user_id', Auth::id());
        }

        $cycleSummary = self::computeCycleSummary($summaryQuery->get(), $periodStart, $periodEnd);

        $olderPendingCount = (Auth::user()->can('checkinout.approve') || in_array(Auth::user()->role, ['admin', 'super_admin', 'developer']))
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
        if (!Auth::user()->can('checkinout.approve') && 
            !in_array(Auth::user()->role, ['super_admin', 'developer']) && 
            !in_array(Auth::user()->email, ['hend@gama.com', 'RawanEssam@gamma.com', 'esraa.abdulla30@gmail.com'])) {
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
        if (!Auth::user()->can('checkinout.approve') && 
            !in_array(Auth::user()->role, ['super_admin', 'developer']) && 
            !in_array(Auth::user()->email, ['hend@gama.com', 'RawanEssam@gamma.com', 'esraa.abdulla30@gmail.com'])) {
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
        if (!Auth::user()->can('checkinout.export') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

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
