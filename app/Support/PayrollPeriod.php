<?php

namespace App\Support;

use Carbon\Carbon;

class PayrollPeriod
{
    public static function current(?Carbon $referenceDate = null): array
    {
        $referenceDate ??= Carbon::today();

        return self::fromDate($referenceDate);
    }

    public static function fromRequest(?string $periodStart = null, ?Carbon $fallbackReferenceDate = null): array
    {
        if (blank($periodStart)) {
            return self::current($fallbackReferenceDate);
        }

        try {
            $requestedDate = Carbon::parse($periodStart);
        } catch (\Throwable $e) {
            return self::current($fallbackReferenceDate);
        }

        return self::fromDate($requestedDate);
    }

    public static function fromDate(Carbon $date): array
    {
        $startDay = (int) \App\Support\Settings::get('payroll_start_day', 26);
        $endDay = (int) \App\Support\Settings::get('payroll_end_day', 25);

        $periodStart = self::normalizeStart($date);
        
        // Ensure end date falls correctly after the start date
        if ($endDay < $startDay) {
            $periodEnd = $periodStart->copy()->addMonthNoOverflow()->day($endDay)->endOfDay();
        } else {
            $periodEnd = $periodStart->copy()->day($endDay)->endOfDay();
        }
        
        $currentPeriod = self::currentStart();

        return [
            'start' => $periodStart,
            'end' => $periodEnd,
            'previous_start' => $periodStart->copy()->subMonthNoOverflow()->day($startDay)->startOfDay(),
            'next_start' => $periodStart->copy()->addMonthNoOverflow()->day($startDay)->startOfDay(),
            'label' => $periodStart->format('d M Y') . ' → ' . $periodEnd->format('d M Y'),
            'slug' => $periodStart->format('Y-m-d'),
            'is_current' => $periodStart->isSameDay($currentPeriod),
        ];
    }

    public static function currentStart(?Carbon $referenceDate = null): Carbon
    {
        $referenceDate ??= Carbon::today();

        return self::normalizeStart($referenceDate);
    }

    protected static function normalizeStart(Carbon $date): Carbon
    {
        $startDay = (int) \App\Support\Settings::get('payroll_start_day', 26);
        
        if ($date->day >= $startDay) {
            return $date->copy()->day($startDay)->startOfDay();
        }

        return $date->copy()->subMonthNoOverflow()->day($startDay)->startOfDay();
    }
}
