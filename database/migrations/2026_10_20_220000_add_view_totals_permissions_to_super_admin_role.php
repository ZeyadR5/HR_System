<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $perms = (array) ($superAdmin->permissions ?? []);
            $newPerms = ['overtime.view_totals', 'leave.view_totals', 'permission.view_totals'];
            foreach ($newPerms as $perm) {
                if (!in_array($perm, $perms, true)) {
                    $perms[] = $perm;
                }
            }
            $superAdmin->permissions = array_values($perms);
            $superAdmin->save();
        }
    }

    public function down(): void
    {
        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $perms = (array) ($superAdmin->permissions ?? []);
            $removePerms = ['overtime.view_totals', 'leave.view_totals', 'permission.view_totals'];
            $superAdmin->permissions = array_values(array_diff($perms, $removePerms));
            $superAdmin->save();
        }
    }
};
