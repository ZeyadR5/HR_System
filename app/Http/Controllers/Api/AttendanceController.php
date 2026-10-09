<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CheckInOut;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Check permission helper based on web controller logic
     */
    private function canManageAttendance($user)
    {
        return $user->role === 'super_admin' || 
               in_array($user->email, [
                   'hend@gama.com', 
                   'RawanEssam@gamma.com', 
                   'esraa.abdulla30@gmail.com'
               ]);
    }

    /**
     * View manual attendance log for current cycle (paginated)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Define cycle using web logic or basic period query
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = CheckInOut::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($this->canManageAttendance($user) || $user->role === 'admin') {
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $records = $query->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'records'      => $records,
                'period_start' => $periodStart->toDateString(),
                'period_end'   => $periodEnd->toDateString(),
                'period_label' => $period['label'],
            ]
        ], 200);
    }

    /**
     * Store check-in / check-out
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Specific authorization logic from web controller
        if (!$this->canManageAttendance($user)) {
            return response()->json([
                'success' => false,
                'message' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:حضور,انصراف',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'نوع الحركة غير صالح (يجب أن يكون "حضور" أو "انصراف")',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Check if movement already exists for today
        $existing = CheckInOut::where('user_id', $user->id)
            ->where('date', now()->toDateString())
            ->where('type', $request->type)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'لقد قمت بتسجيل ' . $request->type . ' بالفعل اليوم.'
            ], 400);
        }

        $attendance = CheckInOut::create([
            'user_id'     => $user->id,
            'name'        => $user->name,
            'date'        => now()->toDateString(),
            'day'         => now()->locale('ar')->dayName,
            'type'        => $request->type,
            'status'      => 'accepted',
            'actioned_by' => 'نظام تلقائي',
        ]);

        if ($attendance) {
            AuditLog::log($user->name, 'Created', 'CheckInOut', $user->name, "Type: {$request->type}, Date: " . now()->toDateString());
            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل ال' . $request->type . ' بنجاح!',
                'data'    => $attendance
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء تسجيل الحركة'
        ], 500);
    }

    /**
     * Admin view to get all attendance records (with optional filter)
     */
    public function adminIndex(Request $request)
    {
        if (!in_array(Auth::user()->role, ['admin', 'super_admin', 'developer'])) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح للوصول لهذه الصفحة'
            ], 403);
        }

        $query = CheckInOut::orderByDesc('date')->orderByDesc('created_at');

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $records = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $records
        ], 200);
    }

    /**
     * Admin Accept Attendance
     */
    public function accept($id)
    {
        $user = Auth::user();
        if (!$this->canManageAttendance($user)) {
            return response()->json([
                'success' => false,
                'message' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.'
            ], 403);
        }

        $record = CheckInOut::findOrFail($id);
        $record->update([
            'status'      => 'accepted',
            'actioned_by' => $user->name,
        ]);

        AuditLog::log($user->name, 'Accepted', 'CheckInOut', $record->name, "Type: {$record->type}, Date: {$record->date}");

        return response()->json([
            'success' => true,
            'message' => 'تم قبول حركة الحضور/الانصراف بنجاح'
        ], 200);
    }

    /**
     * Admin Refuse Attendance
     */
    public function refuse(Request $request, $id)
    {
        $user = Auth::user();
        if (!$this->canManageAttendance($user)) {
            return response()->json([
                'success' => false,
                'message' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'refuse_reason' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى كتابة سبب الرفض',
                'errors'  => $validator->errors()
            ], 422);
        }

        $record = CheckInOut::findOrFail($id);
        $record->update([
            'status'        => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by'   => $user->name,
        ]);

        AuditLog::log($user->name, 'Refused', 'CheckInOut', $record->name, "Reason: {$request->refuse_reason}, Date: {$record->date}");

        return response()->json([
            'success' => true,
            'message' => 'تم رفض حركة الحضور/الانصراف بنجاح'
        ], 200);
    }
}
