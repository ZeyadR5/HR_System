<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Leave;
use App\Models\Permission;
use App\Models\Overtime;
use App\Models\Penalty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\PayrollPeriod;
use App\Support\Settings;
use Carbon\Carbon;

class EmployeeProfileController extends Controller
{
    /**
     * Display current user's profile with monthly calendar navigation.
     */
    public function myProfile(Request $request)
    {
        $user = Auth::user();
        return $this->renderProfileView($user, $request);
    }

    /**
     * Admin view of a specific employee profile.
     */
    public function show(Request $request, $id)
    {
        if (!Auth::user()->can('employees.view_profiles') && Auth::id() != $id) {
            abort(403, 'غير مصرح لك باستعراض هذا البروفايل');
        }

        // Require system password unlock when viewing other employees' profiles
        if (Auth::id() != $id && !session('unlocked_show_user_id_' . $id) && session('unlocked_edit_user_id') != $id) {
            return redirect()->route('employee_profiles.index')->withErrors(['system_password' => 'يُرجى إدخال كلمة مرور النظام أولاً لعرض بروفايل الموظف.']);
        }

        $user = User::findOrFail($id);
        return $this->renderProfileView($user, $request);
    }

    /**
     * Calculate stats for employee profile edit view.
     */
    private function getProfileStats(User $user)
    {
        $period = PayrollPeriod::current();
        
        // Leaves
        $monthLeaves = Leave::where('user_id', $user->id)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        $yearLeaves = Leave::where('user_id', $user->id)
            ->whereYear('date', now()->year)
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        // Permissions
        $permissions = Permission::where('user_id', $user->id)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->get();

        $totalPermissionMinutes = 0;
        foreach ($permissions as $p) {
            if ($p->from && $p->to) {
                $from = Carbon::parse($p->from);
                $to   = Carbon::parse($p->to);
                $totalPermissionMinutes += $from->diffInMinutes($to);
            }
        }

        // Overtime
        $overtimeHours = Overtime::where('user_id', $user->id)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', 'accepted')
            ->sum('total_hours');

        $overtimeValue = 0;
        if ($user->hourly_rate > 0) {
            $overtimeValue = $overtimeHours * 1.5 * $user->hourly_rate;
        }

        return [
            'period_label' => $period['label'],
            'leaves' => [
                'month' => $monthLeaves,
                'year'  => $yearLeaves,
                'month_limit' => 2,
                'year_limit'  => 14,
            ],
            'permissions' => [
                'minutes' => $totalPermissionMinutes,
                'hours'   => round($totalPermissionMinutes / 60, 2),
                'limit_minutes' => \App\Support\Settings::get('monthly_permissions_hours', 3) * 60,
            ],
            'overtime' => [
                'hours' => round($overtimeHours, 2),
                'value' => round($overtimeValue, 2),
            ]
        ];
    }

