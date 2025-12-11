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

class TransactionsExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle, WithMapping
{
    protected $transactions;
    protected $month;
    protected $year;

    public function __construct($transactions, $month, $year)
    {
        $this->transactions = $transactions;
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return $this->transactions;
    }

    public function map($transaction): array
    {
        return [
            $transaction->reference_number ?? '',
            Carbon::parse($transaction->created_at)->format('d/m/Y H:i'),
            $transaction->transaction_type ?? '',
            $transaction->account->account_number ?? '',
            $transaction->account->account_name ?? '',
            'TZS ' . number_format($transaction->debit_amount ?? 0, 2),
            'TZS ' . number_format($transaction->credit_amount ?? 0, 2),
            'TZS ' . number_format($transaction->balance ?? 0, 2),
            $transaction->narration ?? '',
            $transaction->created_by_name ?? '',
            $transaction->status ?? 'COMPLETED'
        ];
    }

    public function headings(): array
    {
        return [
            'Reference Number',
            'Date & Time',
            'Transaction Type',
            'Account Number',
            'Account Name',
            'Debit Amount',
            'Credit Amount',
            'Balance',
            'Narration',
            'Processed By',
            'Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style the header row
        $sheet->getStyle('A1:K1')->applyFromArray([
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
        $sheet->getStyle('A1:K' . $highestRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'E5E5E5']
                ]
            ]
        ]);

        // Highlight credit transactions in green
        for ($row = 2; $row <= $highestRow; $row++) {
            $credit = $sheet->getCell('G' . $row)->getValue();
            if ($credit && floatval(str_replace(['TZS ', ','], '', $credit)) > 0) {
                $sheet->getStyle('G' . $row)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => '16A34A']
                    ]
                ]);
            }
            
            // Highlight debit transactions in red
            $debit = $sheet->getCell('F' . $row)->getValue();
            if ($debit && floatval(str_replace(['TZS ', ','], '', $debit)) > 0) {
                $sheet->getStyle('F' . $row)->applyFromArray([
                    'font' => [
                        'color' => ['rgb' => 'DC2626']
                    ]
                ]);
            }
        }

        return [];
    }

    public function title(): string
    {
        return 'Transactions';
    }
}