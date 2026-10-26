<?php

namespace App\Http\Controllers;

use App\Models\Incentive;
use App\Models\User;
use App\Support\PayrollPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class IncentiveController extends Controller
{
    // صفحة إضافة حافز
    public function create()
    {
        if (!Auth::user()->can('incentives.create') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $users = User::orderBy('name')->get();

        return view('incentive', compact('users'));
    }

    // حفظ الحافز
    public function store(Request $request)
    {
        if (!Auth::user()->can('incentives.create') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'evaluation' => 'required|string|max:2000',
        ]);

        $user = User::findOrFail($request->user_id);

        Incentive::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'evaluation' => $request->evaluation,
            'date' => now()->toDateString(),
        ]);

        return redirect()->back()->with('success', 'تم إضافة الحافز بنجاح!');
    }

    // صفحة عرض الحوافز
    public function index(Request $request)
    {
        if (!Auth::user()->can('incentives.view') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        // عرض كل الموظفين
        $usersQuery = User::query();

        if ($request->filled('user_id')) {
            $usersQuery->where('id', $request->user_id);
        }

        if ($request->filled('job_title')) {
            $usersQuery->where('job_title', $request->job_title);
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortDir = strtolower($request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'job_title') {
            $usersQuery->orderByRaw('CASE WHEN job_title IS NULL OR job_title = "" THEN 1 ELSE 0 END')
                       ->orderBy('job_title', $sortDir)
                       ->orderBy('name', 'asc');
        } else {
            $usersQuery->orderBy('name', $sortDir);
        }

        $users = $usersQuery->get();

        // قائمة كل الموظفين المتاحين للفلترة
        $filterUsers = User::orderBy('name')->get();

        $jobTitles = User::whereNotNull('job_title')
            ->where('job_title', '!=', '')
            ->distinct()
            ->pluck('job_title')
            ->sort()
            ->values();

        $query = Incentive::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $incentives = $query->get();

        return view('view_incentives', [
            'incentives' => $incentives,
            'users' => $users,
            'filterUsers' => $filterUsers,
            'jobTitles' => $jobTitles,
            'periodStart' => $periodStart,
            'periodEnd' => $periodEnd,
            'periodLabel' => $period['label'],
            'selectedPeriodStart' => $period['slug'],
            'previousPeriodStart' => $period['previous_start']->format('Y-m-d'),
            'nextPeriodStart' => $period['next_start']->format('Y-m-d'),
            'isCurrentPeriod' => $period['is_current'],
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    // تصدير Excel
    public function export(Request $request)
    {
        if (!Auth::user()->can('incentives.export') && !in_array(Auth::user()->role, ['super_admin', 'developer'])) abort(403);

        $period = PayrollPeriod::fromRequest($request->query('period_start'));
        $periodStart = $period['start'];
        $periodEnd = $period['end'];

        $incentives = Incentive::whereBetween('date', [
                $periodStart->toDateString(),
                $periodEnd->toDateString(),
            ])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->get();

        $filename = 'incentives_' . $periodStart->format('Y_m_d') . '_to_' . $periodEnd->format('Y_m_d') . '.xlsx';

        return Excel::download(
            new \App\Exports\IncentiveExport($incentives, $periodStart, $periodEnd),
            $filename
        );
    }
}
