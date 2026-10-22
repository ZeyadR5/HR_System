<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\EmployeeDocument;
use App\Models\AuditLog;
use App\Models\Leave;
use App\Models\Permission;
use App\Models\Overtime;
use App\Models\Penalty;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        if (Auth::id() != $id && !session('unlocked_employee_profiles')) {
            return redirect()->route('employee_profiles.index')->withErrors(['system_password' => 'يُرجى إدخال كلمة مرور النظام أولاً لعرض بروفايل الموظف.']);
        }

        $user = User::withTrashed()->findOrFail($id);
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
            abort(403, 'غير مصرح لك بالوصول لملفات الموظفين');
        }

        if (!session('unlocked_employee_profiles')) {
            return view('employee_profiles.unlock');
        }

        $query = User::query();

        if ($request->filled('user_id')) {
            $query->where('id', $request->user_id);
        }

        if ($request->filled('job_title')) {
            $query->where('job_title', $request->job_title);
        }

        if ($request->filled('insured')) {
            if ($request->insured === '1') {
                $query->where('is_insured', true);
            } elseif ($request->insured === '0') {
                $query->where(function ($q) {
                    $q->where('is_insured', false)->orWhereNull('is_insured');
                });
            }
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortDir = strtolower($request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'job_title') {
            $query->orderByRaw('CASE WHEN job_title IS NULL OR job_title = "" THEN 1 ELSE 0 END')
                  ->orderBy('job_title', $sortDir)
                  ->orderBy('name', 'asc');
        } elseif ($sortBy === 'is_insured') {
            $query->orderBy('is_insured', $sortDir)
                  ->orderBy('name', 'asc');
        } else {
            $query->orderBy('name', $sortDir);
        }

        $users = $query->paginate(20)->withQueryString();
        $allUsers = User::orderBy('name')->get();
        $jobTitles = User::whereNotNull('job_title')->where('job_title', '!=', '')->distinct()->pluck('job_title')->sort()->values();

        return view('employee_profiles.index', compact('users', 'allUsers', 'jobTitles', 'sortBy', 'sortDir'));
    }

    /**
     * Display the archived employees page.
     */
    public function archive(Request $request)
    {
        if (!Auth::user()->can('employees.view_profiles')) {
            abort(403, 'غير مصرح لك بالوصول لأرشيف الموظفين');
        }

        if (!session('unlocked_employee_profiles')) {
            return view('employee_profiles.unlock');
        }

        $query = User::onlyTrashed();

        if ($request->filled('user_id')) {
            $query->where('id', $request->user_id);
        }

        if ($request->filled('job_title')) {
            $query->where('job_title', $request->job_title);
        }

        if ($request->filled('insured')) {
            if ($request->insured === '1') {
                $query->where('is_insured', true);
            } elseif ($request->insured === '0') {
                $query->where(function ($q) {
                    $q->where('is_insured', false)->orWhereNull('is_insured');
                });
            }
        }

        $sortBy = $request->get('sort_by', 'deleted_at');
        $sortDir = strtolower($request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        if ($sortBy === 'job_title') {
            $query->orderByRaw('CASE WHEN job_title IS NULL OR job_title = "" THEN 1 ELSE 0 END')
                  ->orderBy('job_title', $sortDir)
                  ->orderBy('deleted_at', 'desc');
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', $sortDir);
        } else {
            $query->orderBy('deleted_at', $sortDir);
        }

        $users = $query->paginate(20)->withQueryString();
        $allArchivedUsers = User::onlyTrashed()->orderBy('name')->get();
        $jobTitles = User::onlyTrashed()->whereNotNull('job_title')->where('job_title', '!=', '')->distinct()->pluck('job_title')->sort()->values();

        return view('employee_profiles.archive', compact('users', 'allArchivedUsers', 'jobTitles', 'sortBy', 'sortDir'));
    }

    /**
     * Restore an archived employee back to active employees list.
     */
    public function restore(Request $request, $id)
    {
        if (!Auth::user()->can('employees.edit_profiles')) {
            abort(403, 'غير مصرح لك باسترجاع موظف');
        }

        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        AuditLog::log(
            Auth::user()->name,
            'Restored',
            'Employee Profile',
            $user->name,
            "تم استرجاع حساب الموظف «{$user->name}» من الأرشيف وإعادته للموظفين النشطين"
        );

        return redirect()->route('employee_profiles.index', ['highlight_user_id' => $user->id])->with(
            'success',
            "🎉 تم استرجاع الموظف «{$user->name}» من الأرشيف بنجاح وإعادته للموظفين النشطين!"
        );
    }

    public function unlockModule(Request $request)
    {
        if (!Auth::user()->can('employees.view_profiles')) {
            abort(403, 'غير مصرح لك باستعراض ملفات الموظفين');
        }

        $request->validate([
            'system_password' => 'required'
        ], [
            'system_password.required' => 'كلمة مرور النظام مطلوبة للدخول'
        ]);

        $correctPassword = Settings::get('system_edit_password', '123456');

        if ($request->system_password !== $correctPassword) {
            return back()->withErrors(['system_password' => 'كلمة مرور النظام غير صحيحة!']);
        }

        session(['unlocked_employee_profiles' => true]);

        return redirect()->route('employee_profiles.index');
    }

    public function lockModule(Request $request)
    {
        session()->forget('unlocked_employee_profiles');
        return redirect()->route('employee_profiles.index');
    }

    public function unlockShow(Request $request, $id)
    {
        session(['unlocked_employee_profiles' => true]);
        return redirect()->route('employee_profiles.show', $id);
    }

    public function unlockEdit(Request $request, $id)
    {
        session(['unlocked_employee_profiles' => true]);
        return redirect()->route('employee_profiles.edit', $id);
    }

    public function edit($id)
    {
        if (!Auth::user()->can('employees.edit_profiles')) {
            abort(403, 'غير مصرح لك بتعديل بروفايل الموظف');
        }

        if (!session('unlocked_employee_profiles')) {
            return redirect()->route('employee_profiles.index')->withErrors(['system_password' => 'يُرجى إدخال كلمة مرور النظام أولاً للوصول لملفات الموظفين.']);
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

        if (!session('unlocked_employee_profiles')) {
            return redirect()->route('employee_profiles.index')->withErrors(['system_password' => 'يُرجى إدخال كلمة مرور النظام أولاً.']);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'job_title'                  => 'nullable|string|max:255',
            'hourly_rate'                => 'required|numeric|min:0',
            'salary_amount'              => 'nullable|numeric|min:0',
            'working_hours'              => 'nullable|numeric|min:0',
            'basic_incentive'            => 'nullable|numeric|min:0',
            'hire_date'                  => 'nullable|date',
            'evaluation'                 => 'nullable|string|in:A,B,C',
            'role'                       => 'nullable|exists:roles,name',
            'profile_picture'            => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'hourly_rate.required'               => 'قيمة الساعة مطلوبة',
            'evaluation.in'                      => 'تقييم الموظف يجب أن يكون A أو B أو C فقط.',
            'profile_picture.image'              => 'صورة البروفايل يجب أن تكون ملف صورة صالح.',
            'profile_picture.max'                => 'صورة البروفايل لا تتجاوز 5 ميجابايت.',
            'national_id_card.mimes'             => 'صورة البطاقة تقبل صور أو ملفات PDF فقط.',
            'national_id_card.max'               => 'صورة البطاقة لا تتجاوز 5 ميجابايت.',
            'qualification_certificate.mimes'    => 'صورة المؤهل تقبل صور أو ملفات PDF فقط.',
            'qualification_certificate.max'      => 'صورة المؤهل لا تتجاوز 5 ميجابايت.',
            'other_document.mimes'               => 'الوثائق الأخرى تقبل صور أو ملفات PDF فقط.',
            'other_document.max'                 => 'الوثائق الأخرى لا تتجاوز 5 ميجابايت.',
        ]);

        $updateData = [
            'job_title'       => $request->job_title,
            'hourly_rate'     => $request->hourly_rate,
            'salary_amount'   => $request->filled('salary_amount') ? $request->salary_amount : null,
            'working_hours'   => $request->filled('working_hours') ? $request->working_hours : null,
            'basic_incentive' => $request->filled('basic_incentive') ? $request->basic_incentive : 0,
            'hire_date'       => $request->filled('hire_date') ? $request->hire_date : null,
            'evaluation'      => $request->filled('evaluation') ? $request->evaluation : null,
        ];

        if ($request->has('is_insured')) {
            $updateData['is_insured'] = $request->boolean('is_insured');
        }

        if ($request->filled('role') && Auth::user()->role === 'developer') {
            $updateData['role'] = $request->role;
        }

        // معالجة حذف الصورة الشخصية (الوثائق الرسمية تُدار عبر AJAX بشكل منفصل)
        $docKeys = [
            'profile_picture' => 'delete_profile_picture',
        ];

        foreach ($docKeys as $column => $deleteParam) {
            if ($request->boolean($deleteParam) && !empty($user->{$column})) {
                if (Storage::disk('public')->exists($user->{$column})) {
                    Storage::disk('public')->delete($user->{$column});
                }
                $updateData[$column] = null;
            }
        }

        // رفع صورة البروفايل
        if ($request->hasFile('profile_picture')) {
            if (!empty($user->profile_picture) && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $updateData['profile_picture'] = $this->storeUploadedFile($request->file('profile_picture'), 'profile_pictures');
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

        $redirectParams = array_filter([
            'page'      => $request->input('return_page'),
            'job_title' => $request->input('return_job_title'),
            'user_id'   => $request->input('return_user_id'),
            'sort_by'   => $request->input('return_sort_by'),
            'sort_dir'  => $request->input('return_sort_dir'),
        ]);

        return redirect()->route('employee_profiles.index', $redirectParams)
            ->with('success', "✅ تم تحديث بروفايل {$user->name} بنجاح!")
            ->with('highlight_user_id', $user->id);
    }

    /**
     * Smart create employee form.
     */
    public function create()
    {
        if (!Auth::user()->can('employees.create')) {
            abort(403, 'غير مصرح لك بإضافة موظف');
        }

        if (!session('unlocked_employee_profiles')) {
            return redirect()->route('employee_profiles.index')->withErrors(['system_password' => 'يُرجى إدخال كلمة مرور النظام أولاً.']);
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

        if (!session('unlocked_employee_profiles')) {
            return redirect()->route('employee_profiles.index')->withErrors(['system_password' => 'يُرجى إدخال كلمة مرور النظام أولاً.']);
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
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:4',
            'job_title'     => 'nullable|string|max:255',
            'hourly_rate'   => 'nullable|numeric|min:0',
            'salary_amount'   => 'nullable|numeric|min:0',
            'working_hours'   => 'nullable|numeric|min:0',
            'basic_incentive' => 'nullable|numeric|min:0',
            'hire_date'       => 'nullable|date',
            'evaluation'      => 'nullable|string|in:A,B,C',
            'role'            => 'required|exists:roles,name',
        ], [
            'name.required'     => 'اسم الموظف مطلوب.',
            'email.required'    => 'البريد الإلكتروني مطلوب.',
            'email.unique'      => 'هذا البريد مأخوذ من قبل، حاول استخدام كلمة أخرى.',
            'password.required' => 'كلمة السر مطلوبة.',
            'role.required'     => 'فئة الموظف مطلوبة.',
            'evaluation.in'     => 'تقييم الموظف يجب أن يكون A أو B أو C فقط.',
        ]);

        $user = User::create([
            'name'            => trim($request->name),
            'email'           => $email,
            'password'        => \Illuminate\Support\Facades\Hash::make($request->password),
            'job_title'       => $request->job_title ?: null,
            'hourly_rate'     => $request->hourly_rate ?: 0,
            'salary_amount'   => $request->filled('salary_amount') ? $request->salary_amount : null,
            'working_hours'   => $request->filled('working_hours') ? $request->working_hours : null,
            'basic_incentive' => $request->filled('basic_incentive') ? $request->basic_incentive : 0,
            'hire_date'       => $request->filled('hire_date') ? $request->hire_date : null,
            'evaluation'      => $request->filled('evaluation') ? $request->evaluation : null,
            'role'            => $request->role,
            'is_insured'      => $request->boolean('is_insured'),
        ]);

        $fileUpdates = [];
        if ($request->hasFile('profile_picture')) {
            $fileUpdates['profile_picture'] = $this->storeUploadedFile($request->file('profile_picture'), 'profile_pictures');
        }
        foreach (['national_id_card', 'qualification_certificate', 'other_document'] as $docCol) {
            foreach (\Illuminate\Support\Arr::wrap($request->file($docCol)) as $uploaded) {
                if ($uploaded && $uploaded->isValid()) {
                    EmployeeDocument::create([
                        'user_id'       => $user->id,
                        'type'          => $docCol,
                        'path'          => $this->storeUploadedFile($uploaded, "employee_documents/{$user->id}"),
                        'original_name' => $uploaded->getClientOriginalName(),
                    ]);
                }
            }
        }
        if (!empty($fileUpdates)) {
            $user->update($fileUpdates);
        }

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
            // Reassign instructions created by user to current admin to prevent cascade loss
            if (Schema::hasTable('instructions') && Schema::hasColumn('instructions', 'created_by')) {
                DB::table('instructions')->where('created_by', $user->id)->update(['created_by' => Auth::id()]);
            }

            // Revoke active API tokens so archived user cannot make authenticated requests
            if (Schema::hasTable('personal_access_tokens')) {
                DB::table('personal_access_tokens')->where('tokenable_id', $user->id)->where('tokenable_type', User::class)->delete();
            }

            // Soft-delete the user: sets deleted_at and preserves all data, documents, and historical records
            $user->delete();
        });

        AuditLog::log(
            Auth::user()->name,
            'Archived',
            'Employee Profile',
            $userName,
            "تم نقل حساب الموظف إلى الأرشيف (Email: {$userEmail}, Job: {$user->job_title}) مع الحفاظ على كافة وثائقه وسجلاته"
        );

        $redirectParams = array_filter([
            'page'      => $request->input('return_page'),
            'job_title' => $request->input('return_job_title'),
            'user_id'   => $request->input('return_user_id'),
            'sort_by'   => $request->input('return_sort_by'),
            'sort_dir'  => $request->input('return_sort_dir'),
        ]);

        return redirect()->route('employee_profiles.index', $redirectParams)->with(
            'success',
            "🗄️ تم نقل الموظف «{$userName}» إلى الأرشيف بنجاح! جميع بياناته ووثائقه وسجلاته محفوظة بالكامل ويمكن مراجعتها أو استرجاعه في أي وقت."
        );
    }

    /**
     * تحديث الموظف لصورته الشخصية بنفسه من صفحته.
     */
    public function updateMyProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'profile_picture.required' => 'يُرجى اختيار صورة أولاً.',
            'profile_picture.image'    => 'الملف المختار يجب أن يكون صورة صالحة.',
            'profile_picture.mimes'    => 'صيغ الصور المسموح بها: JPG, PNG, WEBP.',
            'profile_picture.max'      => 'أقصى حجم للصورة هو 5 ميجابايت.',
        ]);

        $user = Auth::user();

        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $this->storeUploadedFile($request->file('profile_picture'), 'profile_pictures');
        $user->update(['profile_picture' => $path]);

        AuditLog::log(
            $user->name,
            'Updated',
            'Profile Picture',
            $user->name,
            'قام الموظف بتحديث صورته الشخصية'
        );

        return back()->with('success', '✅ تم تحديث صورتك الشخصية بنجاح!');
    }

    /**
     * استعراض أو تحميل وثيقة موظف بشكل آمن.
     */
    public function viewDocument(Request $request, $id, $type)
    {
        $user = User::withTrashed()->findOrFail($id);

        // الصلاحية: الموظف نفسه أو مسؤول مع صلاحية view_profiles
        if (Auth::id() != $user->id && !Auth::user()->can('employees.view_profiles')) {
            abort(403, 'غير مصرح لك باستعراض هذه الوثيقة.');
        }

        $allowedTypes = ['national_id_card', 'qualification_certificate', 'other_document', 'profile_picture'];
        if (!in_array($type, $allowedTypes, true)) {
            abort(404, 'نوع الوثيقة غير معروف.');
        }

        if ($type === 'profile_picture') {
            $path = $user->profile_picture;
        } else {
            $docQuery = \App\Models\EmployeeDocument::where('user_id', $user->id)->where('type', $type);
            $doc = $request->query('file')
                ? (clone $docQuery)->where('id', $request->query('file'))->first()
                : $docQuery->orderBy('id')->first();
            $path = $doc?->path;
        }

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'الملف المطلوب غير موجود أو لم يتم رفعه بعد.');
        }

        $fullPath = Storage::disk('public')->path($path);
        $mimeType = Storage::disk('public')->mimeType($path);
        $filename = basename($path);

        if ($request->query('download') == 1) {
            return Storage::disk('public')->download($path, $filename);
        }

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * حفظ ملف مرفوع بطريقة موثوقة (لا تعتمد على getRealPath الذي قد يفشل مع open_basedir أو الملفات المؤقتة).
     */
    private function storeUploadedFile($file, string $dir): string
    {
        $contents = @file_get_contents($file->getPathname());
        if ($contents === false) {
            throw new \RuntimeException('تعذّر قراءة الملف المؤقت المرفوع (' . $file->getPathname() . ').');
        }

        $ext = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');
        $path = trim($dir, '/') . '/' . \Illuminate\Support\Str::random(40) . '.' . $ext;
        Storage::disk('public')->put($path, $contents);

        return $path;
    }

    /**
     * رفع ملف أو صورة بروفايل عبر AJAX بشكل فوري مع استجابة JSON.
     */
    public function uploadDocumentAjax(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $allowedTypes = ['profile_picture', 'national_id_card', 'qualification_certificate', 'other_document'];
        $type = $request->input('type');

        if (!in_array($type, $allowedTypes, true)) {
            return response()->json([
                'success' => false,
                'message' => 'نوع الملف غير صالح.'
            ], 422);
        }

        // التحقق من الصلاحيات:
        // صورة البروفايل: يمكن للموظف نفسه رفعها أو للـ HR/Admin
        // الوثائق الرسمية الـ 3: للـ HR/Admin فقط حصراً
        if ($type === 'profile_picture') {
            $canAvatar = Auth::id() == $user->id || Auth::user()->can('employees.avatar_manage');

            if (!$canAvatar) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك بتحديث صورة هذا الموظف.'
                ], 403);
            }
        } else {
            $canUploadDocs = Auth::user()->can('employees.documents_upload');

            if (!$canUploadDocs) {
                return response()->json([
                    'success' => false,
                    'message' => 'غير مصرح لك برفع وثائق الموظفين (يلزم تفعيل صلاحية رفع الوثائق في لوحة التحكم).'
                ], 403);
            }
        }

        if (!$request->hasFile('file')) {
            $raw = $_FILES['file']['error'] ?? null;
            $limit = ini_get('upload_max_filesize');
            $msg = in_array($raw, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? "حجم الملف أكبر من المسموح في السيرفر ({$limit}). صغّر الصورة أو ارفع الحد في إعدادات PHP."
                : ($raw === null && (int) $request->server('CONTENT_LENGTH') > 0 && empty($_POST)
                    ? "حجم الطلب أكبر من المسموح في السيرفر (post_max_size = " . ini_get('post_max_size') . ")."
                    : 'لم يصل الملف للسيرفر، حاول مرة أخرى.');
            \Log::warning('Employee document upload: file missing', ['php_error' => $raw, 'upload_max_filesize' => $limit, 'post_max_size' => ini_get('post_max_size')]);
            return response()->json([
                'success' => false,
                'message' => $msg
            ], 422);
        }

        $file = $request->file('file');

        try {
            if ($type === 'profile_picture') {
                $request->validate([
                    'file' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
                ], [
                    'file.image' => 'صورة البروفايل يجب أن تكون ملف صورة صالح.',
                    'file.mimes' => 'الصيغ المسموحة: JPG, PNG, WEBP.',
                    'file.max'   => 'أقصى حجم للصورة 5 ميجابايت.',
                ]);

                if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                    Storage::disk('public')->delete($user->profile_picture);
                }

                $path = $this->storeUploadedFile($file, 'profile_pictures');
                $user->update(['profile_picture' => $path]);
            } else {
                $request->validate([
                    'file' => 'file|mimes:jpeg,png,jpg,webp,pdf|max:5120',
                ], [
                    'file.mimes' => 'الصيغ المسموحة للوثائق: صور (JPG, PNG, WEBP) أو ملفات PDF.',
                    'file.max'   => 'أقصى حجم للوثيقة 5 ميجابايت.',
                ]);

                if (EmployeeDocument::where('user_id', $user->id)->where('type', $type)->count() >= 10) {
                    return response()->json([
                        'success' => false,
                        'message' => 'الحد الأقصى 10 ملفات لكل نوع.',
                    ], 422);
                }

                $path = $this->storeUploadedFile($file, "employee_documents/{$user->id}");
                $doc = EmployeeDocument::create([
                    'user_id'       => $user->id,
                    'type'          => $type,
                    'path'          => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }

            $typeLabels = [
                'profile_picture'           => 'الصورة الشخصية',
                'national_id_card'          => 'بطاقة الرقم القومي',
                'qualification_certificate' => 'المؤهل الدراسي',
                'other_document'            => 'الوثائق الأخرى',
            ];

            AuditLog::log(
                Auth::user()->name,
                'Uploaded',
                'Employee Document',
                $user->name,
                "تم رفع {$typeLabels[$type]} للموظف {$user->name}"
            );

            $user->refresh();
            $ext = strtoupper(pathinfo($path, PATHINFO_EXTENSION));
            $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']);
            $url = route('employee_profiles.document', [$user->id, $type]);
            $fileId = null;
            $downloadUrl = $url . '?download=1';
            if (isset($doc)) {
                $fileId = $doc->id;
                $url = $doc->url();
                $downloadUrl = $doc->downloadUrl();
            }

            return response()->json([
                'success'      => true,
                'message'      => "✅ تم رفع {$typeLabels[$type]} بنجاح!",
                'type'         => $type,
                'file_id'      => $fileId,
                'file_name'    => $file->getClientOriginalName(),
                'url'          => $url,
                'download_url' => $downloadUrl,
                'avatar_url'   => $user->profile_picture_url,
                'extension'    => $ext,
                'is_image'     => $isImg,
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json([
                'success' => false,
                'message' => collect($ve->errors())->flatten()->first() ?? 'الملف المختار غير صالح.',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفع الملف: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * حذف وثيقة معينة عبر AJAX بشكل فوري.
     */
    public function deleteDocumentAjax(Request $request, $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $type = $request->input('type');

        if (!in_array($type, ['profile_picture', 'national_id_card', 'qualification_certificate', 'other_document'], true)) {
            return response()->json(['success' => false, 'message' => 'نوع غير صالح.'], 422);
        }

        if ($type === 'profile_picture') {
            $canAvatar = Auth::id() == $user->id || Auth::user()->can('employees.avatar_manage');

            if (!$canAvatar) {
                return response()->json(['success' => false, 'message' => 'غير مصرح لك بحذف الصورة.'], 403);
            }
        } else {
            $canDeleteDocs = Auth::user()->can('employees.documents_delete');

            if (!$canDeleteDocs) {
                return response()->json(['success' => false, 'message' => 'غير مصرح لك بحذف وثائق الموظفين (يلزم تفعيل صلاحية حذف الوثائق في لوحة التحكم).'], 403);
            }
        }

        if ($type === 'profile_picture') {
            if (!empty($user->profile_picture) && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->update(['profile_picture' => null]);
        } else {
            $doc = EmployeeDocument::where('user_id', $user->id)
                ->where('type', $type)
                ->where('id', $request->input('file_id'))
                ->first();

            if (!$doc) {
                return response()->json(['success' => false, 'message' => 'الملف غير موجود.'], 404);
            }

            if (Storage::disk('public')->exists($doc->path)) {
                Storage::disk('public')->delete($doc->path);
            }
            $doc->delete();
        }
        $user->refresh();

        return response()->json([
            'success'    => true,
            'message'    => '🗑️ تم حذف الملف بنجاح!',
            'type'       => $type,
            'avatar_url' => $user->profile_picture_url,
        ]);
    }

    /**
     * تبديل حالة التأمين الاجتماعي للموظف عبر AJAX مباشرة من جدول الموظفين.
     */
    public function toggleInsuranceAjax(Request $request, $id)
    {
        if (!Auth::user()->can('employees.edit_profiles')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل بيانات التأمين للموظفين.'
            ], 403);
        }

        $user = User::findOrFail($id);
        $isInsured = $request->boolean('is_insured');
        $user->is_insured = $isInsured;
        $user->save();

        AuditLog::log(
            Auth::user()->name,
            'Updated',
            'Employee Profile',
            $user->name,
            'تحديث حالة التأمين: ' . ($isInsured ? 'مؤمن عليه' : 'غير مؤمن عليه')
        );

        return response()->json([
            'success'    => true,
            'is_insured' => (bool)$user->is_insured,
            'message'    => $isInsured 
                ? "تم حفظ الموظف «{$user->name}» كمؤمن عليه." 
                : "تم حفظ الموظف «{$user->name}» كغير مؤمن عليه."
        ]);
    }
}


