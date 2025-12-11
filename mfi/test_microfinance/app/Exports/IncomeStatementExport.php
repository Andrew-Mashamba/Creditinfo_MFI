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

class IncomeStatementExport implements FromArray, WithStyles, ShouldAutoSize, WithTitle, WithColumnWidths, WithEvents
{
    protected $incomeData;
    protected $expenseData;
    protected $comparisonYears;
    protected $selectedYear;

    public function __construct($incomeData, $expenseData, $comparisonYears, $selectedYear)
    {
        $this->incomeData = $incomeData;
        $this->expenseData = $expenseData;
        $this->comparisonYears = $comparisonYears;
        $this->selectedYear = $selectedYear;
    }

    public function array(): array
    {
        $data = [];
        $noteNumber = 1;
        
        // Title
        $data[] = ['INCOME STATEMENT', '', '', ''];
        $data[] = ['For the Year Ended December 31, ' . $this->selectedYear, '', '', ''];
        $data[] = ['', '', '', ''];
        
        // Header row
        $data[] = ['PARTICULARS', 'Note', $this->comparisonYears[0], $this->comparisonYears[1]];
        
        // INCOME SECTION
        $data[] = ['INCOME', '', '', ''];
        
        // Calculate income categories
        $interestIncome = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        $interestOnSavings = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        $otherIncome = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        
        foreach($this->incomeData as $income) {
            $accountName = strtolower($income['account_name']);
            
            if (str_contains($accountName, 'interest') && (str_contains($accountName, 'loan') || str_contains($accountName, 'mikopo'))) {
                $interestIncome[$this->comparisonYears[0]] += $income['years'][$this->comparisonYears[0]] ?? 0;
                $interestIncome[$this->comparisonYears[1]] += $income['years'][$this->comparisonYears[1]] ?? 0;
            }
            elseif (str_contains($accountName, 'interest') && (str_contains($accountName, 'saving') || str_contains($accountName, 'deposit') || str_contains($accountName, 'akiba'))) {
                $interestOnSavings[$this->comparisonYears[0]] += $income['years'][$this->comparisonYears[0]] ?? 0;
                $interestOnSavings[$this->comparisonYears[1]] += $income['years'][$this->comparisonYears[1]] ?? 0;
            }
            else {
                $otherIncome[$this->comparisonYears[0]] += $income['years'][$this->comparisonYears[0]] ?? 0;
                $otherIncome[$this->comparisonYears[1]] += $income['years'][$this->comparisonYears[1]] ?? 0;
            }
        }
        
        // Also check expenses for interest on savings
        foreach($this->expenseData as $expense) {
            $accountName = strtolower($expense['account_name']);
            if (str_contains($accountName, 'interest') && (str_contains($accountName, 'saving') || str_contains($accountName, 'deposit') || str_contains($accountName, 'member'))) {
                $interestOnSavings[$this->comparisonYears[0]] += $expense['years'][$this->comparisonYears[0]] ?? 0;
                $interestOnSavings[$this->comparisonYears[1]] += $expense['years'][$this->comparisonYears[1]] ?? 0;
            }
        }
        
        // Income rows
        $data[] = ['Interest Income on Loans', $noteNumber++, number_format($interestIncome[$this->comparisonYears[0]], 2), number_format($interestIncome[$this->comparisonYears[1]], 2)];
        $data[] = ['Less: Interest on Savings', $noteNumber++, '(' . number_format($interestOnSavings[$this->comparisonYears[0]], 2) . ')', '(' . number_format($interestOnSavings[$this->comparisonYears[1]], 2) . ')'];
        $data[] = ['Other Income', $noteNumber++, number_format($otherIncome[$this->comparisonYears[0]], 2), number_format($otherIncome[$this->comparisonYears[1]], 2)];
        
        // Net interest income calculation
        $netInterestIncome = [
            $this->comparisonYears[0] => $interestIncome[$this->comparisonYears[0]] - $interestOnSavings[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $interestIncome[$this->comparisonYears[1]] - $interestOnSavings[$this->comparisonYears[1]]
        ];
        
        $totalIncome = [
            $this->comparisonYears[0] => $netInterestIncome[$this->comparisonYears[0]] + $otherIncome[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $netInterestIncome[$this->comparisonYears[1]] + $otherIncome[$this->comparisonYears[1]]
        ];
        
        $data[] = ['TOTAL INCOME', '', number_format($totalIncome[$this->comparisonYears[0]], 2), number_format($totalIncome[$this->comparisonYears[1]], 2)];
        $data[] = ['', '', '', ''];
        
        // EXPENSES SECTION
        $data[] = ['OPERATING EXPENSES', '', '', ''];
        
        // Categorize expenses
        $administrativeExpenses = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        $personnelExpenses = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        $operatingExpenses = [$this->comparisonYears[0] => 0, $this->comparisonYears[1] => 0];
        
        foreach($this->expenseData as $expense) {
            $accountName = strtolower($expense['account_name']);
            
            // Skip interest expenses as they're already counted above
            if (str_contains($accountName, 'interest')) {
                continue;
            }
            
            if (str_contains($accountName, 'salary') || str_contains($accountName, 'wage') || 
                str_contains($accountName, 'staff') || str_contains($accountName, 'employee') ||
                str_contains($accountName, 'payroll') || str_contains($accountName, 'utumishi')) {
                $personnelExpenses[$this->comparisonYears[0]] += $expense['years'][$this->comparisonYears[0]] ?? 0;
                $personnelExpenses[$this->comparisonYears[1]] += $expense['years'][$this->comparisonYears[1]] ?? 0;
            }
            elseif (str_contains($accountName, 'admin') || str_contains($accountName, 'office') || 
                    str_contains($accountName, 'rent') || str_contains($accountName, 'utility') ||
                    str_contains($accountName, 'utawala')) {
                $administrativeExpenses[$this->comparisonYears[0]] += $expense['years'][$this->comparisonYears[0]] ?? 0;
                $administrativeExpenses[$this->comparisonYears[1]] += $expense['years'][$this->comparisonYears[1]] ?? 0;
            }
            else {
                $operatingExpenses[$this->comparisonYears[0]] += $expense['years'][$this->comparisonYears[0]] ?? 0;
                $operatingExpenses[$this->comparisonYears[1]] += $expense['years'][$this->comparisonYears[1]] ?? 0;
            }
        }
        
        $data[] = ['Administrative Expenses', $noteNumber++, number_format($administrativeExpenses[$this->comparisonYears[0]], 2), number_format($administrativeExpenses[$this->comparisonYears[1]], 2)];
        $data[] = ['Personnel Expenses', $noteNumber++, number_format($personnelExpenses[$this->comparisonYears[0]], 2), number_format($personnelExpenses[$this->comparisonYears[1]], 2)];
        $data[] = ['Operating Expenses', $noteNumber++, number_format($operatingExpenses[$this->comparisonYears[0]], 2), number_format($operatingExpenses[$this->comparisonYears[1]], 2)];
        
        $totalExpenses = [
            $this->comparisonYears[0] => $administrativeExpenses[$this->comparisonYears[0]] + $personnelExpenses[$this->comparisonYears[0]] + $operatingExpenses[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $administrativeExpenses[$this->comparisonYears[1]] + $personnelExpenses[$this->comparisonYears[1]] + $operatingExpenses[$this->comparisonYears[1]]
        ];
        
        $data[] = ['TOTAL OPERATING EXPENSES', '', number_format($totalExpenses[$this->comparisonYears[0]], 2), number_format($totalExpenses[$this->comparisonYears[1]], 2)];
        $data[] = ['', '', '', ''];
        
        // PROFIT BEFORE TAX
        $profitBeforeTax = [
            $this->comparisonYears[0] => $totalIncome[$this->comparisonYears[0]] - $totalExpenses[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $totalIncome[$this->comparisonYears[1]] - $totalExpenses[$this->comparisonYears[1]]
        ];
        
        $data[] = ['SURPLUS/(DEFICIT) BEFORE TAX', '', number_format($profitBeforeTax[$this->comparisonYears[0]], 2), number_format($profitBeforeTax[$this->comparisonYears[1]], 2)];
        
        // TAX
        $taxRate = 0.30;
        $taxExpense = [
            $this->comparisonYears[0] => max(0, $profitBeforeTax[$this->comparisonYears[0]] * $taxRate),
            $this->comparisonYears[1] => max(0, $profitBeforeTax[$this->comparisonYears[1]] * $taxRate)
        ];
        
        $data[] = ['Less: Tax Expense (30%)', $noteNumber++, number_format($taxExpense[$this->comparisonYears[0]], 2), number_format($taxExpense[$this->comparisonYears[1]], 2)];
        
        // NET PROFIT
        $netProfit = [
            $this->comparisonYears[0] => $profitBeforeTax[$this->comparisonYears[0]] - $taxExpense[$this->comparisonYears[0]],
            $this->comparisonYears[1] => $profitBeforeTax[$this->comparisonYears[1]] - $taxExpense[$this->comparisonYears[1]]
        ];
        
        $data[] = ['NET SURPLUS/(DEFICIT) FOR THE YEAR', '', number_format($netProfit[$this->comparisonYears[0]], 2), number_format($netProfit[$this->comparisonYears[1]], 2)];
        
        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 10,
            'C' => 20,
            'D' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        
        // Add borders to all cells
        $sheet->getStyle('A1:D' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
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
                
                // Style title
                $sheet->mergeCells('A1:D1');
                $sheet->mergeCells('A2:D2');
                $sheet->getStyle('A1:D2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
                
                // Style header row
                $sheet->getStyle('A4:D4')->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E5E5E5']
                    ]
                ]);
                
                // Style section headers
                for ($row = 1; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    
                    if (in_array($cellValue, ['INCOME', 'OPERATING EXPENSES'])) {
                        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'DBEAFE']
                            ]
                        ]);
                    }
                    
                    if (in_array($cellValue, ['TOTAL INCOME', 'TOTAL OPERATING EXPENSES', 'SURPLUS/(DEFICIT) BEFORE TAX', 'NET SURPLUS/(DEFICIT) FOR THE YEAR'])) {
                        $sheet->getStyle('A' . $row . ':D' . $row)->applyFromArray([
                            'font' => ['bold' => true],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'FEF3C7']
                            ]
                        ]);
                    }
                }
                
                // Right-align amount columns
                $sheet->getStyle('C4:D' . $highestRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT]
                ]);
                
                // Center-align note column
                $sheet->getStyle('B4:B' . $highestRow)->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);
            },
        ];
    }

    public function title(): string
    {
        return 'Income Statement';
    }
}