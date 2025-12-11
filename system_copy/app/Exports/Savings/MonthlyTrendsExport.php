<?php

namespace App\Exports\Savings;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCharts;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class MonthlyTrendsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithMapping
{
    protected $monthlyTotals;
    protected $year;

    public function __construct($monthlyTotals, $year)
    {
        $this->monthlyTotals = $monthlyTotals;
        $this->year = $year;
    }

    public function collection()
    {
        return collect($this->monthlyTotals);
    }

    public function map($month): array
    {
        $deposits = $month['total_deposits'] ?? 0;
        $withdrawals = $month['total_withdrawals'] ?? 0;
        $netChange = $deposits - $withdrawals;
        
        return [
            Carbon::create()->month($month['month'])->format('F'),
            'TZS ' . number_format($deposits, 2),
            'TZS ' . number_format($withdrawals, 2),
            'TZS ' . number_format($netChange, 2),
            number_format($month['transaction_count'] ?? 0),
            $netChange > 0 ? 'Positive' : ($netChange < 0 ? 'Negative' : 'Neutral')
        ];
    }

    public function headings(): array
    {
        return [
            'Month',
            'Total Deposits',
            'Total Withdrawals',
            'Net Change',
            'Transaction Count',
            'Trend'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:F1')->applyFromArray([
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
        $sheet->getStyle('A1:F' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E5E5']
                ]
            ]
        ]);

        // Color code the trends
        for ($row = 2; $row <= $highestRow; $row++) {
            $trend = $sheet->getCell('F' . $row)->getValue();
            $color = 'FFFFFF';
            
            if ($trend === 'Positive') {
                $color = 'DCFCE7'; // Light green
            } elseif ($trend === 'Negative') {
                $color = 'FEE2E2'; // Light red
            } else {
                $color = 'F3F4F6'; // Light gray
            }
            
            $sheet->getStyle('F' . $row)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $color]
                ]
            ]);
        }

        // Add title
        $sheet->insertNewRowBefore(1, 2);
        $sheet->setCellValue('A1', 'Monthly Savings Trends Report');
        $sheet->setCellValue('A2', 'Year: ' . $this->year);
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        
        $sheet->getStyle('A1:A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        // Add summary row at the bottom
        $lastRow = $highestRow + 3;
        $sheet->setCellValue('A' . ($lastRow - 1), 'YEARLY TOTALS');
        $sheet->mergeCells('A' . ($lastRow - 1) . ':A' . ($lastRow - 1));
        
        // Calculate totals
        $totalDeposits = 0;
        $totalWithdrawals = 0;
        $totalTransactions = 0;
        
        foreach ($this->monthlyTotals as $month) {
            $totalDeposits += $month['total_deposits'] ?? 0;
            $totalWithdrawals += $month['total_withdrawals'] ?? 0;
            $totalTransactions += $month['transaction_count'] ?? 0;
        }
        
        $sheet->setCellValue('B' . ($lastRow - 1), 'TZS ' . number_format($totalDeposits, 2));
        $sheet->setCellValue('C' . ($lastRow - 1), 'TZS ' . number_format($totalWithdrawals, 2));
        $sheet->setCellValue('D' . ($lastRow - 1), 'TZS ' . number_format($totalDeposits - $totalWithdrawals, 2));
        $sheet->setCellValue('E' . ($lastRow - 1), number_format($totalTransactions));
        
        $sheet->getStyle('A' . ($lastRow - 1) . ':F' . ($lastRow - 1))->applyFromArray([
            'font' => [
                'bold' => true
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E5E7EB']
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Monthly Trends';
    }
}