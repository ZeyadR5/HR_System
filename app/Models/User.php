<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
        'salary_amount',
        'working_hours',
        'profile_picture',
        'national_id_card',
        'qualification_certificate',
        'other_document',
        'onesignal_id',
        'shift_group_id',
        'is_insured',
        'basic_incentive',
        'hire_date',
        'evaluation',
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
            'salary_amount' => 'decimal:2',
            'working_hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
            'basic_incentive' => 'decimal:2',
            'hire_date' => 'date',
            'is_insured' => 'boolean',
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

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin' || $this->isDeveloper();
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
     * Send Notification to this user (Database, Mail in background, and Mobile).
     */
    public function sendOneSignalNotification($title, $message, $actionedBy = null)
    {
        try {
            // 1. تسجيل الإشعار في قاعدة البيانات فوراً (لحساب الموظف بالسيستم)
            $this->notify(new \App\Notifications\MobilePushNotification($title, $message, $actionedBy));

            // 2. إرسال إشعار الهاتف السحابي فوراً (OneSignal Push Notification)
            try {
                \App\Services\OneSignalService::sendToUser($this->id, $title, $message);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('OneSignal Error: ' . $e->getMessage());
            }

            // 3. إرسال الإيميل الرسمي
            $recipientEmail = $this->email;
            $recipientName  = $this->name;
            if (!empty($recipientEmail)) {
                try {
                    \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(
                        new \App\Mail\EmployeeNotificationMail($recipientName, $title, $message, $actionedBy)
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error('Email Error: ' . $e->getMessage());
                }
            }

            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify all management roles (admin, super_admin, developer, technical_support)
     * via database notification and OneSignal push notification.
     */
    public static function notifyManagement(string $title, string $message, ?string $actionedBy = null, ?int $exceptUserId = null): void
    {
        $managementRoles = ['admin', 'super_admin', 'developer', 'technical_support'];

        $admins = self::whereIn('role', $managementRoles)
            ->when($exceptUserId, fn($q) => $q->where('id', '!=', $exceptUserId))
            ->get();

        if ($admins->isEmpty()) {
            return;
        }

        // 1. تسجيل الإشعارات في قاعدة البيانات لجميع أفراد الإدارة
        foreach ($admins as $admin) {
            try {
                $admin->notify(new \App\Notifications\MobilePushNotification($title, $message, $actionedBy));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("Failed to DB-notify admin {$admin->id}: " . $e->getMessage());
            }
        }

        // 2. إرسال إشعار سحابي موحد لجميع أفراد الإدارة عبر OneSignal
        try {
            \App\Services\OneSignalService::sendToUsers($admins->pluck('id')->all(), $title, $message);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to OneSignal-notify management: " . $e->getMessage());
        }
    }

    /**
     * Get user's avatar image URL or fallback to initial avatar.
     */
    public function getProfilePictureUrlAttribute(): string
    {
        if ($this->profile_picture && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->profile_picture)) {
            return asset('storage/' . $this->profile_picture);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=0284c7&color=ffffff&bold=true';
    }

    /**
     * ملفات الموظف الرسمية (يمكن رفع أكثر من ملف لكل نوع).
     */
    public function documentFiles()
    {
        return $this->hasMany(EmployeeDocument::class)->orderBy('id');
    }

    /**
     * ملفات نوع معين (بطاقة / مؤهل / أخرى) الموجودة فعلياً على القرص.
     */
    public function documentFilesOf(string $type)
    {
        try {
            return $this->documentFiles
                ->where('type', $type)
                ->filter(fn ($doc) => $doc->exists())
                ->values();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('documentFilesOf fallback: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Check if at least one document of the type exists on disk.
     */
    public function hasDocument(string $type): bool
    {
        return $this->documentFilesOf($type)->isNotEmpty();
    }
}
