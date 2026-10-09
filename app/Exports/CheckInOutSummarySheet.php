<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CheckInOutSummarySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $summary;
    protected $periodStart;
    protected $periodEnd;

    public function __construct($summary, $periodStart, $periodEnd)
    {
        $this->summary = $summary;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function collection()
    {
        return $this->summary->map(function ($record) {
            return [
                'Employee Name'                  => $record->name ?? 'Unspecified',
                'Regular Shifts (Excl. Fridays)' => (int) ($record->regular_shifts ?? 0),
                'Friday Shifts'                  => (int) ($record->friday_shifts ?? 0),
                'Accepted Leave Days'            => (float) ($record->accepted_leaves ?? 0),
                'Total (Shifts + Leaves)'        => (float) ($record->total_shifts_and_leaves ?? $record->shifts_count ?? 0),
                'Friday Hours (x1.5)'            => (float) round($record->friday_bonus_hours ?? 0, 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Regular Shifts (Excl. Fridays)',
            'Friday Shifts',
            'Accepted Leave Days',
            'Total (Shifts + Leaves)',
            'Friday Hours (x1.5)',
        ];
    }

    public function title(): string
    {
        return 'ملخص الحضور والانصراف';
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
