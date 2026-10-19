<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftRosterEntry extends Model
{
    protected $fillable = [
        'shift_roster_id',
        'user_id',
        'employee_name',
        'department',
        'section_name',
        'sub_section',
        'shift_type',
        'shift_group_id',
        'day_name',
        'specific_date',
        'is_friday',
        'col_index',
        'row_index',
    ];

    protected $casts = [
        'specific_date' => 'date',
        'is_friday'     => 'boolean',
        'col_index'     => 'integer',
        'row_index'     => 'integer',
    ];

    public function roster(): BelongsTo
    {
        return $this->belongsTo(ShiftRoster::class, 'shift_roster_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function shiftGroup(): BelongsTo
    {
        return $this->belongsTo(ShiftGroup::class, 'shift_group_id');
    }
}