    /**
     * Render the employee profile view with monthly data and annual leave balance.
     */
    private function renderProfileView(User $user, Request $request)
    {
        $year = (int) $request->input('year', now()->year);
        $month = (int) $request->input('month', now()->month);

        if ($month < 1 || $month > 12) {
            $month = (int) now()->month;
        }

        if ($year < 2020 || $year > 2035) {
            $year = (int) now()->year;
        }

        $startDay = (int) \App\Support\Settings::get('payroll_start_day', 26);
        $endDay = (int) \App\Support\Settings::get('payroll_end_day', 25);

        if ($endDay < $startDay) {
            $periodEnd = Carbon::createFromDate($year, $month, $endDay)->endOfDay();
            $periodStart = $periodEnd->copy()->subMonthNoOverflow()->day($startDay)->startOfDay();
        } else {
            $periodEnd = Carbon::createFromDate($year, $month, $endDay)->endOfDay();
            $periodStart = $periodEnd->copy()->day($startDay)->startOfDay();
        }

        $startStr = $periodStart->format('Y-m-d');
        $endStr   = $periodEnd->format('Y-m-d');

        $selectedDate = Carbon::createFromDate($year, $month, 1);
        $prevDate = $selectedDate->copy()->subMonth();
        $nextDate = $selectedDate->copy()->addMonth();

        $arabicMonths = [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];

        $startMonthName = $arabicMonths[$periodStart->month];
        $endMonthName   = $arabicMonths[$periodEnd->month];

        $selectedMonthLabel = "شهر {$endMonthName} {$year} (من {$startDay} {$startMonthName} إلى {$endDay} {$endMonthName})";

        // --- Monthly Activity Data (26th to 25th Payroll Period) ---
        // 1. Leaves (الإجازات)
        $leaves = Leave::where('user_id', $user->id)
            ->whereBetween('date', [$startStr, $endStr])
            ->orderBy('date', 'desc')
            ->get();
        $monthAcceptedLeavesDays = $leaves->where('status', 'accepted')->sum(function($l) {
            return (float) ($l->days_count ?? 0);
        });

        // 2. Overtime (الإضافي)
        $overtimes = Overtime::where('user_id', $user->id)
            ->whereBetween('date', [$startStr, $endStr])
            ->orderBy('date', 'desc')
            ->get();
        $monthAcceptedOvertimeHours = $overtimes->where('status', 'accepted')->sum(function($o) {
            return (float) ($o->total_hours ?? 0);
        });
        $monthOvertimeValue = ($user->hourly_rate > 0) ? ($monthAcceptedOvertimeHours * 1.5 * $user->hourly_rate) : 0;

        // 3. Permissions (الأذونات)
        $permissions = Permission::where('user_id', $user->id)
            ->whereBetween('date', [$startStr, $endStr])
            ->orderBy('date', 'desc')
            ->get();
        $monthPermissionMinutes = 0;
        foreach ($permissions as $p) {
            if ($p->status === 'accepted' && $p->from && $p->to) {
                $from = Carbon::parse($p->from);
                $to   = Carbon::parse($p->to);
                $monthPermissionMinutes += $from->diffInMinutes($to);
            }
        }
        $monthPermissionHours = round($monthPermissionMinutes / 60, 2);

        // 4. Penalties (الجزاءات)
        $penalties = Penalty::where('user_id', $user->id)
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->orderBy('created_at', 'desc')
            ->get();
        $monthAcceptedPenaltiesCount = $penalties->where('status', 'accepted')->count();
        $monthPenaltiesAmount = $penalties->where('status', 'accepted')->sum(function($p) {
            if (is_numeric($p->amount)) {
                return (float) $p->amount;
            }
            $extracted = preg_replace('/[^0-9.]/', '', (string) $p->amount);
            return is_numeric($extracted) ? (float) $extracted : 0;
        });

        // --- Annual Leave Balance Calculation ---
        $annualQuota = 14; // Official 14 days annual leave balance
        $yearUsedLeavesDays = Leave::where('user_id', $user->id)
            ->where(function($q) {
                $q->whereYear('date', now()->year)
                  ->orWhere('date', 'like', now()->year . '-%');
            })
            ->where('status', 'accepted')
            ->get()
            ->sum(function($l) {
                return (float) ($l->days_count ?? 0);
            });
        $remainingAnnualLeaves = max(0, $annualQuota - $yearUsedLeavesDays);

        $profileRoute = (request()->routeIs('employee_profiles.show') || Auth::id() !== $user->id)
            ? route('employee_profiles.show', $user->id)
            : route('my_profile');

        return view('employee_profiles.show', [
            'user' => $user,
            'year' => $year,
            'month' => $month,
            'selectedDate' => $selectedDate,
            'prevYear' => $prevDate->year,
            'prevMonth' => $prevDate->month,
            'nextYear' => $nextDate->year,
            'nextMonth' => $nextDate->month,
            'selectedMonthLabel' => $selectedMonthLabel,
            'arabicMonths' => $arabicMonths,
            'profileRoute' => $profileRoute,
            // Monthly Records & Totals
            'leaves' => $leaves,
            'monthAcceptedLeavesDays' => $monthAcceptedLeavesDays,
            'overtimes' => $overtimes,
            'monthAcceptedOvertimeHours' => $monthAcceptedOvertimeHours,
            'monthOvertimeValue' => $monthOvertimeValue,
            'permissions' => $permissions,
            'monthPermissionMinutes' => $monthPermissionMinutes,
            'monthPermissionHours' => $monthPermissionHours,
            'penalties' => $penalties,
            'monthAcceptedPenaltiesCount' => $monthAcceptedPenaltiesCount,
            'monthPenaltiesAmount' => $monthPenaltiesAmount,
            // Annual Leave Balance
            'annualQuota' => $annualQuota,
            'yearUsedLeavesDays' => $yearUsedLeavesDays,
            'remainingAnnualLeaves' => $remainingAnnualLeaves,
        ]);
    }

