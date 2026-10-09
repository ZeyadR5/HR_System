<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OvertimeMonthlySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $monthlyTotals;
    protected $periodStart;
    protected $periodEnd;

    public function __construct($monthlyTotals, $periodStart, $periodEnd)
    {
        $this->monthlyTotals = $monthlyTotals;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function collection()
    {
        return $this->monthlyTotals->map(function ($record) {
            $amount = 0;
            if ($record->user && $record->user->hourly_rate > 0) {
                $amount = $record->total * 1.5 * $record->user->hourly_rate;
            }

            $totalMinutes = (int) round($record->total * 60);
            $hours = intdiv($totalMinutes, 60);
            $mins = $totalMinutes % 60;

            if ($hours > 0 && $mins > 0) {
                $readableDuration = "{$hours}h {$mins}m";
            } elseif ($hours > 0) {
                $readableDuration = "{$hours}h";
            } else {
                $readableDuration = "{$mins}m";
            }

            return [
                'Employee Name'      => $record->name ?? 'Unspecified',
                'Job Title'          => $record->user->job_title ?? '—',
                'Readable Duration'  => $readableDuration,
                'Total Minutes'      => $totalMinutes,
                'OT Amount (EGP)'    => $amount > 0 ? number_format($amount, 2) . ' EGP' : 'Hourly rate not set',
                'Period'             => $this->periodStart->format('d M Y') . ' → ' . $this->periodEnd->format('d M Y'),
            ];
        });
    }

    public function headings(): array
    {
        return ['Employee Name', 'Job Title', 'Duration', 'Total Minutes (SUM)', 'Overtime Amount (EGP)', 'Period'];
    }

    public function title(): string
    {
        return 'إجمالي الإضافي';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4F81BD']
                ]
            ],
        ];
    }
}
