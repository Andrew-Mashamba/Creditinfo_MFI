<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StatementOfCashFlowExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $statementData;
    protected $institution;

    public function __construct($statementData, $institution = null)
    {
        $this->statementData = $statementData;
        $this->institution = $institution;
    }

    public function array(): array
    {
        $data = [];
        
        // Header information
        $data[] = [$this->institution->name ?? 'NBC SACCOS'];
        $data[] = ['STATEMENT OF CASH FLOW'];
        $data[] = ['For the period from ' . $this->statementData['period']['start_date'] . ' to ' . $this->statementData['period']['end_date']];
        $data[] = ['(All amounts in Tanzanian Shillings)'];
        $data[] = []; // Empty row

        // Operating Activities
        $data[] = ['CASH FLOWS FROM OPERATING ACTIVITIES'];
        $data[] = [];

        // Income details
        if (!empty($this->statementData['operating_activities']['income_details'])) {
            $data[] = ['Cash Inflows:', '', ''];
            foreach ($this->statementData['operating_activities']['income_details'] as $income) {
                $data[] = ['', $income['account_name'], number_format($income['amount'], 2)];
            }
        }

        // Expense details
        if (!empty($this->statementData['operating_activities']['expense_details'])) {
            $data[] = ['Cash Outflows:', '', ''];
            foreach ($this->statementData['operating_activities']['expense_details'] as $expense) {
                $data[] = ['', $expense['account_name'], '(' . number_format($expense['amount'], 2) . ')'];
            }
        }

        $data[] = ['Net Cash from Operating Activities', '', 
                  $this->statementData['operating_activities']['net_cash_flow'] >= 0 ? 
                  number_format($this->statementData['operating_activities']['net_cash_flow'], 2) : 
                  '(' . number_format(abs($this->statementData['operating_activities']['net_cash_flow']), 2) . ')'];
        $data[] = []; // Empty row

        // Investing Activities
        $data[] = ['CASH FLOWS FROM INVESTING ACTIVITIES'];
        $data[] = [];

        // Sale details
        if (!empty($this->statementData['investing_activities']['sale_details'])) {
            $data[] = ['Cash Inflows:', '', ''];
            foreach ($this->statementData['investing_activities']['sale_details'] as $sale) {
                $data[] = ['', 'Sale of ' . $sale['account_name'], number_format($sale['amount'], 2)];
            }
        }

        // Purchase details
        if (!empty($this->statementData['investing_activities']['purchase_details'])) {
            $data[] = ['Cash Outflows:', '', ''];
            foreach ($this->statementData['investing_activities']['purchase_details'] as $purchase) {
                $data[] = ['', 'Purchase of ' . $purchase['account_name'], '(' . number_format($purchase['amount'], 2) . ')'];
            }
        }

        $data[] = ['Net Cash from Investing Activities', '', 
                  $this->statementData['investing_activities']['net_cash_flow'] >= 0 ? 
                  number_format($this->statementData['investing_activities']['net_cash_flow'], 2) : 
                  '(' . number_format(abs($this->statementData['investing_activities']['net_cash_flow']), 2) . ')'];
        $data[] = []; // Empty row

        // Financing Activities
        $data[] = ['CASH FLOWS FROM FINANCING ACTIVITIES'];
        $data[] = [];

        // Loan proceeds
        if (!empty($this->statementData['financing_activities']['loan_proceed_details'])) {
            $data[] = ['Cash Inflows:', '', ''];
            foreach ($this->statementData['financing_activities']['loan_proceed_details'] as $proceed) {
                $data[] = ['', $proceed['account_name'] . ' Proceeds', number_format($proceed['amount'], 2)];
            }
        }

        // Capital contributions
        if (!empty($this->statementData['financing_activities']['capital_contribution_details'])) {
            foreach ($this->statementData['financing_activities']['capital_contribution_details'] as $contribution) {
                $data[] = ['', $contribution['account_name'] . ' Contribution', number_format($contribution['amount'], 2)];
            }
        }

        // Loan repayments
        if (!empty($this->statementData['financing_activities']['loan_repayment_details'])) {
            $data[] = ['Cash Outflows:', '', ''];
            foreach ($this->statementData['financing_activities']['loan_repayment_details'] as $repayment) {
                $data[] = ['', $repayment['account_name'] . ' Repayment', '(' . number_format($repayment['amount'], 2) . ')'];
            }
        }

        // Capital withdrawals
        if (!empty($this->statementData['financing_activities']['capital_withdrawal_details'])) {
            foreach ($this->statementData['financing_activities']['capital_withdrawal_details'] as $withdrawal) {
                $data[] = ['', $withdrawal['account_name'] . ' Withdrawal', '(' . number_format($withdrawal['amount'], 2) . ')'];
            }
        }

        $data[] = ['Net Cash from Financing Activities', '', 
                  $this->statementData['financing_activities']['net_cash_flow'] >= 0 ? 
                  number_format($this->statementData['financing_activities']['net_cash_flow'], 2) : 
                  '(' . number_format(abs($this->statementData['financing_activities']['net_cash_flow']), 2) . ')'];
        $data[] = []; // Empty row

        // Cash Flow Summary
        $data[] = []; // Empty row
        $data[] = ['Net Increase (Decrease) in Cash', '', 
                  $this->statementData['cash_flow_summary']['net_cash_flow'] >= 0 ? 
                  number_format($this->statementData['cash_flow_summary']['net_cash_flow'], 2) : 
                  '(' . number_format(abs($this->statementData['cash_flow_summary']['net_cash_flow']), 2) . ')'];
        
        $data[] = ['Cash at Beginning of Period', '', number_format($this->statementData['cash_flow_summary']['beginning_cash'], 2)];
        $data[] = ['Cash at End of Period', '', number_format($this->statementData['cash_flow_summary']['ending_cash'], 2)];

        return $data;
    }

    public function headings(): array
    {
        return [
            'Description',
            'Details',
            'Amount (TZS)'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 40,
            'C' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        
        // Style header rows (institution name, title, period)
        $sheet->getStyle('A1:C4')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8FAFC'],
            ],
        ]);

        // Style main title
        $sheet->getStyle('A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '1E40AF'],
            ],
        ]);

        // Style period info
        $sheet->getStyle('A3')->applyFromArray([
            'font' => [
                'size' => 11,
                'color' => ['rgb' => '6B7280'],
            ],
        ]);

        // Style currency note
        $sheet->getStyle('A4')->applyFromArray([
            'font' => [
                'size' => 10,
                'italic' => true,
                'color' => ['rgb' => '6B7280'],
            ],
        ]);

        // Dynamic styling based on content
        for ($row = 1; $row <= $lastRow; $row++) {
            $cellValue = $sheet->getCell("A{$row}")->getValue();
            
            // Style section headers (Operating, Investing, Financing)
            if (is_string($cellValue) && strpos($cellValue, 'CASH FLOWS FROM') !== false) {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E40AF'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(25);
            }
            // Style subsection headers (Cash Inflows, Cash Outflows)
            elseif (is_string($cellValue) && (strpos($cellValue, 'Cash Inflows:') !== false || strpos($cellValue, 'Cash Outflows:') !== false)) {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '374151'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E5E7EB'],
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(20);
            }
            // Style net cash flow rows
            elseif (is_string($cellValue) && strpos($cellValue, 'Net Cash from') !== false) {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F3F4F6'],
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '374151'],
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D1D5DB'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(22);
            }
            // Style final summary rows
            elseif (is_string($cellValue) && (strpos($cellValue, 'Net Increase') !== false || strpos($cellValue, 'Cash at') !== false)) {
                $sheet->getStyle("A{$row}:C{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1F2937'],
                    ],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(22);
            }
            // Style regular rows
            else {
                $sheet->getRowDimension($row)->setRowHeight(18);
            }
        }

        // Style amount column
        $sheet->getStyle('C1:C' . $lastRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
            'numberFormat' => [
                'formatCode' => '#,##0.00',
            ],
        ]);

        // Style description column
        $sheet->getStyle('A1:A' . $lastRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style details column
        $sheet->getStyle('B1:B' . $lastRow)->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style all cells with borders
        $sheet->getStyle('A1:C' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Set header row heights
        for ($row = 1; $row <= 4; $row++) {
            $sheet->getRowDimension($row)->setRowHeight(20);
        }

        // Freeze panes at row 5 (after header)
        $sheet->freezePane('A5');

        return [];
    }

    public function title(): string
    {
        return 'Statement of Cash Flow';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set print settings
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                
                // Set margins
                $sheet->getPageMargins()->setTop(0.75);
                $sheet->getPageMargins()->setRight(0.75);
                $sheet->getPageMargins()->setLeft(0.75);
                $sheet->getPageMargins()->setBottom(0.75);
                
                // Set print area
                $lastRow = $sheet->getHighestRow();
                $sheet->getPageSetup()->setPrintArea("A1:C{$lastRow}");
                
                // Add header and footer
                $sheet->getHeaderFooter()->setOddHeader('&C&B&16' . ($this->institution->name ?? 'NBC SACCOS') . ' - Statement of Cash Flow');
                $sheet->getHeaderFooter()->setOddFooter('&L&D &T&C&RPage &P of &N');
            },
        ];
    }
}
