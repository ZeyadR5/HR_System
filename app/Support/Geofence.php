<?php

namespace App\Support;

/**
 * التحقق من تواجد الموظف داخل نطاق الشركة (Geofencing).
 * الإعدادات تتحدد من صفحة إعدادات النظام:
 *  - geofence_enabled  : تفعيل/تعطيل الميزة
 *  - office_latitude / office_longitude : إحداثيات مقر الشركة
 *  - geofence_radius_m : نصف القطر المسموح بالمتر
 *  - require_selfie    : إلزام التقاط صورة سيلفي عند تسجيل الحضور
 */
class Geofence
{
    public static function enabled(): bool
    {
        return (bool) Settings::get('geofence_enabled', false)
            && Settings::get('office_latitude') !== null
            && Settings::get('office_longitude') !== null;
    }

    public static function radius(): int
    {
        return (int) Settings::get('geofence_radius_m', 100);
    }

    public static function requireSelfie(): bool
    {
        return (bool) Settings::get('require_selfie', false);
    }

    /** المسافة بالمتر بين نقطتين (Haversine). */
    public static function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): int
    {
        $earth = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return (int) round($earth * 2 * atan2(sqrt($a), sqrt(1 - $a)));
    }

    public static function distanceFromOffice(float $lat, float $lng): ?int
    {
        $oLat = Settings::get('office_latitude');
        $oLng = Settings::get('office_longitude');

        if ($oLat === null || $oLng === null) {
            return null;
        }

        return self::distanceMeters((float) $oLat, (float) $oLng, $lat, $lng);
    }
}
