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

class StatementOfComprehensiveIncomeExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithColumnWidths, WithEvents
{
    protected $statementData;
    protected $startDate;
    protected $endDate;

    public function __construct($statementData, $startDate = null, $endDate = null)
    {
        $this->statementData = $statementData;
        $this->startDate = $startDate ?: Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = $endDate ?: Carbon::now()->format('Y-m-d');
    }

    public function array(): array
    {
        $data = [];
        
        // Add header information
        $data[] = ['STATEMENT OF COMPREHENSIVE INCOME', '', ''];
        $data[] = ['For the period from ' . Carbon::parse($this->startDate)->format('F d, Y') . ' to ' . Carbon::parse($this->endDate)->format('F d, Y'), '', ''];
        $data[] = ['(All amounts in Tanzanian Shillings)', '', ''];
        $data[] = ['', '', '']; // Empty row
        
        // Add summary section
        $totalIncome = $this->statementData['income']['total'];
        $totalExpenses = $this->statementData['expenses']['total'];
        $netIncome = $totalIncome - $totalExpenses;
        
        $data[] = ['INCOME STATEMENT SUMMARY', '', ''];
        $data[] = ['Total Revenue:', 'TZS ' . number_format($totalIncome, 2), ''];
        $data[] = ['Total Expenses:', 'TZS ' . number_format($totalExpenses, 2), ''];
        $data[] = ['Net Income:', 'TZS ' . number_format($netIncome, 2), ''];
        $data[] = ['', '', '']; // Empty row
        
        // Add performance metrics
        $data[] = ['PERFORMANCE METRICS', '', ''];
        $data[] = ['Profit Margin:', $totalIncome > 0 ? number_format(($netIncome / $totalIncome) * 100, 1) . '%' : 'N/A', ''];
        $data[] = ['Expense Ratio:', $totalIncome > 0 ? number_format(($totalExpenses / $totalIncome) * 100, 1) . '%' : 'N/A', ''];
        $data[] = ['Performance:', $netIncome > 0 ? 'Profitable' : 'Loss', ''];
        $data[] = ['', '', '']; // Empty row
        
        // Add REVENUE section
        $data[] = ['REVENUE', '', ''];
        foreach ($this->statementData['income']['categories'] as $categoryCode => $category) {
            $data[] = [$category['name'], '', ''];
            foreach ($category['accounts'] as $account) {
                $data[] = ['', is_object($account) ? $account->account_name : $account['account_name'], number_format(is_object($account) ? $account->current_balance : $account['current_balance'], 2)];
            }
            $data[] = ['', 'Subtotal', number_format($category['subtotal'], 2)];
            $data[] = ['', '', '']; // Empty row after each category
        }
        $data[] = ['TOTAL REVENUE', '', number_format($totalIncome, 2)];
        $data[] = ['', '', '']; // Empty row
        
        // Add EXPENSES section
        $data[] = ['EXPENSES', '', ''];
        foreach ($this->statementData['expenses']['categories'] as $categoryCode => $category) {
            $data[] = [$category['name'], '', ''];
            foreach ($category['accounts'] as $account) {
                $data[] = ['', is_object($account) ? $account->account_name : $account['account_name'], number_format(is_object($account) ? $account->current_balance : $account['current_balance'], 2)];
            }
            $data[] = ['', 'Subtotal', number_format($category['subtotal'], 2)];
            $data[] = ['', '', '']; // Empty row after each category
        }
        $data[] = ['TOTAL EXPENSES', '', number_format($totalExpenses, 2)];
        $data[] = ['', '', '']; // Empty row
        
        // Add NET INCOME
        $data[] = ['NET INCOME (LOSS)', '', number_format($netIncome, 2)];
        $data[] = ['', '', '']; // Empty row
        
        // Add performance summary
        $data[] = ['PERFORMANCE SUMMARY', '', ''];
        if ($netIncome > 0) {
            $data[] = ['Status:', 'PROFITABLE PERIOD', ''];
            $data[] = ['Net Income:', 'TZS ' . number_format($netIncome, 2), ''];
        } else {
            $data[] = ['Status:', 'LOSS PERIOD', ''];
            $data[] = ['Net Loss:', 'TZS ' . number_format(abs($netIncome), 2), ''];
        }
        
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
                
                // Style section headers (REVENUE, EXPENSES, etc.)
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    
                    if (in_array($cellValue, ['REVENUE', 'EXPENSES', 'INCOME STATEMENT SUMMARY', 'PERFORMANCE METRICS', 'PERFORMANCE SUMMARY'])) {
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
                    if (in_array($cellValue, ['TOTAL REVENUE', 'TOTAL EXPENSES', 'NET INCOME (LOSS)'])) {
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
                    if ($cellValue === 'STATEMENT OF COMPREHENSIVE INCOME') {
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
        return 'Statement of Comprehensive Income';
    }
}