    public function index(Request $request)
    {
        if (!Auth::user()->can('employees.view_profiles')) {
            abort(403, 'غير مصرح لك بالوصول ملفات الموظفين');
        }

        $query = User::query();

        if ($request->filled('user_id')) {
            $query->where('id', $request->user_id);
        }

        if ($request->filled('job_title')) {
            $query->where('job_title', $request->job_title);
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortDir = strtolower($request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'job_title') {
            $query->orderByRaw('CASE WHEN job_title IS NULL OR job_title = "" THEN 1 ELSE 0 END')
                  ->orderBy('job_title', $sortDir)
                  ->orderBy('name', 'asc');
        } else {
            $query->orderBy('name', $sortDir);
        }

        $users = $query->paginate(20)->withQueryString();
        $allUsers = User::orderBy('name')->get();
        $jobTitles = User::whereNotNull('job_title')->where('job_title', '!=', '')->distinct()->pluck('job_title')->sort()->values();

        return view('employee_profiles.index', compact('users', 'allUsers', 'jobTitles', 'sortBy', 'sortDir'));
    }


    public function unlockShow(Request $request, $id)
    {
        if (!Auth::user()->can('employees.view_profiles') && Auth::id() != $id) {
            abort(403, 'غير مصرح لك باستعراض هذا البروفايل');
        }

        $request->validate([
            'system_password' => 'required'
        ], [
            'system_password.required' => 'كلمة مرور النظام مطلوبة'
        ]);

        $correctPassword = Settings::get('system_edit_password', '123456');

        if ($request->system_password !== $correctPassword) {
            return back()->withErrors(['system_password' => 'كلمة مرور النظام غير صحيحة!']);
        }

        // Set session valid for viewing this user ID
        session(['unlocked_show_user_id_' . $id => true]);

        return redirect()->route('employee_profiles.show', $id);
    }

    public function unlockEdit(Request $request, $id)
    {
        if (!Auth::user()->can('employees.edit_profiles')) {
            abort(403, 'غير مصرح لك بتعديل بروفايل الموظف');
        }

        $request->validate([
            'system_password' => 'required'
        ], [
            'system_password.required' => 'كلمة مرور النظام مطلوبة'
        ]);

        $correctPassword = Settings::get('system_edit_password', '123456');

        if ($request->system_password !== $correctPassword) {
            return back()->withErrors(['system_password' => 'كلمة مرور النظام غير صحيحة!']);
        }

        // Set session valid for this user ID
        session(['unlocked_edit_user_id' => $id]);

        return redirect()->route('employee_profiles.edit', $id);
    }

    public function edit($id)
    {
        if (!Auth::user()->can('employees.edit_profiles')) {
            abort(403, 'غير مصرح لك بتعديل بروفايل الموظف');
        }

        if (session('unlocked_edit_user_id') != $id) {
            return redirect()->route('employee_profiles.index')->withErrors(['system_password' => 'يُرجى فك القفل أولاً بالضغط على زر التعديل.']);
        }

        $user = User::findOrFail($id);
        $stats = $this->getProfileStats($user);
        $roles = \App\Models\Role::orderBy('id')->get();
        return view('employee_profiles.edit', compact('user', 'stats', 'roles'));
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->can('employees.edit_profiles')) {
            abort(403, 'غير مصرح لك بتعديل بروفايل الموظف');
        }

        if (session('unlocked_edit_user_id') != $id) {
            return redirect()->route('employee_profiles.index')->withErrors(['system_password' => 'يُرجى فك القفل أولاً بالضغط على زر التعديل.']);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'job_title' => 'nullable|string|max:255',
            'hourly_rate' => 'required|numeric|min:0',
            'role' => 'nullable|exists:roles,name'
        ], [
            'hourly_rate.required' => 'قيمة الساعة مطلوبة',
        ]);

