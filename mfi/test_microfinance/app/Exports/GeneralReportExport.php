<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class GeneralReportExport implements WithMultipleSheets
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            'Overview' => new GeneralOverviewSheet($this->reportData, $this->filters),
            'Client Summary' => new ClientSummarySheet($this->reportData, $this->filters),
            'Loan Summary' => new LoanSummarySheet($this->reportData, $this->filters),
            'Deposit Summary' => new DepositSummarySheet($this->reportData, $this->filters),
            'Branch Performance' => new BranchPerformanceSheet($this->reportData, $this->filters),
            'Product Performance' => new ProductPerformanceSheet($this->reportData, $this->filters),
            'Staff Performance' => new StaffPerformanceSheet($this->reportData, $this->filters),
            'Financial Summary' => new FinancialSummarySheet($this->reportData, $this->filters),
            'Operational Metrics' => new OperationalMetricsSheet($this->reportData, $this->filters),
        ];
    }
}

class GeneralOverviewSheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Overview';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'GENERAL REPORT - OVERVIEW');
                $sheet->mergeCells('A1:C1');
                
                // Add report period
                $reportPeriod = $this->filters['report_period'] ?? 'General Report';
                $sheet->setCellValue('A2', 'Report Period: ' . $reportPeriod);
                $sheet->mergeCells('A2:C2');
                
                // Add generation date
                $sheet->setCellValue('A3', 'Generated on: ' . Carbon::now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A3:C3');
                
                // Add empty row
                $sheet->setCellValue('A4', '');
                
                // Add headers
                $sheet->setCellValue('A5', 'Metric');
                $sheet->setCellValue('B5', 'Value');
                $sheet->setCellValue('C5', 'Notes');
                
                // Add overview data
                $overviewData = $this->reportData['overview'] ?? [];
                $row = 6;
                
                $metrics = [
                    'Total Members' => $overviewData['total_members'] ?? 0,
                    'Active Members' => $overviewData['active_members'] ?? 0,
                    'New Members This Period' => $overviewData['new_members_this_period'] ?? 0,
                    'Total Loans Outstanding' => $overviewData['total_loans_outstanding'] ?? 0,
                    'Total Loan Portfolio (TZS)' => number_format($overviewData['total_loan_portfolio'] ?? 0, 2),
                    'Total Deposits (TZS)' => number_format($overviewData['total_deposits'] ?? 0, 2),
                    'Total Assets (TZS)' => number_format($overviewData['total_assets'] ?? 0, 2),
                    'Total Liabilities (TZS)' => number_format($overviewData['total_liabilities'] ?? 0, 2),
                    'Net Worth (TZS)' => number_format($overviewData['net_worth'] ?? 0, 2),
                    'Number of Branches' => $overviewData['number_of_branches'] ?? 0,
                    'Number of Staff' => $overviewData['number_of_staff'] ?? 0,
                    'Loan Approval Rate (%)' => $overviewData['loan_approval_rate'] ?? 0,
                ];
                
                foreach ($metrics as $metric => $value) {
                    $sheet->setCellValue('A' . $row, $metric);
                    $sheet->setCellValue('B' . $row, $value);
                    $row++;
                }
                
                // Add borders
                $sheet->getStyle('A5:C' . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}

class ClientSummarySheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Client Summary';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 15,
            'D' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'CLIENT SUMMARY');
                $sheet->mergeCells('A1:D1');
                
                $clientSummary = $this->reportData['client_summary'] ?? [];
                $row = 3;
                
                // By Type
                $sheet->setCellValue('A' . $row, 'CLIENTS BY TYPE');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Type');
                $sheet->setCellValue('B' . $row, 'Count');
                $sheet->setCellValue('C' . $row, 'Percentage');
                $sheet->setCellValue('D' . $row, 'Notes');
                $row++;
                
                $byType = $clientSummary['by_type'] ?? [];
                foreach ($byType as $type => $data) {
                    $sheet->setCellValue('A' . $row, ucfirst($type));
                    $sheet->setCellValue('B' . $row, $data['count'] ?? 0);
                    $sheet->setCellValue('C' . $row, ($data['percentage'] ?? 0) . '%');
                    $row++;
                }
                
                $row++; // Empty row
                
                // By Age Group
                $sheet->setCellValue('A' . $row, 'CLIENTS BY AGE GROUP');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Age Group');
                $sheet->setCellValue('B' . $row, 'Count');
                $sheet->setCellValue('C' . $row, 'Percentage');
                $sheet->setCellValue('D' . $row, 'Notes');
                $row++;
                
                $byAgeGroup = $clientSummary['by_age_group'] ?? [];
                foreach ($byAgeGroup as $ageGroup => $data) {
                    $sheet->setCellValue('A' . $row, $ageGroup);
                    $sheet->setCellValue('B' . $row, $data['count'] ?? 0);
                    $sheet->setCellValue('C' . $row, ($data['percentage'] ?? 0) . '%');
                    $row++;
                }
                
                $row++; // Empty row
                
                // By Gender
                $sheet->setCellValue('A' . $row, 'CLIENTS BY GENDER');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Gender');
                $sheet->setCellValue('B' . $row, 'Count');
                $sheet->setCellValue('C' . $row, 'Percentage');
                $sheet->setCellValue('D' . $row, 'Notes');
                $row++;
                
                $byGender = $clientSummary['by_gender'] ?? [];
                foreach ($byGender as $gender => $data) {
                    $sheet->setCellValue('A' . $row, ucfirst($gender));
                    $sheet->setCellValue('B' . $row, $data['count'] ?? 0);
                    $sheet->setCellValue('C' . $row, ($data['percentage'] ?? 0) . '%');
                    $row++;
                }
            },
        ];
    }
}

class LoanSummarySheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Loan Summary';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 20,
            'D' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'LOAN SUMMARY');
                $sheet->mergeCells('A1:D1');
                
                $loanSummary = $this->reportData['loan_summary'] ?? [];
                $row = 3;
                
                // By Status
                $sheet->setCellValue('A' . $row, 'LOANS BY STATUS');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Status');
                $sheet->setCellValue('B' . $row, 'Count');
                $sheet->setCellValue('C' . $row, 'Amount (TZS)');
                $sheet->setCellValue('D' . $row, 'Percentage');
                $row++;
                
                $byStatus = $loanSummary['by_status'] ?? [];
                foreach ($byStatus as $status => $data) {
                    $sheet->setCellValue('A' . $row, ucfirst($status));
                    $sheet->setCellValue('B' . $row, $data['count'] ?? 0);
                    $sheet->setCellValue('C' . $row, number_format($data['amount'] ?? 0, 2));
                    $sheet->setCellValue('D' . $row, ($data['percentage'] ?? 0) . '%');
                    $row++;
                }
                
                $row++; // Empty row
                
                // By Product
                $sheet->setCellValue('A' . $row, 'LOANS BY PRODUCT');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Product');
                $sheet->setCellValue('B' . $row, 'Count');
                $sheet->setCellValue('C' . $row, 'Amount (TZS)');
                $sheet->setCellValue('D' . $row, 'Percentage');
                $row++;
                
                $byProduct = $loanSummary['by_product'] ?? [];
                foreach ($byProduct as $product => $data) {
                    $sheet->setCellValue('A' . $row, ucfirst(str_replace('_', ' ', $product)));
                    $sheet->setCellValue('B' . $row, $data['count'] ?? 0);
                    $sheet->setCellValue('C' . $row, number_format($data['amount'] ?? 0, 2));
                    $sheet->setCellValue('D' . $row, ($data['percentage'] ?? 0) . '%');
                    $row++;
                }
                
                $row++; // Empty row
                
                // By Risk Category
                $sheet->setCellValue('A' . $row, 'LOANS BY RISK CATEGORY');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Risk Category');
                $sheet->setCellValue('B' . $row, 'Count');
                $sheet->setCellValue('C' . $row, 'Amount (TZS)');
                $sheet->setCellValue('D' . $row, 'Percentage');
                $row++;
                
                $byRiskCategory = $loanSummary['by_risk_category'] ?? [];
                foreach ($byRiskCategory as $risk => $data) {
                    $sheet->setCellValue('A' . $row, ucfirst(str_replace('_', ' ', $risk)));
                    $sheet->setCellValue('B' . $row, $data['count'] ?? 0);
                    $sheet->setCellValue('C' . $row, number_format($data['amount'] ?? 0, 2));
                    $sheet->setCellValue('D' . $row, ($data['percentage'] ?? 0) . '%');
                    $row++;
                }
            },
        ];
    }
}

class DepositSummarySheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Deposit Summary';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 20,
            'D' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'DEPOSIT SUMMARY');
                $sheet->mergeCells('A1:D1');
                
                $depositSummary = $this->reportData['deposit_summary'] ?? [];
                $row = 3;
                
                // By Type
                $sheet->setCellValue('A' . $row, 'DEPOSITS BY TYPE');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Type');
                $sheet->setCellValue('B' . $row, 'Count');
                $sheet->setCellValue('C' . $row, 'Amount (TZS)');
                $sheet->setCellValue('D' . $row, 'Percentage');
                $row++;
                
                $byType = $depositSummary['by_type'] ?? [];
                foreach ($byType as $type => $data) {
                    $sheet->setCellValue('A' . $row, ucfirst(str_replace('_', ' ', $type)));
                    $sheet->setCellValue('B' . $row, $data['count'] ?? 0);
                    $sheet->setCellValue('C' . $row, number_format($data['amount'] ?? 0, 2));
                    $sheet->setCellValue('D' . $row, ($data['percentage'] ?? 0) . '%');
                    $row++;
                }
                
                $row++; // Empty row
                
                // By Balance Range
                $sheet->setCellValue('A' . $row, 'DEPOSITS BY BALANCE RANGE');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Balance Range');
                $sheet->setCellValue('B' . $row, 'Count');
                $sheet->setCellValue('C' . $row, 'Percentage');
                $sheet->setCellValue('D' . $row, 'Notes');
                $row++;
                
                $byBalanceRange = $depositSummary['by_balance_range'] ?? [];
                foreach ($byBalanceRange as $range => $data) {
                    $sheet->setCellValue('A' . $row, $range);
                    $sheet->setCellValue('B' . $row, $data['count'] ?? 0);
                    $sheet->setCellValue('C' . $row, ($data['percentage'] ?? 0) . '%');
                    $row++;
                }
                
                $row++; // Empty row
                
                // Summary metrics
                $sheet->setCellValue('A' . $row, 'SUMMARY METRICS');
                $sheet->mergeCells('A' . $row . ':D' . $row);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Average Balance (TZS)');
                $sheet->setCellValue('B' . $row, number_format($depositSummary['average_balance'] ?? 0, 2));
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Total Interest Paid (TZS)');
                $sheet->setCellValue('B' . $row, number_format($depositSummary['total_interest_paid'] ?? 0, 2));
            },
        ];
    }
}

class BranchPerformanceSheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Branch Performance';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 15,
            'G' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'BRANCH PERFORMANCE');
                $sheet->mergeCells('A1:G1');
                
                // Add headers
                $sheet->setCellValue('A2', 'Branch Name');
                $sheet->setCellValue('B2', 'Total Clients');
                $sheet->setCellValue('C2', 'Total Loans');
                $sheet->setCellValue('D2', 'Loan Amount (TZS)');
                $sheet->setCellValue('E2', 'Total Deposits (TZS)');
                $sheet->setCellValue('F2', 'Staff Count');
                $sheet->setCellValue('G2', 'Performance Score');
                
                $branchPerformance = $this->reportData['branch_performance'] ?? [];
                $row = 3;
                
                foreach ($branchPerformance as $branch) {
                    $sheet->setCellValue('A' . $row, $branch['branch_name'] ?? '');
                    $sheet->setCellValue('B' . $row, $branch['total_clients'] ?? 0);
                    $sheet->setCellValue('C' . $row, $branch['total_loans'] ?? 0);
                    $sheet->setCellValue('D' . $row, number_format($branch['loan_amount'] ?? 0, 2));
                    $sheet->setCellValue('E' . $row, number_format($branch['total_deposits'] ?? 0, 2));
                    $sheet->setCellValue('F' . $row, $branch['staff_count'] ?? 0);
                    $sheet->setCellValue('G' . $row, ($branch['performance_score'] ?? 0) . '%');
                    $row++;
                }
                
                // Add borders
                $sheet->getStyle('A2:G' . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}

class ProductPerformanceSheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Product Performance';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 15,
            'C' => 20,
            'D' => 20,
            'E' => 15,
            'F' => 15,
            'G' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'PRODUCT PERFORMANCE');
                $sheet->mergeCells('A1:G1');
                
                // Add headers
                $sheet->setCellValue('A2', 'Product Name');
                $sheet->setCellValue('B2', 'Total Loans');
                $sheet->setCellValue('C2', 'Total Amount (TZS)');
                $sheet->setCellValue('D2', 'Average Amount (TZS)');
                $sheet->setCellValue('E2', 'Interest Rate (%)');
                $sheet->setCellValue('F2', 'Default Rate (%)');
                $sheet->setCellValue('G2', 'Profitability (%)');
                
                $productPerformance = $this->reportData['product_performance'] ?? [];
                $row = 3;
                
                foreach ($productPerformance as $product) {
                    $sheet->setCellValue('A' . $row, $product['product_name'] ?? '');
                    $sheet->setCellValue('B' . $row, $product['total_loans'] ?? 0);
                    $sheet->setCellValue('C' . $row, number_format($product['total_amount'] ?? 0, 2));
                    $sheet->setCellValue('D' . $row, number_format($product['average_amount'] ?? 0, 2));
                    $sheet->setCellValue('E' . $row, $product['interest_rate'] ?? 0);
                    $sheet->setCellValue('F' . $row, $product['default_rate'] ?? 0);
                    $sheet->setCellValue('G' . $row, $product['profitability'] ?? 0);
                    $row++;
                }
                
                // Add borders
                $sheet->getStyle('A2:G' . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}

class StaffPerformanceSheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Staff Performance';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 20,
            'C' => 20,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 20,
            'H' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'STAFF PERFORMANCE');
                $sheet->mergeCells('A1:H1');
                
                // Add headers
                $sheet->setCellValue('A2', 'Staff Name');
                $sheet->setCellValue('B2', 'Position');
                $sheet->setCellValue('C2', 'Department');
                $sheet->setCellValue('D2', 'Clients Served');
                $sheet->setCellValue('E2', 'Loans Processed');
                $sheet->setCellValue('F2', 'Deposits Handled');
                $sheet->setCellValue('G2', 'Performance Rating (%)');
                $sheet->setCellValue('H2', 'Customer Satisfaction');
                
                $staffPerformance = $this->reportData['staff_performance'] ?? [];
                $row = 3;
                
                foreach ($staffPerformance as $staff) {
                    $sheet->setCellValue('A' . $row, $staff['staff_name'] ?? '');
                    $sheet->setCellValue('B' . $row, $staff['position'] ?? '');
                    $sheet->setCellValue('C' . $row, $staff['department'] ?? '');
                    $sheet->setCellValue('D' . $row, $staff['clients_served'] ?? 0);
                    $sheet->setCellValue('E' . $row, $staff['loans_processed'] ?? 0);
                    $sheet->setCellValue('F' . $row, $staff['deposits_handled'] ?? 0);
                    $sheet->setCellValue('G' . $row, $staff['performance_rating'] ?? 0);
                    $sheet->setCellValue('H' . $row, $staff['customer_satisfaction'] ?? 0);
                    $row++;
                }
                
                // Add borders
                $sheet->getStyle('A2:H' . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}

class FinancialSummarySheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Financial Summary';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'FINANCIAL SUMMARY');
                $sheet->mergeCells('A1:C1');
                
                $financialSummary = $this->reportData['financial_summary'] ?? [];
                $row = 3;
                
                // Income
                $sheet->setCellValue('A' . $row, 'INCOME');
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $row++;
                
                $income = $financialSummary['income'] ?? [];
                $sheet->setCellValue('A' . $row, 'Interest Income (TZS)');
                $sheet->setCellValue('B' . $row, number_format($income['interest_income'] ?? 0, 2));
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Fee Income (TZS)');
                $sheet->setCellValue('B' . $row, number_format($income['fee_income'] ?? 0, 2));
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Other Income (TZS)');
                $sheet->setCellValue('B' . $row, number_format($income['other_income'] ?? 0, 2));
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Total Income (TZS)');
                $sheet->setCellValue('B' . $row, number_format($income['total_income'] ?? 0, 2));
                $row++;
                
                $row++; // Empty row
                
                // Expenses
                $sheet->setCellValue('A' . $row, 'EXPENSES');
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $row++;
                
                $expenses = $financialSummary['expenses'] ?? [];
                $sheet->setCellValue('A' . $row, 'Operating Expenses (TZS)');
                $sheet->setCellValue('B' . $row, number_format($expenses['operating_expenses'] ?? 0, 2));
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Staff Costs (TZS)');
                $sheet->setCellValue('B' . $row, number_format($expenses['staff_costs'] ?? 0, 2));
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Administrative Costs (TZS)');
                $sheet->setCellValue('B' . $row, number_format($expenses['administrative_costs'] ?? 0, 2));
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Total Expenses (TZS)');
                $sheet->setCellValue('B' . $row, number_format($expenses['total_expenses'] ?? 0, 2));
                $row++;
                
                $row++; // Empty row
                
                // Profitability
                $sheet->setCellValue('A' . $row, 'PROFITABILITY');
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $row++;
                
                $profitability = $financialSummary['profitability'] ?? [];
                $sheet->setCellValue('A' . $row, 'Net Profit (TZS)');
                $sheet->setCellValue('B' . $row, number_format($profitability['net_profit'] ?? 0, 2));
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Profit Margin (%)');
                $sheet->setCellValue('B' . $row, $profitability['profit_margin'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Return on Assets (%)');
                $sheet->setCellValue('B' . $row, $profitability['roa'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Return on Equity (%)');
                $sheet->setCellValue('B' . $row, $profitability['roe'] ?? 0);
            },
        ];
    }
}

class OperationalMetricsSheet implements WithTitle, WithStyles, WithColumnWidths, WithEvents
{
    protected $reportData;
    protected $filters;

    public function __construct($reportData, $filters = [])
    {
        $this->reportData = $reportData;
        $this->filters = $filters;
    }

    public function title(): string
    {
        return 'Operational Metrics';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            2 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9E2F3']
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add title
                $sheet->setCellValue('A1', 'OPERATIONAL METRICS');
                $sheet->mergeCells('A1:C1');
                
                $operationalMetrics = $this->reportData['operational_metrics'] ?? [];
                $row = 3;
                
                // Efficiency Ratios
                $sheet->setCellValue('A' . $row, 'EFFICIENCY RATIOS');
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $row++;
                
                $efficiencyRatios = $operationalMetrics['efficiency_ratios'] ?? [];
                $sheet->setCellValue('A' . $row, 'Cost to Income Ratio (%)');
                $sheet->setCellValue('B' . $row, $efficiencyRatios['cost_to_income_ratio'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Operating Efficiency (%)');
                $sheet->setCellValue('B' . $row, $efficiencyRatios['operating_efficiency'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Staff Productivity');
                $sheet->setCellValue('B' . $row, $efficiencyRatios['staff_productivity'] ?? 0);
                $row++;
                
                $row++; // Empty row
                
                // Service Metrics
                $sheet->setCellValue('A' . $row, 'SERVICE METRICS');
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $row++;
                
                $serviceMetrics = $operationalMetrics['service_metrics'] ?? [];
                $sheet->setCellValue('A' . $row, 'Average Processing Time');
                $sheet->setCellValue('B' . $row, $serviceMetrics['average_processing_time'] ?? '');
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Customer Satisfaction');
                $sheet->setCellValue('B' . $row, $serviceMetrics['customer_satisfaction'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Complaint Resolution Time');
                $sheet->setCellValue('B' . $row, $serviceMetrics['complaint_resolution_time'] ?? '');
                $row++;
                
                $row++; // Empty row
                
                // Risk Metrics
                $sheet->setCellValue('A' . $row, 'RISK METRICS');
                $sheet->mergeCells('A' . $row . ':C' . $row);
                $row++;
                
                $riskMetrics = $operationalMetrics['risk_metrics'] ?? [];
                $sheet->setCellValue('A' . $row, 'Portfolio at Risk (%)');
                $sheet->setCellValue('B' . $row, $riskMetrics['portfolio_at_risk'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Provision Coverage (%)');
                $sheet->setCellValue('B' . $row, $riskMetrics['provision_coverage'] ?? 0);
                $row++;
                
                $sheet->setCellValue('A' . $row, 'Capital Adequacy (%)');
                $sheet->setCellValue('B' . $row, $riskMetrics['capital_adequacy'] ?? 0);
            },
        ];
    }
}
