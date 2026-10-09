<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\Settings;

class DeveloperSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'payroll_start_day' => Settings::get('payroll_start_day', 26),
            'payroll_end_day' => Settings::get('payroll_end_day', 25),
            'annual_leaves_limit' => Settings::get('annual_leaves_limit', 14),
            'monthly_leaves_limit' => Settings::get('monthly_leaves_limit', 2),
            'monthly_permissions_limit' => Settings::get('monthly_permissions_limit', 4),
            'monthly_permissions_hours' => Settings::get('monthly_permissions_hours', 3),
            'daily_overtime_limit' => Settings::get('daily_overtime_limit', 5),
            'settlement_start_day' => Settings::get('settlement_start_day', 1),
            'settlement_end_day' => Settings::get('settlement_end_day', 10),
        ];

        return view('developer.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'payroll_start_day' => 'required|integer|min:1|max:31',
            'payroll_end_day' => 'required|integer|min:1|max:31',
            'annual_leaves_limit' => 'required|integer|min:0',
            'monthly_leaves_limit' => 'required|integer|min:0',
            'monthly_permissions_limit' => 'required|integer|min:0',
            'monthly_permissions_hours' => 'required|integer|min:0',
            'daily_overtime_limit' => 'required|integer|min:0',
            'settlement_start_day' => 'required|integer|min:1|max:31',
            'settlement_end_day' => 'required|integer|min:1|max:31',
        ]);

        Settings::set('payroll_start_day', $request->payroll_start_day);
        Settings::set('payroll_end_day', $request->payroll_end_day);
        Settings::set('annual_leaves_limit', $request->annual_leaves_limit);
        Settings::set('monthly_leaves_limit', $request->monthly_leaves_limit);
        Settings::set('monthly_permissions_limit', $request->monthly_permissions_limit);
        Settings::set('monthly_permissions_hours', $request->monthly_permissions_hours);
        Settings::set('daily_overtime_limit', $request->daily_overtime_limit);
        Settings::set('settlement_start_day', $request->settlement_start_day);
        Settings::set('settlement_end_day', $request->settlement_end_day);

        return back()->with('success', '✅ تم حفظ إعدادات النظام بنجاح! جميع العمليات ستعتمد على هذه الأرقام من الآن.');
    }
}
