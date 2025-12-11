<?php

namespace App\Exports;

use App\Models\LoansModel;
use App\Models\ClientsModel;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\DB;

class LoansToInsidersReportExport implements WithMultipleSheets
{
    protected $insiderLoans;
    protected $relatedPartyLoans;
    protected $insiderCategories;
    protected $reportDate;

    public function __construct($insiderLoans, $relatedPartyLoans, $insiderCategories, $reportDate)
    {
        $this->insiderLoans = $insiderLoans;
        $this->relatedPartyLoans = $relatedPartyLoans;
        $this->insiderCategories = $insiderCategories;
        $this->reportDate = $reportDate;
    }

    public function sheets(): array
    {
        return [
            'Insider Loans' => new InsiderLoansSheet($this->insiderLoans),
            'Related Party Loans' => new RelatedPartyLoansSheet($this->relatedPartyLoans),
            'Summary' => new InsiderLoansSummarySheet($this->insiderCategories, $this->reportDate),
        ];
    }
}

class InsiderLoansSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $insiderLoans;

    public function __construct($insiderLoans)
    {
        $this->insiderLoans = $insiderLoans;
    }

    public function collection()
    {
        return collect($this->insiderLoans);
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Loan ID',
            'Client Number',
            'Client Name',
            'Employee Name',
            'Position',
            'Department',
            'Loan Amount (TZS)',
            'Outstanding Balance (TZS)',
            'Interest Rate (%)',
            'Disbursement Date',
            'Maturity Date',
            'Loan Status',
            'Days in Arrears',
            'Approval Status',
            'Collateral Value (TZS)',
            'Guarantor Count'
        ];
    }

    public function map($loan): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $loan['loan_id'] ?? 'N/A',
            $loan['client_number'] ?? 'N/A',
            $loan['client_name'] ?? 'N/A',
            $loan['employee_name'] ?? 'N/A',
            $loan['employee_position'] ?? 'N/A',
            $loan['employee_department'] ?? 'N/A',
            number_format($loan['loan_amount'], 2),
            number_format($loan['outstanding_balance'], 2),
            $loan['interest_rate'],
            $loan['disbursement_date'] ?? 'N/A',
            $loan['maturity_date'] ?? 'N/A',
            $loan['loan_status'] ?? 'N/A',
            $loan['days_in_arrears'],
            $loan['approval_status'] ?? 'N/A',
            number_format($loan['collateral_value'], 2),
            $loan['guarantor_count']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styles
        $sheet->getStyle('A1:Q1')->applyFromArray([
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
            $sheet->getStyle('A2:Q' . $lastRow)->applyFromArray([
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
                    $sheet->getStyle('A' . $row . ':Q' . $row)->applyFromArray([
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
            'B' => 15,  // Loan ID
            'C' => 15,  // Client Number
            'D' => 25,  // Client Name
            'E' => 25,  // Employee Name
            'F' => 20,  // Position
            'G' => 20,  // Department
            'H' => 18,  // Loan Amount
            'I' => 20,  // Outstanding Balance
            'J' => 15,  // Interest Rate
            'K' => 15,  // Disbursement Date
            'L' => 15,  // Maturity Date
            'M' => 12,  // Loan Status
            'N' => 15,  // Days in Arrears
            'O' => 15,  // Approval Status
            'P' => 18,  // Collateral Value
            'Q' => 15,  // Guarantor Count
        ];
    }

    public function title(): string
    {
        return 'Insider Loans';
    }
}

class RelatedPartyLoansSheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $relatedPartyLoans;

    public function __construct($relatedPartyLoans)
    {
        $this->relatedPartyLoans = $relatedPartyLoans;
    }

    public function collection()
    {
        return collect($this->relatedPartyLoans);
    }

    public function headings(): array
    {
        return [
            'S/N',
            'Loan ID',
            'Client Number',
            'Client Name',
            'Relationship Type',
            'Loan Amount (TZS)',
            'Outstanding Balance (TZS)',
            'Interest Rate (%)',
            'Disbursement Date',
            'Maturity Date',
            'Loan Status',
            'Days in Arrears',
            'Approval Status',
            'Collateral Value (TZS)',
            'Guarantor Count'
        ];
    }

    public function map($loan): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $loan['loan_id'] ?? 'N/A',
            $loan['client_number'] ?? 'N/A',
            $loan['client_name'] ?? 'N/A',
            $loan['relationship_type'] ?? 'N/A',
            number_format($loan['loan_amount'], 2),
            number_format($loan['outstanding_balance'], 2),
            $loan['interest_rate'],
            $loan['disbursement_date'] ?? 'N/A',
            $loan['maturity_date'] ?? 'N/A',
            $loan['loan_status'] ?? 'N/A',
            $loan['days_in_arrears'],
            $loan['approval_status'] ?? 'N/A',
            number_format($loan['collateral_value'], 2),
            $loan['guarantor_count']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Header styles
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '059669']
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
            $sheet->getStyle('A2:O' . $lastRow)->applyFromArray([
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
                    $sheet->getStyle('A' . $row . ':O' . $row)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F0FDF4']
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
            'B' => 15,  // Loan ID
            'C' => 15,  // Client Number
            'D' => 25,  // Client Name
            'E' => 20,  // Relationship Type
            'F' => 18,  // Loan Amount
            'G' => 20,  // Outstanding Balance
            'H' => 15,  // Interest Rate
            'I' => 15,  // Disbursement Date
            'J' => 15,  // Maturity Date
            'K' => 12,  // Loan Status
            'L' => 15,  // Days in Arrears
            'M' => 15,  // Approval Status
            'N' => 18,  // Collateral Value
            'O' => 15,  // Guarantor Count
        ];
    }

    public function title(): string
    {
        return 'Related Party Loans';
    }
}

class InsiderLoansSummarySheet implements WithStyles, WithColumnWidths, WithTitle
{
    protected $insiderCategories;
    protected $reportDate;

    public function __construct($insiderCategories, $reportDate)
    {
        $this->insiderCategories = $insiderCategories;
        $this->reportDate = $reportDate;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 20,
            'D' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add summary data
        $sheet->setCellValue('A1', 'Loans to Insiders Report Summary');
        $sheet->setCellValue('A2', 'Report Date: ' . $this->reportDate);
        $sheet->setCellValue('A4', 'Category');
        $sheet->setCellValue('B4', 'Count');
        $sheet->setCellValue('C4', 'Total Amount (TZS)');
        $sheet->setCellValue('D4', 'Average Amount (TZS)');

        $row = 5;
        foreach ($this->insiderCategories as $category => $data) {
            $sheet->setCellValue('A' . $row, ucfirst(str_replace('_', ' ', $category)));
            $sheet->setCellValue('B' . $row, $data['count']);
            $sheet->setCellValue('C' . $row, number_format($data['total_amount'], 2));
            $sheet->setCellValue('D' . $row, number_format($data['average_amount'], 2));
            $row++;
        }

        // Style the header
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16
            ]
        ]);

        $sheet->getStyle('A4:D4')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '7C3AED']
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

        // Style data rows
        $lastRow = $row - 1;
        if ($lastRow >= 5) {
            $sheet->getStyle('A5:D' . $lastRow)->applyFromArray([
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
        }

        return [];
    }
}
