<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $newPerms = [
            'employees.documents_upload',
            'employees.documents_delete',
            'employees.avatar_manage',
        ];

        // تحديث كل الفئات التي تملك صلاحية تعديل ملفات الموظفين أو السوبر أدمن
        $roles = Role::all();
        foreach ($roles as $role) {
            $perms = (array) ($role->permissions ?? []);
            if ($role->name === 'super_admin' || in_array('employees.edit_profiles', $perms, true)) {
                $role->permissions = array_values(array_unique(array_merge($perms, $newPerms)));
                $role->save();
            }
        }

        Role::flushCache();
    }

    public function down(): void
    {
        $remove = [
            'employees.documents_upload',
            'employees.documents_delete',
            'employees.avatar_manage',
        ];

        foreach (Role::all() as $role) {
            $perms = (array) ($role->permissions ?? []);
            $role->permissions = array_values(array_diff($perms, $remove));
            $role->save();
        }

        Role::flushCache();
    }
};
