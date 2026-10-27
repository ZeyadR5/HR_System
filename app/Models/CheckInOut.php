<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckInOut extends Model
{
    use HasFactory, LogsIpAddress;

    protected $fillable = [
        'user_id',
        'name',
        'date',
        'day',
        'type',
        'status',
        'refuse_reason',
        'actioned_by',
        'latitude',
        'longitude',
        'distance_m',
        'selfie_path',
        'source',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
