<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNote;
use App\Models\Incentive;
use App\Models\AuditLog;
use App\Models\User;
use App\Models\Overtime;
use App\Models\Leave;
use App\Models\Permission;
use App\Models\Penalty;
use App\Models\Settlement;
use App\Models\CheckInOut;
use App\Support\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AdminReportController extends Controller
{
    protected $allowedEmails = ['z@z.z', 'Y@Y.Y', 'a@a.a'];

    private function hasProfileAccess($user)
    {
        return in_array($user->email, $this->allowedEmails) || $user->role === 'super_admin';
    }

    // ========================================================
    // EMPLOYEE / USER GENERAL DATA
    // ========================================================

    /**
     * Get Incentives
     */
    public function incentiveIndex(Request $request)
    {
        $user = Auth::user();
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = Incentive::whereBetween('created_at', [
            $periodStart->toDateString() . ' 00:00:00',
            $periodEnd->toDateString() . ' 23:59:59',
        ]);

        if (!in_array($user->role, ['admin', 'super_admin', 'developer'])) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        }

        $records = $query->orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'data' => $records], 200);
    }

    /**
     * Get Admin Notes
     */
    public function notesIndex(Request $request)
    {
        $user = Auth::user();
        $query = AdminNote::orderByDesc('created_at');

        if (!in_array($user->role, ['admin', 'super_admin', 'developer'])) {
            $query->where('user_id', $user->id);
        } else {
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        }

        $records = $query->get();
        return response()->json(['success' => true, 'data' => $records], 200);
    }

    // ========================================================
    // SUPER ADMIN WRITE OPERATIONS
    // ========================================================

    /**
     * Super Admin: Manual Entry for Overtime/Leave/Permission
     */
    public function employeeEntry(Request $request)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'غير مصرح للوصول لهذه الصفحة'], 403);
        }

        $validator = Validator::make($request->all(), [
            'entry_type'    => 'required|in:overtime,leave,permission',
            'employee_name' => 'required|string|max:255',
            'date'          => 'required|date',
            'day'           => 'required|string|max:50',
            'reason'        => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'المدخلات غير صالحة', 'errors' => $validator->errors()], 422);
        }

        $employee = User::where('name', $request->employee_name)->first();
        $userId = $employee?->id;

        $type = $request->entry_type;

        if ($type === 'overtime') {
            $subValidator = Validator::make($request->all(), [
                'from' => 'required|date_format:H:i',
                'to'   => 'required|date_format:H:i|after:from',
            ]);

            if ($subValidator->fails()) {
                return response()->json(['success' => false, 'message' => 'أوقات العمل الإضافي غير صالحة', 'errors' => $subValidator->errors()], 422);
            }

            $fromTime = Carbon::createFromFormat('H:i', $request->from);
            $toTime = Carbon::createFromFormat('H:i', $request->to);
            $totalHours = round($fromTime->diffInMinutes($toTime) / 60, 2);

            $entry = Overtime::create([
                'user_id'     => $userId,
                'name'        => $request->employee_name,
                'date'        => $request->date,
                'day'         => $request->day,
                'reason'      => $request->reason,
                'from'        => $request->from,
                'to'          => $request->to,
                'total_hours' => $totalHours,
                'status'      => 'accepted',
                'actioned_by' => $admin->name,
            ]);

            AuditLog::log($admin->name, 'Created', 'Overtime', $request->employee_name, "Manual Entry - Hours: {$totalHours}");

            return response()->json(['success' => true, 'message' => "تم إضافة الإضافي لـ {$request->employee_name} بنجاح!", 'data' => $entry], 201);
        }

        if ($type === 'leave') {
            $subValidator = Validator::make($request->all(), [
                'substitute' => 'required|string|max:255',
                'days_count' => 'required|integer|min:1',
            ]);

            if ($subValidator->fails()) {
                return response()->json(['success' => false, 'message' => 'بيانات الإجازة المضافة غير صالحة', 'errors' => $subValidator->errors()], 422);
            }

            $entry = Leave::create([
                'user_id'     => $userId,
                'name'        => $request->employee_name,
                'date'        => $request->date,
                'day'         => $request->day,
                'reason'      => $request->reason,
                'substitute'  => $request->substitute,
                'days_count'  => $request->days_count,
                'status'      => 'accepted',
                'actioned_by' => $admin->name,
            ]);

            AuditLog::log($admin->name, 'Created', 'Leave', $request->employee_name, "Manual Entry - Days: {$request->days_count}");

            return response()->json(['success' => true, 'message' => "تم إضافة الإجازة لـ {$request->employee_name} بنجاح!", 'data' => $entry], 201);
        }

        if ($type === 'permission') {
            $subValidator = Validator::make($request->all(), [
                'permission_type' => 'required|string|in:إذن تأخير,إذن انصراف باكر,إذن نسيان بصمة حضور,إذن نسيان بصمة انصراف',
                'perm_from'       => 'nullable|date_format:H:i',
                'perm_to'         => 'nullable|date_format:H:i|after:perm_from',
            ]);

            if ($subValidator->fails()) {
                return response()->json(['success' => false, 'message' => 'بيانات الإذن المضافة غير صالحة', 'errors' => $subValidator->errors()], 422);
            }

            $entry = Permission::create([
                'user_id'         => $userId,
                'name'            => $request->employee_name,
                'date'            => $request->date,
                'day'             => $request->day,
                'reason'          => $request->reason,
                'from'            => $request->perm_from,
                'to'              => $request->perm_to,
                'permission_type' => $request->permission_type,
                'status'          => 'accepted',
                'actioned_by'     => $admin->name,
            ]);

            AuditLog::log($admin->name, 'Created', 'Permission', $request->employee_name, "Manual Entry - Type: {$request->permission_type}");

            return response()->json(['success' => true, 'message' => "تم إضافة الإذن لـ {$request->employee_name} بنجاح!", 'data' => $entry], 201);
        }

        return response()->json(['success' => false, 'message' => 'نوع الإدخال غير صالح'], 400);
    }

    /**
     * Add Note
     */
    public function notesStore(Request $request)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'غير مصرح للوصول لهذه الصفحة'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'note'    => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'الملاحظة والملف مطلوبان بشكل صحيح', 'errors' => $validator->errors()], 422);
        }

        $employee = User::findOrFail($request->user_id);

        $note = AdminNote::create([
            'user_id'     => $employee->id,
            'name'        => $employee->name,
            'note'        => $request->note,
            'actioned_by' => $admin->name,
        ]);

        AuditLog::log($admin->name, 'Created', 'Admin Note', $employee->name, "Note: " . substr($request->note, 0, 50));

        return response()->json(['success' => true, 'message' => 'تم إضافة الملاحظة بنجاح', 'data' => $note], 201);
    }

    /**
     * Add Incentive
     */
    public function incentiveStore(Request $request)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'غير مصرح للوصول لهذه الصفحة'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'reason'  => 'required|string|max:500',
            'amount'  => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'بيانات الحافز غير صالحة', 'errors' => $validator->errors()], 422);
        }

        $employee = User::findOrFail($request->user_id);

        $inc = Incentive::create([
            'user_id'     => $employee->id,
            'name'        => $employee->name,
            'reason'      => $request->reason,
            'amount'      => $request->amount,
            'status'      => 'accepted',
            'actioned_by' => $admin->name,
        ]);

        AuditLog::log($admin->name, 'Created', 'Incentive', $employee->name, "Amount: {$request->amount}");

        return response()->json(['success' => true, 'message' => 'تم إضافة الحافز بنجاح', 'data' => $inc], 201);
    }

    // ========================================================
    // PROFILES & SECURITY (إدارة الموظفين والسرية)
    // ========================================================

    /**
     * Super Admin / Authorized Emails: Get all employee profiles
     */
    public function profilesIndex(Request $request)
    {
        $user = Auth::user();
        if (!$this->hasProfileAccess($user)) {
            return response()->json(['success' => false, 'message' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.'], 403);
        }

        $query = User::orderBy('name');
        if ($request->has('user_id') && $request->user_id) {
            $query->where('id', $request->user_id);
        }

        $users = $query->paginate(20);
        return response()->json(['success' => true, 'data' => $users], 200);
    }

    /**
     * Super Admin / Authorized Emails: View single employee stats & profile
     */
    public function profileShow($id)
    {
        $user = Auth::user();
        if (!$this->hasProfileAccess($user) && $user->id != $id) {
            return response()->json(['success' => false, 'message' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.'], 403);
        }

        $employee = User::findOrFail($id);
        
        // Calculate Stats
        $period = PayrollPeriod::current();
        
        $monthLeaves = Leave::where('user_id', $employee->id)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        $yearLeaves = Leave::where('user_id', $employee->id)
            ->whereYear('date', now()->year)
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        $permissions = Permission::where('user_id', $employee->id)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->get();

        $totalPermMinutes = 0;
        foreach ($permissions as $p) {
            if ($p->from && $p->to) {
                $totalPermMinutes += Carbon::parse($p->from)->diffInMinutes(Carbon::parse($p->to));
            }
        }

        $overtimeHours = Overtime::where('user_id', $employee->id)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', 'accepted')
            ->sum('total_hours');

        $overtimeValue = $employee->hourly_rate > 0 ? ($overtimeHours * 1.5 * $employee->hourly_rate) : 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'employee' => $employee,
                'stats'    => [
                    'period_label' => $period['label'],
                    'leaves' => [
                        'month' => $monthLeaves,
                        'year'  => $yearLeaves,
                        'month_limit' => 2,
                        'year_limit'  => 14,
                    ],
                    'permissions' => [
                        'minutes' => $totalPermMinutes,
                        'hours'   => round($totalPermMinutes / 60, 2),
                        'limit_minutes' => \App\Support\Settings::get('monthly_permissions_hours', 3) * 60,
                    ],
                    'overtime' => [
                        'hours' => round($overtimeHours, 2),
                        'value' => round($overtimeValue, 2),
                    ]
                ]
            ]
        ], 200);
    }

    /**
     * Super Admin / Authorized Emails: Update employee job_title & hourly_rate
     */
    public function profileUpdate(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->hasProfileAccess($user)) {
            return response()->json(['success' => false, 'message' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.'], 403);
        }

        $employee = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'job_title'   => 'nullable|string|max:255',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'قيمة الساعة مطلوبة وقيمتها صحيحة', 'errors' => $validator->errors()], 422);
        }

        $employee->update([
            'job_title'   => $request->job_title,
            'hourly_rate' => $request->hourly_rate,
        ]);

        AuditLog::log(
            $user->name, 
            'Updated', 
            'Employee Profile (Mobile)', 
            $employee->name, 
            "Job: {$request->job_title}, Rate: {$request->hourly_rate}"
        );

        return response()->json(['success' => true, 'message' => "✅ تم تحديث بروفايل {$employee->name} بنجاح!"], 200);
    }

    /**
     * Super Admin: Reset employee password
     */
    public function resetPassword(Request $request, $id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin') {
            return response()->json(['success' => false, 'message' => 'غير مصرح للوصول لهذه الصفحة'], 403);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل ومطابقة للتأكيد', 'errors' => $validator->errors()], 422);
        }

        $employee = User::findOrFail($id);
        $employee->update([
            'password' => Hash::make($request->password)
        ]);

        AuditLog::log($admin->name, 'Changed Password', 'Auth', $employee->name);

        return response()->json(['success' => true, 'message' => 'تم تغيير كلمة المرور بنجاح!'], 200);
    }

    /**
     * Audit Logs
     */
    public function auditLogs(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) {
            return response()->json(['success' => false, 'message' => 'غير مصرح للوصول لهذه الصفحة'], 403);
        }

        $logs = AuditLog::orderByDesc('created_at')->paginate(50);
        return response()->json(['success' => true, 'data' => $logs], 200);
    }

    /**
     * Full Payroll Summary report stats
     */
    public function fullReport(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) {
            return response()->json(['success' => false, 'message' => 'غير مصرح للوصول لهذه الصفحة'], 403);
        }

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        // Compute full totals in current cycle
        $totalOvertime = Overtime::whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->where('status', 'accepted')
            ->sum('total_hours');

        $totalLeaves = Leave::whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->where('status', 'accepted')
            ->sum('days_count');

        $totalPermissions = Permission::whereBetween('date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->where('status', 'accepted')
            ->count();

        $totalPenalties = Penalty::whereBetween('created_at', [
                $periodStart->toDateString() . ' 00:00:00',
                $periodEnd->toDateString() . ' 23:59:59',
            ])
            ->where('status', 'accepted')
            ->sum('amount');

        $totalIncentives = Incentive::whereBetween('created_at', [
                $periodStart->toDateString() . ' 00:00:00',
                $periodEnd->toDateString() . ' 23:59:59',
            ])
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data'    => [
                'period_label'     => $period['label'],
                'total_overtime'   => round($totalOvertime, 2),
                'total_leaves'     => $totalLeaves,
                'total_permissions'=> $totalPermissions,
                'total_penalties'  => round($totalPenalties, 2),
                'total_incentives' => round($totalIncentives, 2),
            ]
        ], 200);
    }
}
