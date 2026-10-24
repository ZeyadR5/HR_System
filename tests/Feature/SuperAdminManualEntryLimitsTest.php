<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PayrollPeriod;
use App\Support\Settings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperAdminManualEntryLimitsTest extends TestCase
{
    protected User $supervisor;
    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function ($table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name');
                $table->json('permissions')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password')->nullable();
                $table->string('role')->default('user');
                $table->string('job_title')->nullable();
                $table->decimal('hourly_rate', 10, 2)->default(0);
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function ($table) {
                $table->id();
                $table->string('user_name')->nullable();
                $table->string('action')->nullable();
                $table->string('module')->nullable();
                $table->string('target')->nullable();
                $table->text('details')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('leaves')) {
            Schema::create('leaves', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name')->nullable();
                $table->date('date')->nullable();
                $table->string('day')->nullable();
                $table->text('reason')->nullable();
                $table->string('substitute')->nullable();
                $table->integer('days_count')->default(1);
                $table->string('status')->default('accepted');
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name')->nullable();
                $table->date('date')->nullable();
                $table->string('day')->nullable();
                $table->text('reason')->nullable();
                $table->string('from')->nullable();
                $table->string('to')->nullable();
                $table->string('permission_type')->nullable();
                $table->string('status')->default('accepted');
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('overtimes')) {
            Schema::create('overtimes', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name')->nullable();
                $table->date('date')->nullable();
                $table->string('day')->nullable();
                $table->text('reason')->nullable();
                $table->string('from')->nullable();
                $table->string('to')->nullable();
                $table->decimal('total_hours', 8, 2)->default(0);
                $table->string('status')->default('accepted');
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        // Clean tables
        User::query()->delete();
        Leave::query()->delete();
        Permission::query()->delete();
        Overtime::query()->delete();
        AuditLog::query()->delete();

        Role::flushCache();
        Role::firstOrCreate(['name' => 'super_admin'], [
            'display_name' => 'Super Admin',
            'permissions' => [
                'employees.manual_entry',
            ],
        ]);
        Role::firstOrCreate(['name' => 'user'], [
            'display_name' => 'Employee',
            'permissions' => [],
        ]);

        $this->supervisor = User::create([
            'name'     => 'المشرف المسؤول',
            'email'    => 'supervisor@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'super_admin',
        ]);

        $this->employee = User::create([
            'name'     => 'أحمد محمود',
            'email'    => 'ahmed_limits@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);
    }

    public function test_check_limits_returns_user_balances_and_detects_monthly_leave_breach(): void
    {
        $date = '2026-10-20';
        $period = PayrollPeriod::fromRequest($date);

        // Employee already took 2 leaves in this period (max monthly limit is 2)
        Leave::create([
            'user_id'     => $this->employee->id,
            'name'        => $this->employee->name,
            'date'        => $period['start']->copy()->addDays(2)->toDateString(),
            'day'         => 'الاثنين',
            'reason'      => 'إجازة سابقة 1',
            'substitute'  => 'زميل',
            'days_count'  => 1,
            'status'      => 'accepted',
        ]);
        Leave::create([
            'user_id'     => $this->employee->id,
            'name'        => $this->employee->name,
            'date'        => $period['start']->copy()->addDays(5)->toDateString(),
            'day'         => 'الخميس',
            'reason'      => 'إجازة سابقة 2',
            'substitute'  => 'زميل',
            'days_count'  => 1,
            'status'      => 'accepted',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->getJson(route('super_admin.entry.check_limits', [
                'employee_name' => $this->employee->name,
                'entry_type'    => 'leave',
                'date'          => $date,
                'days_count'    => 1,
            ]));

        $response->assertStatus(200);
        $response->assertJson([
            'has_user'    => true,
            'user_name'   => $this->employee->name,
            'is_breached' => true,
        ]);

        $this->assertStringContainsString('استنفد رصيد إجازاته الشهري بالكامل', $response->json('warnings.0'));
    }

    public function test_check_limits_detects_annual_leave_breach(): void
    {
        $date = '2026-10-20';
        $annualLimit = Settings::get('annual_leaves_limit', 14);

        // Add leaves totaling 14 days in current year
        Leave::create([
            'user_id'     => $this->employee->id,
            'name'        => $this->employee->name,
            'date'        => '2026-02-10',
            'day'         => 'الثلاثاء',
            'reason'      => 'إجازة سنوية',
            'substitute'  => 'زميل',
            'days_count'  => $annualLimit,
            'status'      => 'accepted',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->getJson(route('super_admin.entry.check_limits', [
                'employee_name' => $this->employee->name,
                'entry_type'    => 'leave',
                'date'          => $date,
                'days_count'    => 1,
            ]));

        $response->assertStatus(200);
        $response->assertJson([
            'is_breached' => true,
        ]);

        $warnings = implode(' ', $response->json('warnings'));
        $this->assertStringContainsString('استنفد رصيد إجازاته السنوي', $warnings);
    }

    public function test_check_limits_detects_monthly_permission_breach(): void
    {
        $date = '2026-10-20';
        $period = PayrollPeriod::fromRequest($date);

        // Add 3 hours (180 mins) permission
        Permission::create([
            'user_id'         => $this->employee->id,
            'name'            => $this->employee->name,
            'date'            => $period['start']->copy()->addDays(3)->toDateString(),
            'day'             => 'الأربعاء',
            'reason'          => 'إذن سابق 3 ساعات',
            'from'            => '09:00',
            'to'              => '12:00',
            'permission_type' => 'إذن تأخير',
            'status'          => 'accepted',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->getJson(route('super_admin.entry.check_limits', [
                'employee_name'   => $this->employee->name,
                'entry_type'      => 'permission',
                'date'            => $date,
                'from'            => '10:00',
                'to'              => '11:00',
                'permission_type' => 'إذن تأخير',
            ]));

        $response->assertStatus(200);
        $response->assertJson([
            'is_breached' => true,
        ]);
        $warnings = implode(' ', $response->json('warnings'));
        $this->assertStringContainsString('استنفد رصيد أذوناته الشهري بالكامل', $warnings);
    }

    public function test_check_limits_detects_daily_overtime_breach(): void
    {
        $date = '2026-10-20';

        // Add 5 hours overtime today
        Overtime::create([
            'user_id'     => $this->employee->id,
            'name'        => $this->employee->name,
            'date'        => $date,
            'day'         => 'الثلاثاء',
            'reason'      => 'إضافي 5 ساعات',
            'from'        => '16:00',
            'to'          => '21:00',
            'total_hours' => 5,
            'status'      => 'accepted',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->getJson(route('super_admin.entry.check_limits', [
                'employee_name' => $this->employee->name,
                'entry_type'    => 'overtime',
                'date'          => $date,
                'from'          => '21:00',
                'to'            => '23:00',
            ]));

        $response->assertStatus(200);
        $response->assertJson([
            'is_breached' => true,
        ]);
        $warnings = implode(' ', $response->json('warnings'));
        $this->assertStringContainsString('الحد الأقصى للإضافي اليومي', $warnings);
    }

    public function test_manual_entry_store_prompts_override_when_limit_is_breached_without_confirmation(): void
    {
        $date = '2026-10-20';
        $period = PayrollPeriod::fromRequest($date);

        // Exhaust monthly leaves
        Leave::create([
            'user_id'     => $this->employee->id,
            'name'        => $this->employee->name,
            'date'        => $period['start']->copy()->addDays(1)->toDateString(),
            'day'         => 'الأحد',
            'reason'      => 'إجازة 2 يوم',
            'substitute'  => 'زميل',
            'days_count'  => 2,
            'status'      => 'accepted',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('super_admin.entry.store'), [
                'entry_type'         => 'leave',
                'employee_name'      => $this->employee->name,
                'date'               => $date,
                'day'                => 'الثلاثاء',
                'reason'             => 'إجازة استثنائية زائدة',
                'substitute'         => 'محمد',
                'days_count'         => 1,
                'confirmed_override' => '0',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('needs_override_confirmation', true);
        $response->assertSessionHas('breached_warnings');

        // Verify that the extra leave was NOT created yet
        $this->assertSame(1, Leave::where('user_id', $this->employee->id)->count());
    }

    public function test_manual_entry_store_succeeds_when_override_is_confirmed(): void
    {
        $date = '2026-10-20';
        $period = PayrollPeriod::fromRequest($date);

        // Exhaust monthly leaves
        Leave::create([
            'user_id'     => $this->employee->id,
            'name'        => $this->employee->name,
            'date'        => $period['start']->copy()->addDays(1)->toDateString(),
            'day'         => 'الأحد',
            'reason'      => 'إجازة 2 يوم',
            'substitute'  => 'زميل',
            'days_count'  => 2,
            'status'      => 'accepted',
        ]);

        $response = $this->actingAs($this->supervisor)
            ->post(route('super_admin.entry.store'), [
                'entry_type'         => 'leave',
                'employee_name'      => $this->employee->name,
                'date'               => $date,
                'day'                => 'الثلاثاء',
                'reason'             => 'إجازة استثنائية زائدة',
                'substitute'         => 'محمد',
                'days_count'         => 1,
                'confirmed_override' => '1',
            ]);

        $response->assertRedirect(route('super_admin.entry'));
        $response->assertSessionHas('success');

        // Leave is now created (2 leaves)
        $this->assertSame(2, Leave::where('user_id', $this->employee->id)->count());

        // Verify AuditLog includes override note
        $log = AuditLog::where('target', $this->employee->name)->latest()->first();
        $this->assertNotNull($log);
        $this->assertStringContainsString('كسر حدود معتمد كاستثناء', $log->details);
    }

    public function test_check_limits_safely_handles_missing_or_null_date(): void
    {
        $response = $this->actingAs($this->supervisor)
            ->getJson(route('super_admin.entry.check_limits', [
                'employee_name' => $this->employee->name,
                'entry_type'    => 'leave',
                'date'          => '',
            ]));

        $response->assertOk()
            ->assertJsonPath('has_user', true);
    }
}
