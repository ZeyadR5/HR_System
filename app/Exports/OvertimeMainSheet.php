<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OvertimeMainSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $overtime;
    protected $periodStart;
    protected $periodEnd;

    public function __construct($overtime, $periodStart, $periodEnd)
    {
        $this->overtime = $overtime;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function collection()
    {
        return $this->overtime->map(function ($item, $index) {
            $totalMinutes = (int) round($item->total_hours * 60);
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
                'N'                 => $index + 1,
                'Employee Name'     => $item->name,
                'Job Title'         => $item->user->job_title ?? '—',
                'Readable Duration' => $readableDuration,
                'Total Minutes'     => $totalMinutes,
                'Reason'            => $item->reason,
                'Date'              => \Carbon\Carbon::parse($item->date)->format('d M Y'),
                'Day'               => $item->day,
                'From'              => $item->from ? \Carbon\Carbon::parse($item->from)->format('h:i A') : '-',
                'To'                => $item->to ? \Carbon\Carbon::parse($item->to)->format('h:i A') : '-',
                'Status'            => ucfirst($item->status),
                'Refuse Reason'     => $item->refuse_reason ?? '—',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'N', 'Employee Name', 'Job Title', 'Duration', 'Total Minutes (SUM)', 'Reason',
            'Date', 'Day', 'From', 'To', 'Status', 'Refuse Reason',
        ];
    }

    public function title(): string
    {
        return 'سجل الإضافي';
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
