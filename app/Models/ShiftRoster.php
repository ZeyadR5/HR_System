<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftRoster extends Model
{
    protected $fillable = [
        'title',
        'department',
        'start_date',
        'end_date',
        'grid_data',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'grid_data'  => 'array',
    ];

    public function entries(): HasMany
    {
        return $this->hasMany(ShiftRosterEntry::class, 'shift_roster_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedPeriodAttribute(): string
    {
        return $this->start_date->format('Y/m/d') . ' - ' . $this->end_date->format('Y/m/d');
    }

    public function getDepartmentBadgeAttribute(): array
    {
        return match ($this->department) {
            'تمريض' => ['bg' => '#fce7f3', 'text' => '#9d174d', 'border' => '#fbcfe8', 'icon' => '🩺'],
            'استقبال' => ['bg' => '#e0e7ff', 'text' => '#3730a3', 'border' => '#c7d2fe', 'icon' => '🛎️'],
            'عمال' => ['bg' => '#ecfdf5', 'text' => '#065f46', 'border' => '#a7f3d0', 'icon' => '🧹'],
            default => ['bg' => '#f1f5f9', 'text' => '#334155', 'border' => '#cbd5e1', 'icon' => '📋'],
        };
    }
}
