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

class NonCompliantMembersExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithMapping
{
    protected $nonCompliantMembers;
    protected $month;
    protected $year;

    public function __construct($nonCompliantMembers, $month, $year)
    {
        $this->nonCompliantMembers = $nonCompliantMembers;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return collect($this->nonCompliantMembers);
    }

    public function map($member): array
    {
        return [
            $member['client_number'] ?? '',
            ($member['first_name'] ?? '') . ' ' . ($member['last_name'] ?? ''),
            $member['phone_number'] ?? 'N/A',
            $member['email'] ?? 'N/A',
            $member['status'] ?? '',
            $member['reason'] ?? 'No savings account or zero balance',
            Carbon::parse($member['created_at'] ?? now())->format('d/m/Y'),
            $member['last_contact'] ?? 'Not contacted',
            'Pending'
        ];
    }

    public function headings(): array
    {
        return [
            'Client Number',
            'Member Name',
            'Phone Number',
            'Email',
            'Member Status',
            'Non-Compliance Reason',
            'Join Date',
            'Last Contact',
            'Action Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:I1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'DC2626']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Add borders to all cells
        $highestRow = $sheet->getHighestRow();
        $sheet->getStyle('A1:I' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E5E5']
                ]
            ]
        ]);

        // Highlight all rows with light red background
        $sheet->getStyle('A2:I' . $highestRow)->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FEF2F2']
            ]
        ]);

        // Add title
        $sheet->insertNewRowBefore(1, 2);
        $sheet->setCellValue('A1', 'Non-Compliant Members Report');
        $sheet->setCellValue('A2', Carbon::create($this->year, $this->month)->format('F Y'));
        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        
        $sheet->getStyle('A1:A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => 'DC2626']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Add note at the bottom
        $lastRow = $highestRow + 3;
        $sheet->setCellValue('A' . $lastRow, 'Note: These members require immediate attention to comply with SACCOS savings requirements.');
        $sheet->mergeCells('A' . $lastRow . ':I' . $lastRow);
        $sheet->getStyle('A' . $lastRow)->applyFromArray([
            'font' => [
                'italic' => true,
                'color' => ['rgb' => '6B7280']
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Non-Compliant Members';
    }
}