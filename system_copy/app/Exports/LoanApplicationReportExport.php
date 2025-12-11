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

class LoanApplicationReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $applications;
    protected $reportData;

    public function __construct($applications, $reportData = [])
    {
        $this->applications = $applications;
        $this->reportData = $reportData;
    }

    public function collection()
    {
        return $this->applications;
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Application Date',
            'Client Name',
            'Client Number',
            'Loan ID',
            'Loan Product',
            'Amount (TZS)',
            'Interest Rate (%)',
            'Term (Months)',
            'Branch',
            'Status',
            'Processing Days'
        ];
    }

    public function map($application): array
    {
        return [
            $application->id ?? '',
            $application->application_date ?? 'N/A',
            $application->client_name ?? 'N/A',
            $application->client_number ?? 'N/A',
            $application->loan_id ?? 'N/A',
            $application->loan_product_name ?? 'N/A',
            (float)$application->principle,
            (float)$application->interest,
            $application->tenure ?? 'N/A',
            $application->branch_name ?? 'N/A',
            $application->status ?? 'N/A',
            $application->processing_days ?? 'N/A'
        ];
    }

    public function title(): string
    {
        return 'Loan Applications';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // S/N
            'B' => 15,  // Application Date
            'C' => 25,  // Client Name
            'D' => 15,  // Client Number
            'E' => 15,  // Loan ID
            'F' => 20,  // Loan Product
            'G' => 15,  // Amount
            'H' => 12,  // Interest Rate
            'I' => 12,  // Term
            'J' => 20,  // Branch
            'K' => 12,  // Status
            'L' => 15,  // Processing Days
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
                    'startColor' => ['rgb' => '2C3E50']
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
                $sheet = $event->sheet->getDelegate();
                
                // Add report header
                $sheet->insertNewRowBefore(1, 6);
                
                // Report title
                $sheet->setCellValue('A1', 'LOAN APPLICATION REPORT');
                $sheet->mergeCells('A1:L1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Report period
                $sheet->setCellValue('A2', 'Report Period: ' . $this->reportData['startDate'] . ' to ' . $this->reportData['endDate']);
                $sheet->mergeCells('A2:L2');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Status filter
                if (!empty($this->reportData['statusFilter'])) {
                    $sheet->setCellValue('A3', 'Status Filter: ' . $this->reportData['statusFilter']);
                    $sheet->mergeCells('A3:L3');
                    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }
                
                // Generated date
                $sheet->setCellValue('A4', 'Generated On: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A4:L4');
                $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Summary row
                $sheet->setCellValue('A5', 'Summary: Total Applications: ' . $this->reportData['totalApplications'] . 
                    ' | Approved: ' . $this->reportData['approvedApplications'] . 
                    ' | Pending: ' . $this->reportData['pendingApplications'] . 
                    ' | Rejected: ' . $this->reportData['rejectedApplications'] . 
                    ' | Total Amount: ' . number_format($this->reportData['totalApplicationAmount'], 2) . ' TZS');
                $sheet->mergeCells('A5:L5');
                $sheet->getStyle('A5')->getFont()->setBold(true);
                $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Empty row
                $sheet->setCellValue('A6', '');
                
                // Adjust header row position
                $headerRow = 7;
                $sheet->getStyle('A' . $headerRow . ':L' . $headerRow)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF']
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2C3E50']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);
                
                // Apply borders to data rows
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A7:L' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);
                
                // Auto-fit columns
                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                // Set row height for header
                $sheet->getRowDimension($headerRow)->setRowHeight(25);
            },
        ];
    }
}
