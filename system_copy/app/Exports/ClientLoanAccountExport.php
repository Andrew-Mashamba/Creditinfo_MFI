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

class ClientLoanAccountExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $clientLoans;
    protected $summary;
    protected $clientNumber;

    public function __construct($clientLoans, $summary = [], $clientNumber = '')
    {
        $this->clientLoans = $clientLoans;
        $this->summary = $summary;
        $this->clientNumber = $clientNumber;
    }

    public function collection()
    {
        return $this->clientLoans;
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Loan ID',
            'Product Name',
            'Principal Amount (TZS)',
            'Outstanding Balance (TZS)',
            'Interest Rate (%)',
            'Status',
            'Days in Arrears',
            'Next Payment Date',
            'Next Payment Amount (TZS)',
            'Branch Name'
        ];
    }

    public function map($loan): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $loan->loan_id ?? 'N/A',
            $loan->product_name ?? 'N/A',
            number_format($loan->principle ?? 0, 2),
            number_format($loan->outstanding_balance ?? 0, 2),
            $loan->interest ?? 'N/A',
            $loan->status ?? 'N/A',
            $loan->days_in_arrears > 0 ? $loan->days_in_arrears . ' days' : 'Current',
            $loan->next_payment_date ? date('Y-m-d', strtotime($loan->next_payment_date)) : 'N/A',
            number_format($loan->next_payment_amount ?? 0, 2),
            $loan->branch_name ?? 'N/A'
        ];
    }

    public function title(): string
    {
        return 'Loan Accounts';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // S/N
            'B' => 15,  // Loan ID
            'C' => 20,  // Product Name
            'D' => 18,  // Principal Amount
            'E' => 20,  // Outstanding Balance
            'F' => 15,  // Interest Rate
            'G' => 12,  // Status
            'H' => 15,  // Days in Arrears
            'I' => 18,  // Next Payment Date
            'J' => 20,  // Next Payment Amount
            'K' => 20,  // Branch Name
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1f2937']
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
                
                // Add summary information at the top
                $summaryRow = 1;
                $sheet->insertNewRowBefore($summaryRow, 6);
                
                // Report title
                $sheet->setCellValue('A1', 'CLIENT LOAN ACCOUNT REPORT');
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Client information
                $sheet->setCellValue('A2', 'Client Number: ' . $this->clientNumber);
                $sheet->setCellValue('A3', 'Generated On: ' . now()->format('Y-m-d H:i:s'));
                
                // Summary statistics
                if (!empty($this->summary)) {
                    $sheet->setCellValue('A4', 'Total Loans: ' . ($this->summary['totalLoans'] ?? 0));
                    $sheet->setCellValue('A5', 'Total Loan Amount: ' . number_format($this->summary['totalLoanAmount'] ?? 0, 2) . ' TZS');
                    $sheet->setCellValue('A6', 'Total Outstanding: ' . number_format($this->summary['totalOutstanding'] ?? 0, 2) . ' TZS');
                }
                
                // Adjust row heights
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->getRowDimension(7)->setRowHeight(20); // Header row
                
                // Add borders to all data cells
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                $sheet->getStyle('A7:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]);
            }
        ];
    }
}
