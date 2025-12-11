<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LoanPortfolioReportExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    protected $reportData;
    protected $reportDate;

    public function __construct($reportData, $reportDate)
    {
        $this->reportData = $reportData;
        $this->reportDate = $reportDate;
    }

    public function array(): array
    {
        $data = [];
        
        // Add summary data
        $data[] = ['LOAN PORTFOLIO REPORT SUMMARY'];
        $data[] = ['Report Date', $this->reportData['period']['end_date']];
        $data[] = ['Total Portfolio', number_format($this->reportData['portfolio_summary']['total_portfolio'], 2)];
        $data[] = ['Number of Loans', $this->reportData['portfolio_summary']['number_of_loans']];
        $data[] = ['Average Loan Size', number_format($this->reportData['portfolio_summary']['average_loan_size'], 2)];
        $data[] = ['Largest Loan', number_format($this->reportData['portfolio_summary']['largest_loan'], 2)];
        $data[] = ['Smallest Loan', number_format($this->reportData['portfolio_summary']['smallest_loan'], 2)];
        $data[] = []; // Empty row
        
        // Add financial metrics
        $data[] = ['FINANCIAL METRICS'];
        $data[] = ['Interest Income', number_format($this->reportData['financial_metrics']['total_interest_income'], 2)];
        $data[] = ['Portfolio Yield', number_format($this->reportData['financial_metrics']['portfolio_yield'], 2) . '%'];
        $data[] = ['Average Interest Rate', number_format($this->reportData['financial_metrics']['average_interest_rate'], 2) . '%'];
        $data[] = ['Provision for Losses', number_format($this->reportData['financial_metrics']['provision_for_losses'], 2)];
        $data[] = []; // Empty row
        
        // Add risk analysis
        $data[] = ['RISK ANALYSIS'];
        $data[] = ['Portfolio at Risk', number_format($this->reportData['risk_analysis']['portfolio_at_risk'], 2)];
        $data[] = ['PAR Ratio', number_format($this->reportData['risk_analysis']['portfolio_at_risk_ratio'], 2) . '%'];
        $data[] = ['NPL Ratio', number_format($this->reportData['risk_analysis']['non_performing_loan_ratio'], 2) . '%'];
        $data[] = []; // Empty row
        
        // Add delinquency analysis
        $data[] = ['DELINQUENCY ANALYSIS'];
        $data[] = ['Category', 'Amount', 'Count', 'Percentage'];
        foreach ($this->reportData['risk_analysis']['delinquency_buckets'] as $category => $bucket) {
            $categoryName = $this->getCategoryDisplayName($category);
            $percentage = $this->reportData['portfolio_summary']['total_portfolio'] > 0 ? 
                number_format(($bucket['amount'] / $this->reportData['portfolio_summary']['total_portfolio']) * 100, 1) : 0;
            $data[] = [
                $categoryName,
                number_format($bucket['amount'], 2),
                $bucket['count'],
                $percentage . '%'
            ];
        }
        $data[] = []; // Empty row
        
        // Add trend analysis
        $data[] = ['TREND ANALYSIS'];
        $data[] = ['Month-over-Month Growth', number_format($this->reportData['trend_analysis']['month_over_month_growth'], 2) . '%'];
        $data[] = ['Year-over-Year Growth', number_format($this->reportData['trend_analysis']['year_over_year_growth'], 2) . '%'];
        $data[] = ['Current Portfolio', number_format($this->reportData['trend_analysis']['current_portfolio'], 2)];
        $data[] = []; // Empty row
        
        // Add portfolio by type
        $data[] = ['PORTFOLIO BY TYPE'];
        $data[] = ['Loan Type', 'Amount', 'Percentage'];
        foreach ($this->reportData['portfolio_by_type'] as $type => $amount) {
            $percentage = $this->reportData['portfolio_summary']['total_portfolio'] > 0 ? 
                number_format(($amount / $this->reportData['portfolio_summary']['total_portfolio']) * 100, 1) : 0;
            $data[] = [
                $type,
                number_format($amount, 2),
                $percentage . '%'
            ];
        }
        $data[] = []; // Empty row
        
        // Add detailed loan data
        $data[] = ['DETAILED LOAN PORTFOLIO'];
        $data[] = [
            'Loan ID',
            'Client Number',
            'Business Name',
            'Category',
            'Outstanding Balance',
            'Outstanding Principal',
            'Outstanding Interest',
            'Outstanding Penalties',
            'Days Past Due',
            'Risk Level',
            'Interest Rate',
            'Status'
        ];
        
        foreach ($this->reportData['loan_details'] as $loan) {
            $data[] = [
                $loan['loan_id'],
                $loan['client_number'],
                $loan['business_name'],
                $loan['category'],
                number_format($loan['outstanding_balance'], 2),
                number_format($loan['outstanding_principal'], 2),
                number_format($loan['outstanding_interest'], 2),
                number_format($loan['outstanding_penalties'], 2),
                $loan['days_past_due'],
                $loan['risk_level'],
                number_format($loan['interest_rate'], 2) . '%',
                $loan['status']
            ];
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return 'Loan Portfolio Report';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 25,
            'D' => 20,
            'E' => 18,
            'F' => 18,
            'G' => 18,
            'H' => 18,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (title)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Style headers and sections
                $this->styleHeaders($sheet);
                $this->styleSections($sheet);
                $this->addBorders($sheet);
            },
        ];
    }

    private function styleHeaders($sheet)
    {
        // Style section headers
        $sectionHeaders = ['LOAN PORTFOLIO REPORT SUMMARY', 'FINANCIAL METRICS', 'RISK ANALYSIS', 
                          'DELINQUENCY ANALYSIS', 'TREND ANALYSIS', 'PORTFOLIO BY TYPE', 'DETAILED LOAN PORTFOLIO'];
        
        foreach ($sectionHeaders as $header) {
            $row = $this->findRowByText($sheet, $header);
            if ($row) {
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '366092'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
            }
        }
    }

    private function styleSections($sheet)
    {
        // Style column headers for detailed loan data
        $headerRow = $this->findRowByText($sheet, 'DETAILED LOAN PORTFOLIO') + 1;
        if ($headerRow) {
            $sheet->getStyle("A{$headerRow}:L{$headerRow}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);
        }
    }

    private function addBorders($sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        $sheet->getStyle("A1:{$highestColumn}{$highestRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    private function findRowByText($sheet, $text)
    {
        $highestRow = $sheet->getHighestRow();
        
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell("A{$row}")->getValue();
            if ($cellValue === $text) {
                return $row;
            }
        }
        
        return null;
    }

    private function getCategoryDisplayName($category)
    {
        $categoryNames = [
            'current' => 'Current',
            '1-30_days' => '1-30 Days',
            '31-60_days' => '31-60 Days',
            '61-90_days' => '61-90 Days',
            '91-180_days' => '91-180 Days',
            'over_180_days' => 'Over 180 Days',
        ];
        
        return $categoryNames[$category] ?? $category;
    }
}
