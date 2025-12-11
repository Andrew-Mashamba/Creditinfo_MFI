<?php

namespace App\Exports\Savings;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class AccountsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithMapping
{
    protected $accountsData;
    protected $month;
    protected $year;

    public function __construct($accountsData, $month, $year)
    {
        $this->accountsData = $accountsData;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return collect($this->accountsData);
    }

    public function map($account): array
    {
        return [
            $account['account_number'] ?? '',
            $account['account_name'] ?? '',
            ($account['first_name'] ?? '') . ' ' . ($account['last_name'] ?? ''),
            $account['client_number'] ?? '',
            $account['product_name'] ?? '',
            'TZS ' . number_format($account['balance'] ?? 0, 2),
            $account['status'] ?? '',
            Carbon::parse($account['created_at'] ?? now())->format('d/m/Y'),
            Carbon::parse($account['updated_at'] ?? now())->format('d/m/Y H:i')
        ];
    }

    public function headings(): array
    {
        return [
            'Account Number',
            'Account Name',
            'Member Name',
            'Client Number',
            'Product',
            'Balance',
            'Status',
            'Created Date',
            'Last Updated'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:I1')->applyFromArray([
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
        $sheet->getStyle('A1:I' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E5E5']
                ]
            ]
        ]);

        // Highlight inactive accounts
        for ($row = 2; $row <= $highestRow; $row++) {
            $status = $sheet->getCell('G' . $row)->getValue();
            if ($status !== 'ACTIVE') {
                $sheet->getStyle('A' . $row . ':I' . $row)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEE2E2']
                    ]
                ]);
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Accounts';
    }
}