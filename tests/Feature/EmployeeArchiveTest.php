<?php

namespace Tests\Feature;

use App\Models\EmployeeDocument;
use App\Models\Incentive;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeArchiveTest extends TestCase
{
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
                $table->decimal('salary_amount', 10, 2)->nullable();
                $table->decimal('working_hours', 10, 2)->nullable();
                $table->string('profile_picture')->nullable();
                $table->string('national_id_card')->nullable();
                $table->string('qualification_certificate')->nullable();
                $table->string('other_document')->nullable();
                $table->boolean('is_insured')->default(false);
                $table->decimal('basic_incentive', 10, 2)->default(0)->nullable();
                $table->date('hire_date')->nullable();
                $table->string('evaluation', 10)->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            Schema::table('users', function ($table) {
                if (!Schema::hasColumn('users', 'deleted_at')) {
                    $table->softDeletes();
                }
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

        if (!Schema::hasTable('employee_documents')) {
            Schema::create('employee_documents', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('type', 50);
                $table->string('path');
                $table->string('original_name')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('incentives')) {
            Schema::create('incentives', function ($table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('name');
                $table->string('evaluation', 2000);
                $table->date('date');
                $table->string('created_ip')->nullable();
                $table->timestamps();
            });
        }

        foreach (['leaves', 'permissions', 'overtimes', 'penalties', 'notifications'] as $tbl) {
            if (!Schema::hasTable($tbl)) {
                Schema::create($tbl, function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('notifiable_type')->nullable();
                    $table->unsignedBigInteger('notifiable_id')->nullable();
                    $table->timestamp('read_at')->nullable();
                    $table->date('date')->nullable();
                    $table->string('status')->nullable();
                    $table->timestamps();
                });
            }
        }

        Role::flushCache();
        Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Admin',
            'permissions' => [
                'employees.view_profiles',
                'employees.edit_profiles',
                'employees.documents_upload',
                'employees.documents_delete',
                'employees.avatar_manage',
                'incentives.create',
                'incentives.view',
            ],
        ]);
        Role::firstOrCreate(['name' => 'user'], [
            'display_name' => 'Employee',
            'permissions' => [],
        ]);
        Role::flushCache();
    }

    public function test_archiving_employee_preserves_data_and_hides_from_active_lists(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name'     => 'المدير العام',
            'email'    => 'admin_archive_' . uniqid() . '@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'        => 'موظف تجريبي للأرشفة',
            'email'       => 'emp_archive_' . uniqid() . '@example.com',
            'password'    => bcrypt('123456'),
            'role'        => 'user',
            'job_title'   => 'خدمة عملاء',
            'is_insured'  => true,
        ]);

        // Add an incentive to the employee
        Incentive::create([
            'user_id'    => $employee->id,
            'name'       => $employee->name,
            'evaluation' => 'ممتاز',
            'date'       => now()->toDateString(),
        ]);

        // Add a document to the employee
        EmployeeDocument::create([
            'user_id'       => $employee->id,
            'type'          => 'national_id_card',
            'path'          => 'employee_documents/' . $employee->id . '/id_card.jpg',
            'original_name' => 'id_card.jpg',
        ]);

        // 1. Employee is active and appears in index & incentives
        $resIndex = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->get(route('employee_profiles.index'));
        $resIndex->assertStatus(200);
        $resIndex->assertSee('موظف تجريبي للأرشفة');

        $resIncentive = $this->actingAs($admin)->get(route('incentives.create'));
        $resIncentive->assertStatus(200);
        $resIncentive->assertSee('موظف تجريبي للأرشفة');

        // 2. Archive the employee
        $sysPass = \App\Support\Settings::get('system_edit_password', '123456');
        $resDelete = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->post(route('employee_profiles.destroy', $employee->id), [
                'system_password' => $sysPass,
            ]);
        $resDelete->assertRedirect(route('employee_profiles.index'));

        // Verify user is soft-deleted in DB and not hard-deleted
        $this->assertSoftDeleted('users', ['id' => $employee->id]);
        $this->assertDatabaseHas('incentives', ['user_id' => $employee->id]);
        $this->assertDatabaseHas('employee_documents', ['user_id' => $employee->id]);

        // 3. Employee does NOT appear in active profiles index table anymore
        $this->assertNull(User::find($employee->id));
        $this->assertNotNull(User::onlyTrashed()->find($employee->id));

        $resIndexAfter = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->get(route('employee_profiles.index'));
        $resIndexAfter->assertStatus(200);
        $resIndexAfter->assertDontSee('user-row-' . $employee->id);

        // 4. Employee does NOT appear in incentives create dropdown anymore
        $resIncentiveAfter = $this->actingAs($admin)->get(route('incentives.create'));
        $resIncentiveAfter->assertStatus(200);
        $resIncentiveAfter->assertDontSee('موظف تجريبي للأرشفة');

        // 5. Employee appears in archive page
        $resArchive = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->get(route('employee_profiles.archive'));
        $resArchive->assertStatus(200);
        $resArchive->assertSee('موظف تجريبي للأرشفة');
        $resArchive->assertSee('أرشيف الموظفين');

        // 6. Archived employee profile can still be viewed with their data intact
        $resShow = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->get(route('employee_profiles.show', $employee->id));
        $resShow->assertStatus(200);
        $resShow->assertSee('موظف تجريبي للأرشفة');
        $resShow->assertSee('هذا الحساب موجود في أرشيف الموظفين');

        // 7. Restore the employee
        $resRestore = $this->actingAs($admin)
            ->post(route('employee_profiles.restore', $employee->id));
        $resRestore->assertRedirect();

        // Verify user is restored in DB
        $this->assertNotSoftDeleted('users', ['id' => $employee->id]);

        // 8. Employee appears in active index again
        $resIndexRestored = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->get(route('employee_profiles.index'));
        $resIndexRestored->assertStatus(200);
        $resIndexRestored->assertSee('موظف تجريبي للأرشفة');
    }
}
