<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_name',
        'action',
        'module',
        'target',
        'details',
        'ip_address',
    ];

    /**
     * Boot the model to automatically add IP address.
     */
    protected static function booted()
    {
        static::creating(function ($auditLog) {
            if (empty($auditLog->ip_address) && request()) {
                $auditLog->ip_address = request()->ip();
            }
        });
    }

    /**
     * Helper to log an action easily.
     */
    public static function log($userName, $action, $module, $target, $details = null, $ipAddress = null)
    {
        return self::create([
            'user_name'  => $userName,
            'action'     => $action,
            'module'     => $module,
            'target'     => $target,
            'details'    => $details,
            'ip_address' => $ipAddress ?? request()->ip(),
        ]);
    }
}
