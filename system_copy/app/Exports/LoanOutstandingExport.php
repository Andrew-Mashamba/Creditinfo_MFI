<?php

namespace App\Exports;

use App\Services\LoanOutstandingService;
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

class LoanOutstandingExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
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
        $service = new LoanOutstandingService();
        $data = $service->calculateLoanOutstanding();
        $loans = collect($data['loans']);

        // Apply search filter
        if ($this->search) {
            $loans = $loans->filter(function($loan) {
                return stripos($loan->loan_account_number, $this->search) !== false ||
                       stripos($loan->client_number, $this->search) !== false ||
                       stripos($loan->client_name, $this->search) !== false ||
                       stripos($loan->loan_id, $this->search) !== false;
            });
        }

        // Apply status filter
        if ($this->filterStatus === 'overdue') {
            $loans = $loans->filter(function($loan) {
                return $loan->is_overdue;
            });
        } elseif ($this->filterStatus === 'performing') {
            $loans = $loans->filter(function($loan) {
                return !$loan->is_overdue;
            });
        } elseif ($this->filterStatus === 'high_risk') {
            $loans = $loans->filter(function($loan) {
                return $loan->days_in_arrears > 90;
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
            'Client Name',
            'Principal Outstanding (TZS)',
            'Interest Outstanding (TZS)',
            'Total Outstanding (TZS)',
            'Overdue Amount (TZS)',
            'Days Overdue',
            'Status',
            'Disbursement Date',
            'Principal Amount (TZS)',
            'Interest Rate (%)',
            'Repayment Progress (%)',
            'Total Installments',
            'Paid Installments',
            'First Installment Date',
            'Last Installment Date'
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
            $loan->client_name,
            number_format($loan->principal_outstanding, 2),
            number_format($loan->interest_outstanding, 2),
            number_format($loan->total_outstanding, 2),
            number_format($loan->overdue_amount, 2),
            $loan->days_in_arrears > 0 ? $loan->days_in_arrears : '-',
            $loan->is_overdue ? 'Overdue' : 'Current',
            $loan->disbursement_date,
            number_format($loan->principle, 2),
            number_format($loan->interest, 2),
            number_format($loan->repayment_progress, 2),
            $loan->total_installments,
            $loan->paid_installments,
            $loan->first_installment_date,
            $loan->last_installment_date
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Loan Outstanding Report';
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
