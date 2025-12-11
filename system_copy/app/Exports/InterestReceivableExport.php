<?php

namespace App\Exports;

use App\Services\LoanInterestReceivableService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InterestReceivableExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $search;
    protected $filterStatus;

    public function __construct($search = '', $filterStatus = 'all')
    {
        $this->search = $search;
        $this->filterStatus = $filterStatus;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $service = new LoanInterestReceivableService();
        $data = $service->calculateInterestReceivable();
        $loans = collect($data['loans']);

        // Apply search filter
        if ($this->search) {
            $loans = $loans->filter(function($loan) {
                return stripos($loan->loan_account_number, $this->search) !== false ||
                       stripos($loan->client_number, $this->search) !== false ||
                       stripos($loan->loan_id, $this->search) !== false;
            });
        }

        // Apply status filter
        if ($this->filterStatus === 'overdue') {
            $loans = $loans->filter(function($loan) {
                return $loan->overdue_interest > 0;
            });
        } elseif ($this->filterStatus === 'current') {
            $loans = $loans->filter(function($loan) {
                return $loan->overdue_interest == 0 && $loan->total_interest_receivable > 0;
            });
        }

        return $loans;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Loan Account Number',
            'Client Number',
            'Loan ID',
            'Interest Scheduled (TZS)',
            'Interest Paid (TZS)',
            'Interest Receivable (TZS)',
            'Overdue Interest (TZS)',
            'Future Interest (TZS)',
            'Principal Amount (TZS)',
            'Interest Rate (%)',
            'Disbursement Date',
            'Status',
            'Total Installments',
            'Paid Installments'
        ];
    }

    /**
     * @param mixed $loan
     * @return array
     */
    public function map($loan): array
    {
        return [
            $loan->loan_account_number,
            $loan->client_number,
            $loan->loan_id,
            number_format($loan->total_interest_scheduled, 2),
            number_format($loan->total_interest_paid, 2),
            number_format($loan->total_interest_receivable, 2),
            number_format($loan->overdue_interest, 2),
            number_format($loan->future_interest, 2),
            number_format($loan->principle, 2),
            number_format($loan->interest, 2),
            $loan->disbursement_date,
            $loan->status,
            $loan->total_installments,
            $loan->paid_installments
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Interest Receivable Report';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2563EB']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
        ];
    }
}
