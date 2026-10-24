<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Permission;
use App\Models\User;
use App\Support\PayrollPeriod;
use App\Support\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdminEntryController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('super_admin_entry', compact('users'));
    }

    /**
     * AJAX endpoint للتحقق الفوري من حدود ورصيد الموظف (إجازات، أذونات، إضافي)
     */
    public function checkLimits(Request $request)
    {
        $employeeName = trim($request->input('employee_name', ''));
        $type = $request->input('entry_type', '');
        $date = $request->input('date', now()->toDateString());

        $user = !empty($employeeName) ? User::where('name', $employeeName)->first() : null;

        $params = [
            'days_count'      => $request->input('days_count', 1),
            'from'            => $request->input('from'),
            'to'              => $request->input('to'),
            'permission_type' => $request->input('permission_type'),
        ];

        $result = $this->evaluateLimits($user, $type, $date, $params);

        return response()->json($result);
    }

    /**
     * تقييم حدود الرصيد لموظف معين والتحقق من كسر الحدود.
     */
    public function evaluateLimits(?User $user, string $type, string $dateStr, array $params = []): array
    {
        if (!$user) {
            return [
                'has_user'     => false,
                'is_breached'  => false,
                'warnings'     => [],
                'stats'        => null,
                'info_message' => 'الموظف غير مسجل بالنظام (إدخال يدوي حر بدون رصيد آلي).',
            ];
        }

        try {
            $date = Carbon::parse($dateStr);
        } catch (\Exception $e) {
            $date = now();
        }

        $period = PayrollPeriod::fromRequest($date->toDateString());
        $warnings = [];
        $isBreached = false;
        $stats = [];

        if ($type === 'leave') {
            $daysToAdd = max(1, (int) ($params['days_count'] ?? 1));

            $monthUsed = (int) Leave::where('user_id', $user->id)
                ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
                ->where('status', '!=', 'refused')
                ->sum('days_count');

            $yearUsed = (int) Leave::where('user_id', $user->id)
                ->whereYear('date', $date->year)
                ->where('status', '!=', 'refused')
                ->sum('days_count');

            $monthLimit = (int) Settings::get('monthly_leaves_limit', 2);
            $yearLimit = (int) Settings::get('annual_leaves_limit', 14);

            $stats = [
                'month_used'      => $monthUsed,
                'month_limit'     => $monthLimit,
                'month_remaining' => max(0, $monthLimit - $monthUsed),
                'year_used'       => $yearUsed,
                'year_limit'      => $yearLimit,
                'year_remaining'  => max(0, $yearLimit - $yearUsed),
                'period_label'    => $period['label'],
            ];

            if ($monthUsed >= $monthLimit) {
                $isBreached = true;
                $warnings[] = "الموظف استنفد رصيد إجازاته الشهري بالكامل ({$monthUsed} من أصل {$monthLimit} يوم) لدورة القبض ({$period['label']}).";
            } elseif (($monthUsed + $daysToAdd) > $monthLimit) {
                $isBreached = true;
                $warnings[] = "إضافة هذه الإجازة ({$daysToAdd} يوم) ستتجاوز الحد الشهري المسموح به ({$monthLimit} أيام). المستهلك حالياً: {$monthUsed} يوم.";
            }

            if ($yearUsed >= $yearLimit) {
                $isBreached = true;
                $warnings[] = "الموظف استنفد رصيد إجازاته السنوي بالكامل ({$yearUsed} من أصل {$yearLimit} يوم) لعام {$date->year}.";
            } elseif (($yearUsed + $daysToAdd) > $yearLimit) {
                $isBreached = true;
                $warnings[] = "إضافة هذه الإجازة ({$daysToAdd} يوم) ستتجاوز رصيد الإجازات السنوي ({$yearLimit} يوم). المستهلك حالياً: {$yearUsed} يوم.";
            }
        } elseif ($type === 'permission') {
            $from = $params['from'] ?? null;
            $to   = $params['to'] ?? null;

            $requestedMinutes = 0;
            if ($from && $to) {
                try {
                    $requestedMinutes = Carbon::parse($from)->diffInMinutes(Carbon::parse($to));
                } catch (\Exception $e) {
                    $requestedMinutes = 0;
                }
            }

            $permissions = Permission::where('user_id', $user->id)
                ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
                ->where('status', '!=', 'refused')
                ->get();

            $totalMinutes = 0;
            foreach ($permissions as $p) {
                if ($p->from && $p->to) {
                    try {
                        $totalMinutes += Carbon::parse($p->from)->diffInMinutes(Carbon::parse($p->to));
                    } catch (\Exception $e) {
                        // ignore malformed times
                    }
                }
            }

            $limitHours = (float) Settings::get('monthly_permissions_hours', 3);
            $limitMinutes = (int) ($limitHours * 60);

            $stats = [
                'minutes_used'      => $totalMinutes,
                'minutes_limit'     => $limitMinutes,
                'minutes_remaining' => max(0, $limitMinutes - $totalMinutes),
                'hours_used'        => round($totalMinutes / 60, 2),
                'hours_limit'       => $limitHours,
                'period_label'      => $period['label'],
            ];

            if ($totalMinutes >= $limitMinutes) {
                $isBreached = true;
                $usedHours = round($totalMinutes / 60, 1);
                $warnings[] = "الموظف استنفد رصيد أذوناته الشهري بالكامل ({$usedHours} ساعة من أصل {$limitHours} ساعات) لدورة ({$period['label']}).";
            } elseif (($totalMinutes + $requestedMinutes) > $limitMinutes) {
                $isBreached = true;
                $usedHours = round($totalMinutes / 60, 1);
                $remMinutes = max(0, $limitMinutes - $totalMinutes);
                $warnings[] = "إضافة هذا الإذن ستتجاوز الحد الأقصى للأذونات شهرياً ({$limitHours} ساعات). المستهلك حالياً: {$usedHours} ساعة، والمتبقي: {$remMinutes} دقيقة.";
            }
        } elseif ($type === 'overtime') {
            $from = $params['from'] ?? null;
            $to   = $params['to'] ?? null;

            $requestedHours = 0;
            if ($from && $to) {
                try {
                    $f = Carbon::parse($from);
                    $t = Carbon::parse($to);
                    $mins = $f->diffInMinutes($t);
                    if ($mins < 0) {
                        $mins += 24 * 60;
                    }
                    $requestedHours = round($mins / 60, 2);
                } catch (\Exception $e) {
                    $requestedHours = 0;
                }
            }

            $dayUsage = (float) Overtime::where('user_id', $user->id)
                ->where('date', $date->toDateString())
                ->where('status', '!=', 'refused')
                ->sum('total_hours');

            $dailyLimit = (float) Settings::get('daily_overtime_limit', 5);

            $stats = [
                'day_used'      => $dayUsage,
                'day_limit'     => $dailyLimit,
                'day_remaining' => max(0, round($dailyLimit - $dayUsage, 2)),
            ];

            if ($dayUsage >= $dailyLimit) {
                $isBreached = true;
                $warnings[] = "الموظف استنفد الحد الأقصى للإضافي اليومي ({$dayUsage} من أصل {$dailyLimit} ساعات) لتاريخ {$date->toDateString()}.";
            } elseif (($dayUsage + $requestedHours) > $dailyLimit) {
                $isBreached = true;
                $warnings[] = "إضافة هذا الوقت ({$requestedHours} ساعة) ستتجاوز الحد الأقصى اليومي للإضافي ({$dailyLimit} ساعات). المسجل حالياً: {$dayUsage} ساعة.";
            }

            if ($user->shiftGroup && $from && $to && $user->shiftGroup->overlapsWith($from, $to)) {
                $isBreached = true;
                $warnings[] = "الوقت المضاف يتداخل مع ساعات الشيفت الأساسي للموظف في «{$user->shiftGroup->name}» ({$user->shiftGroup->formatted_hours}).";
            }
        }

        return [
            'has_user'     => true,
            'user_name'    => $user->name,
            'job_title'    => $user->job_title,
            'type'         => $type,
            'is_breached'  => $isBreached,
            'warnings'     => $warnings,
            'stats'        => $stats,
        ];
    }

    public function store(Request $request)
    {
        $request->validate([
            'entry_type'    => 'required|in:overtime,leave,permission',
            'employee_name' => 'required|string|max:255',
            'date'          => 'required|date',
            'day'           => 'required|string|max:50',
            'reason'        => 'required|string|max:1000',
        ]);

        $user = User::where('name', $request->employee_name)->first();
        $userId = $user?->id;
        $type = $request->entry_type;

        // تجميع المعاملات لفحص الحدود
        $evalParams = [
            'days_count'      => $request->input('days_count', 1),
            'from'            => $type === 'permission' ? $request->input('perm_from') : $request->input('from'),
            'to'              => $type === 'permission' ? $request->input('perm_to') : $request->input('to'),
            'permission_type' => $request->input('permission_type'),
        ];

        $evaluation = $this->evaluateLimits($user, $type, $request->date, $evalParams);

        // إذا كانت الإضافة كاسرة للحدود ولم يتم تأكيد الاستثناء مسبقاً من المشرف
        if ($evaluation['is_breached'] && !$request->boolean('confirmed_override')) {
            return redirect()->back()
                ->withInput()
                ->with('limit_warning', implode(' • ', $evaluation['warnings']))
                ->with('breached_warnings', $evaluation['warnings'])
                ->with('needs_override_confirmation', true);
        }

        $isOverride = $evaluation['is_breached'];
        $overrideAuditSuffix = $isOverride 
            ? ' [⚠️ كسر حدود معتمد كاستثناء: ' . implode(' | ', $evaluation['warnings']) . ']'
            : '';

        if ($type === 'overtime') {
            $request->validate([
                'from' => 'required|date_format:H:i',
                'to'   => 'required|date_format:H:i|after:from',
            ]);

            $from = Carbon::createFromFormat('H:i', $request->from);
            $to   = Carbon::createFromFormat('H:i', $request->to);
            $totalHours = round($from->diffInMinutes($to) / 60, 2);

            Overtime::create([
                'user_id'     => $userId,
                'name'        => $request->employee_name,
                'date'        => $request->date,
                'day'         => $request->day,
                'reason'      => $request->reason,
                'from'        => $request->from,
                'to'          => $request->to,
                'total_hours' => $totalHours,
                'status'      => 'accepted',
                'actioned_by' => Auth::user()->name,
            ]);

            AuditLog::log(
                Auth::user()->name, 
                'Created', 
                'Overtime', 
                $request->employee_name, 
                "Manual Entry - Hours: {$totalHours}{$overrideAuditSuffix}"
            );

            $msg = "✅ تم إضافة الإضافي لـ {$request->employee_name} بنجاح!";
            if ($isOverride) {
                $msg .= ' ⚠️ (تم تسجيل الإضافة كاستثناء إداري لتخطي الحد اليومي)';
            }

            return redirect()->route('super_admin.entry')->with('success', $msg);
        }

        if ($type === 'leave') {
            $request->validate([
                'substitute' => 'required|string|max:255',
                'days_count' => 'required|integer|min:1',
            ]);

            Leave::create([
                'user_id'     => $userId,
                'name'        => $request->employee_name,
                'date'        => $request->date,
                'day'         => $request->day,
                'reason'      => $request->reason,
                'substitute'  => $request->substitute,
                'days_count'  => $request->days_count,
                'status'      => 'accepted',
                'actioned_by' => Auth::user()->name,
            ]);

            AuditLog::log(
                Auth::user()->name, 
                'Created', 
                'Leave', 
                $request->employee_name, 
                "Manual Entry - Days: {$request->days_count}{$overrideAuditSuffix}"
            );

            $msg = "✅ تم إضافة الإجازة لـ {$request->employee_name} بنجاح!";
            if ($isOverride) {
                $msg .= ' ⚠️ (تم تسجيل الإجازة كاستثناء إداري لتخطي رصيد الإجازات المسموح به)';
            }

            return redirect()->route('super_admin.entry')->with('success', $msg);
        }

        if ($type === 'permission') {
            $request->validate([
                'permission_type' => 'required|string|in:إذن تأخير,إذن انصراف باكر,إذن نسيان بصمة حضور,إذن نسيان بصمة انصراف',
                'perm_from'       => 'nullable|date_format:H:i',
                'perm_to'         => 'nullable|date_format:H:i|after:perm_from',
            ]);

            Permission::create([
                'user_id'         => $userId,
                'name'            => $request->employee_name,
                'date'            => $request->date,
                'day'             => $request->day,
                'reason'          => $request->reason,
                'from'            => $request->perm_from,
                'to'              => $request->perm_to,
                'permission_type' => $request->permission_type,
                'status'          => 'accepted',
                'actioned_by'     => Auth::user()->name,
            ]);

            AuditLog::log(
                Auth::user()->name, 
                'Created', 
                'Permission', 
                $request->employee_name, 
                "Manual Entry - Type: {$request->permission_type}{$overrideAuditSuffix}"
            );

            $msg = "✅ تم إضافة الإذن لـ {$request->employee_name} بنجاح!";
            if ($isOverride) {
                $msg .= ' ⚠️ (تم تسجيل الإذن كاستثناء إداري لتخطي رصيد الأذونات المسموح به)';
            }

            return redirect()->route('super_admin.entry')->with('success', $msg);
        }

        return redirect()->back()->withErrors(['error' => 'نوع الإدخال غير صالح']);
    }
}
