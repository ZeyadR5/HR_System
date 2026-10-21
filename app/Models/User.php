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
        'salary_amount',
        'working_hours',
        'profile_picture',
        'national_id_card',
        'qualification_certificate',
        'other_document',
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
            'salary_amount' => 'decimal:2',
            'working_hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
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
     * Get document path for a specific type.
     */
    public function documentPath(string $type): ?string
    {
        return match($type) {
            'national_id_card' => $this->national_id_card,
            'qualification_certificate' => $this->qualification_certificate,
            'other_document' => $this->other_document,
            default => null,
        };
    }

    /**
     * Check if document exists on disk.
     */
    public function hasDocument(string $type): bool
    {
        $path = $this->documentPath($type);
        return !empty($path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    }

    /**
     * Get document file extension or type (pdf/image).
     */
    public function documentExtension(string $type): ?string
    {
        $path = $this->documentPath($type);
        if (!$path) return null;
        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    /**
     * Check if document is an image.
     */
    public function isDocumentImage(string $type): bool
    {
        $ext = $this->documentExtension($type);
        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
    }

    /**
     * Check if document is a PDF.
     */
    public function isDocumentPdf(string $type): bool
    {
        return $this->documentExtension($type) === 'pdf';
    }
}
