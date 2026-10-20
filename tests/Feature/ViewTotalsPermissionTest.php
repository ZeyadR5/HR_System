<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    if (!Schema::hasTable('roles')) {
        Schema::create('roles', function ($table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->json('permissions')->nullable();
            $table->timestamps();
        });
    }
    Role::flushCache();
});

test('view_Overtime blade hides totals section when user lacks overtime.view_totals', function () {
    Role::query()->delete();
    Role::create([
        'name' => 'moderator',
        'display_name' => 'Moderator',
        'permissions' => ['overtime.view_all', 'overtime.approve'],
    ]);
    Role::flushCache();

    $user = new User([
        'name' => 'Reviewer User',
        'role' => 'moderator',
    ]);

    $view = $this->actingAs($user)->view('view_Overtime', [
        'Overtime' => collect(),
        'users' => collect(),
        'monthlyTotals' => collect(),
        'periodStart' => now()->startOfMonth(),
        'periodEnd' => now()->endOfMonth(),
        'periodLabel' => 'Current Cycle',
        'selectedPeriodStart' => now()->startOfMonth()->toDateString(),
        'previousPeriodStart' => now()->subMonth()->toDateString(),
        'nextPeriodStart' => now()->addMonth()->toDateString(),
        'isCurrentPeriod' => true,
        'olderPendingCount' => 0,
    ]);

    $view->assertDontSee('Totals Per Employee');
    $view->assertDontSee('OT Amount (EGP)');
});

test('view_Overtime blade shows totals section when user has overtime.view_totals', function () {
    Role::query()->delete();
    Role::create([
        'name' => 'manager',
        'display_name' => 'Manager',
        'permissions' => ['overtime.view_all', 'overtime.approve', 'overtime.view_totals'],
    ]);
    Role::flushCache();

    $user = new User([
        'name' => 'Manager User',
        'role' => 'manager',
    ]);

    $view = $this->actingAs($user)->view('view_Overtime', [
        'Overtime' => collect(),
        'users' => collect(),
        'monthlyTotals' => collect([
            (object) [
                'name' => 'Test Employee',
                'total' => 5.5,
                'user' => (object) ['hourly_rate' => 50],
            ],
        ]),
        'periodStart' => now()->startOfMonth(),
        'periodEnd' => now()->endOfMonth(),
        'periodLabel' => 'Current Cycle',
        'selectedPeriodStart' => now()->startOfMonth()->toDateString(),
        'previousPeriodStart' => now()->subMonth()->toDateString(),
        'nextPeriodStart' => now()->addMonth()->toDateString(),
        'isCurrentPeriod' => true,
        'olderPendingCount' => 0,
    ]);

    $view->assertSee('Totals Per Employee');
    $view->assertSee('OT Amount (EGP)');
    $view->assertSee('Test Employee');
});

test('developer role automatically sees totals section via Gate', function () {
    $user = new User([
        'name' => 'Dev User',
        'role' => 'developer',
    ]);

    $view = $this->actingAs($user)->view('view_Overtime', [
        'Overtime' => collect(),
        'users' => collect(),
        'monthlyTotals' => collect(),
        'periodStart' => now()->startOfMonth(),
        'periodEnd' => now()->endOfMonth(),
        'periodLabel' => 'Current Cycle',
        'selectedPeriodStart' => now()->startOfMonth()->toDateString(),
        'previousPeriodStart' => now()->subMonth()->toDateString(),
        'nextPeriodStart' => now()->addMonth()->toDateString(),
        'isCurrentPeriod' => true,
        'olderPendingCount' => 0,
    ]);

    $view->assertSee('Totals Per Employee');
    $view->assertSee('OT Amount (EGP)');
});

test('Permissions class registers view_totals for overtime, leave, and permission', function () {
    $all = \App\Support\Permissions::all();
    expect($all)->toContain('overtime.view_totals');
    expect($all)->toContain('leave.view_totals');
    expect($all)->toContain('permission.view_totals');

    $groups = \App\Support\Permissions::groups();
    expect($groups['overtime']['permissions'])->toHaveKey('overtime.view_totals');
    expect($groups['leave']['permissions'])->toHaveKey('leave.view_totals');
    expect($groups['permission']['permissions'])->toHaveKey('permission.view_totals');
});
