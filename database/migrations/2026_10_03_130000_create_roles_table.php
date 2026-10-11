<?php

use App\Support\Permissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
        Schema::enableForeignKeyConstraints();

        // إعادة إنشاء جدول أذونات الموظفين permissions إذا كان قد تم حذفه بالخطأ
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
                $table->date('date');
                $table->string('day');
                $table->string('name');
                $table->text('reason');
                $table->time('from')->nullable();
                $table->time('to')->nullable();
                $table->string('permission_type')->nullable();
                $table->enum('status', ['pending', 'accepted', 'refused'])->default('pending');
                $table->text('refuse_reason')->nullable();
                $table->string('actioned_by')->nullable();
                $table->string('alarm')->nullable();
                $table->string('created_ip', 45)->nullable();
                $table->timestamps();
            });
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();      // slug مخزن في users.role
            $table->string('display_name', 100);
            $table->string('description', 255)->nullable();
            $table->string('color', 20)->default('#64748b');
            $table->boolean('is_system')->default(false);
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        $defaults = Permissions::defaults();
        $now = now();

        $roles = [
            ['name' => 'developer',    'display_name' => 'مطور / مالك النظام', 'description' => 'صلاحيات كاملة على كل النظام + إدارة الفئات', 'color' => '#7c3aed', 'is_system' => true,  'permissions' => []],
            ['name' => 'super_admin',  'display_name' => 'مدير عام',           'description' => 'إدارة كاملة للطلبات والتقارير والموظفين',   'color' => '#dc2626', 'is_system' => true,  'permissions' => $defaults['super_admin']],
            ['name' => 'admin',        'display_name' => 'مدير',               'description' => 'مراجعة وقبول طلبات الموظفين',               'color' => '#2563eb', 'is_system' => true,  'permissions' => $defaults['admin']],
            ['name' => 'user',         'display_name' => 'موظف',               'description' => 'الفئة الافتراضية لأي موظف جديد',            'color' => '#64748b', 'is_system' => true,  'permissions' => $defaults['user']],
            ['name' => 'user_checkin', 'display_name' => 'موظف - تسجيل حضور',  'description' => 'موظف مسموح له بتسجيل الحضور والانصراف من الموقع', 'color' => '#0d9488', 'is_system' => false, 'permissions' => $defaults['user_checkin']],
        ];

        foreach ($roles as $role) {
            $role['permissions'] = json_encode($role['permissions']);
            $role['created_at'] = $now;
            $role['updated_at'] = $now;
            DB::table('roles')->insert($role);
        }

        // الموظفين اللي كان مسموح لهم بالحضور عن طريق إيميل مكتوب في الكود → ننقلهم للفئة الجديدة
        DB::table('users')
            ->where('role', 'user')
            ->whereIn('email', ['hend@gama.com', 'RawanEssam@gamma.com', 'esraa.abdulla30@gmail.com'])
            ->update(['role' => 'user_checkin']);
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'user_checkin')->update(['role' => 'user']);
        Schema::dropIfExists('roles');
    }
};
