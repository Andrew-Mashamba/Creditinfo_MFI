<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncomeReportExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    protected $startDate;
    protected $endDate;
    protected $incomeBreakdown;

    public function __construct($startDate, $endDate, $incomeBreakdown)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->incomeBreakdown = $incomeBreakdown;
    }

    public function collection()
    {
        return collect($this->incomeBreakdown)->map(function ($item) {
            return [
                'account_number' => $item['account_number'],
                'account_name' => $item['account_name'],
                'period_income' => number_format($item['period_income'], 2),
                'ytd_balance' => number_format($item['ytd_balance'], 2),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Account Number',
            'Category',
            'Period Income (' . $this->startDate . ' to ' . $this->endDate . ')',
            'YTD Balance',
        ];
    }

    public function title(): string
    {
        return 'Income Report';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
