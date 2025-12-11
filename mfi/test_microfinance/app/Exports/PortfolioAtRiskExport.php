<?php

namespace App\Exports;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\loans_schedules;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PortfolioAtRiskExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $parRange;
    protected $selectedCategory;

    public function __construct($parRange, $selectedCategory)
    {
        $this->parRange = $parRange;
        $this->selectedCategory = $selectedCategory;
    }

    public function collection()
    {
        $query = LoansModel::query()->where('status', 'ACTIVE');

        if ($this->parRange[1] == 9999) {
            // For above 90 days
            $query->where('days_in_arrears', '>=', $this->parRange[0]);
        } else {
            $query->whereBetween('days_in_arrears', $this->parRange);
        }

        return $query->get()->map(function ($loan) {
            // Get client name
            $client = ClientsModel::where('client_number', $loan->client_number)->first();
            $loan->client_name = $client ? trim($client->first_name . ' ' . $client->middle_name . ' ' . $client->last_name) : 'N/A';

            // Get start date (oldest installment)
            $startDate = loans_schedules::where('loan_id', $loan->id)->oldest()->value('installment_date');
            $loan->start_date = $startDate ? date('Y-m-d', strtotime($startDate)) : 'N/A';

            // Get due date (latest installment)
            $dueDate = loans_schedules::where('loan_id', $loan->id)->latest()->value('installment_date');
            $loan->due_date = $dueDate ? date('Y-m-d', strtotime($dueDate)) : 'N/A';

            // Calculate outstanding amount
            $scheduleQuery = loans_schedules::where('loan_id', $loan->id);
            $totalPrinciple = $scheduleQuery->sum('principle');
            $totalPayment = $scheduleQuery->sum('payment');
            $totalInterest = $scheduleQuery->sum('interest');
            
            $loan->outstanding_amount = $totalPrinciple - ($totalPayment ? $totalPayment - $totalInterest : 0);

            return $loan;
        });
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Client Name',
            'Client Number',
            'Loan Amount (TZS)',
            'Start Date',
            'Due Date',
            'Interest (TZS)',
            'Outstanding Amount (TZS)',
            'Days in Arrears',
            'Status',
            'Risk Category'
        ];
    }

    public function map($loan): array
    {
        static $index = 0;
        $index++;

        // Determine risk category based on days in arrears
        $riskCategory = '';
        if ($loan->days_in_arrears >= 90) {
            $riskCategory = 'DOUBTFUL/LOSS';
        } elseif ($loan->days_in_arrears >= 30) {
            $riskCategory = 'SUBSTANDARD';
        } elseif ($loan->days_in_arrears >= 10) {
            $riskCategory = 'WATCH';
        } else {
            $riskCategory = 'NORMAL';
        }

        return [
            $index,
            $loan->client_name ?? 'N/A',
            $loan->client_number ?? 'N/A',
            number_format($loan->principle, 2),
            $loan->start_date ?? 'N/A',
            $loan->due_date ?? 'N/A',
            number_format($loan->interest, 2),
            number_format($loan->outstanding_amount, 2),
            $loan->days_in_arrears,
            $loan->status,
            $riskCategory
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styles
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E40AF']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);

        // Data rows styles
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A2:K' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC']
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);

            // Alternate row colors
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC']
                        ]
                    ]);
                }
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // S/N
            'B' => 25,  // Client Name
            'C' => 15,  // Client Number
            'D' => 18,  // Loan Amount
            'E' => 12,  // Start Date
            'F' => 12,  // Due Date
            'G' => 15,  // Interest
            'H' => 20,  // Outstanding Amount
            'I' => 15,  // Days in Arrears
            'J' => 12,  // Status
            'K' => 18,  // Risk Category
        ];
    }

    public function title(): string
    {
        $categoryNames = [
            10 => 'PAR 1-10 Days',
            30 => 'PAR 10-30 Days',
            40 => 'PAR 30-90 Days',
            50 => 'PAR Above 90 Days'
        ];

        return $categoryNames[$this->selectedCategory] ?? 'Portfolio at Risk';
    }
}