        $updateData = [
            'job_title' => $request->job_title,
            'hourly_rate' => $request->hourly_rate,
        ];

        if ($request->filled('role') && Auth::user()->role === 'developer') {
            $updateData['role'] = $request->role;
        }

        $user->update($updateData);

        $calcDetails = '';
        if ($request->filled('salary_amount') && $request->filled('working_hours')) {
            $calcDetails = " (مرتب: {$request->salary_amount} ج.م / ساعات: {$request->working_hours})";
        }

        AuditLog::log(
            Auth::user()->name, 
            'Updated', 
            'Employee Profile', 
            $user->name, 
            "Job: {$request->job_title}, Rate: {$request->hourly_rate}{$calcDetails}" . (isset($updateData['role']) ? ", Role: {$updateData['role']}" : "")
        );

        return redirect()->route('employee_profiles.index')->with('success', "✅ تم تحديث بروفايل {$user->name} بنجاح!");
    }

    /**
     * Smart create employee form.
     */
    public function create()
    {
        if (!Auth::user()->can('employees.create')) {
            abort(403, 'غير مصرح لك بإضافة موظف');
        }

        $roles = \App\Models\Role::orderBy('id')->get();
        return view('employee_profiles.create', compact('roles'));
    }

    /**
     * Store newly created employee.
     */
    public function store(Request $request)
    {
        if (!Auth::user()->can('employees.create')) {
            abort(403, 'غير مصرح لك بإضافة موظف');
        }

        // 1. Ensure email is unique by appending a number if it exists
        $email = mb_strtolower(trim($request->email));
        $originalEmail = $email;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $parts = explode('@', $originalEmail);
            if (count($parts) == 2) {
                $email = $parts[0] . $counter . '@' . $parts[1];
            } else {
                $email = $originalEmail . $counter;
            }
            $counter++;
        }
        $request->merge(['email' => $email]);

        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'password'    => 'required|string|min:4',
            'job_title'   => 'nullable|string|max:255',
            'hourly_rate' => 'nullable|numeric|min:0',
            'role'        => 'required|exists:roles,name',
        ], [
            'name.required'     => 'اسم الموظف مطلوب.',
            'email.required'    => 'البريد الإلكتروني مطلوب.',
            'email.unique'      => 'هذا البريد مأخوذ من قبل، حاول استخدام كلمة أخرى.',
            'password.required' => 'كلمة السر مطلوبة.',
            'role.required'     => 'فئة الموظف مطلوبة.',
        ]);

        $user = User::create([
            'name'        => trim($request->name),
            'email'       => $email,
            'password'    => \Illuminate\Support\Facades\Hash::make($request->password),
            'job_title'   => $request->job_title ?: null,
            'hourly_rate' => $request->hourly_rate ?: 0,
            'role'        => $request->role,
        ]);

        AuditLog::log(
            Auth::user()->name,
            'Created',
            'Employee Profile',
            $user->name,
            "Email: {$user->email}, Role: {$user->role}, Job: {$user->job_title}"
        );

        return redirect()->route('employee_profiles.create')->with([
            'success' => "🎉 تم إضافة الموظف «{$user->name}» بنجاح!",
            'created_user' => [
                'name'      => $user->name,
                'email'     => $user->email,
                'password'  => $request->password,
                'job_title' => $user->job_title ?: 'غير محدد',
                'role_name' => $user->roleDisplayName(),
            ]
        ]);
    }

    /**
     * AJAX endpoint to check and generate a unique email in real-time.
     */
    public function checkEmail(Request $request)
    {
        $email = mb_strtolower(trim($request->email));
        if (empty($email)) {
            return response()->json(['email' => '']);
        }

        $originalEmail = $email;
        $counter = 1;
        while (User::where('email', $email)->exists()) {
            $parts = explode('@', $originalEmail);
            if (count($parts) == 2) {
                $email = $parts[0] . $counter . '@' . $parts[1];
            } else {
                $email = $originalEmail . $counter;
            }
            $counter++;
        }

        return response()->json(['email' => $email]);
    }

    /**
     * Delete an employee record and clean up related dependencies cleanly.
     */
    public function destroy(Request $request, $id)
    {
        if (!Auth::user()->can('employees.edit_profiles')) {
            abort(403, 'غير مصرح لك بحذف موظف');
        }

        if (Auth::id() == $id) {
            return back()->withErrors(['system_password' => 'لا يمكنك حذف حسابك الحالي المسجل به الدخول!']);
        }

        $user = User::findOrFail($id);

        if ($user->isDeveloper()) {
            return back()->withErrors(['system_password' => 'لا يمكن حذف حساب المطور (Developer)!']);
        }

        $request->validate([
            'system_password' => 'required'
        ], [
            'system_password.required' => 'كلمة مرور النظام مطلوبة لتأكيد الحذف'
        ]);

        $correctPassword = Settings::get('system_edit_password', '123456');

        if ($request->system_password !== $correctPassword) {
            return back()->withErrors(['system_password' => 'كلمة مرور النظام غير صحيحة! لم يتم الحذف.']);
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $userRole = $user->role;

        DB::transaction(function () use ($user) {
            // Keep shift roster entries intact but detach user_id so schedules don't break
            if (class_exists(\App\Models\ShiftRosterEntry::class) && Schema::hasTable('shift_roster_entries')) {
                \App\Models\ShiftRosterEntry::where('user_id', $user->id)->update(['user_id' => null]);
            }
            if (class_exists(\App\Models\ShiftRoster::class) && Schema::hasTable('shift_rosters')) {
                \App\Models\ShiftRoster::where('created_by', $user->id)->update(['created_by' => null]);
            }

            // Reassign instructions created by user to current admin to prevent cascade loss
            if (Schema::hasTable('instructions')) {
                DB::table('instructions')->where('created_by', $user->id)->update(['created_by' => Auth::id()]);
            }

            // Clean related tables
            if (Schema::hasTable('employee_devices')) {
                DB::table('employee_devices')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('check_in_outs')) {
                DB::table('check_in_outs')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('overtimes')) {
                DB::table('overtimes')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('leaves')) {
                DB::table('leaves')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('permissions')) {
                DB::table('permissions')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('penalties')) {
                DB::table('penalties')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('incentives')) {
                DB::table('incentives')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('admin_notes')) {
                DB::table('admin_notes')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('settlements')) {
                DB::table('settlements')->where('user_id', $user->id)->delete();
            }
            if (Schema::hasTable('notifications')) {
                DB::table('notifications')->where('notifiable_id', $user->id)->where('notifiable_type', User::class)->delete();
            }
            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->where('tokenable_type', User::class)->delete();
            }

            $user->delete();
        });

        AuditLog::log(
            Auth::user()->name,
            'Deleted',
            'Employee Profile',
            $userName,
            "Deleted user: {$userName} ({$userEmail}), Role: {$userRole}"
        );

        return redirect()->route('employee_profiles.index')->with('success', "🗑️ تم حذف حساب الموظف «{$userName}» بنجاح وإزالة كافة بياناته.");
    }
}


