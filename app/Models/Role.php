<?php

namespace App\Models;

use App\Support\Permissions;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'is_system',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system'   => 'boolean',
    ];

    /** كاش داخل الريكوست الواحد عشان نعمل query واحد بس */
    protected static ?array $permissionCache = null;
    protected static ?\Illuminate\Support\Collection $rolesCache = null;

    protected static function booted(): void
    {
        static::saved(fn () => static::flushCache());
        static::deleted(fn () => static::flushCache());
    }

    public static function flushCache(): void
    {
        static::$permissionCache = null;
        static::$rolesCache = null;
    }

    public static function permissionsFor(?string $roleName): array
    {
        if ($roleName === null) {
            return [];
        }

        if (static::$permissionCache === null) {
            static::$permissionCache = static::query()
                ->get(['name', 'permissions'])
                ->mapWithKeys(fn ($r) => [$r->name => (array) ($r->permissions ?? [])])
                ->all();
        }

        return static::$permissionCache[$roleName] ?? [];
    }

    /** كل الفئات (مع كاش للريكوست) */
    public static function allCached(): \Illuminate\Support\Collection
    {
        if (static::$rolesCache === null) {
            static::$rolesCache = static::query()->orderByDesc('is_system')->orderBy('id')->get()->keyBy('name');
        }
        return static::$rolesCache;
    }

    public static function displayNameFor(?string $roleName): string
    {
        return static::allCached()->get($roleName)?->display_name ?? ($roleName ?: '—');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role', 'name');
    }

    public function isDeveloper(): bool
    {
        return $this->name === Permissions::DEVELOPER_ROLE;
    }

    public function isDefault(): bool
    {
        return $this->name === Permissions::DEFAULT_ROLE;
    }

    public function canBeDeleted(): bool
    {
        return !$this->is_system;
    }
}
