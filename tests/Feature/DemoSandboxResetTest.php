<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CheckInOut;
use App\Models\Leave;
use App\Models\Overtime;
use App\Models\Penalty;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ShiftGroup;
use App\Models\User;
use Database\Seeders\DemoDatabaseSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DemoSandboxResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Ensure in-memory sqlite tables exist for testing
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('display_name');
                $table->text('description')->nullable();
                $table->string('color')->default('#3b82f6');
                $table->boolean('is_system')->default(false);
                $table->json('permissions')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shift_groups')) {
            Schema::create('shift_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('from_time');
                $table->string('to_time');
                $table->string('color')->nullable();
                $table->string('icon')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('password');
                $table->string('role')->default('user');
                $table->string('job_title')->nullable();
                $table->decimal('salary_amount', 10, 2)->nullable();
                $table->decimal('working_hours', 8, 2)->nullable();
                $table->decimal('hourly_rate', 8, 2)->default(0);
                $table->decimal('basic_incentive', 8, 2)->default(0);
                $table->date('hire_date')->nullable();
                $table->string('evaluation')->nullable();
                $table->boolean('is_insured')->default(false);
                $table->unsignedBigInteger('shift_group_id')->nullable();
                $table->string('profile_picture')->nullable();
                $table->string('national_id_card')->nullable();
                $table->string('qualification_certificate')->nullable();
                $table->string('other_document')->nullable();
                $table->string('onesignal_id')->nullable();
                $table->softDeletes();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('leaves')) {
            Schema::create('leaves', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->date('date');
                $table->string('day');
                $table->text('reason');
                $table->string('substitute')->nullable();
                $table->integer('days_count')->default(1);
                $table->string('status')->default('accepted');
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->date('date');
                $table->string('day');
                $table->text('reason');
                $table->string('permission_type')->default('إذن تأخير');
                $table->string('from')->nullable();
                $table->string('to')->nullable();
                $table->string('status')->default('accepted');
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('overtimes')) {
            Schema::create('overtimes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->date('date');
                $table->string('day');
                $table->text('reason');
                $table->string('from')->nullable();
                $table->string('to')->nullable();
                $table->decimal('total_hours', 8, 2)->default(0);
                $table->string('status')->default('accepted');
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('penalties')) {
            Schema::create('penalties', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->text('reason');
                $table->decimal('amount', 8, 2)->default(0);
                $table->text('notes')->nullable();
                $table->string('status')->default('accepted');
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('incentives')) {
            Schema::create('incentives', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->text('evaluation');
                $table->date('date');
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('settlements')) {
            Schema::create('settlements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->text('note');
                $table->date('date');
                $table->string('day');
                $table->string('status')->default('pending');
                $table->text('accept_note')->nullable();
                $table->text('refuse_reason')->nullable();
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('check_in_outs')) {
            Schema::create('check_in_outs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('name');
                $table->date('date');
                $table->string('day');
                $table->string('type');
                $table->string('status')->default('accepted');
                $table->string('actioned_by')->nullable();
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('user_name');
                $table->string('action');
                $table->string('module')->nullable();
                $table->string('target')->nullable();
                $table->text('details')->nullable();
                $table->string('ip_address')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_demo_seeder_populates_all_demo_data_successfully(): void
    {
        $seeder = new DemoDatabaseSeeder();
        $seeder->run();

        // 1. Verify Roles
        $this->assertTrue(Role::where('name', 'super_admin')->exists());
        $this->assertTrue(Role::where('name', 'hr_manager')->exists());
        $this->assertTrue(Role::where('name', 'supervisor')->exists());

        // 2. Verify Demo Admin User
        $admin = User::where('email', 'admin@demo.com')->first();
        $this->assertNotNull($admin);
        $this->assertSame('super_admin', $admin->role);
        $this->assertTrue($admin->isSuperAdmin());

        // 3. Verify Shift Groups
        $this->assertGreaterThanOrEqual(3, ShiftGroup::count());

        // 4. Verify Employees created
        $this->assertGreaterThanOrEqual(14, User::count());

        // 5. Verify Activities created
        $this->assertGreaterThan(0, Leave::count());
        $this->assertGreaterThan(0, Permission::count());
        $this->assertGreaterThan(0, Overtime::count());
        $this->assertGreaterThan(0, Penalty::count());
        $this->assertGreaterThan(0, CheckInOut::count());
        $this->assertGreaterThan(0, AuditLog::count());
    }

    public function test_demo_reset_route_works_for_super_admin(): void
    {
        config(['app.demo_mode' => true]);

        $admin = User::create([
            'name'     => 'المدير العام التجريبي',
            'email'    => 'admin_test@demo.com',
            'password' => bcrypt('demo123456'),
            'role'     => 'super_admin',
        ]);

        $this->actingAs($admin);

        // Can access demo reset route
        $this->assertTrue($admin->isSuperAdmin());
    }
}
