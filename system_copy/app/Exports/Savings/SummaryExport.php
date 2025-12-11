<?php

namespace App\Exports\Savings;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class SummaryExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $summaryData;
    protected $month;
    protected $year;

    public function __construct($summaryData, $month, $year)
    {
        $this->summaryData = $summaryData;
        $this->month = $month;
        $this->year = $year;
    }

    public function array(): array
    {
        return [
            [
                'Total Savings',
                'TZS ' . number_format($this->summaryData['total_savings'] ?? 0, 2),
                'Total amount saved across all accounts'
            ],
            [
                'Active Accounts',
                number_format($this->summaryData['active_accounts'] ?? 0),
                'Number of active savings accounts'
            ],
            [
                'Inactive Accounts',
                number_format($this->summaryData['inactive_accounts'] ?? 0),
                'Number of inactive savings accounts'
            ],
            [
                'Total Members',
                number_format($this->summaryData['total_members'] ?? 0),
                'Members with savings accounts'
            ],
            [
                'Total Deposits',
                'TZS ' . number_format($this->summaryData['total_deposits'] ?? 0, 2),
                'Total deposits for the month'
            ],
            [
                'Total Withdrawals',
                'TZS ' . number_format($this->summaryData['total_withdrawals'] ?? 0, 2),
                'Total withdrawals for the month'
            ],
            [
                'Net Change',
                'TZS ' . number_format(($this->summaryData['total_deposits'] ?? 0) - ($this->summaryData['total_withdrawals'] ?? 0), 2),
                'Net savings change for the month'
            ],
            [
                'Average Balance',
                'TZS ' . number_format($this->summaryData['average_balance'] ?? 0, 2),
                'Average account balance'
            ],
            [
                'Total Products',
                number_format($this->summaryData['total_products'] ?? 0),
                'Number of savings products'
            ],
            [
                'Non-Compliant Members',
                number_format($this->summaryData['non_compliant_members'] ?? 0),
                'Members without savings or with zero balance'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Metric',
            'Value',
            'Description'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:C1')->applyFromArray([
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
        $sheet->getStyle('A1:C' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E5E5']
                ]
            ]
        ]);

        // Add title and date
        $sheet->insertNewRowBefore(1, 2);
        $sheet->setCellValue('A1', 'Savings Summary Report');
        $sheet->setCellValue('A2', Carbon::create($this->year, $this->month)->format('F Y'));
        $sheet->mergeCells('A1:C1');
        $sheet->mergeCells('A2:C2');
        
        $sheet->getStyle('A1:A2')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ]);

        return [];
    }

    public function title(): string
    {
        return 'Summary';
    }
}