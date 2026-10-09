<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * 🛡️ إدارة الفئات والصلاحيات — للمطور فقط (محمي بـ developer middleware)
 */
class RoleManagementController extends Controller
{
    public const COLORS = [
        '#7c3aed', '#dc2626', '#2563eb', '#0d9488', '#16a34a',
        '#ea580c', '#db2777', '#0891b2', '#ca8a04', '#64748b',
    ];

    public function index(Request $request)
    {
        $roles = Role::withCount('users')->orderByDesc('is_system')->orderBy('id')->get();

        $usersQuery = User::query()->orderBy('name');
        if ($request->filled('q')) {
            $q = $request->q;
            $usersQuery->where(fn ($w) => $w->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%"));
        }
        if ($request->filled('role')) {
            $usersQuery->where('role', $request->role);
        }
        $users = $usersQuery->paginate(25)->withQueryString();

        $totalPermissions = count(Permissions::all());
        $orphanCount = User::whereNotIn('role', $roles->pluck('name'))->count();

        return view('developer.roles.index', compact('roles', 'users', 'totalPermissions', 'orphanCount'));
    }

    public function create()
    {
        $role = new Role(['color' => self::COLORS[array_rand(self::COLORS)], 'permissions' => []]);
        return view('developer.roles.form', [
            'role'   => $role,
            'groups' => Permissions::groups(),
            'colors' => self::COLORS,
            'roles'  => Role::orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $slug = $this->makeSlug($request->input('name') ?: $data['display_name']);

        $role = Role::create([
            'name'         => $slug,
            'display_name' => $data['display_name'],
            'description'  => $data['description'] ?? null,
            'color'        => $data['color'],
            'is_system'    => false,
            'permissions'  => Permissions::sanitize($request->input('permissions', [])),
        ]);

        AuditLog::log(Auth::user()->name, 'Created', 'Role', $role->display_name, 'Permissions: ' . count($role->permissions));

        return redirect()->route('developer.roles.show', $role)->with('success', "تم إنشاء الفئة \"{$role->display_name}\" بنجاح — ضيف الموظفين ليها من هنا 👇");
    }

    public function show(Role $role)
    {
        $members = $role->users()->orderBy('name')->get();
        $candidates = User::where('role', '!=', $role->name)->orderBy('name')->get(['id', 'name', 'email', 'role']);

        return view('developer.roles.show', [
            'role'       => $role,
            'members'    => $members,
            'candidates' => $candidates,
            'groups'     => Permissions::groups(),
            'granted'    => $role->isDeveloper() ? Permissions::all() : (array) $role->permissions,
        ]);
    }

    public function edit(Role $role)
    {
        return view('developer.roles.form', [
            'role'   => $role,
            'groups' => Permissions::groups(),
            'colors' => self::COLORS,
            'roles'  => Role::orderBy('id')->get(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validated($request, $role);

        $payload = [
            'display_name' => $data['display_name'],
            'description'  => $data['description'] ?? null,
            'color'        => $data['color'],
        ];

        // صلاحيات المطور ثابتة (كل حاجة) ومينفعش تتعدل
        if (!$role->isDeveloper()) {
            $payload['permissions'] = Permissions::sanitize($request->input('permissions', []));
        }

        $before = (array) $role->permissions;
        $role->update($payload);

        $added   = array_diff((array) $role->permissions, $before);
        $removed = array_diff($before, (array) $role->permissions);
        AuditLog::log(Auth::user()->name, 'Updated', 'Role', $role->display_name, '+' . count($added) . ' / -' . count($removed) . ' permissions');

        return redirect()->route('developer.roles.show', $role)->with('success', 'تم حفظ التعديلات ✅ — الصلاحيات الجديدة شغالة فوراً على كل أعضاء الفئة.');
    }

    public function destroy(Role $role)
    {
        if (!$role->canBeDeleted()) {
            return back()->withErrors(['error' => 'الفئات الأساسية للنظام لا يمكن حذفها.']);
        }

        $moved = User::where('role', $role->name)->update(['role' => Permissions::DEFAULT_ROLE]);
        $name = $role->display_name;
        $role->delete();

        AuditLog::log(Auth::user()->name, 'Deleted', 'Role', $name, "Moved {$moved} users to default role");

        return redirect()->route('developer.roles.index')->with('success', "تم حذف الفئة \"{$name}\"" . ($moved ? " ونقل {$moved} موظف لفئة الموظف العادي." : '.'));
    }

    public function addMembers(Request $request, Role $role)
    {
        $request->validate([
            'user_ids'   => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
        ], [
            'user_ids.required' => 'اختار موظف واحد على الأقل.',
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();

        foreach ($users as $user) {
            if ($error = $this->guardRoleChange($user, $role->name)) {
                return back()->withErrors(['error' => $error]);
            }
        }

        foreach ($users as $user) {
            $user->update(['role' => $role->name]);
        }

        AuditLog::log(Auth::user()->name, 'Assigned Role', 'Role', $role->display_name, $users->pluck('name')->implode(', '));

        return back()->with('success', "تم إضافة {$users->count()} موظف لفئة \"{$role->display_name}\" ✅");
    }

    public function removeMember(Role $role, User $user)
    {
        if ($role->isDefault()) {
            return back()->withErrors(['error' => 'دي الفئة الافتراضية — انقل الموظف لفئة تانية بدل ما تشيله.']);
        }
        if ($error = $this->guardRoleChange($user, Permissions::DEFAULT_ROLE)) {
            return back()->withErrors(['error' => $error]);
        }

        $user->update(['role' => Permissions::DEFAULT_ROLE]);
        AuditLog::log(Auth::user()->name, 'Removed Role', 'Role', $role->display_name, $user->name);

        return back()->with('success', "تم نقل {$user->name} لفئة الموظف العادي.");
    }

    public function changeUserRole(Request $request, User $user)
    {
        $request->validate(['role' => ['required', 'string', Rule::exists('roles', 'name')]]);

        if ($error = $this->guardRoleChange($user, $request->role)) {
            return back()->withErrors(['error' => $error]);
        }

        $old = $user->roleDisplayName();
        $user->update(['role' => $request->role]);
        AuditLog::log(Auth::user()->name, 'Changed Role', 'User', $user->name, "{$old} → " . Role::displayNameFor($request->role));

        return back()->with('success', "تم تغيير فئة {$user->name} إلى \"" . Role::displayNameFor($request->role) . '"');
    }

    // ==========================================

    /** حماية من إن المطور يقفل على نفسه أو النظام يفضل من غير مطور */
    private function guardRoleChange(User $user, string $newRole): ?string
    {
        if ($user->isDeveloper() && $newRole !== Permissions::DEVELOPER_ROLE) {
            if ($user->id === Auth::id()) {
                return 'مينفعش تشيل صلاحية المطور من نفسك — كده هتقفل على نفسك برة الصفحة دي.';
            }
            if (User::where('role', Permissions::DEVELOPER_ROLE)->count() <= 1) {
                return 'لازم يفضل مطور واحد على الأقل في النظام.';
            }
        }
        return null;
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        return $request->validate([
            'display_name'  => ['required', 'string', 'max:100', Rule::unique('roles', 'display_name')->ignore($role?->id)],
            'name'          => ['nullable', 'string', 'max:50'],
            'description'   => ['nullable', 'string', 'max:255'],
            'color'         => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ], [
            'display_name.required' => 'اسم الفئة مطلوب.',
            'display_name.unique'   => 'في فئة بنفس الاسم بالفعل.',
            'color.regex'           => 'اللون غير صالح.',
        ]);
    }

    private function makeSlug(string $source): string
    {
        $base = Str::slug($source, '_');
        if ($base === '') {
            $base = 'role';
        }
        $base = Str::limit($base, 40, '');

        $slug = $base;
        $i = 2;
        while (Role::where('name', $slug)->exists()) {
            $slug = $base . '_' . $i++;
        }
        return $slug;
    }

    public function updateSystemPassword(Request $request)
    {
        $request->validate([
            'system_password' => 'required|min:4'
        ], [
            'system_password.required' => 'كلمة المرور مطلوبة',
            'system_password.min' => 'يجب أن لا تقل عن 4 أرقام/حروف'
        ]);

        \App\Support\Settings::set('system_edit_password', $request->system_password);

        return back()->with('success', '✅ تم تغيير كلمة مرور النظام بنجاح!');
    }
}
