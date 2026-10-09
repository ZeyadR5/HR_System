<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PenaltyMonthlySheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $employeeTotals;
    protected $periodStart;
    protected $periodEnd;

    public function __construct($employeeTotals, $periodStart, $periodEnd)
    {
        $this->employeeTotals = $employeeTotals;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function collection()
    {
        return $this->employeeTotals->map(function ($record) {
            return [
                'Employee Name'   => $record['name'],
                'Total Penalties' => $record['total'] . ' penalty',
                'Period'          => $this->periodStart->format('d M Y') . ' → ' . $this->periodEnd->format('d M Y'),
            ];
        });
    }

    public function headings(): array
    {
        return ['Employee Name', 'Total Penalties This Cycle', 'Period'];
    }

    public function title(): string
    {
        return 'إجمالي الجزاءات';
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
