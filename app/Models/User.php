<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'job_title',
        'hourly_rate',
        'onesignal_id',
        'shift_group_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ==========================================
    // مجموعات الشيفتات
    // ==========================================

    public function shiftGroup()
    {
        return $this->belongsTo(ShiftGroup::class, 'shift_group_id');
    }

    // ==========================================
    // الفئات والصلاحيات
    // ==========================================

    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    public function isDeveloper(): bool
    {
        return $this->role === \App\Support\Permissions::DEVELOPER_ROLE;
    }

    /** قائمة صلاحيات المستخدم الفعلية */
    public function permissionList(): array
    {
        if ($this->isDeveloper()) {
            return \App\Support\Permissions::all();
        }
        return Role::permissionsFor($this->role);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isDeveloper()) {
            return true;
        }
        return in_array($permission, Role::permissionsFor($this->role), true);
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function roleDisplayName(): string
    {
        return Role::displayNameFor($this->role);
    }

    /**
     * Send Push Notification to this user (Local).
     */
    public function sendOneSignalNotification($title, $message, $actionedBy = null)
    {
        try {
            $this->notify(new \App\Notifications\MobilePushNotification($title, $message, $actionedBy));
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification Error: ' . $e->getMessage());
            return false;
        }
    }
}
