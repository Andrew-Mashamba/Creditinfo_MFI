<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Carbon\Carbon;

class StatementOfFinancialPositionExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithColumnWidths, WithEvents
{
    protected $statementData;
    protected $asOfDate;

    public function __construct($statementData, $asOfDate = null)
    {
        $this->statementData = $statementData;
        $this->asOfDate = $asOfDate ?: Carbon::now()->format('Y-m-d');
    }

    public function array(): array
    {
        $data = [];
        
        // Add header information
        $data[] = ['STATEMENT OF FINANCIAL POSITION', '', ''];
        $data[] = ['As at ' . Carbon::parse($this->asOfDate)->format('F d, Y'), '', ''];
        $data[] = ['(All amounts in Tanzanian Shillings)', '', ''];
        $data[] = ['', '', '']; // Empty row
        
        // Add summary section
        $data[] = ['FINANCIAL POSITION SUMMARY', '', ''];
        $data[] = ['Total Assets:', 'TZS ' . number_format($this->statementData['totals']['total_assets'], 2), ''];
        $data[] = ['Total Liabilities:', 'TZS ' . number_format($this->statementData['totals']['total_liabilities'], 2), ''];
        $data[] = ['Total Equity:', 'TZS ' . number_format($this->statementData['totals']['total_equity'], 2), ''];
        $data[] = ['', '', '']; // Empty row
        
        // Add financial ratios
        $totalAssets = $this->statementData['totals']['total_assets'];
        $totalLiabilities = $this->statementData['totals']['total_liabilities'];
        $totalEquity = $this->statementData['totals']['total_equity'];
        
        $data[] = ['FINANCIAL RATIOS', '', ''];
        $data[] = ['Debt-to-Equity Ratio:', $totalEquity > 0 ? number_format($totalLiabilities / $totalEquity, 2) : 'N/A', ''];
        $data[] = ['Equity Ratio:', $totalAssets > 0 ? number_format(($totalEquity / $totalAssets) * 100, 1) . '%' : 'N/A', ''];
        $data[] = ['Asset Coverage:', $totalLiabilities > 0 ? number_format($totalAssets / $totalLiabilities, 2) : 'N/A', ''];
        $data[] = ['', '', '']; // Empty row
        
        // Add ASSETS section
        $data[] = ['ASSETS', '', ''];
        foreach ($this->statementData['assets']['categories'] as $categoryCode => $category) {
            $data[] = [$category['name'], '', ''];
            foreach ($category['accounts'] as $account) {
                $data[] = ['', is_object($account) ? $account->account_name : $account['account_name'], number_format(is_object($account) ? $account->current_balance : $account['current_balance'], 2)];
            }
            $data[] = ['', 'Subtotal', number_format($category['subtotal'], 2)];
            $data[] = ['', '', '']; // Empty row after each category
        }
        $data[] = ['TOTAL ASSETS', '', number_format($this->statementData['assets']['total'], 2)];
        $data[] = ['', '', '']; // Empty row
        
        // Add LIABILITIES section
        $data[] = ['LIABILITIES', '', ''];
        foreach ($this->statementData['liabilities']['categories'] as $categoryCode => $category) {
            $data[] = [$category['name'], '', ''];
            foreach ($category['accounts'] as $account) {
                $data[] = ['', is_object($account) ? $account->account_name : $account['account_name'], number_format(is_object($account) ? $account->current_balance : $account['current_balance'], 2)];
            }
            $data[] = ['', 'Subtotal', number_format($category['subtotal'], 2)];
            $data[] = ['', '', '']; // Empty row after each category
        }
        $data[] = ['TOTAL LIABILITIES', '', number_format($this->statementData['liabilities']['total'], 2)];
        $data[] = ['', '', '']; // Empty row
        
        // Add EQUITY section
        $data[] = ['EQUITY', '', ''];
        foreach ($this->statementData['equity']['categories'] as $categoryCode => $category) {
            $data[] = [$category['name'], '', ''];
            foreach ($category['accounts'] as $account) {
                $data[] = ['', is_object($account) ? $account->account_name : $account['account_name'], number_format(is_object($account) ? $account->current_balance : $account['current_balance'], 2)];
            }
            $data[] = ['', 'Subtotal', number_format($category['subtotal'], 2)];
            $data[] = ['', '', '']; // Empty row after each category
        }
        $data[] = ['TOTAL EQUITY', '', number_format($this->statementData['equity']['total'], 2)];
        $data[] = ['', '', '']; // Empty row
        
        // Add grand total
        $data[] = ['TOTAL LIABILITIES AND EQUITY', '', number_format($this->statementData['totals']['total_liabilities_and_equity'], 2)];
        $data[] = ['', '', '']; // Empty row
        
        // Add balance verification
        $data[] = ['BALANCE VERIFICATION', '', ''];
        $data[] = ['Total Assets:', 'TZS ' . number_format($totalAssets, 2), ''];
        $data[] = ['Total Liabilities & Equity:', 'TZS ' . number_format($this->statementData['totals']['total_liabilities_and_equity'], 2), ''];
        $data[] = ['Difference:', 'TZS ' . number_format($this->statementData['totals']['difference'], 2), ''];
        $data[] = ['Status:', $this->statementData['totals']['is_balanced'] ? 'BALANCED' : 'NOT BALANCED', ''];
        
        return $data;
    }

    public function headings(): array
    {
        return [
            'Description',
            'Account/Details',
            'Amount (TZS)'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 35,
            'B' => 40,
            'C' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 12
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
        $sheet->getStyle('A1:C' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E5E5']
                ]
            ]
        ]);

        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                
                // Style section headers (ASSETS, LIABILITIES, EQUITY)
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    
                    if (in_array($cellValue, ['ASSETS', 'LIABILITIES', 'EQUITY', 'FINANCIAL POSITION SUMMARY', 'FINANCIAL RATIOS', 'BALANCE VERIFICATION'])) {
                        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'E3F2FD']
                            ]
                        ]);
                    }
                    
                    // Style totals
                    if (in_array($cellValue, ['TOTAL ASSETS', 'TOTAL LIABILITIES', 'TOTAL EQUITY', 'TOTAL LIABILITIES AND EQUITY'])) {
                        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 11
                            ],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FFF3CD']
                            ]
                        ]);
                    }
                    
                    // Style main title
                    if ($cellValue === 'STATEMENT OF FINANCIAL POSITION') {
                        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 14
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER
                            ]
                        ]);
                    }
                }
                
                // Right-align amount column
                $sheet->getStyle('C1:C' . $highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_RIGHT
                    ]
                ]);
            },
        ];
    }

    public function title(): string
    {
        return 'Statement of Financial Position';
    }
}
