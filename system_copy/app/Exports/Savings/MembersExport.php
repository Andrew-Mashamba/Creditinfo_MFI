<?php

namespace App\Exports\Savings;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class MembersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithMapping
{
    protected $membersData;
    protected $month;
    protected $year;

    public function __construct($membersData, $month, $year)
    {
        $this->membersData = $membersData;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return collect($this->membersData);
    }

    public function map($member): array
    {
        return [
            $member['client_number'] ?? '',
            ($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''),
            $member['phone_number'] ?? 'N/A',
            $member['email'] ?? 'N/A',
            'TZS ' . number_format($member['total_savings'] ?? 0, 2),
            $member['savings_accounts'] ?? 0,
            $member['compliance_status'] ?? '',
            $member['member_status'] ?? '',
            Carbon::parse($member['created_at'] ?? now())->format('d/m/Y'),
            $member['branch_name'] ?? 'Main Branch'
        ];
    }

    public function headings(): array
    {
        return [
            'Client Number',
            'Member Name',
            'Phone Number',
            'Email',
            'Total Savings',
            'Number of Accounts',
            'Compliance Status',
            'Member Status',
            'Join Date',
            'Branch'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2563EB']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Add borders to all cells
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:J' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E5E5']
                ]
            ]
        ]);

        // Highlight non-compliant members
        for ($row = 2; $row <= $highestRow; $row++) {
            $compliance = $sheet->getCell('G' . $row)->getValue();
            if ($compliance !== 'COMPLIANT') {
                $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7']
                    ]
                ]);
            }
            
            // Highlight inactive members
            $status = $sheet->getCell('H' . $row)->getValue();
            if ($status !== 'ACTIVE') {
                $sheet->getStyle('H' . $row)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'DC2626']
                    ]
                ]);
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Members';
    }
}