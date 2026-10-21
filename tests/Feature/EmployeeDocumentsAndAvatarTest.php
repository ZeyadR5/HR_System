<?php

namespace Tests\Feature;

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
                $table->rememberToken();
                $table->timestamps();
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

        Role::flushCache();
        Role::firstOrCreate(['name' => 'admin'], [
            'display_name' => 'Admin',
            'permissions' => ['employees.view_profiles', 'employees.edit_profiles'],
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
                'hourly_rate'               => 150,
                'job_title'                 => 'مهندس شبكات',
                'profile_picture'           => $avatar,
                'national_id_card'          => $nationalId,
                'qualification_certificate' => $qualification,
                'other_document'            => $otherDoc,
            ]);

        $response->assertRedirect(route('employee_profiles.index'));
        $employee->refresh();

        $this->assertNotNull($employee->profile_picture);
        $this->assertNotNull($employee->national_id_card);
        $this->assertNotNull($employee->qualification_certificate);
        $this->assertNotNull($employee->other_document);

        $this->assertTrue($employee->hasDocument('national_id_card'));
        $this->assertTrue($employee->hasDocument('qualification_certificate'));
        $this->assertTrue($employee->hasDocument('other_document'));

        $this->assertTrue(Storage::disk('public')->exists($employee->national_id_card));
        $this->assertTrue(Storage::disk('public')->exists($employee->qualification_certificate));
        $this->assertTrue(Storage::disk('public')->exists($employee->other_document));
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
        $employee1->update(['national_id_card' => $path]);

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

        $employee->refresh();
        $this->assertNotNull($employee->qualification_certificate);
        $this->assertTrue(Storage::disk('public')->exists($employee->qualification_certificate));
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
        $employee->update(['other_document' => $path]);

        $response = $this->actingAs($admin)
            ->post(route('employee_profiles.delete_document_ajax', $employee->id), [
                'type' => 'other_document',
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $employee->refresh();
        $this->assertNull($employee->other_document);
    }
}
