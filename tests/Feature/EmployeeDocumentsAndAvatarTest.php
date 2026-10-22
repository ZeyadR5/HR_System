<?php

namespace Tests\Feature;

use App\Models\EmployeeDocument;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeDocumentsAndAvatarTest extends TestCase
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
                if (!Schema::hasColumn('users', 'is_insured')) {
                    $table->boolean('is_insured')->default(false);
                }
                if (!Schema::hasColumn('users', 'basic_incentive')) {
                    $table->decimal('basic_incentive', 10, 2)->default(0)->nullable();
                }
                if (!Schema::hasColumn('users', 'hire_date')) {
                    $table->date('hire_date')->nullable();
                }
                if (!Schema::hasColumn('users', 'evaluation')) {
                    $table->string('evaluation', 10)->nullable();
                }
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
            ],
        ]);
        Role::firstOrCreate(['name' => 'user'], [
            'display_name' => 'Employee',
            'permissions' => [],
        ]);
        Role::flushCache();
    }

    private function fakePngFile(string $filename = 'test.png'): UploadedFile
    {
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        return UploadedFile::fake()->createWithContent($filename, $bytes);
    }

    public function test_employee_can_update_their_own_profile_picture(): void
    {
        Storage::fake('public');

        $employee = User::create([
            'name'     => 'أحمد محمد',
            'email'    => 'ahmed@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        $file = $this->fakePngFile('my_avatar.png');

        $response = $this->actingAs($employee)
            ->post(route('my_profile.update_picture'), [
                'profile_picture' => $file,
            ]);

        $response->assertSessionHas('success');
        $employee->refresh();

        $this->assertNotNull($employee->profile_picture);
        $this->assertTrue(Storage::disk('public')->exists($employee->profile_picture));
    }

    public function test_admin_can_upload_employee_documents_and_avatar(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'     => 'محمد محمود',
            'email'    => 'mohamed@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        $avatar = $this->fakePngFile('avatar.png');
        $nationalId = UploadedFile::fake()->create('national_id.pdf', 500, 'application/pdf');
        $qualification = UploadedFile::fake()->create('cert.pdf', 500, 'application/pdf');
        $otherDoc = UploadedFile::fake()->create('contract.pdf', 500, 'application/pdf');

        $response = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->post(route('employee_profiles.update', $employee->id), [
                'hourly_rate'     => 150,
                'job_title'       => 'مهندس شبكات',
                'profile_picture' => $avatar,
            ]);

        $response->assertRedirect(route('employee_profiles.index'));
        $employee->refresh();

        $this->assertNotNull($employee->profile_picture);
        $this->assertTrue(Storage::disk('public')->exists($employee->profile_picture));

        // الوثائق الرسمية تُرفع عبر AJAX (أكثر من ملف لكل نوع)
        foreach (['national_id_card', 'qualification_certificate', 'other_document'] as $type) {
            $this->actingAs($admin)
                ->post(route('employee_profiles.upload_document_ajax', $employee->id), [
                    'type' => $type,
                    'file' => $nationalId,
                ])
                ->assertStatus(200);
            $this->assertTrue($employee->fresh()->hasDocument($type));
        }
    }

    public function test_employee_can_view_own_document_but_not_others(): void
    {
        Storage::fake('public');

        $employee1 = User::create([
            'name'     => 'الموظف الأول',
            'email'    => 'emp1@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        $employee2 = User::create([
            'name'     => 'الموظف الثاني',
            'email'    => 'emp2@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        $pdf = UploadedFile::fake()->create('national_id.pdf', 100, 'application/pdf');
        $path = $pdf->store('employee_documents/' . $employee1->id, 'public');
        EmployeeDocument::create([
            'user_id' => $employee1->id,
            'type'    => 'national_id_card',
            'path'    => $path,
        ]);

        // Employee 1 views own document -> 200 OK
        $response1 = $this->actingAs($employee1)
            ->get(route('employee_profiles.document', [$employee1->id, 'national_id_card']));
        $response1->assertStatus(200);

        // Employee 2 views Employee 1's document -> 403 Forbidden
        $response2 = $this->actingAs($employee2)
            ->get(route('employee_profiles.document', [$employee1->id, 'national_id_card']));
        $response2->assertStatus(403);
    }

    public function test_ajax_upload_document_success(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name'     => 'HR Admin',
            'email'    => 'hr@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'     => 'أيمن',
            'email'    => 'ayman@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        $pdf = UploadedFile::fake()->create('qualification.pdf', 300, 'application/pdf');

        $response = $this->actingAs($admin)
            ->post(route('employee_profiles.upload_document_ajax', $employee->id), [
                'type' => 'qualification_certificate',
                'file' => $pdf,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success'   => true,
            'type'      => 'qualification_certificate',
            'extension' => 'PDF',
            'is_image'  => false,
        ]);

        $this->assertSame(1, EmployeeDocument::where('user_id', $employee->id)->where('type', 'qualification_certificate')->count());
        $this->assertTrue($employee->fresh()->hasDocument('qualification_certificate'));
    }

    public function test_ajax_upload_document_forbidden_for_regular_employee(): void
    {
        Storage::fake('public');

        $employee = User::create([
            'name'     => 'موظف عادي',
            'email'    => 'emp_normal@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        $pdf = UploadedFile::fake()->create('national_id.pdf', 300, 'application/pdf');

        // Regular employee trying to upload official document -> 403
        $response = $this->actingAs($employee)
            ->post(route('employee_profiles.upload_document_ajax', $employee->id), [
                'type' => 'national_id_card',
                'file' => $pdf,
            ]);

        $response->assertStatus(403);
    }

    public function test_ajax_delete_document(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name'     => 'Super Admin',
            'email'    => 'super@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'     => 'موظف',
            'email'    => 'test_del@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        $pdf = UploadedFile::fake()->create('other.pdf', 100, 'application/pdf');
        $path = $pdf->store('employee_documents/' . $employee->id, 'public');
        $doc = EmployeeDocument::create([
            'user_id' => $employee->id,
            'type'    => 'other_document',
            'path'    => $path,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('employee_profiles.delete_document_ajax', $employee->id), [
                'type'    => 'other_document',
                'file_id' => $doc->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertNull(EmployeeDocument::find($doc->id));
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_ajax_upload_multiple_files_for_same_type(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name'     => 'HR Admin',
            'email'    => 'hr_multi@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'     => 'موظف متعدد',
            'email'    => 'multi@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        foreach (['a.pdf', 'b.pdf', 'c.pdf'] as $name) {
            $this->actingAs($admin)
                ->post(route('employee_profiles.upload_document_ajax', $employee->id), [
                    'type' => 'national_id_card',
                    'file' => UploadedFile::fake()->create($name, 100, 'application/pdf'),
                ])
                ->assertStatus(200)
                ->assertJson(['success' => true]);
        }

        $this->assertSame(3, EmployeeDocument::where('user_id', $employee->id)->where('type', 'national_id_card')->count());
        $this->assertCount(3, $employee->fresh()->documentFilesOf('national_id_card'));
    }

    public function test_custom_role_with_only_upload_permission_cannot_delete(): void
    {
        Storage::fake('public');

        Role::firstOrCreate(['name' => 'uploader_only'], [
            'display_name' => 'رافع ملفات فقط',
            'permissions'  => ['employees.documents_upload'],
        ]);

        $uploader = User::create([
            'name'     => 'مستخدم رافع فقط',
            'email'    => 'uploader@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'uploader_only',
        ]);

        $targetEmp = User::create([
            'name'     => 'موظف هدف',
            'email'    => 'target@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        // Upload should succeed
        $pdf = UploadedFile::fake()->create('contract.pdf', 100, 'application/pdf');
        $uploadRes = $this->actingAs($uploader)
            ->post(route('employee_profiles.upload_document_ajax', $targetEmp->id), [
                'type' => 'other_document',
                'file' => $pdf,
            ]);
        $uploadRes->assertStatus(200);

        $doc = EmployeeDocument::where('user_id', $targetEmp->id)->first();
        $this->assertNotNull($doc);

        // Delete should fail (403 forbidden)
        $delRes = $this->actingAs($uploader)
            ->post(route('employee_profiles.delete_document_ajax', $targetEmp->id), [
                'type'    => 'other_document',
                'file_id' => $doc->id,
            ]);
        $delRes->assertStatus(403);
    }

    public function test_regular_admin_without_upload_permission_cannot_upload_and_sees_no_upload_button(): void
    {
        Storage::fake('public');

        Role::firstOrCreate(['name' => 'plain_admin'], [
            'display_name' => 'ادمن بدون صلاحية وثائق',
            'permissions'  => ['overtime.view_all', 'leave.view_all'],
        ]);

        $plainAdmin = User::create([
            'name'     => 'أدمن عادي',
            'email'    => 'plain_admin@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'plain_admin',
        ]);

        // 1. Trying to upload via AJAX -> 403 Forbidden
        $pdf = UploadedFile::fake()->create('id.pdf', 100, 'application/pdf');
        $this->actingAs($plainAdmin)
            ->post(route('employee_profiles.upload_document_ajax', $plainAdmin->id), [
                'type' => 'national_id_card',
                'file' => $pdf,
            ])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        // 2. Visiting own profile (my-profile) -> Should NOT see upload button
        $page = $this->actingAs($plainAdmin)->get(route('my_profile'));
        $page->assertStatus(200);
        $page->assertDontSee('اختيار ورفع الملفات الآن');
        $page->assertDontSee('إدارة / رفع الوثائق');
    }

    public function test_edit_view_loads_successfully_when_employee_has_no_avatar(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name'     => 'أدمن كامل',
            'email'    => 'admin_full@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employeeWithoutAvatar = User::create([
            'name'            => 'موظف بدون صورة',
            'email'           => 'no_avatar@example.com',
            'password'        => bcrypt('123456'),
            'role'            => 'user',
            'profile_picture' => null,
        ]);

        $res = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->get(route('employee_profiles.edit', $employeeWithoutAvatar->id));

        $res->assertStatus(200);
        $res->assertSee('تعديل بروفايل موظف');
    }

    public function test_index_renders_employee_avatar_and_insurance_column(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name'     => 'مسؤول النظام',
            'email'    => 'admin_index@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'        => 'موظف تجريبي',
            'email'       => 'emp_test@example.com',
            'password'    => bcrypt('123456'),
            'role'        => 'user',
            'is_insured'  => true,
        ]);

        $res = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->get(route('employee_profiles.index'));

        $res->assertStatus(200);
        $res->assertSee('موظف تجريبي');
        $res->assertSee('insurance_switch_' . $employee->id);
        $res->assertSee('مؤمن عليه');
    }

    public function test_hr_can_toggle_insurance_status_via_ajax(): void
    {
        $admin = User::create([
            'name'     => 'مسؤول HR',
            'email'    => 'hr_toggle@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'       => 'موظف غير مؤمن',
            'email'      => 'emp_not_ins@example.com',
            'password'   => bcrypt('123456'),
            'role'       => 'user',
            'is_insured' => false,
        ]);

        // 1. Toggle to insured
        $response = $this->actingAs($admin)
            ->postJson(route('employee_profiles.toggle_insurance', $employee->id), [
                'is_insured' => true,
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success'    => true,
                'is_insured' => true,
            ]);

        $this->assertTrue((bool)$employee->fresh()->is_insured);

        // 2. Toggle back to not insured
        $response2 = $this->actingAs($admin)
            ->postJson(route('employee_profiles.toggle_insurance', $employee->id), [
                'is_insured' => false,
            ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success'    => true,
                'is_insured' => false,
            ]);

        $this->assertFalse((bool)$employee->fresh()->is_insured);
    }

    public function test_user_without_edit_permission_cannot_toggle_insurance(): void
    {
        $regularUser = User::create([
            'name'     => 'موظف عادي',
            'email'    => 'regular_emp@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'user',
        ]);

        $employee = User::create([
            'name'       => 'موظف هدف',
            'email'      => 'target_emp@example.com',
            'password'   => bcrypt('123456'),
            'role'       => 'user',
            'is_insured' => false,
        ]);

        $response = $this->actingAs($regularUser)
            ->postJson(route('employee_profiles.toggle_insurance', $employee->id), [
                'is_insured' => true,
            ]);

        $response->assertStatus(403);
        $this->assertFalse((bool)$employee->fresh()->is_insured);
    }

    public function test_hr_can_set_basic_incentive_hire_date_and_evaluation(): void
    {
        $admin = User::create([
            'name'     => 'مسؤول HR',
            'email'    => 'hr_fields@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'            => 'موظف تجريبي',
            'email'           => 'test_hr_fields@example.com',
            'password'        => bcrypt('123456'),
            'role'            => 'user',
            'hourly_rate'     => 50,
            'basic_incentive' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->post(route('employee_profiles.update', $employee->id), [
                'job_title'       => 'Nur',
                'hourly_rate'     => 60,
                'basic_incentive' => 1500.50,
                'hire_date'       => '2024-03-15',
                'evaluation'      => 'A',
            ]);

        $response->assertRedirect(route('employee_profiles.index'));

        $employee->refresh();
        $this->assertEquals(1500.50, (float)$employee->basic_incentive);
        $this->assertEquals('2024-03-15', $employee->hire_date->format('Y-m-d'));
        $this->assertEquals('A', $employee->evaluation);
    }

    public function test_evaluation_rejects_invalid_grade(): void
    {
        $admin = User::create([
            'name'     => 'مسؤول HR 2',
            'email'    => 'hr_fields2@example.com',
            'password' => bcrypt('123456'),
            'role'     => 'admin',
        ]);

        $employee = User::create([
            'name'            => 'موظف تجريبي',
            'email'           => 'invalid_grade@example.com',
            'password'        => bcrypt('123456'),
            'role'            => 'user',
            'hourly_rate'     => 50,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['unlocked_employee_profiles' => true])
            ->post(route('employee_profiles.update', $employee->id), [
                'job_title'       => 'Nur',
                'hourly_rate'     => 50,
                'evaluation'      => 'D', // Invalid! Only A, B, C allowed
            ]);

        $response->assertSessionHasErrors('evaluation');
        $this->assertNull($employee->fresh()->evaluation);
    }
}
