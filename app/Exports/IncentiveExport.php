<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncentiveExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $incentives;
    protected $periodStart;
    protected $periodEnd;

    public function __construct($incentives, $periodStart, $periodEnd)
    {
        $this->incentives = $incentives;
        $this->periodStart = $periodStart;
        $this->periodEnd = $periodEnd;
    }

    public function collection()
    {
        $users = User::orderBy('name')->get();

        return $users->map(function ($user, $index) {
            $userIncentive = $this->incentives->where('user_id', $user->id)->first();
            return [
                'N'             => $index + 1,
                'Employee Name' => $user->name,
                'Job Title'     => $user->job_title ?? '—',
                'Evaluation'    => $userIncentive ? $userIncentive->evaluation : '',
                'Notes'         => '',
            ];
        });
    }

    public function headings(): array
    {
        return ['N', 'Employee Name', 'Job Title', 'Evaluation', 'Notes'];
    }

    public function title(): string
    {
        return 'الحوافز';
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
