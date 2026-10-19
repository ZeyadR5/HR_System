<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Overtime;
use App\Models\Leave;
use App\Models\Permission;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\PayrollPeriod;
use App\Support\SubmissionWindow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RequestController extends Controller
{
    // ========================================================
    // EMPLOYEE OPERATIONS
    // ========================================================

    /**
     * Get Overtime History
     */
    public function overtimeIndex(Request $request)
    {
        $user = Auth::user();
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = Overtime::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ]);

        if (in_array($user->role, ['admin', 'super_admin', 'developer'])) {
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $records = $query->orderByDesc('date')->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => $records
        ], 200);
    }

    /**
     * Submit Overtime Request
     */
    public function overtimeStore(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'date'   => 'required|date',
            'day'    => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
            'from'   => 'required|date_format:H:i',
            'to'     => 'required|date_format:H:i|after:from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الطلب غير صالحة',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            SubmissionWindow::assertDateWithinAllowedWindow($request->date, 'Overtime date');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors()
            ], 422);
        }

        $fromTime = Carbon::createFromFormat('H:i', $request->from);
        $toTime = Carbon::createFromFormat('H:i', $request->to);
        $total_hours = round($fromTime->diffInMinutes($toTime) / 60, 2);

        // Prevent submitting overtime before it ends
        $overtimeEndTime = Carbon::parse($request->date . ' ' . $request->to);
        if ($overtimeEndTime->isFuture()) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ لا يمكنك تقديم طلب العمل الإضافي قبل أن ينتهي وقته الفعلي.'
            ], 400);
        }

        // Prevent submitting overtime within scheduled shift hours
        if ($user && $user->shiftGroup) {
            $shift = $user->shiftGroup;
            if ($shift->overlapsWith($request->from, $request->to)) {
                return response()->json([
                    'success' => false,
                    'message' => "🛑 عذراً، لا يمكنك تسجيل وقت إضافي (من {$request->from} إلى {$request->to}) لأنه يقع داخل ساعات شيفتك المسجل في «{$shift->name}» ({$shift->formatted_hours}). الإضافي يُحتسب فقط قبل بداية الشيفت أو بعد نهايته."
                ], 422);
            }
        }

        // Check Daily Overtime Limit (Max 5 hours)
        $dayUsage = Overtime::where('user_id', $user->id)
            ->where('date', $request->date)
            ->where('status', '!=', 'refused')
            ->sum('total_hours');

        $dailyLimit = \App\Support\Settings::get('daily_overtime_limit', 5);
        if (($dayUsage + $total_hours) > $dailyLimit) {
            $remaining = $dailyLimit - $dayUsage;
            if ($remaining < 0) $remaining = 0;
            return response()->json([
                'success' => false,
                'message' => "⚠️ عذراً، لقد تخطيت الحد المسموح به للإضافي لهذا اليوم ({$dailyLimit} ساعات). المتبقي لك هو {$remaining} ساعة فقط."
            ], 400);
        }

        $name = ($user->email === 'guest@gamma.com' && $request->has('name')) 
            ? $request->name 
            : $user->name;

        $over = Overtime::create([
            'user_id'     => $user->id,
            'name'        => $name,
            'date'        => $request->date,
            'day'         => $request->day,
            'total_hours' => $total_hours,
            'reason'      => $request->reason,
            'from'        => $request->from,
            'to'          => $request->to,
            'status'      => 'pending'
        ]);

        if ($over) {
            AuditLog::log($user->name, 'Created', 'Overtime', $name, "Hours: {$total_hours}, Date: {$request->date}");
            return response()->json([
                'success' => true,
                'message' => 'تم تقديم طلب العمل الإضافي بنجاح!',
                'data'    => $over
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء تقديم الطلب'
        ], 500);
    }

    /**
     * Get Leaves History
     */
    public function leaveIndex(Request $request)
    {
        $user = Auth::user();
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = Leave::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ]);

        if (in_array($user->role, ['admin', 'super_admin', 'developer'])) {
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $records = $query->orderByDesc('date')->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => $records
        ], 200);
    }

    /**
     * Submit Leave Request
     */
    public function leaveStore(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'date'       => 'required|date',
            'day'        => 'required|string',
            'reason'     => 'required|string|max:1000',
            'substitute' => 'required|string|max:255',
            'days_count' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الإجازة غير صالحة',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            SubmissionWindow::assertDateWithinAllowedWindow($request->date, 'Leave date');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors()
            ], 422);
        }

        $requestDate = Carbon::parse($request->date);
        $period = PayrollPeriod::fromRequest($request->date);

        // 1. Check Monthly Limit (Max 2 days per cycle)
        $monthUsage = Leave::where('user_id', $user->id)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        if (($monthUsage + $request->days_count) > 2) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ تخطيت الحد المسموح به خلال الشهر وهو يومين، يرجى الرجوع إلى المسؤول.'
            ], 400);
        }

        // 2. Check Yearly Limit (Max 14 days per year)
        $yearUsage = Leave::where('user_id', $user->id)
            ->whereYear('date', $requestDate->year)
            ->where('status', '!=', 'refused')
            ->sum('days_count');

        if (($yearUsage + $request->days_count) > 14) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ عذراً، رصيد إجازاتك السنوي (14 يوم) قد انتهى.'
            ], 400);
        }

        $name = ($user->email === 'guest@gamma.com' && $request->has('name')) 
            ? $request->name 
            : $user->name;

        $leave = Leave::create([
            'user_id'    => $user->id,
            'name'       => $name,
            'date'       => $request->date,
            'day'        => $request->day,
            'reason'     => $request->reason,
            'substitute' => $request->substitute,
            'days_count' => $request->days_count,
            'status'     => 'pending'
        ]);

        if ($leave) {
            AuditLog::log($user->name, 'Created', 'Leave', $name, "Date: {$request->date}");
            return response()->json([
                'success' => true,
                'message' => 'تم تقديم طلب الإجازة بنجاح!',
                'data'    => $leave
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء تقديم الطلب'
        ], 500);
    }

    /**
     * Get Permissions History
     */
    public function permissionIndex(Request $request)
    {
        $user = Auth::user();
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = Permission::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ]);

        if (in_array($user->role, ['admin', 'super_admin', 'developer'])) {
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $records = $query->orderByDesc('date')->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => $records
        ], 200);
    }

    /**
     * Submit Permission Request
     */
    public function permissionStore(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'date'            => 'required|date',
            'day'             => 'required|string',
            'reason'          => 'required|string|max:1000',
            'permission_type' => 'required|string|in:إذن تأخير,إذن انصراف باكر,إذن نسيان بصمة حضور,إذن نسيان بصمة انصراف',
            'from'            => 'nullable|date_format:H:i|required_if:permission_type,إذن تأخير,إذن انصراف باكر',
            'to'              => 'nullable|date_format:H:i|after:from|required_if:permission_type,إذن تأخير,إذن انصراف باكر',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الإذن غير صالحة',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            SubmissionWindow::assertDateWithinAllowedWindow($request->date, 'Permission date');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors()
            ], 422);
        }

        // Check if the permission exceeds 3 hours limit per month (180 minutes)
        $requestedMinutes = 0;
        if ($request->filled('from') && $request->filled('to')) {
            $fromTime = \Carbon\Carbon::parse($request->from);
            $toTime   = \Carbon\Carbon::parse($request->to);
            $requestedMinutes = $fromTime->diffInMinutes($toTime);
        }

        $period = \App\Support\PayrollPeriod::fromRequest($request->date);
        $permissions = Permission::where('user_id', $user->id)
            ->whereBetween('date', [$period['start']->toDateString(), $period['end']->toDateString()])
            ->where('status', '!=', 'refused')
            ->get();
        
        $totalMinutes = 0;
        foreach ($permissions as $p) {
            if ($p->from && $p->to) {
                $p_from = \Carbon\Carbon::parse($p->from);
                $p_to   = \Carbon\Carbon::parse($p->to);
                $totalMinutes += $p_from->diffInMinutes($p_to);
            }
        }

        $maxMinutes = \App\Support\Settings::get('monthly_permissions_hours', 3) * 60;
        if (($totalMinutes + $requestedMinutes) > $maxMinutes) {
            $maxHours = \App\Support\Settings::get('monthly_permissions_hours', 3);
            return response()->json([
                'success' => false,
                'message' => "⚠️ عذراً، لا يمكنك تقديم هذا الإذن لأنك ستتخطى الحد الأقصى المسموح به للأذونات وهو {$maxHours} ساعات شهرياً."
            ], 400);
        }

        $name = ($user->email === 'guest@gamma.com' && $request->has('name')) 
            ? $request->name 
            : $user->name;

        $perm = Permission::create([
            'user_id'         => $user->id,
            'name'            => $name,
            'date'            => $request->date,
            'day'             => $request->day,
            'reason'          => $request->reason,
            'from'            => $request->from,
            'to'              => $request->to,
            'permission_type' => $request->permission_type,
            'status'          => 'pending'
        ]);

        if ($perm) {
            AuditLog::log($user->name, 'Created', 'Permission', $name, "Type: {$request->permission_type}, Date: {$request->date}");
            return response()->json([
                'success' => true,
                'message' => 'تم تقديم طلب الإذن بنجاح!',
                'data'    => $perm
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء تقديم الطلب'
        ], 500);
    }


    // ========================================================
    // ADMIN OPERATIONS
    // ========================================================

    /**
     * Admin: List pending Overtime
     */
    public function adminOvertimeIndex(Request $request)
    {
        $records = Overtime::orderByDesc('date')->orderByDesc('created_at')->paginate(20);
        return response()->json(['success' => true, 'data' => $records], 200);
    }

    /**
     * Admin: Accept Overtime
     */
    public function overtimeAccept($id)
    {
        $user = Auth::user();
        $record = Overtime::findOrFail($id);
        $record->update([
            'status'      => 'accepted',
            'actioned_by' => $user->name,
        ]);

        AuditLog::log($user->name, 'Accepted', 'Overtime', $record->name, "Hours: {$record->total_hours}, Date: {$record->date}");

        return response()->json(['success' => true, 'message' => 'تم قبول طلب العمل الإضافي بنجاح'], 200);
    }

    /**
     * Admin: Refuse Overtime
     */
    public function overtimeRefuse(Request $request, $id)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'refuse_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'يرجى كتابة سبب الرفض', 'errors' => $validator->errors()], 422);
        }

        $record = Overtime::findOrFail($id);
        $record->update([
            'status'        => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by'   => $user->name,
        ]);

        AuditLog::log($user->name, 'Refused', 'Overtime', $record->name, "Reason: {$request->refuse_reason}, Date: {$record->date}");

        return response()->json(['success' => true, 'message' => 'تم رفض طلب العمل الإضافي بنجاح'], 200);
    }

    /**
     * Admin: List Leaves
     */
    public function adminLeaveIndex(Request $request)
    {
        $records = Leave::orderByDesc('date')->orderByDesc('created_at')->paginate(20);
        return response()->json(['success' => true, 'data' => $records], 200);
    }

    /**
     * Admin: Accept Leave
     */
    public function leaveAccept($id)
    {
        $user = Auth::user();
        $record = Leave::findOrFail($id);
        $record->update([
            'status'      => 'accepted',
            'actioned_by' => $user->name,
        ]);
        
        AuditLog::log($user->name, 'Accepted', 'Leave', $record->name, "Date: {$record->date}");

        return response()->json(['success' => true, 'message' => 'تم قبول طلب الإجازة بنجاح'], 200);
    }

    /**
     * Admin: Refuse Leave
     */
    public function leaveRefuse(Request $request, $id)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'refuse_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'يرجى كتابة سبب الرفض', 'errors' => $validator->errors()], 422);
        }

        $record = Leave::findOrFail($id);
        $record->update([
            'status'        => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by'   => $user->name,
        ]);

        AuditLog::log($user->name, 'Refused', 'Leave', $record->name, "Date: {$record->date}, Reason: {$request->refuse_reason}");
        
        return response()->json(['success' => true, 'message' => 'تم رفض طلب الإجازة بنجاح'], 200);
    }

    /**
     * Admin: List Permissions
     */
    public function adminPermissionIndex(Request $request)
    {
        $records = Permission::orderByDesc('date')->orderByDesc('created_at')->paginate(20);
        return response()->json(['success' => true, 'data' => $records], 200);
    }

    /**
     * Admin: Accept Permission
     */
    public function permissionAccept($id)
    {
        $user = Auth::user();
        $record = Permission::findOrFail($id);
        $record->update([
            'status'      => 'accepted',
            'actioned_by' => $user->name,
        ]);

        AuditLog::log($user->name, 'Accepted', 'Permission', $record->name, "Type: {$record->permission_type}, Date: {$record->date}");

        return response()->json(['success' => true, 'message' => 'تم قبول طلب الإذن بنجاح'], 200);
    }

    /**
     * Admin: Refuse Permission
     */
    public function permissionRefuse(Request $request, $id)
    {
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'refuse_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'يرجى كتابة سبب الرفض', 'errors' => $validator->errors()], 422);
        }

        $record = Permission::findOrFail($id);
        $record->update([
            'status'        => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by'   => $user->name,
        ]);

        AuditLog::log($user->name, 'Refused', 'Permission', $record->name, "Type: {$record->permission_type}, Date: {$record->date}, Reason: {$request->refuse_reason}");

        return response()->json(['success' => true, 'message' => 'تم رفض طلب الإذن بنجاح'], 200);
    }
}
