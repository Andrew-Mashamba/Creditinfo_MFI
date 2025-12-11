<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientDetailsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $members;
    protected $summary;
    protected $filters;

    public function __construct($members, $summary = [], $filters = [])
    {
        $this->members = $members;
        $this->summary = $summary;
        $this->filters = $filters;
    }

    public function collection()
    {
        return $this->members;
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Member Number',
            'Full Name',
            'NIDA Number',
            'Gender',
            'Phone Number',
            'Mobile Number',
            'Email Address',
            'Address',
            'Region',
            'District',
            'Branch',
            'Status',
            'Registration Date',
            'Savings Balance (TZS)'
        ];
    }

    public function map($member): array
    {
        return [
            $member->id ?? '',
            $member->client_number ?? 'N/A',
            $member->full_name ?? 'N/A',
            $member->nida_number ?? 'N/A',
            $member->gender ?? 'N/A',
            $member->phone_number ?? 'N/A',
            $member->mobile_phone_number ?? 'N/A',
            $member->email ?? 'N/A',
            $member->address ?? 'N/A',
            $member->region ?? 'N/A',
            $member->district ?? 'N/A',
            $member->branch_name ?? 'N/A',
            $member->status ?? 'N/A',
            $member->registration_date ?? 'N/A',
            number_format($member->savings_balance ?? 0, 2)
        ];
    }

    public function title(): string
    {
        return 'Member Details Report';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // S/N
            'B' => 15,  // Member Number
            'C' => 25,  // Full Name
            'D' => 15,  // NIDA Number
            'E' => 10,  // Gender
            'F' => 15,  // Phone Number
            'G' => 15,  // Mobile Number
            'H' => 25,  // Email Address
            'I' => 30,  // Address
            'J' => 15,  // Region
            'K' => 15,  // District
            'L' => 20,  // Branch
            'M' => 12,  // Status
            'N' => 15,  // Registration Date
            'O' => 18,  // Savings Balance
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1e40af']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                // Get the highest row and column
                $highestRow = $event->sheet->getDelegate()->getHighestRow();
                $highestColumn = $event->sheet->getDelegate()->getHighestColumn();
                
                // Add borders to all cells
                $event->sheet->getDelegate()->getStyle('A1:' . $highestColumn . $highestRow)
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                
                // Auto-fit columns
                foreach (range('A', $highestColumn) as $column) {
                    $event->sheet->getDelegate()->getColumnDimension($column)->setAutoSize(true);
                }
                
                // Add summary information at the bottom
                $summaryRow = $highestRow + 3;
                $event->sheet->getDelegate()->setCellValue('A' . $summaryRow, 'Report Summary:');
                $event->sheet->getDelegate()->getStyle('A' . $summaryRow)->getFont()->setBold(true);
                
                if (!empty($this->summary)) {
                    $summaryRow++;
                    $event->sheet->getDelegate()->setCellValue('A' . $summaryRow, 'Total Members: ' . ($this->summary['totalMembers'] ?? 0));
                    $summaryRow++;
                    $event->sheet->getDelegate()->setCellValue('A' . $summaryRow, 'Active Members: ' . ($this->summary['activeMembers'] ?? 0));
                    $summaryRow++;
                    $event->sheet->getDelegate()->setCellValue('A' . $summaryRow, 'Total Savings: ' . number_format($this->summary['totalSavings'] ?? 0, 2) . ' TZS');
                }
                
                // Add filters information
                if (!empty($this->filters)) {
                    $summaryRow += 2;
                    $event->sheet->getDelegate()->setCellValue('A' . $summaryRow, 'Applied Filters:');
                    $event->sheet->getDelegate()->getStyle('A' . $summaryRow)->getFont()->setBold(true);
                    
                    $summaryRow++;
                    if (!empty($this->filters['branch_filter'])) {
                        $event->sheet->getDelegate()->setCellValue('A' . $summaryRow, 'Branch: ' . $this->filters['branch_filter']);
                        $summaryRow++;
                    }
                    if (!empty($this->filters['status_filter'])) {
                        $event->sheet->getDelegate()->setCellValue('A' . $summaryRow, 'Status: ' . $this->filters['status_filter']);
                        $summaryRow++;
                    }
                    if (!empty($this->filters['custom_numbers'])) {
                        $event->sheet->getDelegate()->setCellValue('A' . $summaryRow, 'Custom Numbers: ' . $this->filters['custom_numbers']);
                        $summaryRow++;
                    }
                }
            },
        ];
    }
}
