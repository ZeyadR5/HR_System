<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penalty;
use App\Models\Settlement;
use App\Models\AuditLog;
use App\Models\User;
use App\Support\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PenaltyController extends Controller
{
    // ========================================================
    // PENALTY OPERATIONS
    // ========================================================

    /**
     * Get penalties for current user or filtered by period
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = Penalty::whereBetween('created_at', [
                $periodStart->toDateString() . ' 00:00:00',
                $periodEnd->toDateString() . ' 23:59:59',
            ]);

        if (in_array($user->role, ['admin', 'super_admin', 'developer'])) {
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->where('user_id', $user->id);
        }

        $records = $query->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'data'    => $records
        ], 200);
    }

    /**
     * Admin/Super-Admin Add Penalty directly
     */
    public function store(Request $request)
    {
        $admin = Auth::user();
        if (!in_array($admin->role, ['admin', 'super_admin', 'developer'])) {
            return response()->json([
                'success' => false,
                'message' => 'عفواً، لا تملك صلاحية القيام بهذا الإجراء.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'reason'  => 'required|string|max:500',
            'amount'  => 'required|numeric|min:0',
            'notes'   => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات الجزاء غير صالحة',
                'errors'  => $validator->errors()
            ], 422);
        }

        $employee = User::findOrFail($request->user_id);

        $penalty = Penalty::create([
            'user_id'     => $employee->id,
            'name'        => $employee->name,
            'reason'      => $request->reason,
            'amount'      => $request->amount,
            'notes'       => $request->notes,
            'status'      => 'accepted',
            'actioned_by' => $admin->name,
        ]);

        if ($penalty) {
            AuditLog::log($admin->name, 'Created', 'Penalty', $employee->name, "Amount: {$request->amount}, Reason: {$request->reason}");
            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الجزاء بنجاح!',
                'data'    => $penalty
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء إضافة الجزاء'
        ], 500);
    }

    /**
     * Super Admin view pending penalties
     */
    public function superAdminIndex(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح للوصول لهذه الصفحة'
            ], 403);
        }

        $records = Penalty::orderByDesc('created_at')->paginate(20);
        return response()->json(['success' => true, 'data' => $records], 200);
    }

    /**
     * Super Admin Accept Penalty
     */
    public function penaltyAccept($id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح للوصول لهذه الصفحة'
            ], 403);
        }

        $penalty = Penalty::findOrFail($id);
        $penalty->update([
            'status'      => 'accepted',
            'actioned_by' => $admin->name,
        ]);

        AuditLog::log($admin->name, 'Accepted', 'Penalty', $penalty->name, "Amount: {$penalty->amount}");

        return response()->json([
            'success' => true,
            'message' => 'تم قبول الجزاء بنجاح'
        ], 200);
    }

    /**
     * Super Admin Refuse Penalty
     */
    public function penaltyRefuse(Request $request, $id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح للوصول لهذه الصفحة'
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

        $penalty = Penalty::findOrFail($id);
        $penalty->update([
            'status'        => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by'   => $admin->name,
        ]);

        AuditLog::log($admin->name, 'Refused', 'Penalty', $penalty->name, "Amount: {$penalty->amount}, Reason: {$request->refuse_reason}");

        return response()->json([
            'success' => true,
            'message' => 'تم رفض الجزاء بنجاح'
        ], 200);
    }


    // ========================================================
    // SETTLEMENT OPERATIONS (التسويات)
    // ========================================================

    /**
     * Get Settlement history
     */
    public function settlementIndex(Request $request)
    {
        $user = Auth::user();
        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $query = Settlement::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ]);

        if ($user->role === 'super_admin') {
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
     * Submit Settlement
     */
    public function settlementStore(Request $request)
    {
        $user = Auth::user();
        $dayOfMonth = now()->day;

        // Restriction: Day 1 to 10 unless super_admin
        if ($user->role !== 'super_admin' && ($dayOfMonth < 1 || $dayOfMonth > 10)) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ عذراً، تقديم طلبات التسوية متاح فقط من يوم 1 إلى يوم 10 من كل شهر.'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'note' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ملاحظات التسوية مطلوبة',
                'errors'  => $validator->errors()
            ], 422);
        }

        $settlement = Settlement::create([
            'user_id' => $user->id,
            'name'    => $user->name,
            'note'    => $request->note,
            'date'    => now()->toDateString(),
            'day'     => now()->locale('ar')->dayName,
            'status'  => 'pending'
        ]);

        if ($settlement) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال التسوية بنجاح!',
                'data'    => $settlement
            ], 201);
        }

        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ أثناء تقديم الطلب'
        ], 500);
    }

    /**
     * Super Admin Settlements list
     */
    public function superAdminSettlementIndex(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح للوصول لهذه الصفحة'
            ], 403);
        }

        $records = Settlement::orderByDesc('date')->orderByDesc('created_at')->paginate(20);
        return response()->json(['success' => true, 'data' => $records], 200);
    }

    /**
     * Super Admin Accept Settlement
     */
    public function settlementAccept(Request $request, $id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح للوصول لهذه الصفحة'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'accept_note' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'بيانات القبول غير صالحة',
                'errors'  => $validator->errors()
            ], 422);
        }

        $settlement = Settlement::findOrFail($id);
        $settlement->update([
            'status'      => 'accepted',
            'accept_note' => $request->accept_note,
            'actioned_by' => $admin->name,
        ]);

        AuditLog::log($admin->name, 'Accepted', 'Settlement', $settlement->name, "Note: {$settlement->note}");

        return response()->json([
            'success' => true,
            'message' => 'تم قبول التسوية بنجاح'
        ], 200);
    }

    /**
     * Super Admin Refuse Settlement
     */
    public function settlementRefuse(Request $request, $id)
    {
        $admin = Auth::user();
        if ($admin->role !== 'super_admin') {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح للوصول لهذه الصفحة'
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

        $settlement = Settlement::findOrFail($id);
        $settlement->update([
            'status'        => 'refused',
            'refuse_reason' => $request->refuse_reason,
            'actioned_by'   => $admin->name,
        ]);

        AuditLog::log($admin->name, 'Refused', 'Settlement', $settlement->name, "Reason: {$request->refuse_reason}");

        return response()->json([
            'success' => true,
            'message' => 'تم رفض التسوية بنجاح'
        ], 200);
    }
}
