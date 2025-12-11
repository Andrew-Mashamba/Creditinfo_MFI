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

class ClientRepaymentHistoryExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, WithColumnWidths, WithEvents
{
    protected $repaymentHistory;
    protected $summary;
    protected $clientNumber;
    protected $startDate;
    protected $endDate;

    public function __construct($repaymentHistory, $summary = [], $clientNumber = '', $startDate = '', $endDate = '')
    {
        $this->repaymentHistory = $repaymentHistory;
        $this->summary = $summary;
        $this->clientNumber = $clientNumber;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return $this->repaymentHistory;
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Payment Date',
            'Loan ID',
            'Product Name',
            'Payment Amount (TZS)',
            'Payment Type',
            'Account Balance (TZS)',
            'Reference Number',
            'Transaction Status',
            'Branch Name'
        ];
    }

    public function map($payment): array
    {
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $payment->payment_date ?? 'N/A',
            $payment->loan_id ?? 'N/A',
            $payment->product_name ?? 'N/A',
            number_format($payment->credit ?? 0, 2),
            $payment->payment_type ?? 'N/A',
            number_format($payment->record_on_account_number_balance ?? 0, 2),
            $payment->reference_number ?? 'N/A',
            $payment->trans_status ?? 'N/A',
            $payment->branch_name ?? 'N/A'
        ];
    }

    public function title(): string
    {
        return 'Repayment History';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // S/N
            'B' => 15,  // Payment Date
            'C' => 15,  // Loan ID
            'D' => 20,  // Product Name
            'E' => 18,  // Payment Amount
            'F' => 15,  // Payment Type
            'G' => 18,  // Account Balance
            'H' => 20,  // Reference Number
            'I' => 15,  // Transaction Status
            'J' => 20,  // Branch Name
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
                $sheet->setCellValue('A1', 'CLIENT REPAYMENT HISTORY REPORT');
                $sheet->mergeCells('A1:J1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Client information
                $sheet->setCellValue('A2', 'Client Number: ' . $this->clientNumber);
                $sheet->setCellValue('A3', 'Report Period: ' . $this->startDate . ' to ' . $this->endDate);
                $sheet->setCellValue('A4', 'Generated On: ' . now()->format('Y-m-d H:i:s'));
                
                // Summary statistics
                if (!empty($this->summary)) {
                    $sheet->setCellValue('A5', 'Total Payments: ' . ($this->summary['totalPayments'] ?? 0));
                    $sheet->setCellValue('A6', 'Total Amount Paid: ' . number_format($this->summary['totalPrincipalPaid'] ?? 0, 2) . ' TZS');
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
