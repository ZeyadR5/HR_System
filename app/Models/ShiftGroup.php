<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShiftGroup extends Model
{
    protected $fillable = [
        'name',
        'from_time',
        'to_time',
        'color',
        'icon',
        'description',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'shift_group_id');
    }

    /**
     * هل الشيفت يعبر منتصف الليل (مثل السهر من 23:00 إلى 08:00)؟
     */
    public function crossesMidnight(): bool
    {
        return $this->toMinutes($this->to_time) < $this->toMinutes($this->from_time);
    }

    /**
     * تحويل الوقت لصيغة دقائق منذ بداية اليوم (0 - 1439)
     */
    public static function toMinutes(string $time): int
    {
        $parts = explode(':', $time);
        $h = (int) ($parts[0] ?? 0);
        $m = (int) ($parts[1] ?? 0);
        return ($h * 60) + $m;
    }

    /**
     * فحص هل فترة الإضافي المطلوب [from, to] تتداخل مع ساعات الشيفت الأساسي؟
     */
    public function overlapsWith(string $from, string $to): bool
    {
        $otStart = self::toMinutes($from);
        $otEnd   = self::toMinutes($to);

        $shiftStart = self::toMinutes($this->from_time);
        $shiftEnd   = self::toMinutes($this->to_time);

        // شيفت عادي ينتهي في نفس اليوم
        if (! $this->crossesMidnight()) {
            return ($otStart < $shiftEnd) && ($otEnd > $shiftStart);
        }

        // شيفت سهر يعبر منتصف الليل (مثلاً من 23:00 إلى 08:00)
        // الفترات النشطة: [shiftStart -> 1440] و [0 -> shiftEnd]
        return ($otEnd > $shiftStart) || ($otStart < $shiftEnd);
    }

    /**
     * نص منسق لعرض أوقات الشيفت بالعربي (مثلاً: 09:00 ص - 04:00 م)
     */
    public function getFormattedHoursAttribute(): string
    {
        $from = Carbon::createFromFormat('H:i:s', strlen($this->from_time) === 5 ? $this->from_time . ':00' : $this->from_time)->translatedFormat('h:i A');
        $to   = Carbon::createFromFormat('H:i:s', strlen($this->to_time) === 5 ? $this->to_time . ':00' : $this->to_time)->translatedFormat('h:i A');

        return "{$from} إلى {$to}";
    }

    /**
     * عدد ساعات الشيفت الأساسية
     */
    public function getDurationHoursAttribute(): float
    {
        $start = self::toMinutes($this->from_time);
        $end   = self::toMinutes($this->to_time);

        if ($end < $start) {
            $end += 1440;
        }

        return round(($end - $start) / 60, 1);
    }
}
