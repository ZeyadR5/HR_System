<?php

namespace App\Http\Controllers;

use App\Models\AdminNote;
use App\Support\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AdminNoteController extends Controller
{
    // صفحة إضافة ملاحظة
    public function create()
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);
        return view('admin_note');
    }

    // حفظ الملاحظة
    public function store(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        AdminNote::create([
            'note' => $request->note,
            'date' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'تم إضافة الملاحظة بنجاح!');
    }

    // صفحة عرض الملاحظات
    public function index(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $notes = AdminNote::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        return view('view_admin_notes', [
            'notes' => $notes,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'periodLabel' => $period['label'],
            'selectedPeriodStart' => $period['slug'],
            'previousPeriodStart' => $period['previous_start']->format('Y-m-d'),
            'nextPeriodStart' => $period['next_start']->format('Y-m-d'),
            'isCurrentPeriod' => $period['is_current'],
        ]);
    }

    // تصدير Excel
    public function export(Request $request)
    {
        if (!in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $notes = AdminNote::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $filename = 'admin_notes_' . $periodStart->format('Y_m_d') . '_to_' . $periodEnd->format('Y_m_d') . '.xlsx';

        return Excel::download(
            new \App\Exports\AdminNoteExport($notes, $periodStart, $periodEnd),
            $filename
        );
    }
}
