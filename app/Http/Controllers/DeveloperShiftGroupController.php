<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ShiftGroup;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeveloperShiftGroupController extends Controller
{
    public const COLORS = [
        '#2563eb', '#0d9488', '#ea580c', '#7c3aed',
        '#dc2626', '#16a34a', '#db2777', '#0891b2',
        '#ca8a04', '#64748b',
    ];

    public const ICONS = ['☀️', '🌤️', '🌆', '🌙', '⭐', '⚡', '🏥', '🔬', '📋', '⏰'];

    public function index(Request $request)
    {
        $shifts = ShiftGroup::withCount('users')->orderBy('from_time')->get();

        $usersQuery = User::query()->with('shiftGroup')->orderBy('name');

        if ($request->filled('q')) {
            $q = $request->q;
            $usersQuery->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('shift')) {
            if ($request->shift === 'unassigned') {
                $usersQuery->whereNull('shift_group_id');
            } else {
                $usersQuery->where('shift_group_id', $request->shift);
            }
        }

        $users = $usersQuery->paginate(25)->withQueryString();

        $totalAssigned = User::whereNotNull('shift_group_id')->count();
        $totalUnassigned = User::whereNull('shift_group_id')->count();

        return view('developer.shifts.index', compact(
            'shifts',
            'users',
            'totalAssigned',
            'totalUnassigned'
        ));
    }

    public function create()
    {
        $shift = new ShiftGroup([
            'color'     => '#2563eb',
            'icon'      => '⏰',
            'from_time' => '09:00',
            'to_time'   => '16:00',
        ]);

        return view('developer.shifts.form', [
            'shift'  => $shift,
            'colors' => self::COLORS,
            'icons'  => self::ICONS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:shift_groups,name',
            'from_time'   => 'required|date_format:H:i',
            'to_time'     => 'required|date_format:H:i',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        $shift = ShiftGroup::create($data);

        AuditLog::log(
            Auth::user()->name,
            'Created',
            'ShiftGroup',
            $shift->name,
            "Hours: {$shift->from_time} - {$shift->to_time}"
        );

        return redirect()->route('developer.shifts.index')->with('success', "تم إنشاء {$shift->name} بنجاح.");
    }

    public function edit(ShiftGroup $shift)
    {
        return view('developer.shifts.form', [
            'shift'  => $shift,
            'colors' => self::COLORS,
            'icons'  => self::ICONS,
        ]);
    }

    public function update(Request $request, ShiftGroup $shift)
    {
        $data = $request->validate([
            'name'        => "required|string|max:100|unique:shift_groups,name,{$shift->id}",
            'from_time'   => 'required|date_format:H:i',
            'to_time'     => 'required|date_format:H:i',
            'color'       => 'nullable|string|max:20',
            'icon'        => 'nullable|string|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        $shift->update($data);

        AuditLog::log(
            Auth::user()->name,
            'Updated',
            'ShiftGroup',
            $shift->name,
            "Hours: {$shift->from_time} - {$shift->to_time}"
        );

        return redirect()->route('developer.shifts.index')->with('success', "تم تحديث بيانات {$shift->name} بنجاح.");
    }

    public function destroy(ShiftGroup $shift)
    {
        $name = $shift->name;
        // Dissociate members
        User::where('shift_group_id', $shift->id)->update(['shift_group_id' => null]);
        $shift->delete();

        AuditLog::log(Auth::user()->name, 'Deleted', 'ShiftGroup', $name, "Shift removed");

        return redirect()->route('developer.shifts.index')->with('success', "تم حذف {$name} وإلغاء ارتباط أعضائه.");
    }

    /**
     * تغيير شيفت موظف سريعاً
     */
    public function assignUser(Request $request, User $user)
    {
        $request->validate([
            'shift_group_id' => 'nullable|exists:shift_groups,id',
        ]);

        $oldShift = $user->shiftGroup?->name ?? 'غير محدد';
        $user->shift_group_id = $request->shift_group_id ?: null;
        $user->save();

        $newShift = $user->fresh()->shiftGroup?->name ?? 'غير محدد';

        AuditLog::log(
            Auth::user()->name,
            'Updated',
            'UserShiftGroup',
            $user->name,
            "Shift changed from {$oldShift} to {$newShift}"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "تم تحديث شيفت {$user->name} إلى {$newShift}.",
                'shift'   => $user->fresh()->shiftGroup,
            ]);
        }

        return redirect()->back()->with('success', "تم تعيين شيفت {$user->name} إلى: {$newShift}.");
    }
}
